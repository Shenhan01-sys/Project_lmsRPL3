#!/bin/bash

# InfinityFree Deployment Preparation Script
echo "🚀 Preparing SmartDev Academic LMS for InfinityFree deployment..."

# Create deployment directory
DEPLOY_DIR="infinityfree-package"
rm -rf $DEPLOY_DIR
mkdir $DEPLOY_DIR

echo "📁 Creating deployment package..."

# Copy essential Laravel files
cp -r app $DEPLOY_DIR/
cp -r bootstrap $DEPLOY_DIR/
cp -r config $DEPLOY_DIR/
cp -r database $DEPLOY_DIR/
cp -r public $DEPLOY_DIR/
cp -r resources $DEPLOY_DIR/
cp -r routes $DEPLOY_DIR/
cp -r storage $DEPLOY_DIR/

# Copy configuration files
cp composer.json $DEPLOY_DIR/
cp composer.lock $DEPLOY_DIR/
cp artisan $DEPLOY_DIR/
cp .env.infinityfree $DEPLOY_DIR/.env
cp .htaccess $DEPLOY_DIR/

# Create vendor directory and install production dependencies
cd $DEPLOY_DIR
echo "📦 Installing production dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Set proper permissions
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chmod 644 .env

# Create SQLite database for initial setup (will switch to MySQL later)
touch database/database.sqlite
chmod 664 database/database.sqlite

# Cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create database setup script
cat > database-setup.php << 'EOF'
<?php
// Database Migration and Seeding Script for InfinityFree

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Artisan;

echo "🔄 Setting up database...\n";

try {
    // Run migrations
    echo "Running migrations...\n";
    Artisan::call('migrate', ['--force' => true]);
    echo Artisan::output();
    
    // Run seeders
    echo "Running seeders...\n";
    Artisan::call('db:seed', ['--force' => true]);
    echo Artisan::output();
    
    echo "✅ Database setup completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
EOF

cd ..

echo "✅ Deployment package created in '$DEPLOY_DIR' directory"
echo ""
echo "📋 Next steps:"
echo "1. Create InfinityFree account at infinityfree.net"
echo "2. Create hosting account and MySQL database"
echo "3. Upload contents of '$DEPLOY_DIR' to htdocs/ via FTP"
echo "4. Update .env file with MySQL credentials"
echo "5. Run database-setup.php via browser"
echo ""
echo "🌍 Your LMS will be live at: https://yoursubdomain.epizy.com"