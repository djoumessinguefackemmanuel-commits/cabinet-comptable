<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class SupportRequest extends Model
{
    public static function create(array $data): int|false
    {
        $success = self::execute(
            'INSERT INTO support_requests (user_id, name, email, subject, message, status, created_at, updated_at)
             VALUES (:user_id, :name, :email, :subject, :message, :status, NOW(), NOW())',
            [
                'user_id' => $data['user_id'] ?? null,
                'name' => $data['name'],
                'email' => $data['email'],
                'subject' => $data['subject'],
                'message' => $data['message'],
                'status' => 'new'
            ]
        );

        return $success ? (int) self::lastInsertId() : false;
    }

    public static function getAll(): array
    {
        return self::query(
            'SELECT * FROM support_requests ORDER BY created_at DESC, id DESC'
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById(int $id): ?array
    {
        $request = self::query(
            'SELECT * FROM support_requests WHERE id = :id LIMIT 1',
            ['id' => $id]
        )->fetch(PDO::FETCH_ASSOC);

        return $request ?: null;
    }

    public static function updateStatus(int $id, string $status): bool
    {
        return self::execute(
            'UPDATE support_requests SET status = :status, updated_at = NOW() WHERE id = :id',
            ['id' => $id, 'status' => $status]
        );
    }
}
