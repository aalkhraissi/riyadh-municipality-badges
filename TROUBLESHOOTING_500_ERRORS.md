# 🚨 Troubleshooting 500 Errors on Production Server

## 🔍 Quick Diagnosis Steps

### Step 1: Run Server Compatibility Check

```bash
# Open in browser
http://your-domain.com/check_server.php
```

This will check:

- ✅ PHP version compatibility
- ✅ Required PHP extensions
- ✅ File permissions
- ✅ Database connection
- ✅ PHP configuration

### Step 2: Enable Error Logging

Add this to the top of your PHP files temporarily:

```php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
```

### Step 3: Check Server Logs

```bash
# Apache error log (common locations)
tail -f /var/log/apache2/error.log
tail -f /var/log/httpd/error.log

# PHP error log
tail -f /var/log/php/error.log

# Nginx error log
tail -f /var/log/nginx/error.log
```

---

## 🐛 Most Common 500 Error Causes & Solutions

### 1. ❌ Missing PHP Extensions

**Symptoms:** PDO errors, JSON errors, etc.

**Check:**

```php
php -m | grep -E "(pdo|json|mbstring|curl)"
```

**Solutions:**

```bash
# Ubuntu/Debian
sudo apt-get install php8.1-mysql php8.1-json php8.1-mbstring php8.1-curl php8.1-xml

# CentOS/RHEL
sudo yum install php-pdo php-json php-mbstring php-curl php-xml

# Restart web server
sudo systemctl restart apache2
# or
sudo systemctl restart httpd
# or
sudo systemctl restart nginx
```

### 2. ❌ Database Connection Issues

**Symptoms:** "Connection failed" errors

**Check:**

```php
// Test in check_server.php
// Or manually:
<?php
require_once 'config/config.php';
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_usr, $db_password);
    echo "✅ Connected successfully";
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage();
}
```

**Solutions:**

- ✅ Verify database credentials in `config/config.php`
- ✅ Check if MySQL server is running: `sudo systemctl status mysql`
- ✅ Verify database exists: `mysql -u root -p -e "SHOW DATABASES;"`
- ✅ Check firewall: `sudo ufw status` or `sudo iptables -L`
- ✅ Verify user permissions: `mysql -u root -p -e "GRANT ALL ON records.* TO 'your_user'@'localhost';"`

### 3. ❌ File Permission Issues

**Symptoms:** "Permission denied" errors

**Check:**

```bash
ls -la /path/to/your/website/
```

**Solutions:**

```bash
# Set correct permissions
find /path/to/your/website/ -type f -exec chmod 644 {} \;
find /path/to/your/website/ -type d -exec chmod 755 {} \;

# For specific files that need write access
chmod 775 config/
chmod 664 config/config.php
chmod 775 uploads/  # if you have upload directory
```

### 4. ❌ PHP Version Incompatibility

**Symptoms:** Syntax errors, undefined functions

**Check:**

```bash
php --version
```

**Solutions:**

- ✅ Upgrade PHP to 7.4+ or 8.0+
- ✅ Check for deprecated functions
- ✅ Update PHP configuration in `.htaccess`

### 5. ❌ Memory/Execution Time Limits

**Symptoms:** "Allowed memory size exhausted" or timeout errors

**Check:**

```php
echo "Memory limit: " . ini_get('memory_limit') . "<br>";
echo "Max execution time: " . ini_get('max_execution_time') . "<br>";
```

**Solutions:**

```bash
# In .htaccess
php_value memory_limit 128M
php_value max_execution_time 300
php_value max_input_time 300

# Or in php.ini
memory_limit = 128M
max_execution_time = 300
max_input_time = 300
```

### 6. ❌ Missing Configuration Files

**Symptoms:** "config/config.php not found"

**Check:**

```bash
ls -la config/
```

**Solutions:**

```bash
# Create config directory
mkdir -p config
chmod 755 config

# Create config file
cp config/config.php.example config/config.php
chmod 644 config/config.php

# Edit with your database credentials
nano config/config.php
```

### 7. ❌ Database Table Issues

**Symptoms:** "Table doesn't exist" errors

**Check:**

```sql
mysql -u your_user -p your_database -e "SHOW TABLES;"
```

**Solutions:**

```bash
# Run setup script
php setup_database_cli.php

# Or manually create tables
mysql -u your_user -p your_database < database_schema.sql
```

### 8. ❌ Path Issues in Production

