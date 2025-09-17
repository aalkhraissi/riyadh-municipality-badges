# 🗄️ Database Setup Guide

## 📋 Overview

This guide explains how to set up the database for the **Riyadh Municipality Control System**. The setup script creates all necessary tables and inserts sample data to get you started quickly.

## 🚀 Quick Start

### Step 1: Run the Setup Script

1. **Open your web browser**
2. **Navigate to**: `http://localhost/your-project-folder/setup_database.php`
3. **Wait for the script to complete** (it will show progress and results)

### Step 2: Verify Setup

The script will create:

- ✅ Database (if it doesn't exist)
- ✅ All required tables
- ✅ Sample data (users, branches, records)
- ✅ Default admin account

### Step 3: Clean Up

**Important Security Step:**

```bash
# Delete the setup file after successful setup
rm setup_database.php
```

### Step 4: Access the Application

1. **Go to**: `http://localhost/your-project-folder/index.php`
2. **Login with admin credentials**:
   - **Username**: `admin`
   - **Password**: `admin123`

## 📊 Database Tables Created

| Table           | Purpose                          | Key Features                   |
| --------------- | -------------------------------- | ------------------------------ |
| `users`         | User authentication & management | Roles, branch access, sessions |
| `branches`      | Branch/division management       | Location, description, status  |
| `records`       | Main employee records            | Complete staff information     |
| `roles`         | Role-based access control        | Permissions, user roles        |
| `user_sessions` | Session management               | Security, login tracking       |
| `audit_log`     | Change tracking                  | Audit trail, data integrity    |

## 👥 Default Users Created

| Username   | Password     | Role    | Description        |
| ---------- | ------------ | ------- | ------------------ |
| `admin`    | `admin123`   | Admin   | Full system access |
| `manager1` | `manager123` | Manager | Branch management  |
| `user1`    | `user123`    | User    | Basic access       |

## 🏢 Sample Branches Created

- **المركز الرئيسي** (Main Center) - Central Riyadh
- **فرع الشمال** (North Branch) - North Riyadh
- **فرع الجنوب** (South Branch) - South Riyadh
- **فرع الشرق** (East Branch) - East Riyadh
- **فرع الغرب** (West Branch) - West Riyadh

## 📄 Sample Records Created

The script creates 5 sample employee records with:

- Complete Arabic names
- Email addresses
- Department information
- Branch assignments
- Realistic data for testing

## 🔧 Configuration

### Database Settings

Edit `config/config.php` to match your environment:

```php
$db_host = "127.0.0.1";      // Your MySQL host
$db_name = "records";        // Database name
$db_usr = "root";           // MySQL username
$db_password = "";          // MySQL password
```

### MySQL Requirements

- **MySQL 5.7+** or **MariaDB 10.0+**
- **UTF8MB4 character set** support
- **InnoDB storage engine**

## 🛠️ Manual Setup (Alternative)

If you prefer to set up manually:

### 1. Create Database

```sql
CREATE DATABASE records CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Run Individual Table Scripts

Copy and run the SQL from `setup_database.php` for each table:

```sql
-- Copy the CREATE TABLE statements from setup_database.php
-- Run them in your MySQL client
```

### 3. Insert Sample Data

```sql
-- Copy the INSERT statements from setup_database.php
-- Run them to populate sample data
```

## 🔒 Security Considerations

### After Setup:

1. ✅ **Delete** `setup_database.php`
2. ✅ **Change default passwords**
3. ✅ **Review user permissions**
4. ✅ **Configure proper database user** (not root)

### Production Setup:

- Use dedicated database user with limited privileges
- Enable SSL connections
- Regular backup procedures
- Monitor audit logs

## 🐛 Troubleshooting

### Common Issues:

#### 1. Permission Denied

```
Solution: Grant proper permissions to MySQL user
GRANT ALL PRIVILEGES ON records.* TO 'your_user'@'localhost';
```

#### 2. Database Already Exists

```
Solution: The script handles this automatically with IF NOT EXISTS
```

#### 3. Character Encoding Issues

```
Solution: Ensure MySQL supports UTF8MB4
SHOW VARIABLES LIKE 'character_set%';
```

#### 4. Connection Failed

```
Solution: Check MySQL service is running and credentials are correct
```

## 📞 Support

If you encounter issues:

1. **Check the browser output** from `setup_database.php`
2. **Verify MySQL credentials** in `config/config.php`
3. **Check MySQL error logs**
4. **Ensure PHP has PDO MySQL extension**

## 🎯 Next Steps

After setup is complete:

1. **Login as admin** and explore the system
2. **Customize branches** to match your organization
3. **Add real users** and assign appropriate roles
4. **Import your data** using the CSV import feature
5. **Configure permissions** based on your needs

---

**🎉 Happy coding with Riyadh Municipality Control System!**
