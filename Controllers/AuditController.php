<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AuditLog;
use App\Services\AuditService;

class AuditController extends Controller
{
    /**
     * Afficher les journaux d'audit.
     */
    public function index(): void
    {
        $filters = [
            'user_id' => (int) ($_GET['user_id'] ?? 0),
            'action' => trim($_GET['action'] ?? ''),
            'start_date' => trim($_GET['start_date'] ?? ''),
            'end_date' => trim($_GET['end_date'] ?? '')
        ];

        $auditService = new AuditService();

        $logs = $auditService->getRecent(200);

        $this->view('admin.audit.index', [
            'logs' => $logs,
            'filters' => $filters
        ]);
    }

    /**
     * Afficher les détails d'une opération enregistrée.
     */
    public function show(string $id): void
    {
        $log = AuditLog::findById((int) $id);

        if (!$log) {
            $this->renderError(404);
            return;
        }

        $this->view('admin.audit.show', [
            'log' => $log
        ]);
    }
}