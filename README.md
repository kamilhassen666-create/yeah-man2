# 🏥 Goba Hospital - Patient Record Management System

A comprehensive web-based Patient Record Management System designed for Goba Hospital, featuring multiple user portals for patients, doctors, medical staff, administrators, and external health offices.

## 📋 Features

### 🔐 Multi-Portal Authentication System
- **Patient Portal** - View medical records, search history, make payments
- **Doctor Portal** - Manage patient records, add consultations, view medical history
- **Medical Staff Portal** - Record medicine dosage, update patient information
- **Admin Portal** - User management, system administration
- **External Health Office Portal** - Upload and transfer patient information

### 📊 Core Functionalities
- **Medical Records Management** - Consultations, surgeries, diagnoses
- **Patient Information System** - Complete patient profiles with medical history
- **Search & Retrieval** - Advanced search by patient ID, name, date, reference number
- **Payment Processing** - Integration with Ethiopian banks (Commercial Bank, Awash Bank, Abyssinia Bank, Telebirr)
- **File Upload & Management** - Medical documents, X-rays, lab results
- **Audit Logging** - Complete user activity tracking
- **Real-time Updates** - Live data synchronization across all portals

## 🛠 Technologies Used

- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: PHP 7.4+
- **Database**: MySQL 8.0+
- **Styling**: Custom CSS with responsive design
- **Icons**: Font Awesome 6.0
- **Security**: Password hashing, SQL injection prevention, session management

## 📂 Project Structure

```
goba-hospital/
├── index.html              # Main homepage
├── setup.php               # Database setup script
├── README.md               # Project documentation
├── assets/
│   ├── css/
│   │   └── style.css       # Main stylesheet
│   └── js/
│       └── script.js       # JavaScript functionality
├── config/
│   ├── database.php        # Database configuration
│   └── setup_database.sql  # Database schema
├── includes/
│   └── functions.php       # Common PHP functions
├── patient/
│   ├── login.php          # Patient login
│   ├── dashboard.php      # Patient dashboard
│   └── logout.php         # Logout functionality
├── doctor/
│   └── login.php          # Doctor login
├── staff/
│   └── login.php          # Staff login
├── admin/
│   └── login.php          # Admin login
└── external/
    └── login.php          # External office login
```

## 🚀 Installation & Setup

### Prerequisites

- **Web Server** (Apache/Nginx)
- **PHP 7.4 or higher**
- **MySQL 8.0 or higher**
- **Web browser** (Chrome, Firefox, Safari, Edge)

### Step-by-Step Installation

1. **Clone or Download the Project**
   ```bash
   git clone <repository-url>
   # OR download and extract the ZIP file
   ```

2. **Set Up Web Server**
   - Place project files in your web server document root
   - Ensure PHP and MySQL are properly configured

3. **Configure Database Connection**
   - Edit `config/database.php`
   - Update database credentials:
   ```php
   define('DB_HOST', 'localhost');     // Your MySQL host
   define('DB_USER', 'your_username'); // Your MySQL username
   define('DB_PASS', 'your_password'); // Your MySQL password
   define('DB_NAME', 'goba_hospital'); // Database name
   ```

4. **Initialize Database**
   - Navigate to `http://your-domain/setup.php`
   - This will create the database schema and insert sample data
   - Follow the on-screen instructions

5. **Access the System**
   - Visit `http://your-domain/index.html`
   - Use the provided demo credentials to test different portals

## 🔑 Demo Credentials

### Admin Portal
- **Username**: `admin`
- **Password**: `admin123`

### Patient Portal
- **Username**: `abebe_kebede`
- **Password**: `password123`

### Doctor Portal
- **Username**: `dr_getachew`
- **Password**: `password123`

### Staff Portal
- **Username**: `almaz_demisse`
- **Password**: `password123`

## 🗄 Database Schema

### Core Tables
- **hospital** - Hospital information
- **patient** - Patient records
- **doctor** - Doctor profiles
- **medical_staff** - Medical staff information
- **consultation** - Patient consultations
- **operation** - Surgical procedures
- **diagnosis** - Medical diagnoses
- **medical_administration** - Medicine dosage records
- **payment** - Payment transactions
- **file_uploads** - Medical documents
- **audit_log** - System activity logs

