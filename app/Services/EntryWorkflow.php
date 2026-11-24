<?php

namespace App\Services;

use App\Core\Auth;
use App\Core\Database;
use App\Models\Entry;
use App\Models\Notification;
use App\Models\Setting;
use PDO;

/**
 * Circuit de validation des écritures :
 * brouillon → (soumise) → validée, ou (rejetée) → corrigée puis resoumise.
 * L'état « soumise/rejetée » est stocké à part (approval_state) : le statut de l'écriture ne change pas.
 */
class EntryWorkflow
{
    public const SUBMITTED = 'submitted';
    public const REJECTED = 'rejected';

    private static bool $ready = false;

    public static function ensureSchema(): void
    {
        if (self::$ready || !empty($_SESSION['_wf_schema_ok'])) {
            self::$ready = true;
            return;
        }

        $pdo = Database::connect();
        foreach ([
            'ALTER TABLE entries ADD COLUMN approval_state VARCHAR(20) NULL',
            'ALTER TABLE entries ADD COLUMN submitted_by INT NULL',
            'ALTER TABLE entries ADD COLUMN submitted_at TIMESTAMP NULL',
            'ALTER TABLE entries ADD COLUMN review_comment TEXT NULL',
            'ALTER TABLE users ADD COLUMN can_approve SMALLINT NOT NULL DEFAULT 0',
        ] as $sql) {
            try {
                $pdo->exec($sql);
            } catch (\Throwable $e) {
                // colonne déjà présente
            }
        }

        $_SESSION['_wf_schema_ok'] = true;
        self::$ready = true;
    }

    /** Le cabinet impose-t-il l'approbation avant validation ? */
    public static function required(): bool
    {
        return Setting::get('entries.require_approval') === '1';
    }

    public static function setRequired(bool $on): void
    {
        Setting::set('entries.require_approval', $on ? '1' : '0');
    }

    public static function canApprove(): bool
    {
        $role = $_SESSION['user']['role'] ?? '';
        if (in_array($role, [ROLE_ADMIN, ROLE_EXPERT], true)) {
            return true;
        }

        self::ensureSchema();
        $st = Database::connect()->prepare('SELECT can_approve FROM users WHERE id = ?');
        $st->execute([Auth::id()]);

        return (int) $st->fetchColumn() === 1;
    }

    public static function setUserCanApprove(int $userId, bool $on): void
    {
        self::ensureSchema();
        Database::connect()->prepare('UPDATE users SET can_approve = ? WHERE id = ?')->execute([$on ? 1 : 0, $userId]);
    }

    public static function companyAllowed(int $companyId): bool
    {
        $ids = Auth::companyIds();

        return $ids === null || in_array($companyId, array_map('intval', $ids), true);
    }

    /** Écriture équilibrée ? */
    public static function balanced(int $entryId): bool
    {
        $d = Entry::getTotalDebit($entryId);

        return $d > 0 && abs($d - Entry::getTotalCredit($entryId)) < 0.01;
    }

    /** @return array{ok:bool,message:string} */
    public static function submit(array $entry, string $comment = ''): array
    {
        self::ensureSchema();
        if ($entry['status'] !== ENTRY_DRAFT) {
            return self::r(false, 'Seule une écriture en brouillon peut être soumise.');
        }
        if (($entry['approval_state'] ?? null) === self::SUBMITTED) {
            return self::r(false, 'Cette écriture est déjà en attente d’approbation.');
        }
        if (!self::balanced((int) $entry['id'])) {
            return self::r(false, 'L’écriture n’est pas équilibrée (débit ≠ crédit) : impossible de la soumettre.');
        }

        Database::connect()->prepare(
            'UPDATE entries SET approval_state = ?, submitted_by = ?, submitted_at = NOW(), review_comment = NULL, updated_at = NOW() WHERE id = ?'
        )->execute([self::SUBMITTED, Auth::id(), $entry['id']]);

        AuditService::log('submit', 'entry', (int) $entry['id'], 'Écriture ' . $entry['reference'] . ' soumise pour approbation');
        self::notify(self::approvers((int) $entry['company_id'], (int) Auth::id()), 'Écriture à approuver',
            'L’écriture ' . $entry['reference'] . ' attend votre approbation.');

        return self::r(true, 'Écriture soumise : elle attend l’approbation d’un responsable.');
    }

