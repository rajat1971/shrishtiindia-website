# Serves the offline copy of the home page at http://localhost:8080 (Ctrl+C to stop).
# Needs Python 3 or Node.js. The enquiry form does not work offline.
$ErrorActionPreference = 'Stop'
$snap = Join-Path (Split-Path -Parent $PSScriptRoot) 'static-snapshot'
Set-Location $snap
Start-Process 'http://localhost:8080/index.html'
if (Get-Command python -ErrorAction SilentlyContinue) { python -m http.server 8080 }
elseif (Get-Command py -ErrorAction SilentlyContinue) { py -m http.server 8080 }
elseif (Get-Command npx -ErrorAction SilentlyContinue) { npx --yes http-server -p 8080 . }
else { Write-Error 'Install Python 3 (python.org) or Node.js (nodejs.org), then run this again.' }
