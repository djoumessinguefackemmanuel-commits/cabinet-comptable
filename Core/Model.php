<?php

namespace App\Core;

abstract class Model
{
    /**
     * Exécuter une requête SQL préparée.
     */
    protected static function query(
        string $sql,
        array $params = []
    ): \PDOStatement
    {
        $statement = Database::connect()->prepare($sql);
        $statement->execute($params);

        return $statement;
    }

    /**
     * Exécuter une requête SQL sans résultat exploitable.
     */
    protected static function execute(string $sql, array $params = []): bool
    {
        $statement = Database::connect()->prepare($sql);

        return $statement->execute($params);
    }

    /**
     * Récupérer un seul enregistrement.
     */
    protected static function findOne(
        string $sql,
        array $params = []
    ): ?array
    {
        $result = self::query($sql, $params)->fetch();

        return $result ?: null;
    }

    /**
     * Récupérer plusieurs enregistrements.
     */
    protected static function findAll(
        string $sql,
        array $params = []
    ): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    /**
     * Récupérer le dernier identifiant inséré.
     */
    protected static function lastInsertId(): string
    {
        return Database::connect()->lastInsertId();
    }

    /**
     * Démarrer une transaction.
     */
    protected static function beginTransaction(): bool
    {
        return Database::connect()->beginTransaction();
    }

    /**
     * Valider une transaction.
     */
    protected static function commit(): bool
    {
        return Database::connect()->commit();
    }

    /**
     * Annuler une transaction.
     */
    protected static function rollback(): bool
    {
        return Database::connect()->rollBack();
    }
}