    /** @return array{ok:bool,message:string} */
    public static function approve(array $entry): array
    {
        self::ensureSchema();
        $isAdmin = ($_SESSION['user']['role'] ?? '') === ROLE_ADMIN;

        if ($entry['status'] !== ENTRY_DRAFT) {
            return self::r(false, 'Seules les écritures en brouillon peuvent être validées.');
        }
        if (self::required() && !self::canApprove()) {
            return self::r(false, 'Vous n’avez pas le droit d’approuver : soumettez l’écriture à un responsable.');
        }
        if (self::required() && !$isAdmin && (int) ($entry['submitted_by'] ?? 0) === (int) Auth::id()) {
            return self::r(false, 'Vous ne pouvez pas approuver une écriture que vous avez soumise vous-même.');
        }
        if (!self::balanced((int) $entry['id'])) {
            return self::r(false, 'L’écriture n’est pas équilibrée (débit ≠ crédit) : validation impossible.');
        }

        Database::connect()->prepare(
            'UPDATE entries SET status = ?, approval_state = NULL, validated_by = ?, validated_at = NOW(), updated_at = NOW() WHERE id = ?'
        )->execute([ENTRY_VALIDATED, Auth::id(), $entry['id']]);

        AuditService::log('validate', 'entry', (int) $entry['id'], 'Écriture ' . $entry['reference'] . ' validée');
        if (!empty($entry['submitted_by']) && (int) $entry['submitted_by'] !== (int) Auth::id()) {
            self::notify([(int) $entry['submitted_by']], 'Écriture validée', 'Votre écriture ' . $entry['reference'] . ' a été validée.');
        }

        return self::r(true, 'Écriture comptable validée avec succès.');
    }

    /** @return array{ok:bool,message:string} */
    public static function reject(array $entry, string $comment): array
    {
        self::ensureSchema();
        if (!self::canApprove()) {
            return self::r(false, 'Vous n’avez pas le droit de rejeter une écriture.');
        }
        if ($entry['status'] !== ENTRY_DRAFT || ($entry['approval_state'] ?? null) !== self::SUBMITTED) {
            return self::r(false, 'Seule une écriture en attente d’approbation peut être rejetée.');
        }
        $comment = trim($comment);
        if ($comment === '') {
            return self::r(false, 'Indiquez le motif du rejet.');
        }

        Database::connect()->prepare(
            'UPDATE entries SET approval_state = ?, review_comment = ?, updated_at = NOW() WHERE id = ?'
        )->execute([self::REJECTED, mb_substr($comment, 0, 1000), $entry['id']]);

        AuditService::log('reject', 'entry', (int) $entry['id'], 'Écriture ' . $entry['reference'] . ' rejetée : ' . mb_substr($comment, 0, 200));
        if (!empty($entry['submitted_by'])) {
            self::notify([(int) $entry['submitted_by']], 'Écriture rejetée', 'L’écriture ' . $entry['reference'] . ' a été rejetée : ' . mb_substr($comment, 0, 200), 'warning');
        }

        return self::r(true, 'Écriture rejetée : le motif a été transmis à son auteur.');
    }

    /** Utilisateurs actifs du même cabinet pouvant approuver. @return int[] */
    private static function approvers(int $companyId, int $exceptUserId): array
    {
        $pdo = Database::connect();
        $st = $pdo->prepare('SELECT tenant_id FROM companies WHERE id = ?');
        $st->execute([$companyId]);
        $tenant = $st->fetchColumn();
        if ($tenant === false || $tenant === null) {
            return [];
        }

        $st = $pdo->prepare(
            "SELECT u.id FROM users u INNER JOIN roles r ON r.id = u.role_id
             WHERE u.tenant_id = ? AND u.status = 'active' AND u.id <> ?
               AND (r.name IN ('admin','expert') OR u.can_approve = 1)"
        );
        $st->execute([(int) $tenant, $exceptUserId]);

        return array_map('intval', $st->fetchAll(PDO::FETCH_COLUMN));
    }

    private static function notify(array $userIds, string $title, string $message, string $type = 'info'): void
    {
        foreach ($userIds as $uid) {
            try {
                Notification::create(['user_id' => $uid, 'title' => $title, 'message' => $message, 'type' => $type]);
            } catch (\Throwable $e) {
                // une notification manquée ne doit pas bloquer le circuit
            }
        }
    }

    private static function r(bool $ok, string $message): array
    {
        return ['ok' => $ok, 'message' => $message];
    }
}
