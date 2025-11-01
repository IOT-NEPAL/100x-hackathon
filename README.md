# Avsar Nepal - Accessible Opportunity Platform

**A production-ready platform connecting differently-abled individuals with opportunities.**

Avsar Nepal is a comprehensive web platform designed to connect students and fresh graduates in Nepal with employment opportunities and internships, helping them gain relevant experience and launch their careers.

## 🌟 Features

### 🔐 Authentication & User Management
- **Multi-role system**: Users (job seekers), Organizers (employers/NGOs), and Admins
- **Secure authentication**: Password hashing, session management, CSRF protection
- **Role-based access control**: Different dashboards and permissions per role
- **Profile management**: Complete user profiles with skills, accessibility needs, and preferences

### 🎯 Opportunities Management
- **CRUD operations**: Create, read, update, delete opportunities
- **Multiple types**: Full-time jobs, part-time jobs, and internships
- **Advanced search**: Filter by type, location, keywords
- **Detailed views**: Comprehensive opportunity information with attachments

### 📋 Application System
- **Easy application process**: Pre-filled user information, cover letters, resume uploads
- **Application tracking**: Status updates (applied, under review, accepted, rejected)
- **Organizer management**: Review and manage applications

### ♿ Accessibility Features
- **High contrast mode**: Toggle for better visibility
- **Font size controls**: Adjustable text size (small to extra-large)
- **Text-to-speech**: Read page content and opportunities aloud
- **Keyboard navigation**: Full keyboard accessibility support
- **Screen reader compatible**: Semantic HTML and ARIA labels
- **Focus indicators**: Clear visual focus states

### 📊 Analytics & Reporting
- **Admin dashboard**: System-wide statistics and user management
- **Organizer insights**: Opportunity performance and application metrics
- **User tracking**: Application history and status tracking
- **Activity logs**: Comprehensive audit trail

### 🎨 Modern UI/UX
- **Bootstrap 5**: Responsive, mobile-first design
- **Custom styling**: Professional appearance with accessibility considerations
- **Interactive elements**: Modals, tooltips, toast notifications
- **Chart visualization**: Application and opportunity statistics

## 🛠️ Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript (ES6+), Bootstrap 5
- **Backend**: PHP 8.0+
- **Database**: MySQL 8.0+
- **Server**: Apache (XAMPP)
- **Libraries**: Chart.js, Font Awesome, Web Speech API

## 📁 Project Structure

```
avsarnepal/
├── admin/                      # Admin dashboard and management
│   ├── admin-dashboard.php     # Main admin dashboard
│   └── [other admin files]    # User, opportunity, and system management
├── organizer/                  # Organization dashboard and tools
│   ├── organizer-dashboard.php # Main organizer dashboard
│   └── [other org files]      # Opportunity management, applications
├── user/                       # User dashboard and features
│   ├── user-dashboard.php      # Main user dashboard
│   └── [other user files]     # Applications, profile management
├── includes/                   # Core system files
│   ├── auth.php               # Authentication functions
│   ├── db.php                 # Database connection
│   ├── header.php             # Common header template
│   └── footer.php             # Common footer template
├── css/                        # Stylesheets
│   └── custom.css             # Custom styles and accessibility features
├── js/                         # JavaScript files
│   ├── accessibility.js       # Accessibility features
│   └── main.js                # Main application logic
├── images/                     # Static images and assets
│   └── logo.png               # Avsar Nepal logo
├── uploads/                    # File upload directories
│   ├── profile_pics/          # User profile pictures
│   ├── resumes/               # Resume uploads
│   └── opportunity_files/     # Opportunity attachments
├── index.php                   # Landing page
├── login.php                   # Login form
├── signup.php                  # Registration form
├── opportunities.php           # Opportunity listings
├── view-opportunity.php        # Opportunity details
├── apply.php                   # Application form
├── profile.php                 # Profile management
├── avsarnepal.sql            # Database schema and sample data
└── README.md                  # This file
```

## 🚀 Setup Instructions

### Prerequisites

1. **XAMPP** (or LAMP/WAMP) with:
   - Apache 2.4+
   - PHP 8.0+ (with extensions: pdo, pdo_mysql, gd, fileinfo)
   - MySQL 8.0+

