@echo off
REM ===================================================================
REM  Arrete proprement IQRA (Flutter Web) : libere le port 8090 en tuant
REM  le `flutter run` qui l'occupe. A lancer si un run est reste bloque.
REM ===================================================================
echo Arret de IQRA (liberation du port 8090)...
powershell -NoProfile -Command "$p = Get-NetTCPConnection -State Listen -LocalPort 8090 -ErrorAction SilentlyContinue | Select-Object -ExpandProperty OwningProcess -Unique; if($p){ $p | ForEach-Object { Write-Host ('  arret PID ' + $_); Stop-Process -Id $_ -Force -ErrorAction SilentlyContinue } } else { Write-Host '  8090 deja libre' }"
echo Termine.
