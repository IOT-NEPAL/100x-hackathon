# Avsar Nepal: Opportunity Platform

**A production-ready platform connecting Nepal's fresh talent with top-notch job opportunities.**

Avsar Nepal is a comprehensive web platform designed to bridge the gap between students and fresh graduates in Nepal and employment opportunities, helping them gain relevant experience and launch their careers.

This platform serves as a standard job board for all users and offers a **premium, verification-based service** for companies seeking the best, pre-vetted candidates.

## 🌟 Core Features

### 🔐 Authentication & User Management

  - **Multi-role system**: **Job Seekers** (freshers/students), **Employers** (Standard and Premium), and **Admins**
  - **Secure authentication**: Password hashing, session management, CSRF protection
  - **Role-based access control**: Different dashboards and permissions per role
  - **Profile management**: Complete user profiles with skills, experience, and preferences

### 🎯 Opportunities Management (Standard Platform)

  - **Job Posting Management**: Employers can create, read, update, and delete opportunities.
  - **Multiple types**: Full-time jobs, part-time jobs, and internships.
  - **Advanced search**: Job seekers can filter by type, location, and keywords.
  - **Detailed views**: Comprehensive opportunity information with attachments.

### 📋 Application System (Standard Platform)

  - **Easy application process**: Pre-filled user information, cover letters, resume uploads.
  - **Application tracking**: Status updates (applied, under review, accepted, rejected).
  - **Employer Dashboard**: Employers can review and manage incoming applications directly.

### 💼 Premium Employer Services (Premium Feature)

This is our premium offering for companies and businesses. When an employer subscribes to this feature, Avsar Nepal handles the initial screening.

  - **Candidate Verification**: Our internal HR team, with expertise across diverse fields, conducts a rigorous verification and interview process for interested job seekers.
  - **Curated Talent Pool**: Premium companies get access to a pre-vetted list of the "best candidates" whose skills, experience, and professionalism have been validated by our team.
  - **Direct Matching**: We provide companies with candidates who are the best fit for their specific roles, saving them time and resources in the hiring process.

### ♿ Accessibility Features

  - **High contrast mode**: Toggle for better visibility
  - **Font size controls**: Adjustable text size
  - **Text-to-speech**: Read page content and opportunities aloud
  - **Keyboard navigation**: Full keyboard accessibility support
  - **Screen reader compatible**: Semantic HTML and ARIA labels

### 📊 Analytics & Reporting

  - **Admin dashboard**: System-wide statistics and user management
  - **Employer insights**: Track opportunity performance and application metrics
  - **User tracking**: Application history and status tracking
  - **Activity logs**: Comprehensive audit trail

### 🎨 Modern UI/UX

  - **Tailwind CSS**: Responsive, mobile-first design
  - **Custom styling**: Professional appearance with accessibility considerations
  - **Interactive elements**: Modals, tooltips, toast notifications
  - **Chart visualization**: Application and opportunity statistics

-----

## 🛠️ Technology Stack

  - **Frontend**: HTML5, CSS3, JavaScript (ES6+), **Tailwind CSS**
  - **Backend**: PHP 8.0+
  - **Database**: MySQL 8.0+
  - **Server**: Apache (XAMPP)
  - **Libraries**: Chart.js, Font Awesome

-----

## 📁 Project Structure

```
avsar-nepal/
├── admin/                 # Admin dashboard and management
│   ├── admin-dashboard.php    # Main admin dashboard
│   └── [other admin files]  # User, opportunity, and system management
├── employer/              # Employer dashboard and tools
│   ├── employer-dashboard.php # Main employer dashboard
│   └── [other org files]    # Opportunity management, applications
├── user/                  # User (Job Seeker) dashboard
│   ├── user-dashboard.php     # Main user dashboard
│   └── [other user files]   # Applications, profile management
├── includes/              # Core system files
│   ├── auth.php             # Authentication functions
│   ├── db.php               # Database connection
│   ├── header.php           # Common header template
│   └── footer.php           # Common footer template
├── css/                   # Stylesheets
│   └── custom.css           # Custom styles
├── js/                    # JavaScript files
│   ├── main.js              # Main application logic
│   └── chatbot.js           # Chatbot actions
├── images/                # Static images and assets
│   └── logo.png             # Avsar Nepal logo
├── uploads/               # File upload directories
│   ├── profile_pics/        # User profile pictures
│   ├── resumes/             # Resume uploads
│   └── opportunity_files/   # Opportunity attachments
├── index.php              # Landing page
├── login.php              # Login form
├── signup.php             # Registration form
├── opportunities.php      # Opportunity listings
├── view-opportunity.php   # Opportunity details
├── apply.php              # Application form
├── profile.php            # Profile management
├── avsar_nepal.sql        # Database schema and sample data
└── README.md              # This file
```

