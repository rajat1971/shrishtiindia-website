# Builds dist\shrishti-heritage.zip, ready for wp-admin > Appearance > Themes > Add New > Upload.
# Usage (PowerShell, from the project folder):  .\tools\package-theme.ps1
$ErrorActionPreference = 'Stop'
$root  = Split-Path -Parent $PSScriptRoot
$theme = Join-Path $root 'wordpress-theme\shrishti-heritage'
$dist  = Join-Path $root 'dist'
New-Item -ItemType Directory -Force -Path $dist | Out-Null
$zip = Join-Path $dist 'shrishti-heritage.zip'
if (Test-Path $zip) { Remove-Item $zip }
Compress-Archive -Path $theme -DestinationPath $zip
Write-Host "Created $zip"