### Installation Steps

1. **Download and Setup XAMPP**
   ```bash
   # Download XAMPP from https://www.apachefriends.org/
   # Install and start Apache and MySQL services
   ```

2. **Deploy Application**
   ```bash
   # Copy the avsarnepal folder to your XAMPP htdocs directory
   cp -r avsarnepal/ /xampp/htdocs/
   ```

3. **Database Setup**
   ```bash
   # Access phpMyAdmin at http://localhost/phpmyadmin
   # Or use MySQL command line:
   mysql -u root -p
   ```
   
   Import the database:
   ```sql
   # In phpMyAdmin: Import > Choose file > avsarnepal.sql
   # Or via command line:
   mysql -u root -p < avsarnepal.sql
   ```

4. **Configure Database Connection**
   
   Edit `includes/db.php` if needed:
   ```php
   private $host = 'localhost';
   private $db_name = 'avsarnepal';
   private $username = 'root';
   private $password = '';  // Your MySQL password
   ```

5. **Set File Permissions**
   ```bash
   # Ensure upload directories are writable
   chmod 755 uploads/
   chmod 755 uploads/profile_pics/
   chmod 755 uploads/resumes/
   chmod 755 uploads/opportunity_files/
   ```

6. **Start the Application**
   
   Open your browser and navigate to:
   ```
   http://localhost/avsarnepal/
   ```

### PHP Configuration

Ensure these settings in your `php.ini`:

```ini
file_uploads = On
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
memory_limit = 256M
```

Restart Apache after making changes.

## 👥 Default Login Credentials

### Admin Accounts
- **Email**: `admin@avsarnepal.com` | **Password**: `admin123`
- **Email**: `superadmin@avsarnepal.com` | **Password**: `admin123`

### Organizer Accounts
- **Email**: `contact@abilityfoundation.org` | **Password**: `org123`
- **Email**: `hr@inclusivetech.com` | **Password**: `org123`
- **Email**: `jobs@equalopportunity.edu` | **Password**: `org123`

### User Accounts
- **Email**: `alex.thompson@email.com` | **Password**: `user123`
- **Email**: `maria.garcia@email.com` | **Password**: `user123`
- **Email**: `david.kim@email.com` | **Password**: `user123`
- **Email**: `emma.wilson@email.com` | **Password**: `user123`
- **Email**: `james.brown@email.com` | **Password**: `user123`

## 🔧 Configuration

### Accessibility Controls

The accessibility features are automatically enabled. Users can:

- **Toggle High Contrast**: `Alt + C` or click the contrast button
- **Increase Font Size**: `Alt + Plus` or click the plus button
- **Decrease Font Size**: `Alt + Minus` or click the minus button
- **Text-to-Speech**: `Alt + S` or click the speaker button
- **Stop Speech**: `Escape` key

### Customization

#### Colors and Branding

Edit `css/custom.css` to modify:

```css
:root {
    --primary-color: #0d6efd;    /* Main brand color */
    --secondary-color: #6c757d;  /* Secondary color */
    --success-color: #198754;    /* Success messages */
    --warning-color: #ffc107;    /* Warnings */
    --danger-color: #dc3545;     /* Errors */
}
```

#### Text Content

- **Site name**: Edit the navbar brand in `includes/header.php`
- **Welcome messages**: Modify content in respective dashboard files
- **Footer content**: Update `includes/footer.php`

#### Email Configuration

For production, update the email handling in:
- Profile recovery features
- Application notifications
- Admin alerts

## 📊 Adding Sample Data

To add more sample data:

1. **Users**: Insert into the `users` table with hashed passwords
2. **Opportunities**: Add entries to the `opportunities` table
3. **Applications**: Create entries in the `applications` table

Example SQL for adding a new organizer:

```sql
INSERT INTO users (name, email, phone, password, role, org_name, contact_person) 
VALUES ('New Organization', 'new@example.com', '+1-555-0000', 
        '$2y$10$TLLbSGf0qDW3Q7p8OzQxh.LwvSP3OAG3sN7OJbBHrGhGKsKT.WDhG', 
        'organizer', 'New Organization Inc', 'Contact Person');
```

