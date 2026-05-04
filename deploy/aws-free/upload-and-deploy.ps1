param(
    [Parameter(Mandatory = $true)]
    [string]$PemPath,

    [Parameter(Mandatory = $true)]
    [string]$HostIp
)

$ErrorActionPreference = 'Stop'

$zipPath = Join-Path $PSScriptRoot 'mientayshop-aws-free.zip'

function Invoke-CheckedCommand {
    param(
        [Parameter(Mandatory = $true)]
        [string]$Command
    )

    Invoke-Expression $Command
    if ($LASTEXITCODE -ne 0) {
        throw "Lenh that bai voi ma loi ${LASTEXITCODE}: $Command"
    }
}

if (-not (Test-Path $PemPath)) {
    throw "Khong tim thay file PEM: $PemPath"
}

if (-not (Test-Path $zipPath)) {
    throw "Khong tim thay file zip deploy: $zipPath"
}

Write-Host "==> Uploading zip to EC2..."
Invoke-CheckedCommand "scp -i `"$PemPath`" `"$zipPath`" ubuntu@${HostIp}:/tmp/mientayshop.zip"

Write-Host "==> Installing unzip bootstrap on EC2..."
Invoke-CheckedCommand "ssh -i `"$PemPath`" ubuntu@${HostIp} `"sudo apt-get update && sudo apt-get install -y unzip`""

Write-Host "==> Unpacking code on EC2..."
Invoke-CheckedCommand "ssh -i `"$PemPath`" ubuntu@${HostIp} `"sudo mkdir -p /var/www/mientayshop && sudo chown -R ubuntu:www-data /var/www/mientayshop && unzip -o /tmp/mientayshop.zip -d /var/www/mientayshop`""

Write-Host "==> Running server setup..."
Invoke-CheckedCommand "ssh -i `"$PemPath`" ubuntu@${HostIp} `"cd /var/www/mientayshop && bash deploy/aws-free/setup-ubuntu-free.sh`""

Write-Host "==> Creating production .env..."
Invoke-CheckedCommand "ssh -i `"$PemPath`" ubuntu@${HostIp} `"cd /var/www/mientayshop && cp deploy/aws-free/.env.production.example .env && sed -i 's#^APP_URL=.*#APP_URL=http://${HostIp}#' .env`""

Write-Host "==> Deploying Laravel app..."
Invoke-CheckedCommand "ssh -i `"$PemPath`" ubuntu@${HostIp} `"cd /var/www/mientayshop && bash deploy/aws-free/deploy-on-server.sh`""

Write-Host ""
Write-Host "Deploy xong. Mo trang: http://$HostIp"
