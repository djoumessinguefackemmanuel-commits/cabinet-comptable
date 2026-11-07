<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\Notification;

class NotificationController extends Controller
{
    /**
     * Afficher les notifications de l'utilisateur connecté.
     */
    public function index(): void
    {
        $userId = Auth::id();

        if ($userId === null) {
            $this->redirect(url('login'));
            return;
        }

        $notifications = Notification::getByUser($userId);
        $unreadCount = Notification::getUnreadCount($userId);

        $this->view('notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ]);
    }

    /**
     * Marquer une notification comme lue.
     */
    public function markAsRead(string $id): void
    {
        $userId = Auth::id();
        if ($userId === null) {
            $this->redirect(url('login'));
            return;
        }

        $notification = Notification::findById((int) $id);

        if (!$notification || (int) $notification['user_id'] !== $userId) {
            $this->renderError(404);
            return;
        }

        if (!Notification::markAsRead((int) $id, $userId)) {
            Session::set('error', 'Impossible de mettre à jour cette notification.');
            $this->redirect(url('notifications'));
            return;
        }

        Session::set('success', 'Notification marquée comme lue.');

        $this->redirect(url('notifications'));
    }

    /**
     * Marquer toutes les notifications comme lues.
     */
    public function markAllAsRead(): void
    {
        $userId = Auth::id();
        if ($userId === null) {
            $this->redirect(url('login'));
            return;
        }

        if (!Notification::markAllAsRead($userId)) {
            Session::set('error', 'Impossible de mettre à jour les notifications.');
            $this->redirect(url('notifications'));
            return;
        }

        Session::set('success', 'Toutes les notifications ont été marquées comme lues.');

        $this->redirect(url('notifications'));
    }

    public function markAllAsReadApi(): void
    {
        $userId = Auth::id();
        if ($userId === null) {
            $this->json(['error' => 'Authentification requise.'], 401);
            return;
        }

        if (!Notification::markAllAsRead($userId)) {
            $this->json(['error' => 'Impossible de mettre à jour les notifications.'], 500);
            return;
        }

        $this->json(['success' => true]);
    }
}