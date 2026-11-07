<?php
namespace App\Validators;
use App\Core\Validator;class AuthValidator
{
    /**
     * Valider les donnÃƒÂ©es de connexion.
     */
    public static function validateLogin(array $data): array
    {
        $validator = new Validator();

        $validator->required('email', $data['email'] ?? '');
        $validator->email('email', $data['email'] ?? '');

        $validator->required('password', $data['password'] ?? '');
        $validator->min('password', $data['password'] ?? '', 8);

        return [
            'valid' => $validator->passes(),
            'errors' => $validator->errors()
        ];
    }

    /**
     * Valider les donnÃƒÂ©es d'inscription.
     */
    public static function validateRegistration(array $data): array
    {
        $validator = new Validator();

        $validator->required('first_name', $data['first_name'] ?? '');
        $validator->min('first_name', $data['first_name'] ?? '', 2);
        $validator->max('first_name', $data['first_name'] ?? '', 100);

        $validator->required('last_name', $data['last_name'] ?? '');
        $validator->min('last_name', $data['last_name'] ?? '', 2);
        $validator->max('last_name', $data['last_name'] ?? '', 100);

        $validator->required('email', $data['email'] ?? '');
        $validator->email('email', $data['email'] ?? '');

        $validator->required('password', $data['password'] ?? '');
        $validator->min('password', $data['password'] ?? '', 8);

        $validator->required(
            'password_confirmation',
            $data['password_confirmation'] ?? ''
        );

        if (
            isset($data['password'], $data['password_confirmation']) &&
            $data['password'] !== $data['password_confirmation']
        ) {
            $validator->addError(
                'password_confirmation',
                'Les mots de passe ne correspondent pas.'
            );
        }

        return [
            'valid' => $validator->passes(),
            'errors' => $validator->errors()
        ];
    }

    /**
     * Valider une demande de rÃƒÂ©initialisation du mot de passe.
     */
    public static function validateForgotPassword(array $data): array
    {
        $validator = new Validator();

        $validator->required('email', $data['email'] ?? '');
        $validator->email('email', $data['email'] ?? '');

        return [
            'valid' => $validator->passes(),
            'errors' => $validator->errors()
        ];
    }

    /**
     * Valider un nouveau mot de passe.
     */
    public static function validateResetPassword(array $data): array
    {
        $validator = new Validator();

        $validator->required('password', $data['password'] ?? '');
        $validator->min('password', $data['password'] ?? '', 8);

        $validator->required(
            'password_confirmation',
            $data['password_confirmation'] ?? ''
        );

        if (
            isset($data['password'], $data['password_confirmation']) &&
            $data['password'] !== $data['password_confirmation']
        ) {
            $validator->addError(
                'password_confirmation',
                'Les mots de passe ne correspondent pas.'
            );
        }

        return [
            'valid' => $validator->passes(),
            'errors' => $validator->errors()
        ];
    }
}
