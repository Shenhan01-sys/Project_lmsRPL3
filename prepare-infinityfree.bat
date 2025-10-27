@echo off
REM InfinityFree Deployment Preparation Script for Windows
echo 🚀 Preparing SmartDev Academic LMS for InfinityFree deployment...

REM Create deployment directory
set DEPLOY_DIR=infinityfree-package
if exist %DEPLOY_DIR% rmdir /s /q %DEPLOY_DIR%
mkdir %DEPLOY_DIR%

echo 📁 Creating deployment package...

REM Copy essential Laravel files
xcopy app %DEPLOY_DIR%\app\ /E /I /H
xcopy bootstrap %DEPLOY_DIR%\bootstrap\ /E /I /H
xcopy config %DEPLOY_DIR%\config\ /E /I /H
xcopy database %DEPLOY_DIR%\database\ /E /I /H
xcopy public %DEPLOY_DIR%\public\ /E /I /H
xcopy resources %DEPLOY_DIR%\resources\ /E /I /H
xcopy routes %DEPLOY_DIR%\routes\ /E /I /H
xcopy storage %DEPLOY_DIR%\storage\ /E /I /H

REM Copy configuration files
copy composer.json %DEPLOY_DIR%\
copy composer.lock %DEPLOY_DIR%\
copy artisan %DEPLOY_DIR%\
copy .env.infinityfree %DEPLOY_DIR%\.env
copy .htaccess %DEPLOY_DIR%\

REM Install production dependencies
cd %DEPLOY_DIR%
echo 📦 Installing production dependencies...
composer install --no-dev --optimize-autoloader --no-interaction

REM Create SQLite database for initial setup
type nul > database\database.sqlite

REM Cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

REM Create database setup script
echo ^<?php > database-setup.php
echo // Database Migration and Seeding Script for InfinityFree >> database-setup.php
echo. >> database-setup.php
echo require_once 'vendor/autoload.php'; >> database-setup.php
echo. >> database-setup.php
echo use Illuminate\Support\Facades\Artisan; >> database-setup.php
echo. >> database-setup.php
echo echo "🔄 Setting up database...\n"; >> database-setup.php
echo. >> database-setup.php
echo try { >> database-setup.php
echo     // Run migrations >> database-setup.php
echo     echo "Running migrations...\n"; >> database-setup.php
echo     Artisan::call('migrate', ['--force' =^> true]); >> database-setup.php
echo     echo Artisan::output(); >> database-setup.php
echo. >> database-setup.php
echo     // Run seeders >> database-setup.php
echo     echo "Running seeders...\n"; >> database-setup.php
echo     Artisan::call('db:seed', ['--force' =^> true]); >> database-setup.php
echo     echo Artisan::output(); >> database-setup.php
echo. >> database-setup.php
echo     echo "✅ Database setup completed successfully!\n"; >> database-setup.php
echo. >> database-setup.php
echo } catch (Exception $e) { >> database-setup.php
echo     echo "❌ Error: " . $e-^>getMessage() . "\n"; >> database-setup.php
echo } >> database-setup.php

cd ..

echo ✅ Deployment package created in '%DEPLOY_DIR%' directory
echo.
echo 📋 Next steps:
echo 1. Create InfinityFree account at infinityfree.net
echo 2. Create hosting account and MySQL database
echo 3. Upload contents of '%DEPLOY_DIR%' to htdocs/ via FTP
echo 4. Update .env file with MySQL credentials
echo 5. Run database-setup.php via browser
echo.
echo 🌍 Your LMS will be live at: https://yoursubdomain.epizy.com
pause