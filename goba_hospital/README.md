# Goba Hospital Patient Record Management System

A comprehensive web-based Patient Record Management System designed for healthcare facilities to efficiently manage patient information, medical records, and hospital operations.

## 🏥 Project Overview

The Goba Hospital Patient Record Management System is a full-featured healthcare management application that provides:

- **Patient Management**: Complete patient registration and medical history tracking
- **Doctor Portal**: Medical consultation recording, surgery documentation, and diagnosis management
- **Staff Portal**: Medication dosage management and patient care documentation
- **Admin Portal**: User management and system administration
- **External Portal**: Patient information transfer between healthcare facilities
- **Payment Processing**: Multi-bank payment integration for Ethiopian banks

## ✨ Features

### 🔐 Multi-User Authentication System
- **Admin**: System administration and user management
- **Doctor**: Patient consultation and medical record management
- **Patient**: Medical history viewing and payment processing
- **Staff**: Medication management and patient care documentation
- **External Health Office**: Patient information transfer

### 📋 Core Functionalities

#### Patient Management
- Complete patient registration with personal and medical information
- Medical history tracking and search functionality
- Emergency contact management
- Blood group and insurance information

#### Medical Records
- **Consultation Records**: Doctor-patient consultation documentation with audio recording
- **Surgery Records**: Detailed surgical procedure documentation
- **Diagnosis Records**: Medical diagnosis with ICD coding support
- **Medication Management**: Dosage tracking and administration schedules

#### Administrative Features
- User registration and management for all user types
- Hospital information management
- System activity logging and monitoring
- Data export and reporting capabilities

#### Payment System
- Multiple Ethiopian bank integration:
  - Commercial Bank of Ethiopia
  - Awash Bank
  - Abyssinia Bank
  - Telebirr mobile payment
- Payment tracking and receipt management

### 🔄 Advanced Features
- **Audio Recording**: Record consultations for better documentation
- **File Upload**: Support for medical documents and images
- **Patient Referrals**: Transfer patients between hospitals
- **Search & Filter**: Advanced search across all medical records
- **Real-time Notifications**: System alerts and updates
- **Responsive Design**: Mobile-friendly interface

## 🛠 Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework**: Bootstrap 5
- **Additional Libraries**:
  - jQuery for enhanced interactivity
  - Flatpickr for date/time selection
  - Font Awesome for icons

## 📁 Project Structure

```
goba_hospital/
├── admin/                      # Admin portal files
│   ├── dashboard.php          # Admin dashboard
│   ├── manage_patients.php    # Patient management
│   ├── manage_doctors.php     # Doctor management
│   ├── manage_staff.php       # Staff management
│   └── hospital_settings.php  # Hospital configuration
├── doctor/                    # Doctor portal files
│   ├── dashboard.php         # Doctor dashboard
│   ├── consultations.php     # Consultation management
│   ├── surgeries.php         # Surgery records
│   ├── diagnoses.php         # Diagnosis management
│   └── patients.php          # Patient search and management
├── patient/                   # Patient portal files
│   ├── dashboard.php         # Patient dashboard
│   ├── medical_history.php   # Medical history viewing
│   ├── payments.php          # Payment management
│   └── profile.php           # Profile management
├── staff/                     # Staff portal files
│   ├── dashboard.php         # Staff dashboard
│   ├── medications.php       # Medication management
│   └── profile.php           # Profile management
├── external/                  # External health office portal
│   ├── dashboard.php         # External dashboard
│   └── transfer_patient.php  # Patient transfer functionality
├── uploads/                   # File upload storage
│   ├── audio/                # Audio recordings
│   ├── files/                # General files
│   └── images/               # Image uploads
├── assets/                    # Static assets
│   ├── css/
│   │   └── style.css         # Main stylesheet
│   ├── js/
│   │   └── main.js           # Main JavaScript file
│   └── images/               # System images
├── includes/                  # Configuration and common files
│   ├── config.php            # Database configuration
│   ├── header.php            # Common header
│   └── footer.php            # Common footer
├── database/
│   └── schema.sql            # Database schema
├── index.php                 # Main landing page
├── login.php                 # Authentication page
├── logout.php                # Logout functionality
├── about.php                 # About page
└── README.md                 # This file
```

## 🚀 Installation

### Prerequisites

- Web server (Apache, Nginx, etc.)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Composer (optional, for dependency management)

### Step-by-Step Installation

1. **Clone or Download the Project**
   ```bash
   git clone <repository-url>
   # or download and extract the ZIP file
   ```

2. **Setup Web Server**
   - Copy the `goba_hospital` folder to your web server's document root
   - For XAMPP: `C:\xampp\htdocs\goba_hospital`
   - For WAMP: `C:\wamp64\www\goba_hospital`
   - For Linux: `/var/www/html/goba_hospital`

