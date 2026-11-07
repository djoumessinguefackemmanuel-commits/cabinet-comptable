<?php

namespace App\Core;

class Validator
{
    /**
     * Stocker les erreurs de validation.
     */
    private array $errors = [];

    /**
     * Vérifier qu'un champ est obligatoire.
     */
    public function required(string $field, $value): self
    {
        if ($value === null || trim((string) $value) === '') {
            $this->errors[$field] = "Le champ {$field} est obligatoire.";
        }

        return $this;
    }

    /**
     * Vérifier le format d'une adresse email.
     */
    public function email(string $field, string $value): self
    {
        if (
            $value !== ''
            && !filter_var($value, FILTER_VALIDATE_EMAIL)
        ) {
            $this->errors[$field] = "L'adresse email est invalide.";
        }

        return $this;
    }

    /**
     * Vérifier la longueur minimale.
     */
    public function min(string $field, string $value, int $length): self
    {
        if (mb_strlen($value) < $length) {
            $this->errors[$field] =
                "Le champ {$field} doit contenir au moins {$length} caractères.";
        }

        return $this;
    }

    /**
     * Vérifier la longueur maximale.
     */
    public function max(string $field, string $value, int $length): self
    {
        if (mb_strlen($value) > $length) {
            $this->errors[$field] =
                "Le champ {$field} ne doit pas dépasser {$length} caractères.";
        }

        return $this;
    }

    /**
     * Vérifier qu'une valeur est numérique.
     */
    public function numeric(string $field, $value): self
    {
        if ($value !== '' && !is_numeric($value)) {
            $this->errors[$field] =
                "Le champ {$field} doit être numérique.";
        }

        return $this;
    }

    /**
     * Vérifier qu'une valeur est une date valide.
     */
    public function date(string $field, string $value): self
    {
        if ($value !== '') {
            $date = \DateTime::createFromFormat('Y-m-d', $value);

            if (!$date || $date->format('Y-m-d') !== $value) {
                $this->errors[$field] =
                    "Le champ {$field} doit contenir une date valide.";
            }
        }

        return $this;
    }

    /**
     * Ajouter une erreur manuellement.
     */
    public function addError(string $field, string $message): self
    {
        $this->errors[$field] = $message;

        return $this;
    }

    /**
     * Vérifier si les données sont valides.
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Vérifier si des erreurs existent.
     */
    public function fails(): bool
    {
        return !$this->passes();
    }

    /**
     * Récupérer toutes les erreurs.
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
