<?php

namespace App\Middleware;

class ExerciseMiddleware
{
    /**
     * Vérifier qu'un exercice comptable est sélectionné et ouvert.
     */
    public function handle(): bool
    {
        $exerciseId = $_SESSION['current_exercise_id'] ?? null;

        if (empty($exerciseId)) {

            $_SESSION['_flash_error'] =
                'Veuillez sélectionner un exercice comptable.';

            header('Location: ' . url('exercises'));

            exit;
        }

        $db = \App\Core\Database::connect();

        $statement = $db->prepare(
            'SELECT id, status
             FROM exercises
             WHERE id = :id
             LIMIT 1'
        );

        $statement->execute([
            'id' => $exerciseId
        ]);

        $exercise = $statement->fetch();

        if (!$exercise) {

            unset($_SESSION['current_exercise_id']);

            $_SESSION['_flash_error'] =
                'L\'exercice comptable sélectionné est introuvable.';

            header('Location: ' . url('exercises'));

            exit;
        }

        if ($exercise['status'] !== EXERCISE_OPEN) {

            $_SESSION['_flash_error'] =
                'Cet exercice comptable est fermé ou verrouillé.';

            header('Location: ' . url('exercises'));

            exit;
        }

        return true;
    }
}
