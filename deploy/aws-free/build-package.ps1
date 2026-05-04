param(
    [string]$OutputPath = "$PSScriptRoot\mientayshop-aws-free.zip"
)

$ErrorActionPreference = 'Stop'

$projectRoot = Resolve-Path (Join-Path $PSScriptRoot '..\..')
$tempRoot = Join-Path $env:TEMP ("mientayshop-deploy-" + [guid]::NewGuid().ToString('N'))

Write-Host "==> Preparing clean deploy folder..."
New-Item -ItemType Directory -Path $tempRoot | Out-Null

$excludeDirs = @(
    '.git',
    'node_modules',
    'vendor',
    'python',
    '.idea',
    '.vscode'
)

$excludeFiles = @(
    '.env',
    'public\hot',
    'public\storage',
    'deploy\aws-free\markethub-aws-free.zip',
    'deploy\aws-free\markethub.sql',
    'deploy\aws-free\nginx-markethub.conf',
    'deploy\aws-free\mientayshop-aws-free.zip'
)

try {
    Get-ChildItem -LiteralPath $projectRoot -Force | ForEach-Object {
        if ($excludeDirs -contains $_.Name) {
            return
        }

        $target = Join-Path $tempRoot $_.Name
        Copy-Item -LiteralPath $_.FullName -Destination $target -Recurse -Force
    }

    foreach ($file in $excludeFiles) {
        $path = Join-Path $tempRoot $file
        if (Test-Path -LiteralPath $path) {
            Remove-Item -LiteralPath $path -Force
        }
    }

    $logDir = Join-Path $tempRoot 'storage\logs'
    if (Test-Path -LiteralPath $logDir) {
        Get-ChildItem -LiteralPath $logDir -Filter '*.log' -Force | Remove-Item -Force
    }

    $frameworkDir = Join-Path $tempRoot 'storage\framework'
    if (Test-Path -LiteralPath $frameworkDir) {
        Get-ChildItem -LiteralPath $frameworkDir -Recurse -Force |
            Where-Object { -not $_.PSIsContainer -and $_.Name -ne '.gitignore' } |
            Remove-Item -Force
    }

    if (Test-Path -LiteralPath $OutputPath) {
        Remove-Item -LiteralPath $OutputPath -Force
    }

    Write-Host "==> Creating zip package..."
    Compress-Archive -Path (Join-Path $tempRoot '*') -DestinationPath $OutputPath -Force
    Write-Host "Package created: $OutputPath"
}
finally {
    if (Test-Path -LiteralPath $tempRoot) {
        Remove-Item -LiteralPath $tempRoot -Recurse -Force
    }
}
