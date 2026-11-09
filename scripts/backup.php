<?php

/**
 * Sauvegarde complète de la base de données (MySQL/MariaDB ou PostgreSQL).
 *
 * Usage :  php scripts/backup.php [fichier.sql.gz]
 * Utilise les mêmes variables d'environnement que l'application (DATABASE_URL, DB_*).
 * Le fichier produit contient des instructions INSERT, y compris les documents
 * envoyés (si STORAGE_DRIVER=database). Conservez-le dans un endroit sûr et chiffré.
 */

if (PHP_SAPI !== 'cli') {
    exit("Ce script s'exécute uniquement en ligne de commande.\n");
}

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/config/app.php';
require BASE_PATH . '/config/constants.php';
require BASE_PATH . '/config/database.php';
require BASE_PATH . '/app/Core/Database.php';

$pdo = App\Core\Database::connect();
$isPg = DB_DRIVER === 'pgsql';

$target = $argv[1] ?? (BASE_PATH . '/storage/backup-' . date('Ymd-His') . '.sql.gz');
$dir = dirname($target);
if (!is_dir($dir) && !mkdir($dir, 0700, true) && !is_dir($dir)) {
    fwrite(STDERR, "Impossible de créer le dossier $dir\n");
    exit(1);
}

$out = gzopen($target, 'wb9');
if ($out === false) {
    fwrite(STDERR, "Impossible d'écrire $target\n");
    exit(1);
}

$quote = static fn(string $name): string => $isPg ? '"' . str_replace('"', '""', $name) . '"' : '`' . str_replace('`', '``', $name) . '`';

$tables = $isPg
    ? $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_type = 'BASE TABLE' ORDER BY table_name")->fetchAll(PDO::FETCH_COLUMN)
    : $pdo->query('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"')->fetchAll(PDO::FETCH_COLUMN);

gzwrite($out, '-- Sauvegarde du ' . date('c') . " (" . DB_DRIVER . ")\n");
gzwrite($out, $isPg ? "SET session_replication_role = replica;\n" : "SET FOREIGN_KEY_CHECKS=0;\n");

$total = 0;
foreach ($tables as $table) {
    // Les sessions et tentatives de connexion n'ont pas à être sauvegardées.
    if (in_array($table, ['app_sessions', 'login_attempts'], true)) {
        continue;
    }

    $statement = $pdo->query('SELECT * FROM ' . $quote($table));
    $count = 0;

    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        $columns = array_map($quote, array_keys($row));
        $values = [];
        foreach ($row as $value) {
            if ($value === null) {
                $values[] = 'NULL';
            } elseif (is_resource($value)) {
                $hex = bin2hex(stream_get_contents($value));
                $values[] = $isPg ? "'\\x" . $hex . "'" : '0x' . ($hex === '' ? '0' : $hex);
            } else {
                $values[] = $pdo->quote((string) $value);
            }
        }
        gzwrite($out, 'INSERT INTO ' . $quote($table) . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $values) . ");\n");
        $count++;
    }

    echo str_pad($table, 28) . $count . " ligne(s)\n";
    $total += $count;
}

gzwrite($out, $isPg ? "SET session_replication_role = DEFAULT;\n" : "SET FOREIGN_KEY_CHECKS=1;\n");
gzclose($out);
chmod($target, 0600);

echo "Sauvegarde terminée : $total ligne(s) → $target\n";