### Authentication Tables
- **patient_login** - Patient authentication
- **doctor_login** - Doctor authentication
- **staff_login** - Staff authentication
- **admin_login** - Admin authentication
- **external_health_office** - External office authentication

## 👥 User Roles & Permissions

### Patient
- View personal medical records
- Search medical history
- Make payments
- Update profile information
- Download medical documents

### Doctor
- View/edit patient records
- Add consultations, diagnoses, operations
- Search patient database
- Upload medical files
- Generate medical reports

### Medical Staff
- Record medicine administration
- Update patient information
- View assigned patient records
- Manage medication inventory

### Administrator
- User management (create/edit/delete users)
- System configuration
- View audit logs
- Manage hospital information
- Generate system reports

### External Health Office
- Upload patient information
- Transfer patients to other hospitals
- Share medical records
- Generate transfer reports

## 🔧 Configuration

### Environment Setup
1. Ensure PHP extensions are enabled:
   - PDO MySQL
   - JSON
   - Session
   - File Upload

2. Configure PHP settings:
   ```ini
   upload_max_filesize = 10M
   post_max_size = 10M
   max_execution_time = 300
   memory_limit = 128M
   ```

3. Set appropriate file permissions:
   ```bash
   chmod 755 /path/to/project
   chmod 644 *.php
   chmod 777 uploads/ (if exists)
   ```

## 🔒 Security Features

- **Password Hashing** - Secure password storage using PHP's password_hash()
- **SQL Injection Prevention** - Prepared statements for all database queries
- **Session Management** - Secure session handling with timeout
- **Input Validation** - Server-side validation for all user inputs
- **Access Control** - Role-based access restrictions
- **Audit Logging** - Complete activity tracking
- **File Upload Security** - File type and size validation

## 🌍 Payment Integration

The system supports Ethiopian banking integration:

- **Commercial Bank of Ethiopia**
- **Awash Bank**
- **Abyssinia Bank**
- **Telebirr** (Mobile payment)

Payment methods include:
- Credit/Debit Cards
- Mobile Payments
- Bank Transfers
- Cash Payments

## 📱 Responsive Design

The system is fully responsive and works on:
- Desktop computers
- Tablets
- Mobile phones
- Various screen sizes and orientations

## 🔄 API Integration

The system is designed with future API integration in mind:
- RESTful architecture
- JSON response format
- Modular design for easy extension
- External system integration capabilities

## 📈 Future Enhancements

Planned features for future versions:
- Mobile app development
- Telemedicine integration
- AI-powered diagnosis assistance
- Integration with medical devices
- Advanced reporting and analytics
- Multi-language support
- SMS/Email notifications

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `config/database.php`
   - Ensure MySQL server is running
   - Verify database exists

2. **PHP Errors**
   - Check PHP error logs
   - Ensure all required PHP extensions are installed
   - Verify file permissions

3. **Login Issues**
   - Clear browser cache and cookies
   - Check if user account is active
   - Verify credentials are correct

4. **File Upload Problems**
   - Check PHP upload settings
   - Verify directory permissions
   - Ensure file size is within limits

## 📞 Support

For technical support or questions about the system:

- **Hospital IT Department**: it@gobahospital.et
- **System Administrator**: admin@gobahospital.et
- **Emergency Support**: +251-22-XXX-XXXX

## 📜 License

This project is proprietary software developed for Goba Hospital. All rights reserved.

## 👨‍💻 Development Team

- **Project Lead**: [Your Name]
- **Backend Development**: PHP/MySQL Implementation
- **Frontend Development**: HTML/CSS/JavaScript
- **Database Design**: MySQL Schema Design
- **Testing**: System Integration Testing

## 📊 System Statistics

- **Database Tables**: 15+ core tables
- **User Roles**: 5 distinct roles
- **File Types Supported**: PDF, DOC, DOCX, JPG, PNG
- **Maximum File Size**: 10MB
- **Session Timeout**: 30 minutes
- **Password Requirements**: Minimum 6 characters

## 🎯 Project Goals

1. **Digitize Medical Records** - Replace paper-based systems
2. **Improve Efficiency** - Reduce medical decision-making time
3. **Enhance Security** - Secure patient data management
4. **Enable Collaboration** - Multi-hospital integration
5. **Support Growth** - Scalable system architecture

---

**Version**: 1.0.0  
**Last Updated**: January 2024  
**Compatibility**: PHP 7.4+, MySQL 8.0+, Modern Browsers