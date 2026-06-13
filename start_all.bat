@echo off
REM ===================================================================
REM  IQRA - demarre tous les services de dev en une fois.
REM
REM  Verifie d'abord que MariaDB tourne (port 3306), puis lance dans
REM  des fenetres separees :
REM    - Backend Laravel (API + admin Blade)  ->  http://localhost:8000
REM    - Frontend React (site candidat)        ->  http://localhost:3000
REM  (Flutter web reste a part : flutter_app\run_web.bat -> 8090)
REM
REM  Usage : double-clic, ou `start_all.bat` dans un terminal.
REM ===================================================================
cd /d "%~dp0"

echo [1/3] Verification de MariaDB (port 3306)...
powershell -NoProfile -Command "if (Get-NetTCPConnection -State Listen -LocalPort 3306 -ErrorAction SilentlyContinue) { exit 0 } else { exit 1 }"
if errorlevel 1 goto :nodb
echo       OK - MariaDB ecoute sur 3306.

echo [2/3] Demarrage du backend Laravel sur http://localhost:8000 ...
start "IQRA backend (8000)" cmd /k "cd /d "%~dp0job-backoffice" && php artisan serve --host=127.0.0.1 --port=8000"

echo [3/3] Demarrage du frontend React sur http://localhost:3000 ...
start "IQRA web (3000)" cmd /k "cd /d "%~dp0job-app-frontend" && npm run dev -- --port 3000 --strictPort"

echo.
echo Services lances dans des fenetres separees :
echo    - Backend  : http://localhost:8000   (API + admin Blade)
echo    - Frontend : http://localhost:3000   (site React)
echo    - Flutter web (optionnel) : flutter_app\run_web.bat
echo.
echo Ferme les fenetres "IQRA ..." pour arreter les services.
goto :end

:nodb
echo.
echo   [!] MariaDB ne tourne pas (port 3306 ferme).
echo       Demarre-la d'abord (ex : XAMPP Control Panel -^> MySQL -^> Start),
echo       puis relance start_all.bat.
echo.

:end
pause
