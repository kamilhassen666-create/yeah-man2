# Goba Hospital Patient Record Management System

A comprehensive web-based hospital management system built with PHP, MySQL, and modern web technologies. This system provides secure patient record management, doctor consultations, staff management, and administrative functions.

## 🏥 Features

### Core Functionality
- **Patient Portal**: View medical records, manage appointments, process payments
- **Doctor Portal**: Record consultations, surgeries, diagnoses, access patient history
- **Staff Portal**: Manage medication dosages and patient information
- **Admin Portal**: Complete system administration and user management
- **External Health Office Portal**: Upload and transfer patient information

### Key Features
- 🔐 **Secure Authentication**: Role-based access control for different user types
- 📊 **Comprehensive Records**: Patient consultations, surgeries, diagnoses, and payments
- 🎤 **Audio Recording**: Record consultation audio for better documentation
- 💳 **Payment Processing**: Multiple payment methods (Commercial Bank, Awash Bank, Abyssinia Bank, Telebirr)
- 🔍 **Advanced Search**: Search patients by ID, name, national ID, or phone
- 📈 **Analytics & Reporting**: Comprehensive system statistics and reports
- 📱 **Responsive Design**: Modern, mobile-friendly interface
- 🔄 **Referral System**: Patient referrals between hospitals and departments

## 🚀 Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache, Nginx, etc.)
- Composer (optional, for dependency management)

### Setup Instructions

1. **Clone or Download the Project**
   ```bash
   git clone <repository-url>
   cd goba_hospital
   ```

2. **Database Setup**
   ```bash
   # Create MySQL database
   mysql -u root -p
   CREATE DATABASE goba_hospital;
   USE goba_hospital;
   
   # Import the schema
   mysql -u root -p goba_hospital < database/schema.sql
   ```

3. **Configuration**
   - Edit `includes/config.php` with your database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'goba_hospital');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

4. **Web Server Configuration**
   - Point your web server to the project directory
   - Ensure PHP has write permissions for the `uploads/` directory

5. **Access the Application**
   - Navigate to `http://localhost/goba_hospital`
   - Use the demo accounts below to test different user types

## 👥 Demo Accounts

| User Type | Username | Password | Portal |
|-----------|----------|----------|---------|
| Admin | admin | admin123 | Admin Portal |
| Patient | abebe | abebe123 | Patient Portal |
| Doctor | dr.yohannes | doctor123 | Doctor Portal |
| Staff | nurse.bethel | staff123 | Staff Portal |

## 📁 Project Structure

```
goba_hospital/
├── admin/                 # Admin portal files
├── doctor/               # Doctor portal files
├── external/             # External health office portal
├── patient/              # Patient portal files
├── staff/                # Staff portal files
├── uploads/              # File uploads directory
│   ├── audio/           # Audio recordings
│   ├── files/           # Other files
│   └── images/          # Images
├── database/             # Database files
│   └── schema.sql       # Database schema
├── assets/               # Static assets
│   ├── css/             # Stylesheets
│   ├── js/              # JavaScript files
│   └── images/          # Images
├── includes/             # Common includes
│   ├── config.php       # Configuration
│   ├── header.php       # Header template
│   └── footer.php       # Footer template
├── index.php            # Main entry point
├── login.php            # Login page
├── logout.php           # Logout functionality
├── about.php            # About page
└── README.md            # This file
```

## 🔧 Configuration

### Database Configuration
Edit `includes/config.php` to match your database settings:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'goba_hospital');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### File Upload Settings
The system supports file uploads for:
- Audio recordings of consultations
- Patient documents
- Medical images

Ensure the `uploads/` directory and its subdirectories have proper write permissions.

## 🛡️ Security Features

- **Password Hashing**: All passwords are securely hashed using PHP's password_hash()
- **SQL Injection Prevention**: Prepared statements for all database queries
- **XSS Protection**: Input sanitization and output escaping
- **Session Management**: Secure session handling with proper validation
- **Role-based Access Control**: Different permissions for different user types

## 📊 Database Schema

The system includes the following main tables:
- `patients` - Patient information
- `doctors` - Doctor information
- `staff` - Medical staff information
- `consultations` - Medical consultations
- `surgeries` - Surgical procedures
- `diagnoses` - Medical diagnoses
- `payments` - Payment records
- `patient_referrals` - Patient referrals
- `hospitals` - Hospital information

## 🎨 User Interface

### Design Features
- **Modern UI**: Clean, professional design using Bootstrap 5
- **Responsive**: Works on desktop, tablet, and mobile devices
- **Accessibility**: WCAG compliant design elements
- **Dark Mode Support**: Optional dark theme for better user experience

### Color Scheme
- Primary: Blue (#0d6efd)
- Success: Green (#198754)
- Warning: Yellow (#ffc107)
- Danger: Red (#dc3545)
- Info: Cyan (#0dcaf0)

## 🔍 Search Functionality

The system provides comprehensive search capabilities:
- **Patient Search**: By ID, name, national ID, phone number
- **Medical Records**: Search consultations, surgeries, diagnoses
- **Payment History**: Search payment records
- **Real-time Search**: Instant results as you type

## 📱 Mobile Support

The system is fully responsive and optimized for:
- Desktop computers
- Tablets
- Mobile phones
- Touch devices

## 🚀 Performance Optimization

- **Database Indexing**: Optimized queries with proper indexing
- **Caching**: Session-based caching for frequently accessed data
- **Image Optimization**: Compressed images for faster loading
- **Minified Assets**: Compressed CSS and JavaScript files

## 🔧 Customization

### Adding New Features
1. Create new PHP files in appropriate portal directories
2. Add database tables if needed
3. Update navigation menus
4. Test thoroughly

### Styling Customization
Edit `assets/css/style.css` to customize:
- Colors and themes
- Layout and spacing
- Typography
- Component styles

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `includes/config.php`
   - Ensure MySQL service is running
   - Verify database exists

2. **File Upload Issues**
   - Check directory permissions for `uploads/`
   - Verify PHP upload settings in `php.ini`
   - Ensure sufficient disk space

3. **Session Issues**
   - Check PHP session configuration
   - Verify session directory permissions
   - Clear browser cookies if needed

### Error Logging
Enable error logging in `includes/config.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 📈 Monitoring and Maintenance

### Regular Tasks
- Database backups
- Log file rotation
- Security updates
- Performance monitoring

### Backup Strategy
```bash
# Database backup
mysqldump -u username -p goba_hospital > backup.sql

# File backup
tar -czf uploads_backup.tar.gz uploads/
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 📞 Support

For support and questions:
- Email: support@gobahospital.com
- Phone: +251-123-456-789
- Address: Goba, Ethiopia

## 🔄 Version History

- **v1.0.0** - Initial release with core functionality
- **v1.1.0** - Added audio recording feature
- **v1.2.0** - Enhanced payment processing
- **v1.3.0** - Improved search functionality
- **v1.4.0** - Added external health office portal

---

**Goba Hospital Patient Record Management System** - Streamlining healthcare delivery through innovative technology.