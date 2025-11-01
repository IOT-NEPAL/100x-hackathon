# 📦 AVSAR - Complete Installation Guide

## 🎯 Prerequisites Checklist

Before you begin, make sure you have:

- ✅ **XAMPP** installed (Apache + MySQL)
- ✅ **Web Browser** (Chrome, Firefox, Edge, etc.)
- ✅ **Text Editor** (VS Code, Sublime, Notepad++, etc.)
- ✅ **Google Account** (for Gemini API - free)

---

## 📥 Step-by-Step Installation

### **Step 1: Install XAMPP (If Not Installed)**

1. Download XAMPP from: https://www.apachefriends.org/
2. Install XAMPP to default location: `C:\xampp`
3. Run XAMPP Control Panel
4. Start **Apache** and **MySQL** services

---

### **Step 2: Setup Project Files**

Your files should already be in: `C:\xampp\htdocs\avsar\`

**Verify file structure:**
```
C:\xampp\htdocs\avsar\
├── index.php
├── login.php
├── signin.php
├── database.sql
├── db_config.php
├── user/ (folder)
├── organizer/ (folder)
├── includes/ (folder)
└── uploads/ (folder)
```

---

### **Step 3: Create Database**

#### Option A: Using phpMyAdmin (Recommended)

1. **Open phpMyAdmin:**
   - Browser: `http://localhost/phpmyadmin`

2. **Create Database:**
   - Click "New" in left sidebar
   - Database name: `avsar_db`
   - Collation: `utf8mb4_general_ci`
   - Click "Create"

3. **Import Schema:**
   - Click on `avsar_db` in left sidebar
   - Click "Import" tab
   - Click "Choose File"
   - Select: `C:\xampp\htdocs\avsar\database.sql`
   - Scroll down, click "Go"
   - ✅ Success message should appear

#### Option B: Using MySQL Command Line

```bash
# Open XAMPP Shell or Command Prompt
cd C:\xampp\mysql\bin

# Login to MySQL
mysql -u root -p
# (Press Enter when asked for password - default is empty)

# Create and import
CREATE DATABASE avsar_db;
USE avsar_db;
SOURCE C:/xampp/htdocs/avsar/database.sql;
exit;
```

---

### **Step 4: Configure Gemini API Key**

✅ **Already configured!** Your API key is set in `db_config.php`:
```php
define('GEMINI_API_KEY', 'AIzaSyDNmkp0npHbiH66BAao_gEn4lOR8JusaDs');
```

To verify or change:
1. Open `db_config.php` in text editor
2. Check line 9 for API key
3. Save file if changed

---

### **Step 5: Test API Connection**

1. **Open browser:**
   ```
   http://localhost/avsar/test-gemini-api.php
   ```

2. **Expected result:**
   ```
   ✅ API Connection Successful!
   AI Response: Hello! API is working!
   ```

3. **If you see error:**
   - Check internet connection
   - Verify API key is correct
   - Try again in 1 minute (rate limit)

---

### **Step 6: Test Application**

1. **Open main page:**
   ```
   http://localhost/avsar/
   ```

2. **You should see:**
   - Black and yellow themed landing page
   - "AVSAR" logo
   - "LOG IN" button (green gradient)
   - "SIGN UP" button

---

## 👥 Create Test Accounts

### **Create Student Account**

1. Go to: `http://localhost/avsar/`
2. Click **"SIGN UP"**
3. Select **"Student"**
4. Fill form:
   ```
   Full Name: John Doe
   Email: student@test.com
   Password: test123
   Confirm Password: test123
   ```
5. Check "I agree to terms"
6. Click **"SIGN UP"**
7. Wait for redirect to login
8. Login with credentials

### **Create Employer Account**

1. Go to: `http://localhost/avsar/`
2. Click **"SIGN UP"**
3. Select **"Employer"**
4. Fill form:
   ```
   Contact Person: Jane Smith
   Organization Name: ABC Company
   Email: employer@test.com
   Phone: 1234567890
   Password: test123
   Confirm Password: test123
   ```
5. Check "I agree to terms"
6. Click **"SIGN UP"**
7. Login with credentials

---

## 🧪 Testing Complete Workflow

### **As Student:**

1. **Add Skills:**
   - Login as student
   - Click "My Skills" in navigation
   - Type: "Python" → Click to add
   - Add more: "JavaScript", "HTML", "CSS"
   - Click "Save Skills"
   - ✅ Success message

2. **Test AI Chatbot:**
   - Click "AI Career Guide"
   - Type: "What jobs can I get?"
   - Press Enter
   - ✅ AI should respond with recommendations

3. **Browse Jobs:**
   - Click "Browse Jobs" (after employer posts one)
   - View job details
   - Apply for job

### **As Employer:**

1. **Post a Job:**
   - Login as employer
   - Click "Post New Job"
   - Fill form:
     ```
     Title: Web Developer
     Type: Employment
     Location: Remote
     Description: Build amazing web applications
     Requirements: HTML, CSS, JavaScript, React
     ```
   - Check "Active"
   - Click "Post Job"
   - ✅ Success message

2. **Wait for Applications:**
   - Student applies for job
   - Check "Applications" page
   - Review application
   - Send job offer

---

## 🔍 Verification Checklist

### ✅ Database:
- [ ] Database `avsar_db` created
- [ ] All 6 tables imported
- [ ] No errors in phpMyAdmin

