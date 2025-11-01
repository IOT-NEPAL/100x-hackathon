# Avsar Nepal: Opportunity Platform  
### Bridging Nepal's Emerging Talent with Tomorrow's Opportunities

Avsar Nepal is a dynamic web platform designed to solve a critical challenge in Nepal's job market: connecting students and fresh graduates with the valuable work experience they need to launch their careers. We provide a standard job board for everyone and a premium, verification-based service for companies seeking the best, pre-vetted candidates.

---

## 🌟 What We Offer

### For Job Seekers: Launch Your Career  
Your journey starts here. After a quick login, you can build a verified profile that stands out to employers.

**Build & Verify Your Profile:** Showcase your skills, education, and projects. Get your profile verified by our team to build credibility and unlock access to premium opportunities.

**Discover Your Perfect Role:** Browse a wide array of full-time jobs, part-time gigs, and internships. Use our powerful search and filters, or simply chat with our AI-powered chatbot to find roles that match your unique skills and ambitions.

**Streamlined Applications:** Apply to opportunities with ease. Your profile information auto-fills applications, and you can track your status every step of the way.

---

### For Employers: Find Verified Talent  
**Standard Job Board:** Post opportunities, manage applications, and connect with a broad pool of talent directly through your dashboard.

**🌟 Premium Talent Service (Our Flagship Offering):** Subscribe to our premium service and let us do the heavy lifting. Our expert HR team conducts rigorous verifications and initial screenings of job seekers, providing you with a curated shortlist of the best, interview-ready candidates tailored to your specific needs.

---

### For Everyone: An Inclusive Experience  
We are committed to accessibility for all users, featuring:

- Screen reader navigation support  
- Full keyboard navigation  

---

## 🛠 Technology Stack

- **Frontend:** HTML5, CSS3, JavaScript (ES6+), Tailwind CSS  
- **Backend:** PHP 8.0+  
- **Database:** MySQL 8.0+  
- **Server:** Apache (via XAMPP)  
- **Libraries:** Chart.js, Font Awesome  

---

## 📁 Project Structure
avsar-nepal/
├── admin/ # Admin dashboard for system management
├── employer/ # Employer dashboard and tools
├── user/ # Job Seeker dashboard (profile, applications)
├── includes/ # Core system files (auth, database, headers)
├── css/ # Stylesheets and custom designs
├── js/ # JavaScript logic (including chatbot)
├── images/ # Logos and static assets
├── uploads/ # Directories for resumes, profile pictures, etc.
├── index.php # Public landing page
├── login.php # User authentication
├── opportunities.php # Main job listing page
├── avsar_nepal.sql # Database schema
└── README.md # You are here!

---

## 🚀 Getting Started

### Prerequisites  
- **XAMPP:** Ensure you have XAMPP installed and running on your machine.

### Installation Steps  

**1. Setup the Project:**  
Copy the `avsar-nepal` folder into your XAMPP `htdocs` directory.

**2. Setup the Database:**  
- Open phpMyAdmin at `http://localhost/phpmyadmin`  
- Create a new database named `avsar_nepal`  
- Import the provided `avsar_nepal.sql` file into this new database to create all the necessary tables and sample data.

**3. Configure the Connection (if needed):**  
The database connection settings are in `includes/db.php`.  
If your MySQL setup uses a different password (not blank), update the credentials there.

**4. Set File Permissions:**  
Ensure the `uploads/` directory and its subfolders (`profile_pics/`, `resumes/`, etc.) have write permissions so users can upload files.

**5. Launch!**  
- Start Apache and MySQL from your XAMPP control panel.  
- Open your browser and go to `http://localhost/avsar-nepal/`.

---

## 👥 Test the Platform

You can log in and explore the platform using these pre-configured accounts:

**Admin**  
- Email: `admin@avsar.com`  
- Password: `admin123`  
Manages the platform, users, and oversees the premium verification process.

**Employer**  
- Email: `contact@company.com`  
- Password: `org123`  
Can post jobs and, if premium, access the verified talent pool.

**Job Seeker**  
- Email: `alex.thompson@email.com`  
- Password: `user123`  
Can build a profile, get verified, search for jobs, and use the chatbot to find opportunities.

---

## 🔒 Our Commitment to Security

We take security seriously. The platform is built with:

- Bcrypt password hashing to keep user credentials safe  
- CSRF protection on all forms  
- Prepared SQL statements to prevent injection attacks  
- Secure file upload validation to restrict file types and sizes  
- Robust session management  

---

## 📄 License

This project was originally developed by **Team Elite** for a hackathon.  
All rights are reserved to the team. The hackathon organizer, **100 X Nepal**, is granted permission to use this project for marketing and demonstration purposes.

---

### Avsar Nepal: Empowering Talent, Enabling Growth.  
_Last Updated: November 2025_
