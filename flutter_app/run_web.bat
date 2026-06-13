@echo off
REM ===================================================================
REM  Lance IQRA (Flutter Web) de maniere FIABLE sur http://localhost:8090
REM
REM  [1] Libere d'abord le port 8090 : tue tout `flutter run` precedent
REM      reste en zombie -> evite l'erreur "port deja utilise" / blocage.
REM  [2] Device "web-server" : pas de debogueur DWDS attache a Chrome
REM      -> evite "Failed to connect to the web debug service" (bug Win).
REM  [3] Le navigateur s'ouvre tout seul des que le serveur est pret.
REM
REM  8090 = origine autorisee dans Google Cloud (Sign-In).
REM  Usage : double-clic, ou `run_web.bat` dans un terminal.
REM  Pour arreter proprement : Ctrl+C dans cette fenetre, ou stop_web.bat
REM ===================================================================
cd /d "%~dp0"

echo [1/3] Liberation du port 8090 si occupe (run precedent)...
powershell -NoProfile -Command "Get-NetTCPConnection -State Listen -LocalPort 8090 -ErrorAction SilentlyContinue | Select-Object -ExpandProperty OwningProcess -Unique | ForEach-Object { Stop-Process -Id $_ -Force -ErrorAction SilentlyContinue }"

echo [2/3] Le navigateur s'ouvrira des que le serveur repond...
start "" /b powershell -NoProfile -WindowStyle Hidden -Command ^
  "for($i=0;$i -lt 180;$i++){ if(Get-NetTCPConnection -LocalPort 8090 -State Listen -ErrorAction SilentlyContinue){ Start-Process 'http://localhost:8090'; break }; Start-Sleep -Seconds 2 }"

echo [3/3] Lancement de IQRA sur http://localhost:8090 (web-server, fiable)...
flutter run -d web-server --web-port 8090 --web-hostname localhost