-----

## 🚀 Setup Instructions

### Prerequisites

1.  **XAMPP** (or LAMP/WAMP) with:
      - Apache 2.4+
      - PHP 8.0+
      - MySQL 8.0+

### Installation Steps

1.  **Download and Setup XAMPP**

    ```bash
    # Download XAMPP from https://www.apachefriends.org/
    # Install and start Apache and MySQL services
    ```

2.  **Deploy Application**

    ```bash
    # Copy the avsar-nepal folder to your XAMPP htdocs directory
    cp -r avsar-nepal/ /path/to/xampp/htdocs/
    ```

3.  **Database Setup**

    ```bash
    # Access phpMyAdmin at http://localhost/phpmyadmin
    # Create a new database named 'avsar_nepal'
    ```

    Import the database:

    ```sql
    # In phpMyAdmin: Select 'avsar_nepal' > Import > Choose file > avsar_nepal.sql
    # Or via command line:
    mysql -u root -p avsar_nepal < avsar_nepal.sql
    ```

4.  **Configure Database Connection**

    Edit `includes/db.php`:

    ```php
    private $host = 'localhost';
    private $db_name = 'avsar_nepal';
    private $username = 'root';
    private $password = '';  // Your MySQL password
    ```

5.  **Set File Permissions**

    ```bash
    # Ensure upload directories are writable
    chmod 755 uploads/
    chmod 755 uploads/profile_pics/
    chmod 755 uploads/resumes/
    chmod 755 uploads/opportunity_files/
    ```

6.  **Start the Application**

    Open your browser and navigate to:

    ```
    http://localhost/avsar-nepal/
    ```

### PHP Configuration

Ensure these settings in your `php.ini`:

```ini
file_uploads = On
upload_max_filesize = 10M
post_max_size = 10M
```

-----

## 👥 Default Login Credentials

### Admin Accounts

  - **Email**: `admin@avsar.com` | **Password**: `admin123`
  - **Email**: `superadmin@avsar.com` | **Password**: `admin123`

### Employer Accounts

  - **Email**: `contact@company.com` | **Password**: `org123`
  - **Email**: `hr@techsolutions.com` | **Password**: `org123`

### User (Job Seeker) Accounts

  - **Email**: `alex.thompson@email.com` | **Password**: `user123`
  - **Email**: `maria.garcia@email.com` | **Password**: `user123`
  - **Email**: `david.kim@email.com` | **Password**: `user123`

-----

## 🔧 Configuration

### Customization

#### Colors and Branding

Edit `css/custom.css` to modify:

```css
:root {
    --primary-color: #0d6efd;   /* Main brand color */
    --secondary-color: #6c757d; /* Secondary color */
}
```

#### Text Content

  - **Site name**: Edit the navbar brand in `includes/header.php`
  - **Footer content**: Update `includes/footer.php`

-----

## 📊 Adding Sample Data

Example SQL for adding a new Employer:

```sql
INSERT INTO users (name, email, phone, password, role, org_name, contact_person) 
VALUES ('New Company', 'new@company.com', '+977-9800000000', 
        '$2y$10$TLLbSGf0qDW3Q7p8OzQxh.LwvSP3OAG3sN7OJbBHrGhGKsKT.WDhG', 
        'employer', 'New Company Inc', 'Contact Person');
```

-----

## 🔒 Security Features

  - **Password Hashing**: bcrypt with salt
  - **CSRF Protection**: Tokens on all forms
  - **SQL Injection Prevention**: PDO prepared statements
  - **File Upload Security**: Type and size validation
  - **Session Security**: Regeneration and timeout
  - **Input Sanitization**: Output escaping and validation

-----

## 🚀 Deployment

### Production Considerations

1.  **Security Hardening**
    ```php
    // Add to includes/db.php for production
    ini_set('display_errors', 0);
    error_reporting(0);
    ```
2.  **Environment Variables**: Move database credentials to a secure `.env` file.
3.  **Performance Optimization**: Enable PHP OPcache and minify assets.

-----

## 📄 License

This project was initially created for a hackathon. All rights go to Team Elite. The main hackathon organizer, 100 X Nepal, can use it for marketing and demonstration purposes.

-----

**Avsar Nepal: Connecting Talent with Opportunity.**

*Last updated: November 1, 2025*