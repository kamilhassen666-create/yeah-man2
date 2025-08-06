# Goba Hospital Patient Record Management System

A comprehensive hospital management system built with PHP, MySQL, HTML, CSS, and JavaScript for managing patient records, medical consultations, operations, diagnoses, and staff administration.

## Features

### 🏥 Multi-Portal System
- **Patient Portal**: View medical records, search history, manage profile
- **Doctor Portal**: Manage patient records, add consultations, operations, and diagnoses
- **Medical Staff Portal**: Record medicine administration and dosage information
- **Admin Portal**: User management, registration, and system oversight
- **External Health Office Portal**: Patient transfer between healthcare facilities

### 📋 Core Functionality
- Complete patient record management
- Medical consultation tracking with audio support
- Surgery/operation management
- Diagnosis and treatment planning
- Medicine administration tracking
- Payment processing (Commercial Bank, Awash Bank, Abyssinia Bank, Telebirr)
- File upload support (PDF, JPG, PNG)
- Advanced search and filtering
- Reference number system for easy record retrieval

### 🔒 Security Features
- User authentication for all portal types
- Session management
- Password hashing
- Input validation and sanitization
- SQL injection prevention using prepared statements

### 📱 Modern UI/UX
- Responsive design for all devices
- Modern CSS with CSS variables
- Interactive JavaScript functionality
- Clean and intuitive interface
- Accessibility considerations

## Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Architecture**: MVC-inspired structure
- **Security**: PDO with prepared statements, password hashing

## Installation & Setup

### Prerequisites
- Web server (Apache/Nginx)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- PDO MySQL extension enabled

### Step 1: Clone/Download
```bash
git clone <repository-url>
cd goba-hospital-management
```

### Step 2: Database Setup
1. Create a MySQL database named `goba_hospital`
2. Import the database schema:
```sql
mysql -u your_username -p goba_hospital < database/schema.sql
```
3. Import sample data (optional):
```sql
mysql -u your_username -p goba_hospital < database/sample_data.sql
```

### Step 3: Configuration
1. Update database credentials in `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'goba_hospital');
```

### Step 4: Directory Permissions
Ensure the web server has read/write access to:
- `uploads/` (for file uploads)
- `logs/` (for error logging)

### Step 5: Web Server Configuration
Point your web server document root to the project directory.

## Demo Accounts

For testing purposes, the following demo accounts are available:

### Patient Portal
- **Username**: `patient_demo`
- **Password**: `password123`

### Doctor Portal
- **Username**: `doctor_demo`  
- **Password**: `password123`

### Staff Portal
- **Username**: `staff_demo`
- **Password**: `password123`

### Admin Portal
- **Username**: `admin`
- **Password**: `password`

### External Health Office
- **Username**: `external_demo`
- **Password**: `password123`

## File Structure

```
goba-hospital-management/
├── admin/                  # Admin portal files
├── assets/
│   ├── css/               # Stylesheets
│   ├── js/                # JavaScript files
│   └── images/            # Images and icons
├── config/                # Configuration files
│   └── database.php       # Database configuration
├── database/              # Database files
│   ├── schema.sql         # Database schema
│   └── sample_data.sql    # Sample data for testing
├── doctor/                # Doctor portal files
├── external/              # External health office portal
├── includes/              # Common PHP includes
│   └── auth.php           # Authentication system
├── patient/               # Patient portal files
├── staff/                 # Medical staff portal files
├── uploads/               # File upload directory
├── index.php              # Homepage
├── logout.php             # Global logout
└── README.md              # This file
```

## Database Schema

### Main Tables
- `patient` - Patient information and demographics
- `doctor` - Doctor profiles and specializations
- `medical_staff` - Medical staff information
- `hospital` - Hospital details
- `consultation` - Medical consultation records
- `operation` - Surgery and operation records
- `diagnosis` - Diagnosis information
- `medical_administration` - Medicine dosage records
- `payments` - Payment tracking
- `patient_transfer` - Inter-facility patient transfers

### Authentication Tables
- `patient_login` - Patient login credentials
- `doctor_login` - Doctor login credentials
- `staff_login` - Staff login credentials
- `admin_login` - Admin login credentials
- `external_health_office` - External office credentials

## Key Features in Detail

### Patient Management
- Registration using National ID, Passport, or Birth Certificate
- Complete demographic information
- Emergency contact details
- Medical history tracking

### Medical Records
- Consultation notes with audio recording support
- Surgical procedure documentation
- Diagnosis tracking with severity levels
- Treatment plan management
- Prescription management

### Payment Integration
- Support for multiple Ethiopian banks
- Transaction tracking
- Payment status management
- Service billing integration

### Search & Filtering
- Advanced search by patient ID, date, doctor, reference number
- Real-time filtering
- Export capabilities

### File Management
- Medical document uploads (PDF, images)
- Secure file storage
- File type and size validation

## Security Considerations

- All database queries use prepared statements
- Password hashing with PHP's `password_hash()`
- Session-based authentication
- Input validation and sanitization
- CSRF protection ready
- Error logging without exposing sensitive data

## Browser Compatibility

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Development

### Adding New Features
1. Follow the existing MVC-inspired structure
2. Use the authentication system for access control
3. Maintain responsive design principles
4. Add appropriate error handling

### Database Changes
1. Update `database/schema.sql`
2. Create migration scripts if needed
3. Update sample data accordingly

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## Support

For issues and questions:
- Check the documentation
- Review the demo accounts and sample data
- Ensure all dependencies are met
- Verify database configuration

## License

This project is developed for educational and healthcare management purposes. Please ensure compliance with local healthcare data regulations when deploying in production.

## Future Enhancements

- AJAX-powered real-time updates
- Advanced reporting and analytics
- Mobile app integration
- API development for third-party integrations
- Backup and recovery features
- Multi-language support
- Advanced security features (2FA, audit logs)

---

**Goba Hospital Management System** - Efficient, Secure, Comprehensive Healthcare Data Management