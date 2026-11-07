<?php

namespace App\Services;

use App\Models\PasswordResetToken;
use App\Models\User;
use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;

class PasswordResetService
{
    public function request(string $email): bool
    {
        $fromAddress = app_env('MAIL_FROM_ADDRESS', '');
        $fromName = str_replace(["\r", "\n"], '', app_env('MAIL_FROM_NAME', APP_NAME));
        if (!filter_var($fromAddress, FILTER_VALIDATE_EMAIL)) {
            error_log('Réinitialisation du mot de passe impossible : MAIL_FROM_ADDRESS est absent ou invalide.');
            return false;
        }

        $user = User::findByEmail($email);
        if (!$user) {
            return true;
        }

        $token = PasswordResetToken::createForUser((int) $user['id']);
        $resetUrl = BASE_URL . '/reset-password?token=' . rawurlencode($token);
        $subject = 'Réinitialisation de votre mot de passe';
        $body = "Bonjour,\n\n"
            . "Une demande de réinitialisation a été faite pour votre compte. "
            . "Utilisez ce lien dans l'heure :\n"
            . $resetUrl
            . "\n\nSi vous n'êtes pas à l'origine de cette demande, ignorez ce message.";

        if (!$this->sendResetEmail($email, $fromAddress, $fromName, $subject, $body)) {
            PasswordResetToken::deleteForUser((int) $user['id']);
            return false;
        }

        return true;
    }

    private function sendResetEmail(
        string $email,
        string $fromAddress,
        string $fromName,
        string $subject,
        string $body
    ): bool {
        if (app_env('MAIL_MAILER', 'php') !== 'smtp') {
            $headers = [
                'From: ' . mb_encode_mimeheader($fromName, 'UTF-8') . ' <' . $fromAddress . '>',
                'Content-Type: text/plain; charset=UTF-8'
            ];

            if (!mail($email, mb_encode_mimeheader($subject, 'UTF-8'), $body, implode("\r\n", $headers))) {
                error_log('La fonction mail() a échoué lors d’une demande de réinitialisation.');
                return false;
            }

            return true;
        }

        if (!class_exists(PHPMailer::class)) {
            error_log('PHPMailer est absent. Exécutez composer install pour activer le transport SMTP.');
            return false;
        }

        $encryption = strtolower(app_env('MAIL_ENCRYPTION', 'ssl'));
        if (!in_array($encryption, ['ssl', 'tls'], true)) {
            error_log('MAIL_ENCRYPTION doit être défini à ssl ou tls pour le transport SMTP.');
            return false;
        }

        $mailer = new PHPMailer(true);
        try {
            $mailer->isSMTP();
            $mailer->Host = app_env('MAIL_HOST', '');
            $mailer->SMTPAuth = true;
            $mailer->Username = app_env('MAIL_USERNAME', '');
            $mailer->Password = app_env('MAIL_PASSWORD', '');
            $mailer->SMTPSecure = $encryption === 'ssl'
                ? PHPMailer::ENCRYPTION_SMTPS
                : PHPMailer::ENCRYPTION_STARTTLS;
            $mailer->Port = (int) app_env('MAIL_PORT', $encryption === 'ssl' ? '465' : '587');
            $mailer->Timeout = 15;
            $mailer->CharSet = 'UTF-8';
            $mailer->setFrom($fromAddress, $fromName);
            $mailer->addAddress($email);
            $mailer->Subject = $subject;
            $mailer->Body = $body;
            $mailer->send();
            return true;
        } catch (MailException $exception) {
            error_log('Échec de l’envoi SMTP du lien de réinitialisation : ' . $mailer->ErrorInfo);
            return false;
        }
    }

    public function reset(string $token, string $password): bool
    {
        return $token !== ''
            && PasswordResetToken::resetPassword(hash('sha256', $token), $password);
    }
}
