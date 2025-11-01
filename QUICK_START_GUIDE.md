# 🚀 AVSAR - Quick Start Guide

## Get Started in 10 Minutes!

### ⚡ Prerequisites
- XAMPP installed and running
- Web browser (Chrome/Firefox recommended)
- Google AI Studio account (free)

---

## 📝 Step-by-Step Setup

### 1️⃣ Database Setup (2 minutes)

1. **Start XAMPP**
   - Open XAMPP Control Panel
   - Start **Apache** and **MySQL**

2. **Import Database**
   - Open browser: `http://localhost/phpmyadmin`
   - Click **"New"** in left sidebar
   - Database name: `avsar_db`
   - Click **"Create"**
   - Select `avsar_db`
   - Click **"Import"** tab
   - Choose file: `database.sql`
   - Click **"Go"**
   - ✅ Success! Tables created

---

### 2️⃣ Configure API Key (3 minutes)

1. **Get Gemini API Key**
   - Visit: https://makersuite.google.com/app/apikey
   - Click **"Get API key"**
   - Click **"Create API key in new project"**
   - Copy the key (starts with `AIza...`)

2. **Add to Project**
   - Open `db_config.php`
   - Line 9: Replace `YOUR_GEMINI_API_KEY_HERE` with your key
   ```php
   define('GEMINI_API_KEY', 'AIzaSyD...your-key-here');
   ```
   - Save file

---

### 3️⃣ First Login (2 minutes)

1. **Access Application**
   - Open browser: `http://localhost/avsar/`

2. **Create Student Account**
   - Click **"SIGN UP"**
   - Select **"Student"**
   - Fill form:
     - Full Name: Your Name
     - Email: student@test.com
     - Password: test123
     - Confirm Password: test123
   - Check **"I agree to terms"**
   - Click **"SIGN UP"**
   - Wait for redirect to login
   - Login with your credentials

---

### 4️⃣ Add Your Skills (2 minutes)

1. **Navigate to Skills**
   - Click **"My Skills"** in top navigation

2. **Add Skills**
   - Type in search box: "Python"
   - Click on "Python" from dropdown
   - Add more skills:
     - "JavaScript"
     - "HTML"
     - "CSS"
     - "React"
   - Click **"Save Skills"**
   - ✅ Success message appears!

---

### 5️⃣ Test AI Chatbot (1 minute)

1. **Open AI Career Guide**
   - Click **"AI Career Guide"** in navigation

2. **Ask a Question**
   - Type: "What jobs can I get?"
   - Press Enter
   - Watch typing indicator...
   - AI responds with recommendations!

---

### 6️⃣ Create Employer Account (Optional)

1. **Logout**
   - Click your name in top right
   - Click **"Logout"**

2. **Sign Up as Employer**
   - Click **"SIGN UP"**
   - Select **"Employer"**
   - Fill form:
     - Contact Person: Manager Name
     - Organization Name: ABC Company
     - Email: employer@test.com
     - Phone: 1234567890
     - Password: test123
   - Click **"SIGN UP"**

---

## 🎯 What to Try Next

### As Student:
- ✅ View **Dashboard** - See statistics
- ✅ Browse **Recommended Jobs** (if posted)
- ✅ Check **Job Offers** page
- ✅ Chat with **AI Career Guide**
- ✅ Edit **Profile**

### As Employer:
- ✅ **Post a Job** (create supporting page)
- ✅ View **Applications**
- ✅ Send **Job Offers** to students
- ✅ Track **Job Performance**

---

## 📁 File Structure Reference

```
avsar/
├── 📄 index.php              ← Landing page
├── 📄 login.php              ← Login (redirects by role)
├── 📄 signin.php             ← Signup (student/employer)
├── 📄 logout.php             ← Logout handler
├── 📄 db_config.php          ← Database & API config
├── 📄 database.sql           ← Database structure
│
├── 📁 user/                  ← Student pages
│   ├── user-dashboard.php    ← Main dashboard
│   ├── my-skills.php         ← Skills management
│   ├── career-guidance-ai.php ← AI chatbot
│   └── job-offers.php        ← Job offers
│
├── 📁 organizer/             ← Employer pages
│   ├── organizer-dashboard.php ← Main dashboard
│   └── send-job-offer.php    ← Send offers
│
├── 📁 includes/              ← Shared components
│   ├── header.php            ← Navigation
│   └── footer.php            ← Scripts
│
└── 📁 uploads/               ← File uploads
```

---

## 🆘 Troubleshooting

### Problem: "Connection failed"
**Solution:**
- ✅ Check MySQL is running in XAMPP
- ✅ Verify database name is `avsar_db`
- ✅ Check credentials in `db_config.php`

### Problem: "AI not responding"
**Solution:**
- ✅ Verify API key in `db_config.php`
- ✅ Check browser console (F12) for errors
- ✅ Ensure API key is valid at Google AI Studio

### Problem: "No recommended jobs"
**Solution:**
- ✅ Add skills to your profile first
- ✅ Ensure jobs are posted in database
- ✅ Jobs must have `is_active = 1`

### Problem: "Page not found"
**Solution:**
- ✅ Verify folder structure matches above
- ✅ Check file names (case-sensitive on Linux)
- ✅ Ensure Apache is running

---

## 🎓 Sample Data for Testing

### Create Test Job (Manual SQL)
```sql
INSERT INTO opportunities 
(organizer_id, title, description, requirements, type, location, is_active)
VALUES 
(2, 'Web Developer', 'Build amazing websites', 
'HTML, CSS, JavaScript, React', 'employment', 'Remote', 1);
```

Replace `organizer_id` with actual employer user ID.

### Test Users
```
Student:
- Email: student@test.com
- Password: test123

Employer:
- Email: employer@test.com
- Password: test123
```

---

## ✨ Key Features to Explore

### 🎯 Smart Job Matching
Dashboard shows jobs matching your skills with match scores!

### 🤖 AI Career Assistant
Context-aware chatbot that knows:
- Your actual skills
- All available jobs
- Job requirements

### 📊 Live Statistics
Real-time counts for:
- Applications
- Job offers
- Views (employers)

### 🔔 Notifications
Bell icon shows:
- New job offers
- Accepted applications
- New applicants (employers)

### 💡 Skills Autocomplete
120+ predefined skills + custom skill support

---

## 📚 Documentation

- **Full System Docs:** `README_COMPLETE_SYSTEM.md`
- **API Setup:** `GEMINI_API_SETUP.md`
- **This Guide:** `QUICK_START_GUIDE.md`

---

## 🎉 You're All Set!

Your job matching platform is ready to use. Here's what you've got:

✅ Student dashboard with job recommendations  
✅ Employer dashboard with analytics  
✅ AI-powered career guidance  
✅ Skills management system  
✅ Job offers workflow  
✅ Beautiful dark theme UI  
✅ Responsive design  
✅ Secure authentication  

**Happy job matching!** 🚀

---

**Need Help?**
- Check `README_COMPLETE_SYSTEM.md` for detailed documentation
- Review browser console for errors (F12)
- Verify all files are in correct locations
- Ensure database is properly imported

**Version:** 1.0.0  
**Last Updated:** October 31, 2025

