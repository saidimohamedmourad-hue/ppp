@echo off
REM ===================================================================
REM  Lance IQRA (Flutter Web) de maniere FIABLE sur http://localhost:8090
REM
REM  Utilise le device "web-server" : pas de debogueur DWDS attache a
REM  Chrome -> evite l'erreur "Failed to connect to the web debug service"
REM  (bug Flutter/Windows). Le navigateur s'ouvre tout seul des que le
REM  serveur est pret.
REM
REM  8090 = origine autorisee dans Google Cloud (Sign-In).
REM  Usage : double-clic, ou `run_web.bat` dans un terminal.
REM ===================================================================
cd /d "%~dp0"

REM Ouvre Chrome sur localhost:8090 des que le port repond (en parallele).
start "" /b powershell -NoProfile -WindowStyle Hidden -Command ^
  "for($i=0;$i -lt 180;$i++){ if(Get-NetTCPConnection -LocalPort 8090 -State Listen -ErrorAction SilentlyContinue){ Start-Process 'http://localhost:8090'; break }; Start-Sleep -Seconds 2 }"

echo Lancement de IQRA sur http://localhost:8090 (web-server, fiable) ...
flutter run -d web-server --web-port 8090 --web-hostname localhost