3. **Create Database**
   ```sql
   CREATE DATABASE goba_hospital;
   ```

4. **Import Database Schema**
   - Open phpMyAdmin or your MySQL client
   - Select the `goba_hospital` database
   - Import the `database/schema.sql` file

5. **Configure Database Connection**
   Edit `includes/config.php` and update the database settings:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USERNAME', 'your_username');
   define('DB_PASSWORD', 'your_password');
   define('DB_NAME', 'goba_hospital');
   ```

6. **Set Directory Permissions**
   ```bash
   chmod 755 goba_hospital/
   chmod 777 goba_hospital/uploads/
   chmod 777 goba_hospital/uploads/audio/
   chmod 777 goba_hospital/uploads/files/
   chmod 777 goba_hospital/uploads/images/
   ```

7. **Access the Application**
   - Open your browser and navigate to: `http://localhost/goba_hospital`
   - The system will display the home page with login options

## 🔑 Default Login Credentials

### Admin Account
- **Username**: `admin`
- **Password**: `admin123`
- **Access**: Full system administration

> **Note**: Other user accounts (doctors, patients, staff) must be created through the admin panel.

## 📖 User Guide

### For Administrators
1. Login with admin credentials
2. Use the dashboard to view system statistics
3. Manage users through the respective management pages
4. Configure hospital settings and system parameters

### For Doctors
1. Login using doctor credentials (created by admin)
2. Access patient records and medical histories
3. Record consultations, surgeries, and diagnoses
4. Manage patient referrals and transfers

### For Patients
1. Login using patient credentials (created by admin)
2. View complete medical history
3. Search and filter medical records
4. Process payments for medical services

### For Staff
1. Login using staff credentials (created by admin)
2. Manage medication dosages and administration
3. Document patient care activities
4. Access patient information as needed

## 🔧 Configuration

### Hospital Settings
Configure hospital information through the admin panel:
- Hospital name and contact information
- Operating hours and services
- License and certification details

### Payment Integration
To enable payment processing:
1. Contact Ethiopian banks for API credentials
2. Update payment configuration in `includes/config.php`
3. Test payment flows in development environment

### File Upload Settings
Modify upload settings in `includes/config.php`:
```php
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'mp3', 'wav']);
```

## 🔒 Security Features

- **Password Hashing**: All passwords are hashed using PHP's password_hash()
- **SQL Injection Prevention**: Prepared statements used throughout
- **XSS Protection**: Input sanitization and output escaping
- **Session Security**: Secure session management
- **Access Control**: Role-based permission system
- **Activity Logging**: Complete audit trail of system activities

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `includes/config.php`
   - Ensure MySQL service is running
   - Verify database exists and user has proper permissions

2. **File Upload Issues**
   - Check directory permissions on `uploads/` folder
   - Verify PHP upload settings in `php.ini`
   - Ensure file size limits are adequate

3. **Login Problems**
   - Clear browser cache and cookies
   - Verify user credentials in database
   - Check session configuration

4. **Permission Denied Errors**
   - Set proper file permissions (755 for directories, 644 for files)
   - Ensure web server has read/write access to necessary directories

## 📊 Database Schema

The system uses a comprehensive database schema with the following main tables:

- **User Tables**: `admin_login`, `doctor_login`, `patient_login`, `staff_login`, `external_login`
- **Information Tables**: `doctor_info`, `patient_info`, `staff_info`, `hospital_info`, `external_office`
- **Medical Records**: `consultation_records`, `surgery_records`, `diagnosis_records`
- **Support Tables**: `medication_dosage`, `patient_referrals`, `payment_info`, `file_uploads`
- **System Tables**: `system_logs`

## 🔄 Backup and Maintenance

### Regular Backups
1. **Database Backup**:
   ```bash
   mysqldump -u username -p goba_hospital > backup_$(date +%Y%m%d).sql
   ```

2. **File Backup**:
   ```bash
   tar -czf goba_hospital_files_$(date +%Y%m%d).tar.gz goba_hospital/uploads/
   ```

### Maintenance Tasks
- Regular database optimization
- Log file rotation and cleanup
- Security updates and patches
- Performance monitoring

## 🤝 Contributing

To contribute to this project:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📞 Support

For support and questions:
- **Hospital Contact**: +251-22-000-0000
- **Email**: info@gobahospital.com
- **Technical Support**: Available during business hours

## 📄 License

This project is developed for Goba Hospital and is intended for healthcare management purposes. Please contact the hospital administration for licensing and usage terms.

## 🌟 Acknowledgments

- Goba Hospital administration and staff
- Healthcare professionals who provided requirements
- Ethiopian healthcare standards and regulations
- Open source community for tools and libraries used

---

**Version**: 1.0  
**Last Updated**: 2024  
**Developed for**: Goba Hospital, Bale Zone, Oromia Region, Ethiopia