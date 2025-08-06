# Goba Hospital Patient Record Management System

A comprehensive, modern web-based patient record management system designed for Goba Hospital in Ethiopia. This system provides secure, efficient management of medical records with multiple user portals for patients, doctors, medical staff, administrators, and external health offices.

## 🏥 Features

### Multi-Portal System
- **Patient Portal**: View medical records, consultation history, payments
- **Doctor Portal**: Record consultations, manage surgeries, document diagnoses
- **Medical Staff Portal**: Record medicine administration, patient care documentation
- **Admin Portal**: User management, system oversight, record management
- **External Health Office Portal**: Inter-hospital communication, patient referrals

### Core Functionality
- ✅ Comprehensive medical record management
- ✅ Advanced search and filtering capabilities
- ✅ Secure authentication with role-based access control
- ✅ Payment processing for multiple Ethiopian banks
- ✅ Audio recording support for consultations
- ✅ Document upload and management
- ✅ Emergency access features
- ✅ Responsive design for all devices
- ✅ Real-time notifications
- ✅ Data export capabilities

### Supported Medical Records
- Consultations with audio recording
- Surgical operations and procedures
- Medical diagnoses with ICD codes
- Medicine administration records
- Patient referrals between hospitals
- Payment transactions
- Document uploads (PDF, JPG, PNG, DOC, DOCX)

## 📋 System Requirements

### Server Requirements
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **PHP**: Version 7.4 or higher (PHP 8.0+ recommended)
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **Storage**: Minimum 500MB free space (1GB+ recommended)
- **Memory**: 256MB+ RAM allocated to PHP

### PHP Extensions Required
```
- pdo
- pdo_mysql
- mbstring
- openssl
- json
- fileinfo
- gd (for image processing)
```

### Browser Compatibility
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile browsers with JavaScript support

## 🚀 Installation

### Step 1: Clone the Repository
```bash
git clone https://github.com/your-username/goba-hospital.git
cd goba-hospital
```

### Step 2: Configure Database
1. Create a new MySQL database:
```sql
CREATE DATABASE goba_hospital;
```

2. Import the database schema:
```bash
mysql -u username -p goba_hospital < sql/database_schema.sql
```

3. Update database configuration in `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'goba_hospital');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### Step 3: Set Permissions
```bash
# Set proper permissions for uploads directory
chmod 755 uploads/
chown -R www-data:www-data uploads/

# Set permissions for configuration files
chmod 644 config/database.php
```

### Step 4: Configure Virtual Host

#### Apache Configuration
```apache
<VirtualHost *:80>
    ServerName gobahospital.local
    DocumentRoot /path/to/goba-hospital
    
    <Directory /path/to/goba-hospital>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # Security headers
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
</VirtualHost>
```

#### Nginx Configuration
```nginx
server {
    listen 80;
    server_name gobahospital.local;
    root /path/to/goba-hospital;
    index index.php index.html;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Security
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
}
```

### Step 5: SSL Configuration (Production)
```bash
# Using Let's Encrypt
certbot --apache -d yourdomain.com
# or for Nginx
certbot --nginx -d yourdomain.com
```

## 👤 Default Admin Account

After installation, you can log in using the default admin account:
- **User ID**: `admin`
- **Password**: `password`

**⚠️ IMPORTANT**: Change the default password immediately after first login!

## 🔧 Configuration

### Application Settings
Edit `config/database.php` to customize:

```php
// Site settings
define('SITE_URL', 'https://yourdomain.com/');
define('SITE_NAME', 'Your Hospital Name');

// Upload settings
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);

