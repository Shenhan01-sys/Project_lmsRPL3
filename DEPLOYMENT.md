# 🚀 SmartDev Academic LMS - InfinityFree Deployment Guide

## 📋 Prerequisites
- InfinityFree account (100% free forever)
- FTP client (FileZilla recommended) 
- Basic understanding of web hosting

## 🎯 InfinityFree Deployment Steps

### Step 1: Create Account
1. Visit [infinityfree.net](https://infinityfree.net)
2. Click "Create Account"
3. Complete signup process
4. Verify email address

### Step 2: Create Hosting Account  
1. In iFastNet Control Panel, click "Create Account"
2. Choose subdomain: `yourname.free.nf` or `yourname.infinityfreeapp.com`
3. Set password for hosting account
4. Wait for account activation (1-2 minutes)

### Step 3: Database Setup
1. In Control Panel → MySQL Databases
2. Create new database: `smartdev_lms`
3. Create database user with full privileges
4. Note down: database name, username, password, hostname

### Step 4: Upload Files
1. Download `infinityfree-package.zip` from this repository
2. Extract the zip file
3. Update `.env` file with your database credentials
4. Upload all files to `htdocs` folder via:
   - **FTP Client** (FileZilla) - Recommended
   - **File Manager** in Control Panel
5. Set folder permissions: `storage/` and `bootstrap/cache/` to 755

### Step 5: Database Migration
1. Access your site: `http://yourname.free.nf`
2. Run initial setup by visiting: `/setup`
3. Or manually run migrations via online PHP executor

## 🔧 Configuration Files

### `.env` (Production)
```env
APP_NAME="SmartDev Academic LMS"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://yourname.free.nf

DB_CONNECTION=mysql
DB_HOST=sqlXXX.infinityfree.com
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password

SESSION_DRIVER=file
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
MAIL_MAILER=log
```

### `setup.php` (Database Setup)
Automated migration and seeding script for first-time setup.

### `htaccess.txt` → `.htaccess`
Laravel URL rewriting configuration for shared hosting.

## 🌟 Features Available
- ✅ Complete Laravel 12 LMS
- ✅ Student Registration System  
- ✅ Document Upload Management
- ✅ Admin Dashboard
- ✅ REST API Endpoints
- ✅ MySQL Database
- ✅ File Storage System

## 🔗 Access Points
- **Web Interface**: `http://yourname.free.nf`
- **Admin Panel**: `http://yourname.free.nf/login`
- **API Base**: `http://yourname.free.nf/api`

### Default Login
- **Admin**: admin@smartdev.com / password123
- **Test Student**: Register via web interface

## 📱 API Endpoints
- `POST /api/register-calon-siswa` - Student registration
- `POST /api/upload-documents` - Document upload
- `GET /api/registration-status/{userId}` - Registration status
- `GET /api/pending-registrations` - Admin pending list
- `POST /api/approve-registration` - Approve registration
- `POST /api/reject-registration` - Reject registration
- `GET /api/all-registrations` - All registrations

## 🛠️ Troubleshooting

### Common Issues
1. **500 Error**: Check file permissions (755 for folders, 644 for files)
2. **Database Connection**: Verify credentials in `.env`
3. **File Upload Issues**: Check `storage/app/public` permissions
4. **Session Issues**: Clear `storage/framework/sessions`

### Performance Tips
- Enable OPcache in Control Panel
- Use file-based caching (already configured)
- Optimize images before upload
- Regular database cleanup

## 📊 InfinityFree Limits
- **Storage**: 5GB
- **Bandwidth**: Unlimited
- **Databases**: 400 (MySQL)
- **Subdomains**: 400
- **File Upload**: 10MB max
- **PHP Memory**: 512MB

## 🔄 Updates & Maintenance
1. Download updated files from GitHub
2. Backup current installation
3. Upload new files via FTP
4. Run `/setup` for database updates
5. Clear cache if needed

---
**🎯 Status**: Ready for Beta Testing  
**💰 Cost**: 100% FREE Forever  
**📅 Setup Time**: 15-20 minutes  
**🔧 Version**: Laravel 12.x