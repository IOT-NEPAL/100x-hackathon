# Avsar Nepal - Project Structure

## Directory Organization

```
inclusify/
├── admin/                  # Admin dashboard and management pages
├── career_centre/          # Career centre dashboard
├── css/                    # All CSS files
│   ├── custom.css         # Dashboard and custom styles
│   ├── chatbot.css        # Chatbot widget styles
│   ├── employers.css      # Employers page styles
│   ├── students.css       # Students page styles
│   └── career_centers.css # Career centers page styles
├── includes/               # Shared PHP includes
│   ├── auth.php           # Authentication functions
│   ├── db.php             # Database connection
│   ├── header.php         # Shared header component
│   └── footer.php         # Shared footer component
├── js/                     # JavaScript files
│   ├── main.js            # Main JavaScript functionality
│   └── chatbot.js         # Chatbot functionality
├── migrations/             # Database migration scripts
│   ├── migration_add_career_centre.sql
│   └── migration_remove_scholarship_volunteer_training.sql
├── organizer/              # Organizer/Employer dashboard pages
├── uploads/                # User-uploaded files
│   ├── profile_pics/      # Profile pictures
│   ├── opportunity_files/ # Job posting attachments
│   └── resumes/           # User resumes
├── user/                   # User/Student dashboard pages
├── index.php               # Homepage
├── login.php               # Login page
├── signup.php              # Registration page
├── students.php            # Students landing page
├── employers.php           # Employers landing page
├── career_centers.php      # Career centers landing page
├── opportunities.php       # Jobs listing page
├── view-opportunity.php    # Job detail page
├── apply.php               # Application page
├── profile.php             # User profile page
├── styles.css              # Main stylesheet
├── logo.png                # Site logo
└── inclusify.sql           # Database schema

```

## Cleanup Summary

### Files Removed:
-  `index_backup.php` - Backup file
-  `fix-passwords.php` - Temporary utility script
-  `database-update.php` - Temporary database update script
-  `export-data.php` - Temporary export script
-  `inclusify/` subdirectory - Duplicate directory removed

### Files Organized:
-  CSS files moved to `css/` directory
-  Migration SQL files moved to `migrations/` directory
-  Updated CSS paths in PHP files to reflect new structure

### Navigation Updates:
-  Removed "Scholarships" link from header (content was removed earlier)
-  Changed "Opportunities" to "Jobs" in navigation

## File Organization Rules

1. **CSS Files**: All CSS files are in the `css/` directory
   - `styles.css` - Main stylesheet (stays in root)
   - Page-specific CSS files in `css/` subdirectory

2. **JavaScript Files**: All JS files are in the `js/` directory

3. **Includes**: Shared PHP components in `includes/` directory

4. **Migrations**: Database migration scripts in `migrations/` directory

5. **Uploads**: User-uploaded content in `uploads/` subdirectories

## Notes

- The main stylesheet `styles.css` remains in the root for easier access
- All page-specific stylesheets are organized in the `css/` folder
- Database schema file `inclusify.sql` remains in root for easy access
- All temporary/utility scripts have been removed

