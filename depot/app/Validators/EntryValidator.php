<?php
namespace App\Validators;

use App\Core\Validator;

class EntryValidator
{
    /**
     * Valider les informations générales d'une écriture comptable.
     */
    public static function validateCreate(array $data): array
    {
        $validator = new Validator();

        $validator->required('journal_id', $data['journal_id'] ?? '');
        $validator->numeric('journal_id', $data['journal_id'] ?? '');
        if ((int) ($data['journal_id'] ?? 0) <= 0) {
            $validator->addError('journal_id', 'Veuillez sélectionner un journal valide.');
        }

        $validator->required('exercise_id', $data['exercise_id'] ?? '');
        $validator->numeric('exercise_id', $data['exercise_id'] ?? '');
        if ((int) ($data['exercise_id'] ?? 0) <= 0) {
            $validator->addError('exercise_id', 'Veuillez sélectionner un exercice valide.');
        }

        $validator->required('entry_date', $data['entry_date'] ?? '');
        $validator->date('entry_date', $data['entry_date'] ?? '');

        $validator->required('description', $data['description'] ?? '');
        $validator->min('description', $data['description'] ?? '', 3);
        $validator->max('description', $data['description'] ?? '', 255);

        $lines = $data['lines'] ?? [];

        if (!is_array($lines) || count($lines) < 2) {
            $validator->addError(
                'lines',
                'Une écriture doit contenir au moins deux lignes comptables.'
            );
        } else {
            $totalDebit = 0;
            $totalCredit = 0;

            foreach ($lines as $index => $line) {
                if (!is_array($line)) {
                    $validator->addError(
                        'lines',
                        'Une ligne comptable est invalide.'
                    );
                    continue;
                }

                if (
                    empty($line['account_id']) ||
                    !is_numeric($line['account_id'])
                ) {
                    $validator->addError(
                        'lines',
                        'Le compte comptable est obligatoire pour chaque ligne.'
                    );
                }

                $debit = $line['debit'] ?? 0;
                $credit = $line['credit'] ?? 0;

                if (
                    !is_numeric($debit) ||
                    !is_numeric($credit) ||
                    $debit < 0 ||
                    $credit < 0
                ) {
                    $validator->addError(
                        'lines',
                        'Les montants doivent être numériques et positifs.'
                    );
                    continue;
                }

                if ((float) $debit > 0 && (float) $credit > 0) {
                    $validator->addError(
                        'lines',
                        'Une ligne ne peut pas comporter simultanément un débit et un crédit.'
                    );
                }

                if ((float) $debit === 0.0 && (float) $credit === 0.0) {
                    $validator->addError(
                        'lines',
                        'Chaque ligne doit comporter un montant au débit ou au crédit.'
                    );
                }

                $totalDebit += (float) $debit;
                $totalCredit += (float) $credit;
            }

            if (abs($totalDebit - $totalCredit) > 0.001) {
                $validator->addError(
                    'lines',
                    'L’écriture comptable doit être équilibrée : le total débit doit être égal au total crédit.'
                );
            }

            if ($totalDebit <= 0 || $totalCredit <= 0) {
                $validator->addError(
                    'lines',
                    'Les totaux débit et crédit doivent être supérieurs à zéro.'
                );
            }
        }

        return [
            'valid' => $validator->passes(),
            'errors' => $validator->errors()
        ];
    }

    /**
     * Valider la modification d'une écriture comptable.
     */
    public static function validateUpdate(array $data): array
    {
        return self::validateCreate($data);
    }
}
