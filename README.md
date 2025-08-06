# Goba Hospital Patient Record Management System

A comprehensive, web-based patient record management system designed specifically for Goba Hospital in Ethiopia. This system provides secure, efficient digital healthcare record management with support for multiple user types and Ethiopian banking integration.

## 🏥 Features

### Multi-User Portal System
- **Patient Portal**: View medical records, manage personal information, search medical history
- **Doctor Portal**: Manage patient records, consultations, diagnoses, surgical information
- **Medical Staff Portal**: Record medicine administration, patient care management
- **Admin Portal**: User management, system administration, reporting
- **External Health Office Portal**: Upload patient information, coordinate inter-hospital transfers

### Core Functionality
- 📋 **Digital Medical Records**: Replace paper-based systems with secure digital records
- 🔒 **Secure Authentication**: Multi-level access control with role-based permissions
- 🔍 **Advanced Search**: Find patient information by ID, date, or reference number
- 🎵 **Audio Recording Support**: Record consultation audio for detailed documentation
- 💰 **Payment Integration**: Support for Ethiopian banks (CBE, Awash, Abyssinia, Telebirr)
- 📱 **Responsive Design**: Access from any device, anywhere
- 🏥 **Hospital Transfer Management**: Coordinate patient transfers between facilities

### Medical Records Management
- **Consultation Records**: Complete consultation documentation with audio support
- **Surgery Management**: Detailed surgical procedure tracking and post-operative care
- **Diagnosis Tracking**: ICD-10 compatible diagnosis management with severity levels
- **Medication Administration**: Staff-managed medicine dosage and allergy tracking
- **Laboratory Results**: Integration of lab results and imaging reports

## 🚀 Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: PHP 7.4+
- **Database**: MySQL 8.0+
- **Styling**: Custom CSS with Ethiopian flag color scheme
- **Icons**: Font Awesome 6.0
- **Security**: Password hashing, SQL injection prevention, XSS protection

## 📋 Prerequisites

- PHP 7.4 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server
- Composer (for dependency management)

## 🛠️ Installation

### 1. Clone the Repository
```bash
git clone <repository-url>
cd goba-hospital-system
```

### 2. Database Setup
```bash
# Login to MySQL
mysql -u root -p

# Create database and import schema
mysql -u root -p < database/schema.sql
```

### 3. Configure Database Connection
Edit `config/database.php` with your database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'goba_hospital_db');
```

### 4. Set File Permissions
```bash
chmod 755 /workspace
chmod 644 /workspace/*.php
chmod 755 /workspace/uploads
```

### 5. Web Server Configuration

#### Apache (.htaccess)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

#### Nginx
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

## 🔑 Default Admin Credentials

- **Username**: `admin`
- **Password**: `password`

**⚠️ Important**: Change these credentials immediately after first login!

## 📁 Project Structure

```
/workspace/
├── admin/              # Admin portal pages
│   ├── dashboard.php   # Admin dashboard
│   └── register.php    # User registration
├── assets/             # Static assets
│   ├── css/           # Stylesheets
│   ├── js/            # JavaScript files
│   └── images/        # Image assets
├── auth/              # Authentication system
│   ├── login.php      # Login page
│   └── logout.php     # Logout handler
├── config/            # Configuration files
│   └── database.php   # Database configuration
├── database/          # Database files
│   └── schema.sql     # Database schema
├── doctor/            # Doctor portal (to be implemented)
├── external/          # External health office portal (to be implemented)
├── includes/          # Shared PHP includes
│   └── auth.php       # Authentication class
├── patient/           # Patient portal
│   └── dashboard.php  # Patient dashboard
├── staff/             # Medical staff portal (to be implemented)
├── uploads/           # File uploads directory
├── index.php          # Main homepage
└── README.md          # This file
```

## 🎨 Design Features

### Ethiopian Cultural Integration
- Color scheme inspired by Ethiopian flag (Green, Yellow, Red)
- Support for local languages and cultural preferences
- Integration with Ethiopian banking systems

### Modern UI/UX
- Responsive design for mobile and desktop
- Intuitive navigation with role-based menus
- Accessibility features for keyboard navigation
- Print-friendly pages for medical records

## 🔐 Security Features

- **Password Security**: Bcrypt hashing with salt
- **Session Management**: Secure session handling with timeout
- **Input Validation**: Server-side validation and sanitization
- **SQL Injection Prevention**: Prepared statements throughout
- **Access Control**: Role-based access restrictions
- **Account Lockout**: Automatic lockout after failed login attempts

## 📊 Database Schema

### Core Tables
- `patient` - Patient information and demographics
- `doctor` - Doctor profiles and specializations
- `medical_staff` - Medical staff information
- `hospital` - Hospital/facility information

### Medical Records
- `consultation` - Patient consultations with audio support
- `operation` - Surgical procedures and outcomes
- `diagnosis` - Medical diagnoses with ICD-10 codes
- `medical_administration` - Medicine administration records

### System Tables
- `*_login` - Authentication tables for each user type
- `payments` - Payment processing and billing
- `hospital_transfers` - Inter-hospital patient transfers
- `patient_file_uploads` - Document and image uploads

## 🚀 Usage

### Admin Tasks
1. **User Registration**: Register patients, doctors, and staff
2. **User Management**: View, edit, and deactivate user accounts
3. **System Monitoring**: View system statistics and activity logs
4. **Report Generation**: Generate various medical and administrative reports

### Doctor Workflow
1. **Patient Search**: Find patients by ID, name, or reference number
2. **Record Consultation**: Document patient visits with audio recording
3. **Manage Diagnoses**: Record diagnoses with ICD-10 codes
4. **Surgery Documentation**: Record surgical procedures and outcomes

### Patient Experience
1. **View Records**: Access complete medical history
2. **Search History**: Find specific consultations or treatments
3. **Profile Management**: Update personal and contact information
4. **Payment History**: View billing and payment records

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 Development Roadmap

### Phase 1 (Completed)
- ✅ Database schema design
- ✅ Authentication system
- ✅ Basic admin portal
- ✅ User registration system
- ✅ Responsive design framework

### Phase 2 (In Progress)
- 🔄 Complete doctor portal
- 🔄 Patient records management
- 🔄 Medical staff portal
- 🔄 Search functionality

### Phase 3 (Planned)
- ⏳ Payment processing integration
- ⏳ External health office portal
- ⏳ Advanced reporting system
- ⏳ Mobile app development

### Phase 4 (Future)
- ⏳ API development
- ⏳ Third-party integrations
- ⏳ Advanced analytics
- ⏳ Telemedicine features

## 📞 Support

For technical support or questions:
- Email: info@gobahospital.et
- Phone: +251-22-661-0001
- Address: Goba, Bale Zone, Oromia Region, Ethiopia

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Goba Hospital administration and medical staff
- Ethiopian Ministry of Health guidelines
- Open source community contributors
- Font Awesome for iconography

---

**Built with ❤️ for Ethiopian Healthcare**