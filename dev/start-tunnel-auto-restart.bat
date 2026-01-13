@echo off
setlocal enableextensions

title SoleSource Dev Tunnel (Auto-Restart)
set "CONFIG=C:\Users\James Carlo\.cloudflared\config-dev.yml"
set "NAME=Cloudflare Tunnel"

where cloudflared >nul 2>&1
if errorlevel 1 (
    echo cloudflared is not in PATH. Install it or add it to PATH, then rerun.
    pause
    exit /b 1
)

echo Starting %NAME% with config %CONFIG%.
echo Press Ctrl+C to stop auto-restart.
echo.

:: Wait for internet connectivity before starting the loop
:waitnet
    ping -n 1 8.8.8.8 >nul
    if errorlevel 1 (
        echo Waiting for internet...
        timeout /t 3 /nobreak >nul
        goto :waitnet
    )

:loop
    echo [%date% %time%] Launching %NAME%...
    cloudflared tunnel --config "%CONFIG%" run
    set "EXITCODE=%errorlevel%"
    echo [%date% %time%] %NAME% exited with code %EXITCODE%.

    if "%EXITCODE%"=="0" (
        echo Clean exit detected. Stopping auto-restart.
        goto :end
    )

    echo Restarting in 5 seconds... Press Ctrl+C to cancel.
    timeout /t 5 /nobreak >nul
    goto :loop

:end
pause
