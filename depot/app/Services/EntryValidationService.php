<?php

namespace App\Services;

use App\Core\Validator;

class EntryValidationService
{
    private AccountingService $accountingService;

    public function __construct()
    {
        $this->accountingService = new AccountingService();
    }

    /**
     * Valider les données générales d'une écriture comptable.
     */
    public function validate(array $entry): array
    {
        $validator = new Validator();

        $validator->required(
            'date',
            $entry['date'] ?? ''
        );

        $validator->date(
            'date',
            $entry['date'] ?? ''
        );

        $validator->required(
            'journal_id',
            $entry['journal_id'] ?? ''
        );

        $validator->required(
            'exercise_id',
            $entry['exercise_id'] ?? ''
        );

        $validator->required(
            'reference',
            $entry['reference'] ?? ''
        );

        $validator->required(
            'description',
            $entry['description'] ?? ''
        );

        $errors = $validator->errors();

        // Vérification des lignes comptables.
        $lines = $entry['lines'] ?? [];

        if (!is_array($lines) || count($lines) < 2) {

            $errors['lines'] =
                'Une écriture doit contenir au moins deux lignes comptables.';

        } else {

            foreach ($lines as $index => $line) {

                if (!is_array($line)) {

                    $errors['lines_' . $index] =
                        'Les données de cette ligne sont invalides.';

                    continue;
                }

                if (!$this->accountingService->isValidLine($line)) {

                    $errors['lines_' . $index] =
                        'Chaque ligne doit avoir un débit ou un crédit valide.';
                }

                if (empty($line['account_id'])) {

                    $errors['account_' . $index] =
                        'Le compte comptable est obligatoire pour chaque ligne.';
                }
            }

            if (
                empty($errors['lines'])
                && $this->accountingService->isBalanced($lines) === false
            ) {

                $errors['balance'] =
                    'L\'écriture comptable doit être équilibrée entre le débit et le crédit.';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
