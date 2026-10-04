@echo off
title Launch GYMZONE
echo ========================================================
echo        Starting GYMZONE - Explore Your Fitness
echo ========================================================
echo.

:: Check if htdocs destination exists, copy if needed
if not exist "C:\xampp\htdocs\gymzone" (
    echo Copying project files to C:\xampp\htdocs\gymzone ...
    xcopy /E /I /Y "%~dp0*" "C:\xampp\htdocs\gymzone\"
    echo Files copied successfully.
)

echo Opening GYMZONE Database Setup in your default browser...
start http://localhost/gymzone/setup_db.php
echo.
echo If MySQL is already running, setup will take 1-2 seconds.
echo After setup completes, visit: http://localhost/gymzone/index.php
echo.
pause
