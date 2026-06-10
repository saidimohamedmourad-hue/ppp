# ===================================================================
#  Lance IQRA (Flutter Web) de maniere FIABLE sur http://localhost:8090
#
#  Par defaut : device "web-server" -> AUCUN debogueur DWDS attache a
#  Chrome, donc PAS d'erreur "Failed to connect to the web debug service"
#  (bug Flutter/Windows). Le navigateur s'ouvre tout seul quand c'est pret.
#
#  Usage :
#    ./run_web.ps1            (web-server, fiable, recommande)
#    ./run_web.ps1 -Chrome    (device chrome avec debogueur ; peut echouer
#                              sur le debug service selon la machine)
# ===================================================================
param([switch]$Chrome)

Set-Location -Path $PSScriptRoot

# Detecte chrome.exe (corrige "Failed to launch browser" si install en x86).
if (-not $env:CHROME_EXECUTABLE -or -not (Test-Path $env:CHROME_EXECUTABLE)) {
  foreach ($c in @(
    "C:\Program Files (x86)\Google\Chrome\Application\chrome.exe",
    "C:\Program Files\Google\Chrome\Application\chrome.exe",
    "$env:LOCALAPPDATA\Google\Chrome\Application\chrome.exe")) {
    if (Test-Path $c) { $env:CHROME_EXECUTABLE = $c; break }
  }
}

$device = if ($Chrome) { 'chrome' } else { 'web-server' }
Write-Host "Lancement de IQRA sur http://localhost:8090 (device: $device) ..." -ForegroundColor Cyan

# En mode web-server, ouvre le navigateur des que le port repond.
if (-not $Chrome) {
  Start-Job -ScriptBlock {
    for ($i = 0; $i -lt 180; $i++) {
      if (Get-NetTCPConnection -LocalPort 8090 -State Listen -ErrorAction SilentlyContinue) {
        Start-Process 'http://localhost:8090'; break
      }
      Start-Sleep -Seconds 2
    }
  } | Out-Null
}

flutter run -d $device --web-port 8090 --web-hostname localhost
