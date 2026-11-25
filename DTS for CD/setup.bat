@echo off
echo ============================================
echo  Campus DTS - Setup Script
echo ============================================
echo.

SET PHP=C:\xampp\php\php.exe

REM Always work from the folder where this bat file lives
cd /d "%~dp0"
echo Working directory: %CD%
echo.

REM Check if PHP exists
if not exist "%PHP%" (
    echo [ERROR] PHP not found at C:\xampp\php\php.exe
    echo Make sure XAMPP is installed correctly.
    pause
    exit /b 1
)
echo PHP found OK.
echo.

REM ---- Check if vendor folder already exists ----
if exist "%~dp0vendor\autoload.php" (
    echo [OK] vendor folder found. Skipping Composer install.
    goto skipComposer
)

echo vendor folder not found. Composer install is required.
echo.
echo OPTIONS:
echo   1. Connect to a network with internet access (mobile hotspot recommended)
echo   2. Or copy the vendor folder from another machine that already ran setup
echo.
echo Press any key when ready to attempt Composer install...
pause

REM Download composer.phar if not present
if not exist "%~dp0composer.phar" (
    echo Downloading Composer...
    "%PHP%" -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    "%PHP%" composer-setup.php --install-dir="%~dp0" --filename=composer.phar
    "%PHP%" -r "unlink('composer-setup.php');"
    if not exist "%~dp0composer.phar" (
        echo [ERROR] Failed to download Composer.
        pause
        exit /b 1
    )
    echo Composer downloaded.
)

echo Installing dependencies...
"%PHP%" "%~dp0composer.phar" install --no-interaction --prefer-dist
if %errorlevel% neq 0 (
    echo.
    echo [ERROR] Composer install failed.
    echo.
    echo TIP: This usually means GitHub is blocked on this network.
    echo      Try running setup.bat again using a mobile hotspot.
    echo      OR copy the vendor folder from a machine that completed setup.
    pause
    exit /b 1
)
echo Dependencies installed successfully.

:skipComposer
echo.

REM ---- Create required directories ----
echo Creating required directories...
if not exist "%~dp0bootstrap\cache" mkdir "%~dp0bootstrap\cache"
if not exist "%~dp0storage\app\public" mkdir "%~dp0storage\app\public"
if not exist "%~dp0storage\framework\sessions" mkdir "%~dp0storage\framework\sessions"
if not exist "%~dp0storage\framework\views" mkdir "%~dp0storage\framework\views"
if not exist "%~dp0storage\framework\cache\data" mkdir "%~dp0storage\framework\cache\data"
if not exist "%~dp0storage\logs" mkdir "%~dp0storage\logs"
echo Directories ready.
echo.

REM ---- Environment file ----
echo [1/4] Setting up environment file...
if not exist "%~dp0.env" (
    copy "%~dp0.env.example" "%~dp0.env"
    echo .env file created from .env.example
) else (
    echo .env already exists, skipping.
)

REM ---- App key ----
echo.
echo [2/4] Generating application key...
"%PHP%" artisan key:generate

REM ---- Database migration ----
echo.
echo [3/4] Running database migrations and seeding...
echo.
echo Make sure:
echo   - XAMPP MySQL is running
echo   - Database "dts_campus" exists in phpMyAdmin
echo.
echo Press any key to continue...
pause
"%PHP%" artisan migrate --seed --force
if %errorlevel% neq 0 (
    echo.
    echo [ERROR] Migration failed.
    echo Check that MySQL is running and the database "dts_campus" exists.
    echo You can create it at: http://localhost/phpmyadmin
    pause
    exit /b 1
)

REM ---- Storage link ----
echo.
echo [4/4] Creating storage symlink...
"%PHP%" artisan storage:link

echo.
echo ============================================
echo  Setup Complete!
echo ============================================
echo.
echo  Open your browser:
echo  http://localhost/dts/public
echo.
echo  Default Login Credentials:
echo  Super Admin  : superadmin@dts.edu     / password
echo  Campus Admin : admin@campus-a.edu     / password
echo  Director     : director@campus-a.edu  / password
echo  Staff        : staff@campus-a.edu     / password
echo.
echo ============================================
echo  FOR OTHER CAMPUSES:
echo  Copy this entire folder (including vendor)
echo  to C:\xampp\htdocs\dts on their machine.
echo  Then just run setup.bat - no internet needed.
echo ============================================
echo.
pause