**Symptoms:** File not found errors

**Common Issues:**

- ✅ Absolute paths vs relative paths
- ✅ Case sensitivity differences
- ✅ Missing files after upload

**Solutions:**

```php
// Use absolute paths
define('BASE_PATH', dirname(__FILE__));
require_once BASE_PATH . '/config/config.php';

// Or use relative paths carefully
require_once './config/config.php';
```

---

## 🛠️ Quick Fix Commands

### For Ubuntu/Debian:

```bash
# Install required PHP extensions
sudo apt-get update
sudo apt-get install php8.1-mysql php8.1-json php8.1-mbstring php8.1-curl php8.1-xml php8.1-zip

# Restart web server
sudo systemctl restart apache2

# Check MySQL
sudo systemctl status mysql
sudo mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS records;"

# Fix permissions
sudo chown -R www-data:www-data /var/www/html/your-project/
sudo chmod -R 755 /var/www/html/your-project/
sudo chmod -R 775 /var/www/html/your-project/config/
```

### For CentOS/RHEL:

```bash
# Install PHP extensions
sudo yum install php-pdo php-json php-mbstring php-curl php-xml php-zip

# Restart web server
sudo systemctl restart httpd

# Check MySQL
sudo systemctl status mysqld
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS records;"

# Fix permissions
sudo chown -R apache:apache /var/www/html/your-project/
sudo chmod -R 755 /var/www/html/your-project/
sudo chmod -R 775 /var/www/html/your-project/config/
```

---

## 🔧 Advanced Debugging

### 1. Enable Detailed Error Logging

```php
// Add to top of index.php temporarily
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('error_log', '/var/log/php/your-project-errors.log');
```

### 2. Check PHP-FPM (if using)

```bash
# Check PHP-FPM status
sudo systemctl status php8.1-fpm

# Check PHP-FPM error log
sudo tail -f /var/log/php8.1-fpm.log
```

### 3. Test Individual Components

```php
// Create test file: test_components.php
<?php
echo "PHP Version: " . phpversion() . "<br>";
echo "PDO Available: " . (extension_loaded('pdo') ? 'Yes' : 'No') . "<br>";
echo "MySQL PDO: " . (extension_loaded('pdo_mysql') ? 'Yes' : 'No') . "<br>";

// Test database connection
try {
    require_once 'config/config.php';
    $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_usr, $db_password);
    echo "Database Connection: Success<br>";
} catch (Exception $e) {
    echo "Database Connection: Failed - " . $e->getMessage() . "<br>";
}
```

### 4. Check Web Server Configuration

```bash
# Apache
sudo apache2ctl configtest

# Nginx
sudo nginx -t
```

---

## 📞 Emergency Recovery

### If Site is Completely Down:

1. **Create a simple test page:**

```php
// emergency.php
<?php
phpinfo();
?>
```

2. **Check if PHP is working:**

   - Visit: `http://your-domain.com/emergency.php`

3. **Restore from backup if needed:**

```bash
# Restore files
cp -r /path/to/backup/* /var/www/html/

# Restore database
mysql -u root -p records < backup.sql
```

4. **Contact hosting provider:**
   - If you can't resolve the issue
   - For server-level problems

---

## 🎯 Prevention Tips

### 1. **Always Test Locally First**

```bash
# Use XAMPP, WAMP, or Docker for local testing
# Test all features before deploying
```

### 2. **Use Version Control**

```bash
git add .
git commit -m "Pre-deployment commit"
git push origin main
```

### 3. **Create Deployment Checklist**

- ✅ Backup database
- ✅ Backup files
- ✅ Test locally
- ✅ Check server compatibility
- ✅ Update configuration
- ✅ Test after deployment

### 4. **Monitor Logs Regularly**

```bash
# Check logs daily
tail -f /var/log/apache2/error.log
tail -f /var/log/php/error.log
```

---

## 📞 Support Resources

### Hosting-Specific Help:

- **cPanel:** Check PHP version in cPanel → Software → MultiPHP Manager
- **Plesk:** Check PHP settings in Plesk → PHP Settings
- **Shared Hosting:** Contact support for PHP extension installation

### Quick Contact:

- **Check server control panel**
- **Contact hosting provider support**
- **Check application logs**

---

**🚨 Remember:** Most 500 errors are caused by missing PHP extensions or database connection issues. Run `check_server.php` first to identify the exact problem! 🔍
