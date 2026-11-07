<?php

namespace App\Services;

use App\Core\Database;

class DocumentService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Enregistrer un document envoyé par un utilisateur.
     */
    public function upload(
        array $file,
        int $userId,
        int $companyId,
        ?int $exerciseId = null
    ): array {

        if (
            !isset($file['error'])
            || $file['error'] !== UPLOAD_ERR_OK
        ) {
            throw new \RuntimeException(
                'Une erreur est survenue lors de l\'envoi du document.'
            );
        }

        if ($file['size'] > MAX_FILE_SIZE) {
            throw new \RuntimeException(
                'Le document dépasse la taille maximale autorisée.'
            );
        }

        $extension = strtolower(
            pathinfo($file['name'], PATHINFO_EXTENSION)
        );

        if (!in_array($extension, ALLOWED_FILE_EXTENSIONS, true)) {
            throw new \RuntimeException(
                'Le format du document n\'est pas autorisé.'
            );
        }

        $storageDirectory = STORAGE_PATH . '/documents';

        if (!is_dir($storageDirectory)) {

            if (!mkdir($storageDirectory, 0755, true)) {
                throw new \RuntimeException(
                    'Impossible de créer le dossier de stockage.'
                );
            }
        }

        $fileName = bin2hex(random_bytes(16)) . '.' . $extension;

        $destination = $storageDirectory . '/' . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new \RuntimeException(
                'Impossible d\'enregistrer le document.'
            );
        }

        $sql = "
            INSERT INTO documents (
                user_id,
                company_id,
                exercise_id,
                name,
                file_name,
                file_path,
                file_type,
                file_size,
                created_at
            ) VALUES (
                :user_id,
                :company_id,
                :exercise_id,
                :name,
                :file_name,
                :file_path,
                :file_type,
                :file_size,
                NOW()
            )
        ";

        try {

            $statement = $this->db->prepare($sql);

            $statement->execute([
                'user_id' => $userId,
                'company_id' => $companyId,
                'exercise_id' => $exerciseId,
                'name' => basename($file['name']),
                'file_name' => $fileName,
                'file_path' => 'storage/documents/' . $fileName,
                'file_type' => $extension,
                'file_size' => (int) $file['size']
            ]);

            return [
                'id' => (int) $this->db->lastInsertId(),
                'name' => basename($file['name']),
                'file_name' => $fileName,
                'file_path' => 'storage/documents/' . $fileName,
                'file_type' => $extension,
                'file_size' => (int) $file['size']
            ];

        } catch (\Throwable $exception) {

            if (is_file($destination)) {
                unlink($destination);
            }

            throw $exception;
        }
    }

    /**
     * Récupérer les documents d'un cabinet.
     */
    public function getByCompany(int $companyId): array
    {
        $statement = $this->db->prepare(
            'SELECT *
             FROM documents
             WHERE company_id = :company_id
             ORDER BY created_at DESC'
        );

        $statement->execute([
            'company_id' => $companyId
        ]);

        return $statement->fetchAll();
    }

    /**
     * Supprimer un document.
     */
    public function delete(int $documentId, int $companyId): bool
    {
        $statement = $this->db->prepare(
            'SELECT file_name
             FROM documents
             WHERE id = :id
               AND company_id = :company_id
             LIMIT 1'
        );

        $statement->execute([
            'id' => $documentId,
            'company_id' => $companyId
        ]);

        $document = $statement->fetch();

        if (!$document) {
            return false;
        }

        $deleteStatement = $this->db->prepare(
            'DELETE FROM documents
             WHERE id = :id
               AND company_id = :company_id'
        );

        $deleted = $deleteStatement->execute([
            'id' => $documentId,
            'company_id' => $companyId
        ]);

        if ($deleted) {

            $filePath = STORAGE_PATH
                      . '/documents/'
                      . basename($document['file_name']);

            if (is_file($filePath)) {
                unlink($filePath);
            }
        }

        return $deleted;
    }
}