## 🔒 Security Features

- **Password Hashing**: bcrypt with salt
- **CSRF Protection**: Tokens on all forms
- **SQL Injection Prevention**: PDO prepared statements
- **File Upload Security**: Type and size validation
- **Session Security**: Regeneration and timeout
- **Input Sanitization**: Output escaping and validation

## 🧪 Testing

### Manual Testing Checklist

1. **Authentication**
   - [ ] Sign up with different roles
   - [ ] Login/logout functionality
   - [ ] Password requirements
   - [ ] Role-based redirects

2. **Accessibility**
   - [ ] High contrast mode
   - [ ] Font size adjustments
   - [ ] Text-to-speech functionality
   - [ ] Keyboard navigation
   - [ ] Screen reader compatibility

3. **Core Features**
   - [ ] Opportunity creation and editing
   - [ ] Application submission
   - [ ] File uploads (profiles, resumes)
   - [ ] Search and filtering
   - [ ] Dashboard statistics

### Browser Testing

Test in major browsers:
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## 🚀 Deployment

### Production Considerations

1. **Security Hardening**
   ```php
   // Add to includes/db.php for production
   ini_set('display_errors', 0);
   error_reporting(0);
   ```

2. **Environment Variables**
   - Move database credentials to environment variables
   - Use secure session configurations
   - Enable HTTPS

3. **Performance Optimization**
   - Enable PHP OPcache
   - Optimize images and assets
   - Implement caching strategies

4. **Database Optimization**
   - Add additional indexes for large datasets
   - Implement connection pooling
   - Regular backup procedures

### Docker Deployment (Optional)

Create a `Dockerfile`:

```dockerfile
FROM php:8.1-apache
RUN docker-php-ext-install pdo pdo_mysql
COPY avsarnepal/ /var/www/html/
RUN chown -R www-data:www-data /var/www/html
EXPOSE 80
```

## 🤝 Contributing

### Development Guidelines

1. **Code Style**
   - Follow PSR-12 coding standards
   - Use meaningful variable names
   - Comment complex logic
   - Maintain consistent indentation

2. **Accessibility**
   - Test with screen readers
   - Ensure keyboard navigation
   - Maintain color contrast ratios
   - Use semantic HTML

3. **Security**
   - Validate all inputs
   - Use prepared statements
   - Implement CSRF protection
   - Regular security audits

### Feature Extensions

Easy areas to extend:

1. **Email Notifications**: Add SMTP configuration
2. **Social Login**: Integrate OAuth providers
3. **Advanced Search**: Add more filter options
4. **Real-time Chat**: Add messaging between users and orgs
5. **Calendar Integration**: Schedule interviews and events
6. **Document Verification**: Verify organization credentials
7. **API Development**: Create REST API for mobile apps

## 📞 Support

For support or questions:

- **Documentation**: Check this README and inline comments
- **Issues**: Review error logs in XAMPP control panel
- **Database**: Use phpMyAdmin for data inspection
- **Development**: Check browser console for JavaScript errors

## 📄 License

This project is created for hackathon purposes and educational use. Feel free to modify and extend as needed.

## 🏆 Hackathon Ready

This project is designed to be impressive in hackathon settings:

- **Complete Feature Set**: All major functionality implemented
- **Professional Design**: Modern, accessible UI
- **Real Data**: Sample users, opportunities, and applications
- **Live Demo Ready**: Works immediately after setup
- **Scalable Architecture**: Easy to extend and modify
- **Accessibility Focus**: Demonstrates social impact and inclusivity

## 📈 Future Enhancements

Consider these features for future development:

1. **Mobile App**: React Native or Flutter companion
2. **AI Matching**: ML-based opportunity recommendations
3. **Video Interviews**: Integrated video calling
4. **Skills Assessment**: Built-in testing platform
5. **Mentorship Program**: Connect users with mentors
6. **Company Verification**: Advanced organization validation
7. **Analytics Dashboard**: Advanced reporting and insights
8. **Multi-language**: Internationalization support

---

**Built with ❤️ for accessibility and inclusion**

*Last updated: December 2024*