### ✅ Files:
- [ ] All folders exist (user/, organizer/, includes/, uploads/)
- [ ] Can access index.php in browser
- [ ] No 404 errors

### ✅ API:
- [ ] test-gemini-api.php shows success
- [ ] AI chatbot responds
- [ ] No API errors in console

### ✅ Functionality:
- [ ] Can signup/login
- [ ] Can add skills
- [ ] Can post jobs (employer)
- [ ] Can apply for jobs (student)
- [ ] Can send/receive job offers
- [ ] Notifications work

---

## 🐛 Common Issues & Solutions

### **Issue 1: "Connection failed" error**

**Solution:**
```
1. Check XAMPP → MySQL is running (green)
2. Check database name is "avsar_db"
3. Verify credentials in db_config.php:
   - DB_USER: root
   - DB_PASS: (empty)
```

### **Issue 2: "Table doesn't exist" error**

**Solution:**
```
1. Re-import database.sql
2. In phpMyAdmin, check all 6 tables exist:
   - users
   - opportunities
   - applications
   - job_offers
   - activity_logs
   - sessions
```

### **Issue 3: API not working**

**Solution:**
```
1. Check internet connection
2. Verify API key in db_config.php
3. Test at: http://localhost/avsar/test-gemini-api.php
4. Wait 1 minute and try again (rate limit)
```

### **Issue 4: Page not found (404)**

**Solution:**
```
1. Verify folder structure matches documentation
2. Check file names are correct (case-sensitive on Linux)
3. Ensure Apache is running in XAMPP
4. Clear browser cache
```

### **Issue 5: Styles not loading**

**Solution:**
```
1. Check Bootstrap CDN link in header.php
2. Clear browser cache (Ctrl + F5)
3. Check browser console for errors (F12)
```

### **Issue 6: Login redirect not working**

**Solution:**
```
1. Check role in database (should be 'user' or 'organizer')
2. Clear browser cookies
3. Try incognito/private mode
4. Check PHP session is enabled
```

---

## 📊 Database Table Overview

After import, you should have 6 tables:

| Table | Rows | Purpose |
|-------|------|---------|
| `users` | 0 | User accounts (students & employers) |
| `opportunities` | 0 | Job postings |
| `applications` | 0 | Student applications |
| `job_offers` | 0 | Direct job offers |
| `activity_logs` | 0 | Action tracking |
| `sessions` | 0 | Remember me tokens |

---

## 🔐 Default Credentials

**Database:**
- Host: `localhost`
- Username: `root`
- Password: `` (empty)
- Database: `avsar_db`

**Test Accounts (after signup):**
- Student: `student@test.com` / `test123`
- Employer: `employer@test.com` / `test123`

---

## 🚀 Post-Installation Steps

1. **Change Default Passwords:**
   - Update test account passwords
   - Change MySQL root password (optional)

2. **Configure Production:**
   - Update db_config.php for production
   - Set proper error handling
   - Enable SSL/HTTPS

3. **Backup Database:**
   - Export database regularly
   - Store backups securely

4. **Monitor API Usage:**
   - Check Gemini API quota
   - Monitor request limits
   - Consider paid tier if needed

---

## 📚 Next Steps

After successful installation:

1. ✅ Read `QUICK_START_GUIDE.md` for usage
2. ✅ Read `README_COMPLETE_SYSTEM.md` for features
3. ✅ Read `GEMINI_API_SETUP.md` for API details
4. ✅ Test all features thoroughly
5. ✅ Customize branding/content

---

## 💡 Pro Tips

### **For Development:**
```
- Use Chrome DevTools (F12) to debug
- Check Network tab for API calls
- Use Console for JavaScript errors
- Enable error reporting in PHP
```

### **For Performance:**
```
- Enable MySQL query cache
- Use browser caching
- Optimize images
- Minify CSS/JS for production
```

### **For Security:**
```
- Change default passwords
- Use strong passwords
- Keep API keys secret
- Regular security audits
```

---

## 📞 Support & Resources

### **Documentation:**
- Full System Docs: `README_COMPLETE_SYSTEM.md`
- Quick Start: `QUICK_START_GUIDE.md`
- API Setup: `GEMINI_API_SETUP.md`
- This Guide: `INSTALLATION_GUIDE.md`

### **Troubleshooting:**
- Check browser console (F12)
- Check PHP error logs (xampp/apache/logs/)
- Review database in phpMyAdmin
- Test API connection separately

### **Online Resources:**
- XAMPP Docs: https://www.apachefriends.org/docs/
- PHP Manual: https://www.php.net/manual/
- Bootstrap Docs: https://getbootstrap.com/docs/
- Gemini API: https://ai.google.dev/docs/

---

## ✅ Installation Complete!

If you've completed all steps above, your system should be:

✅ **Database:** Configured and running  
✅ **Files:** In correct location  
✅ **API:** Connected and tested  
✅ **Application:** Accessible in browser  
✅ **Accounts:** Can signup/login  
✅ **Features:** All working  

**You're ready to use AVSAR! 🎉**

Navigate to: `http://localhost/avsar/`

---

**Installation Support:**
If you encounter any issues not covered here, check the troubleshooting section or review the complete documentation files.

**Version:** 1.0.0  
**Last Updated:** October 31, 2025

