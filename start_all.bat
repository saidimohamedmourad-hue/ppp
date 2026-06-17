@echo off
REM ===================================================================
REM  IQRA - demarre TOUS les services de dev en une fois.
REM
REM  Verifie MariaDB (3306), puis lance dans des fenetres separees :
REM    - Backend Laravel (API + admin Blade)   ->  http://localhost:8000
REM    - Assets back-office (Vite, vues Blade admin : Tailwind/Alpine)
REM    - Frontend React (site candidat)          ->  http://localhost:3000
REM    - Flutter web (via run_web.bat)            ->  http://localhost:8090
REM
REM  Usage : double-clic, ou `start_all.bat` dans un terminal.
REM ===================================================================
cd /d "%~dp0"

echo [1/5] Verification de MariaDB (port 3306)...
powershell -NoProfile -Command "if (Get-NetTCPConnection -State Listen -LocalPort 3306 -ErrorAction SilentlyContinue) { exit 0 } else { exit 1 }"
if errorlevel 1 goto :nodb
echo       OK - MariaDB ecoute sur 3306.

echo [2/5] Backend Laravel sur http://localhost:8000 ...
start "IQRA backend (8000)" /D "%~dp0job-backoffice" cmd /k "php artisan serve --host=127.0.0.1 --port=8000"

echo   + File d'attente (analyse IA des CV et des candidatures) ...
start "IQRA queue worker" /D "%~dp0job-backoffice" cmd /k "php artisan queue:work --tries=2"

echo [3/5] Assets back-office (Vite, vues admin) ...
start "IQRA back-office assets (Vite)" /D "%~dp0job-backoffice" cmd /k "npm run dev"

echo [4/5] Frontend React sur http://localhost:3000 ...
start "IQRA web (3000)" /D "%~dp0job-app-frontend" cmd /k "npm run dev -- --port 3000 --strictPort"

echo [5/5] Flutter web sur http://localhost:8090 ...
start "IQRA flutter (8090)" /D "%~dp0flutter_app" cmd /k "run_web.bat"

echo.
echo Services lances dans des fenetres separees :
echo    - Backend         : http://localhost:8000   (API + admin Blade)
echo    - Assets admin    : Vite (hot-reload des vues Blade)
echo    - Frontend React  : http://localhost:3000
echo    - Flutter web     : http://localhost:8090
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
