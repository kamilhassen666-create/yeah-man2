# Goba Hospital Patient Record Management System

A comprehensive web-based patient record management system designed for healthcare facilities to efficiently manage patient information, medical records, consultations, surgeries, and payments.

## 🏥 Features

### Core Functionality
- **Multi-User Portal System**: Separate interfaces for patients, doctors, staff, administrators, and external health offices
- **Patient Record Management**: Comprehensive patient information storage and retrieval
- **Medical Records**: Consultations, surgeries, diagnoses, and medication tracking
- **Audio Recording**: Support for consultation audio recordings
- **Payment Processing**: Multiple payment methods (Commercial Bank, Awash Bank, Abyssinia Bank, Telebirr)
- **Referral System**: Patient referrals between hospitals
- **Search & Filter**: Advanced search functionality for patient records
- **File Upload**: Support for medical documents and images
- **Real-time Notifications**: System alerts and status updates

### User Portals

#### 👤 Patient Portal
- View personal medical records
- Access consultation history
- View surgeries and diagnoses
- Process payments
- Update personal information
- Search medical records

#### 👨‍⚕️ Doctor Portal
- Record consultations with audio support
- Add surgery information
- Create diagnoses
- Search patient medical history
- View patient referrals
- Manage patient records

#### 👩‍⚕️ Staff Portal
- Manage medication dosages
- Access patient information
- Record medication schedules
- Search patient records
- View patient activities

#### 🛡️ Admin Portal
- User management (patients, doctors, staff)
- Hospital information management
- System administration
- Reports and analytics
- Database management

#### 🏢 External Health Office Portal
- Upload patient information
- Track upload status
- Manage external patient data
- File upload capabilities

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework**: Bootstrap 5.3
- **Icons**: Font Awesome 6.0
- **Date Picker**: Flatpickr
- **Security**: Password hashing, SQL injection prevention

## 📋 Requirements

### Server Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- PHP extensions: PDO, PDO_MySQL, mbstring

### Browser Support
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## 🚀 Installation

### Step 1: Clone or Download
```bash
git clone <repository-url>
cd goba_hospital
```

### Step 2: Database Setup
1. Create a MySQL database named `goba_hospital`
2. Import the database schema:
```bash
mysql -u root -p goba_hospital < database/schema.sql
```

### Step 3: Configuration
1. Update database connection settings in `includes/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'goba_hospital');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

2. Update the site URL in `includes/config.php`:
```php
define('SITE_URL', 'http://your-domain.com/goba_hospital');
```

### Step 4: File Permissions
Ensure the upload directories are writable:
```bash
chmod 755 uploads/
chmod 755 uploads/audio/
chmod 755 uploads/files/
chmod 755 uploads/images/
```

### Step 5: Web Server Configuration
Configure your web server to point to the project directory and ensure PHP is enabled.

## 👥 Default Login Accounts

### Admin Account
- **Username**: `admin`
- **Password**: `admin123`

### Sample Patient Accounts
- **Username**: `abebe`
- **Password**: `abebe123`

- **Username**: `fatima`
- **Password**: `fatima123`

### Sample Doctor Accounts
- **Username**: `dr.yohannes`
- **Password**: `doctor123`

- **Username**: `dr.aisha`
- **Password**: `doctor123`

### Sample Staff Accounts
- **Username**: `nurse.bethel`
- **Password**: `staff123`

- **Username**: `pharmacy.tekle`
- **Password**: `staff123`

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
│   ├── files/           # Document uploads
│   └── images/          # Image uploads
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
├── index.php            # Main landing page
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
Configure upload directories and file size limits in `includes/config.php`:

```php
define('UPLOAD_DIR', '../uploads/');
define('AUDIO_DIR', UPLOAD_DIR . 'audio/');
define('FILES_DIR', UPLOAD_DIR . 'files/');
define('IMAGES_DIR', UPLOAD_DIR . 'images/');
```

## 🔒 Security Features

- **Password Hashing**: All passwords are hashed using PHP's password_hash()
- **SQL Injection Prevention**: Prepared statements for all database queries
- **Input Sanitization**: All user inputs are sanitized
- **Session Management**: Secure session handling
- **Role-based Access Control**: Different access levels for different user types
- **File Upload Security**: Restricted file types and size limits

## 📊 Database Schema

The system uses a comprehensive database schema with the following main tables:

- **patients**: Patient information
- **doctors**: Doctor information
- **staff**: Medical staff information
- **consultations**: Medical consultations
- **surgeries**: Surgical procedures
- **diagnoses**: Medical diagnoses
- **payments**: Payment records
- **hospitals**: Hospital information
- **external_patient_info**: External patient data

## 🎨 Customization

### Styling
Modify `assets/css/style.css` to customize the appearance:

```css
:root {
    --primary-color: #0d6efd;
    --secondary-color: #6c757d;
    /* Add your custom colors */
}
```

### JavaScript
Add custom functionality in `assets/js/script.js`:

```javascript
// Add your custom JavaScript functions
function customFunction() {
    // Your code here
}
```

## 🚨 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Verify database credentials in `includes/config.php`
   - Ensure MySQL service is running
   - Check if database exists

2. **File Upload Issues**
   - Verify upload directory permissions
   - Check PHP upload settings in php.ini
   - Ensure sufficient disk space

3. **Session Issues**
   - Check PHP session configuration
   - Verify session directory permissions
   - Clear browser cookies

4. **Audio Recording Not Working**
   - Ensure HTTPS is enabled (required for microphone access)
   - Check browser permissions for microphone
   - Verify browser compatibility

## 📞 Support

For technical support or questions:

- **Email**: support@gobahospital.com
- **Phone**: +251-123-456-789
- **Address**: Goba, Ethiopia

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📈 Future Enhancements

- Mobile app development
- API integration
- Advanced analytics
- Machine learning for diagnosis assistance
- Telemedicine features
- Integration with laboratory systems
- Electronic prescription system

## 🙏 Acknowledgments

- Bootstrap for the responsive framework
- Font Awesome for the icons
- Flatpickr for the date picker
- All contributors and testers

---

**Note**: This system is designed for educational and demonstration purposes. For production use, additional security measures and compliance with healthcare regulations should be implemented.