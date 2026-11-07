<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Notification;
use App\Models\SupportRequest;
use App\Models\User;

class SupportController extends Controller
{
    private const STATUSES = ['new', 'in_progress', 'resolved'];

    public function showContactForm(): void
    {
        $user = Auth::user() ?? [];
        $this->render('support.contact', [
            'title' => 'Contacter l’administrateur',
            'old' => [
                'name' => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
                'email' => $user['email'] ?? ''
            ]
        ], 'layouts.auth');
    }

    public function submitContactRequest(): void
    {
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'subject' => trim($_POST['subject'] ?? ''),
            'message' => trim($_POST['message'] ?? ''),
            'user_id' => Auth::id()
        ];

        $validator = new Validator();
        $validator->required('name', $data['name']);
        $validator->min('name', $data['name'], 2);
        $validator->max('name', $data['name'], 150);
        $validator->required('email', $data['email']);
        $validator->email('email', $data['email']);
        $validator->max('email', $data['email'], 150);
        $validator->required('subject', $data['subject']);
        $validator->max('subject', $data['subject'], 200);
        $validator->required('message', $data['message']);
        $validator->min('message', $data['message'], 10);
        $validator->max('message', $data['message'], 5000);

        if (!$validator->passes()) {
            $this->render('support.contact', [
                'title' => 'Contacter l’administrateur',
                'old' => $data,
                'errors' => $validator->errors()
            ], 'layouts.auth');
            return;
        }

        $requestId = SupportRequest::create($data);

        if (!$requestId) {
            Session::set('error', 'Votre demande n’a pas pu être enregistrée. Veuillez réessayer.');
            $this->redirect(url('contact-admin'));
            return;
        }

        $notificationFailed = false;
        $notifiedAdministrators = 0;
        foreach (User::getAllByRole(ROLE_ADMIN) as $administrator) {
            if (($administrator['status'] ?? '') !== USER_ACTIVE) {
                continue;
            }

            $notificationId = Notification::create([
                'user_id' => (int) $administrator['id'],
                'title' => 'Nouvelle demande de contact',
                'message' => $data['name'] . ' (' . $data['email'] . ') : ' . $data['subject'],
                'type' => 'info'
            ]);

            if (!$notificationId) {
                $notificationFailed = true;
                error_log('Unable to notify administrator about support request #' . $requestId);
                continue;
            }

            $notifiedAdministrators++;
        }

        if ($notificationFailed) {
            Session::set(
                'error',
                'Votre demande a été enregistrée, mais une ou plusieurs notifications n’ont pas pu être envoyées. Elle reste consultable dans la boîte de réception administrateur.'
            );
            $this->redirect(url('contact-admin'));
            return;
        }

        if ($notifiedAdministrators === 0) {
            Session::set(
                'error',
                'Votre demande a été enregistrée dans la boîte de réception, mais aucun administrateur actif n’a pu être notifié.'
            );
            $this->redirect(url('contact-admin'));
            return;
        }

        Session::set('success', 'Votre demande a été transmise à l’administrateur du site.');
        $this->redirect(url('contact-admin'));
    }

    public function index(): void
    {
        if (!$this->requireAdministrator()) {
            return;
        }

        $this->view('admin.support.index', [
            'requests' => SupportRequest::getAll()
        ]);
    }

    public function updateStatus(string $id): void
    {
        if (!$this->requireAdministrator()) {
            return;
        }

        $requestId = filter_var($id, FILTER_VALIDATE_INT);
        $status = $_POST['status'] ?? '';

        if (!$requestId || !in_array($status, self::STATUSES, true)) {
            Session::set('error', 'La demande ou le statut sélectionné est invalide.');
            $this->redirect(url('admin/support'));
            return;
        }

        if (!SupportRequest::findById($requestId)) {
            $this->renderError(404);
            return;
        }

        if (!SupportRequest::updateStatus($requestId, $status)) {
            Session::set('error', 'Impossible de mettre à jour le statut de cette demande.');
            $this->redirect(url('admin/support'));
            return;
        }

        Session::set('success', 'Le statut de la demande a été mis à jour.');
        $this->redirect(url('admin/support'));
    }

    private function requireAdministrator(): bool
    {
        if (!Auth::check()) {
            $this->redirect(url('login'));
            return false;
        }

        if (!Auth::hasRole(ROLE_ADMIN)) {
            $this->renderError(403);
            return false;
        }

        return true;
    }
}
