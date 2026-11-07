<?php

namespace App\Validators;

use App\Core\Validator;
use App\Models\Role;
use App\Models\User;

class UserValidator
{
    /**
     * Valider la création d'un utilisateur.
     */
    public static function validateCreate(array $data): array
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
        if (
            filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL) &&
            User::emailExists(trim($data['email']))
        ) {
            $validator->addError('email', 'Cette adresse e-mail est déjà utilisée.');
        }

        $validator->required('password', $data['password'] ?? '');
        $validator->min('password', $data['password'] ?? '', 8);

        $validator->required('password_confirmation', $data['password_confirmation'] ?? '');
        if (($data['password'] ?? '') !== ($data['password_confirmation'] ?? '')) {
            $validator->addError('password_confirmation', 'La confirmation du mot de passe ne correspond pas.');
        }

        $validator->required('role_id', $data['role_id'] ?? '');
        $validator->numeric('role_id', $data['role_id'] ?? '');
        self::validateRole($validator, $data['role_id'] ?? '');
        $validator->required('status', $data['status'] ?? '');

        if (!in_array($data['status'] ?? '', [USER_ACTIVE, USER_INACTIVE, USER_SUSPENDED], true)) {
            $validator->addError('status', 'Le statut sélectionné est invalide.');
        }

        return [
            'valid' => $validator->passes(),
            'errors' => $validator->errors()
        ];
    }

    /**
     * Valider la modification d'un utilisateur.
     */
    public static function validateUpdate(array $data, ?int $userId = null): array
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
        if (
            filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL) &&
            User::emailExists(trim($data['email']), $userId)
        ) {
            $validator->addError('email', 'Cette adresse e-mail est déjà utilisée.');
        }

        $validator->required('role_id', $data['role_id'] ?? '');
        $validator->numeric('role_id', $data['role_id'] ?? '');
        self::validateRole($validator, $data['role_id'] ?? '');
        $validator->required('status', $data['status'] ?? '');

        if (!in_array($data['status'] ?? '', [USER_ACTIVE, USER_INACTIVE, USER_SUSPENDED], true)) {
            $validator->addError('status', 'Le statut sélectionné est invalide.');
        }

        if (
            isset($data['password']) &&
            $data['password'] !== ''
        ) {
            $validator->min('password', $data['password'], 8);
        }

        return [
            'valid' => $validator->passes(),
            'errors' => $validator->errors()
        ];
    }

    /**
     * Valider le changement de statut d'un utilisateur.
     */
    public static function validateStatus(string $status): array
    {
        $validator = new Validator();

        $validator->required('status', $status);

        $allowedStatuses = [
            USER_ACTIVE,
            USER_INACTIVE,
            USER_SUSPENDED
        ];

        if (
            !in_array($status, $allowedStatuses, true)
        ) {
            $validator->addError(
                'status',
                'Le statut sélectionné est invalide.'
            );
        }

        return [
            'valid' => $validator->passes(),
            'errors' => $validator->errors()
        ];
    }

    private static function validateRole(Validator $validator, $roleId): void
    {
        if (
            is_numeric($roleId)
            && (int) $roleId > 0
            && (string) (int) $roleId === (string) $roleId
            && Role::findById((int) $roleId) !== null
        ) {
            return;
        }

        $validator->addError('role_id', 'Le rôle sélectionné est invalide.');
    }
}
