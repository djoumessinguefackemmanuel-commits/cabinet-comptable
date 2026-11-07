$ErrorActionPreference = 'Stop'

$projectRoot = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$backupDirectory = $env:CABINET_BACKUP_DIR
if ([string]::IsNullOrWhiteSpace($backupDirectory)) {
    throw 'CABINET_BACKUP_DIR doit désigner un dossier de sauvegarde hors du projet.'
}

$backupDirectory = [System.IO.Path]::GetFullPath($backupDirectory)
$projectRootPrefix = $projectRoot.TrimEnd([char]'\') + '\'
$isProjectRoot = $backupDirectory.TrimEnd([char]'\').Equals(
    $projectRoot.TrimEnd([char]'\'),
    [System.StringComparison]::OrdinalIgnoreCase
)
$isInsideProject = $backupDirectory.StartsWith(
    $projectRootPrefix,
    [System.StringComparison]::OrdinalIgnoreCase
)
if ($isProjectRoot -or $isInsideProject) {
    throw 'Le dossier de sauvegarde ne doit pas se trouver dans le projet web.'
}

foreach ($name in @('DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME')) {
    if ([string]::IsNullOrWhiteSpace([Environment]::GetEnvironmentVariable($name))) {
        throw "La variable d'environnement $name est obligatoire."
    }
}

if (-not (Test-Path -LiteralPath $backupDirectory)) {
    New-Item -ItemType Directory -Path $backupDirectory | Out-Null
}

$dumpCommand = if ([string]::IsNullOrWhiteSpace($env:MYSQLDUMP_PATH)) {
    'mysqldump'
} else {
    $env:MYSQLDUMP_PATH
}

$timestamp = [DateTime]::UtcNow.ToString('yyyyMMdd-HHmmss')
$backupFile = Join-Path $backupDirectory "$($env:DB_DATABASE)-$timestamp.sql"
$temporaryFile = "$backupFile.partial"
$previousPassword = $env:MYSQL_PWD

try {
    $env:MYSQL_PWD = $env:DB_PASSWORD
    & $dumpCommand `
        "--host=$($env:DB_HOST)" `
        "--port=$($env:DB_PORT)" `
        "--user=$($env:DB_USERNAME)" `
        '--single-transaction' `
        '--routines' `
        '--triggers' `
        '--events' `
        "--result-file=$temporaryFile" `
        '--databases' `
        $env:DB_DATABASE

    if ($LASTEXITCODE -ne 0) {
        throw "mysqldump a échoué avec le code $LASTEXITCODE."
    }
    if (-not (Test-Path -LiteralPath $temporaryFile) -or (Get-Item $temporaryFile).Length -eq 0) {
        throw 'mysqldump n’a produit aucun fichier de sauvegarde.'
    }

    Move-Item -LiteralPath $temporaryFile -Destination $backupFile
    Write-Output "Sauvegarde créée : $backupFile"
}
finally {
    if (Test-Path -LiteralPath $temporaryFile) {
        Remove-Item -LiteralPath $temporaryFile
    }

    if ($null -eq $previousPassword) {
        Remove-Item Env:\MYSQL_PWD -ErrorAction SilentlyContinue
    } else {
        $env:MYSQL_PWD = $previousPassword
    }
}