// Security settings
define('SESSION_TIMEOUT', 3600); // 1 hour
define('PASSWORD_MIN_LENGTH', 8);
```

### Email Configuration (Optional)
For notification features, configure SMTP settings in `config/email.php`:

```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
```

## 📖 User Guide

### For Administrators

#### Registering New Users
1. Log in to Admin Portal
2. Navigate to "Register" section
3. Fill in user details
4. Select user type (Patient, Doctor, Staff, External Office)
5. Generate secure login credentials

#### Managing Hospital Data
- Add/edit hospital information
- Manage user accounts
- Generate system reports
- Monitor system activity

### For Doctors

#### Recording Patient Consultations
1. Access Doctor Portal
2. Go to "Insert" > "Consultation"
3. Select patient by SSN
4. Record consultation details
5. Optional: Upload audio recording
6. Save with generated reference number

#### Managing Patient Records
- Search patient history
- View comprehensive medical records
- Update diagnoses and treatments
- Schedule follow-up appointments

### For Patients

#### Viewing Medical Records
1. Log in to Patient Portal
2. Navigate to "Medical Records"
3. Filter by type (Consultations, Operations, Diagnoses)
4. Click "View" for detailed information

#### Making Payments
1. Go to "Payments" section
2. Select payment type
3. Choose bank (Commercial Bank, Awash Bank, Abyssinia Bank, Telebirr)
4. Complete transaction

## 🔒 Security Features

### Authentication
- Bcrypt password hashing with configurable cost
- Session-based authentication with timeout
- Role-based access control
- Login attempt monitoring

### Data Protection
- SQL injection prevention using prepared statements
- XSS protection with input sanitization
- CSRF protection for forms
- Secure file upload validation

### Privacy
- Patient data encryption at rest
- Audit logs for all medical record access
- HIPAA-compliant data handling
- Secure inter-hospital communication

## 🔧 Maintenance

### Regular Tasks
```bash
# Backup database daily
mysqldump -u username -p goba_hospital > backup_$(date +%Y%m%d).sql

# Clear temporary files weekly
find uploads/temp -type f -mtime +7 -delete

# Update system logs
tail -f /var/log/apache2/error.log
```

### Performance Optimization
- Enable PHP OPcache
- Configure MySQL query cache
- Use CDN for static assets
- Implement database indexing

### Monitoring
- Monitor disk space in uploads directory
- Check database performance
- Review error logs regularly
- Monitor user activity

## 🚨 Troubleshooting

### Common Issues

#### "Database connection failed"
- Check database credentials in `config/database.php`
- Verify MySQL service is running
- Check database user permissions

#### "Permission denied" on file uploads
```bash
chmod 755 uploads/
chown -R www-data:www-data uploads/
```

#### Session timeout issues
- Increase session timeout in `config/database.php`
- Check PHP session configuration
- Verify session storage permissions

#### Slow performance
- Enable MySQL slow query log
- Optimize database indices
- Check server resources

### Debug Mode
Enable debug mode for development:
```php
// In config/database.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

**⚠️ Disable debug mode in production!**

## 📞 Support

### Technical Support
- **Phone**: +251-11-1234567
- **Email**: support@gobahospital.et
- **Documentation**: [Wiki Pages]

### Emergency Contact
- **24/7 Hotline**: +251-11-9876543
- **Emergency Email**: emergency@gobahospital.et

## 🤝 Contributing

We welcome contributions to improve the system:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

### Development Setup
```bash
# Clone for development
git clone https://github.com/your-username/goba-hospital.git
cd goba-hospital

# Set up local environment
cp config/database.example.php config/database.php
# Edit configuration

# Set up test database
mysql -u root -p -e "CREATE DATABASE goba_hospital_test;"
mysql -u root -p goba_hospital_test < sql/database_schema.sql
```

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Goba Hospital medical staff for requirements and testing
- Ethiopian Ministry of Health for healthcare standards compliance
- Open source community for libraries and frameworks used

---

## 📚 Additional Documentation

- [API Documentation](docs/api.md)
- [Database Schema](docs/database.md)
- [Security Guidelines](docs/security.md)
- [Deployment Guide](docs/deployment.md)

---

**Version**: 1.0.0  
**Last Updated**: 2024  
**Developed for**: Goba Hospital, Ethiopia