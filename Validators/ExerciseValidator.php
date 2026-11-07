
<?php

class ExerciseValidator
{
    /**
     * Valider la création d'un exercice comptable.
     */
    public static function validateCreate(array $data): array
    {
        $validator = new Validator();

        $validator->required('company_id', $data['company_id'] ?? '');
        $validator->numeric('company_id', $data['company_id'] ?? '');

        $validator->required('name', $data['name'] ?? '');
        $validator->min('name', $data['name'] ?? '', 3);
        $validator->max('name', $data['name'] ?? '', 100);

        $validator->required('start_date', $data['start_date'] ?? '');
        $validator->date('start_date', $data['start_date'] ?? '');

        $validator->required('end_date', $data['end_date'] ?? '');
        $validator->date('end_date', $data['end_date'] ?? '');

        if (
            !empty($data['start_date']) &&
            !empty($data['end_date']) &&
            strtotime($data['start_date']) !== false &&
            strtotime($data['end_date']) !== false &&
            strtotime($data['end_date']) <= strtotime($data['start_date'])
        ) {
            $validator->addError(
                'end_date',
                'La date de fin doit être postérieure à la date de début.'
            );
        }

        return [
            'valid' => !$validator->hasErrors(),
            'errors' => $validator->getErrors()
        ];
    }

    /**
     * Valider la modification d'un exercice comptable.
     */
    public static function validateUpdate(array $data): array
    {
        return self::validateCreate($data);
    }

    /**
     * Valider le changement de statut d'un exercice.
     */
    public static function validateStatus(array $data): array
    {
        $validator = new Validator();

        $validator->required('status', $data['status'] ?? '');

        $allowedStatuses = [
            EXERCISE_OPEN,
            EXERCISE_CLOSED,
            EXERCISE_LOCKED
        ];

        if (!in_array($data['status'] ?? '', $allowedStatuses, true)) {
            $validator->addError(
                'status',
                'Le statut de l’exercice comptable est invalide.'
            );
        }

        return [
            'valid' => !$validator->hasErrors(),
            'errors' => $validator->getErrors()
        ];
    }
}
