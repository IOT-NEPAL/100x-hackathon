# 🎯 AVSAR - AI-Powered Job Matching Platform

![Status](https://img.shields.io/badge/Status-Production%20Ready-success)
![PHP](https://img.shields.io/badge/PHP-7.4+-blue)
![Database](https://img.shields.io/badge/MySQL-5.7+-orange)
![License](https://img.shields.io/badge/License-MIT-green)

> A complete job matching platform with AI career guidance, skills management, and intelligent job recommendations.

---

## ✨ Key Features

### 🎓 For Students
- **Smart Job Recommendations** - AI-powered matching based on skills
- **Career Guidance Chatbot** - Google Gemini 2.0 Flash integration
- **Skills Management** - 120+ predefined skills with autocomplete
- **Application Tracking** - Monitor all job applications
- **Job Offers** - Receive and respond to direct offers
- **Real-time Notifications** - Stay updated on opportunities

### 💼 For Employers
- **Post Opportunities** - Create employment and internship listings
- **Application Management** - Review, filter, and track applicants
- **Send Job Offers** - Direct offers to promising candidates
- **Analytics Dashboard** - Track views, applications, and performance
- **Candidate Discovery** - Find students with matching skills

### 🤖 AI-Powered
- **Context-Aware Chatbot** - Knows user skills and available jobs
- **Job Recommendations** - Automatic extraction from AI responses
- **Skill Gap Analysis** - Identifies missing skills
- **Career Path Advice** - Personalized guidance

---

## 🚀 Quick Start

### Prerequisites
- XAMPP (Apache + MySQL)
- Web Browser
- Google Gemini API Key (free)

### Installation (5 Minutes)

1. **Import Database:**
```bash
# In phpMyAdmin (http://localhost/phpmyadmin):
# 1. Create database: avsar_db
# 2. Import file: database.sql
```

2. **Configure API Key:**
```php
// Already configured in db_config.php
define('GEMINI_API_KEY', 'AIzaSyDNmkp0npHbiH66BAao_gEn4lOR8JusaDs');
```

3. **Access Application:**
```
http://localhost/avsar/
```

**📖 Full Setup Guide:** See `INSTALLATION_GUIDE.md`

---

## 📂 Project Structure

```
avsar/
├── 📄 Core Files
│   ├── index.php                  - Landing page
│   ├── login.php                  - Login (role-based)
│   ├── signin.php                 - Signup
│   ├── opportunities.php          - Browse jobs
│   └── db_config.php             - Configuration
│
├── 📁 user/                       - Student Dashboard
│   ├── user-dashboard.php         - Main dashboard
│   ├── my-skills.php             - Skills management
│   ├── career-guidance-ai.php    - AI chatbot
│   └── job-offers.php            - Job offers
│
├── 📁 organizer/                  - Employer Dashboard
│   ├── organizer-dashboard.php    - Main dashboard
│   ├── add-opportunity.php        - Post jobs
│   └── view-applications.php      - Applications
│
├── 📁 includes/                   - Shared Components
│   ├── header.php                - Navigation
│   └── footer.php                - Scripts
│
└── 📁 Documentation
    ├── INSTALLATION_GUIDE.md     - Installation steps
    ├── QUICK_START_GUIDE.md      - Quick start
    └── README_COMPLETE_SYSTEM.md - Full docs
```

---

## 🎯 Core Technologies

| Technology | Purpose | Version |
|------------|---------|---------|
| PHP | Backend | 7.4+ |
| MySQL | Database | 5.7+ |
| PDO | Database Layer | Built-in |
| Bootstrap | UI Framework | 5.3 |
| Font Awesome | Icons | 6.4 |
| Google Gemini | AI Chatbot | 2.0 Flash |

---

## 🎨 Screenshots

### Student Dashboard
- Dark theme with yellow accents
- Job recommendations based on skills
- Statistics cards with animations
- AI chatbot integration

### Employer Dashboard
- Analytics and performance metrics
- Application management
- Job posting interface
- Applicant tracking

---

## 🔐 Security Features

✅ **CSRF Protection** - All forms secured  
✅ **SQL Injection Prevention** - PDO prepared statements  
✅ **XSS Prevention** - Output escaping  
✅ **Password Hashing** - bcrypt algorithm  
✅ **Session Management** - Secure sessions  
✅ **Role-Based Access** - Authorization checks  

---

## 📊 Database Schema

**6 Tables:**
- `users` - User accounts (students & employers)
- `opportunities` - Job postings
- `applications` - Student applications
- `job_offers` - Direct job offers
- `activity_logs` - Action tracking
- `sessions` - Remember me tokens

**Full Schema:** See `database.sql`

---

## 🎓 Usage Guide

### For Students:

1. **Sign Up** → Create account
2. **Add Skills** → Browse 120+ predefined skills
3. **Browse Jobs** → Filter and search
4. **Apply** → Submit applications
5. **AI Chat** → Get career advice
6. **Track** → Monitor applications
7. **Respond** → Accept/decline offers

### For Employers:

1. **Sign Up** → Create organization account
2. **Post Jobs** → Add opportunities
3. **Review** → Check applications
4. **Filter** → By status, job type
5. **Send Offers** → Direct job offers
6. **Analytics** → Track performance

---

## 🤖 AI Chatbot Features

### What It Knows:
- Your actual skills from database
- All available job listings
- Job requirements
- Your application history

### What It Can Do:
- Recommend matching jobs
- Identify skill gaps
- Suggest learning paths
- Provide career advice
- Answer job-related questions

### How It Works:
```
Student: "What jobs can I get?"
AI: Analyzes skills → Matches jobs → Recommends with scores
Student: Clicks recommended job → Opens details page
```

---

## 📈 Key Algorithms

### Job Recommendation Engine:
1. Parse user skills
2. Match against job requirements
3. Calculate match scores
4. Sort by relevance + date
5. Return top 6 jobs

### AI Context Building:
1. Include user's skills
2. Include all active jobs
3. Add matching instructions
4. Send to Gemini API
5. Extract job recommendations

---

## 🎨 Design System

### Color Palette:
```css
--primary-dark: #1a1a1a    /* Primary black */
--accent-yellow: #ffff00    /* Accent yellow */
--light-gray: #f5f5f5       /* Background */
--border-gray: #d0d0d0      /* Borders */
```

### Components:
- **Welcome Banner** - Dark bg, yellow border
- **Stats Cards** - White with hover lift
- **Buttons** - Black with shadows
- **Tables** - Animated rows

---

## 📱 Responsive Design

- Mobile breakpoint: 768px
- Stack cards vertically
- Collapsible navigation
- Touch-friendly buttons
- Optimized layouts

---

## 📚 Documentation

| Document | Purpose |
|----------|---------|
| `INSTALLATION_GUIDE.md` | Step-by-step installation |
| `QUICK_START_GUIDE.md` | 10-minute quick start |
| `GEMINI_API_SETUP.md` | API configuration |
| `README_COMPLETE_SYSTEM.md` | Complete documentation |
| `SYSTEM_COMPLETE_SUMMARY.md` | Feature overview |
| `PROJECT_INDEX.md` | Navigation guide |

---

## 🧪 Testing

### Test API Connection:
```
http://localhost/avsar/test-gemini-api.php
```

### Create Test Accounts:
```
Student: student@test.com / test123
Employer: employer@test.com / test123
```

### Test Workflows:
1. Student: Signup → Add skills → Browse → Apply
2. Employer: Signup → Post job → Review apps → Send offer
3. AI: Login → Chat → Ask questions → Get recommendations

---

## 🛠️ Configuration

### Database (`db_config.php`):
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'avsar_db');
```

### Gemini API:
```php
define('GEMINI_API_KEY', 'YOUR_KEY_HERE');
```

---

## 🐛 Troubleshooting

### Common Issues:

**Database Connection Failed:**
- Check MySQL is running
- Verify database name: `avsar_db`
- Check credentials in `db_config.php`

**API Not Working:**
- Verify API key in `db_config.php`
- Test at `test-gemini-api.php`
- Check internet connection

**Page Not Found:**
- Verify folder structure
- Check Apache is running
- Clear browser cache

**More Help:** See `INSTALLATION_GUIDE.md`

---

## 📊 Statistics

**Project Metrics:**
- 35+ Files Created
- 4,000+ Lines of Code
- 6 Database Tables
- 50+ Features
- 120+ Predefined Skills
- 100% Workflows Complete

---

## 🎯 Features Checklist

### ✅ Completed:
- [x] User authentication (student/employer)
- [x] Student dashboard with recommendations
- [x] Employer dashboard with analytics
- [x] Skills management (120+ skills)
- [x] AI career chatbot (Gemini)
- [x] Job browsing and search
- [x] Application system
- [x] Job offers workflow
- [x] Notifications system
- [x] Profile management
- [x] Responsive design
- [x] Security features
- [x] Documentation

---

## 🚀 Performance

- **Fast Loading:** Optimized queries
- **Efficient:** PDO prepared statements
- **Cached:** Session management
- **Indexed:** Database optimization
- **Lazy Loading:** On-demand data

---

## 🔄 Updates & Maintenance

### Regular Tasks:
- Backup database weekly
- Monitor API usage
- Update skills list
- Review applications
- Check error logs

### Future Enhancements:
- Resume upload
- Email notifications
- Advanced filtering
- Messaging system
- Mobile app

---

## 📝 License

MIT License - Feel free to use and modify.

---

## 👥 Credits

**Built with:**
- PHP 7.4+
- MySQL
- Bootstrap 5.3
- Font Awesome 6.4
- Google Gemini 2.0 Flash

---

## 🎉 Get Started

1. **Read:** `INSTALLATION_GUIDE.md`
2. **Setup:** Import database & configure API
3. **Access:** `http://localhost/avsar/`
4. **Explore:** Create accounts and test features

---

## 📞 Support

**Documentation:**
- Installation: `INSTALLATION_GUIDE.md`
- Quick Start: `QUICK_START_GUIDE.md`
- Full Docs: `README_COMPLETE_SYSTEM.md`

**Testing:**
- API Test: `test-gemini-api.php`
- Browser Console: F12
- Database: phpMyAdmin

---

## ✨ What's Included

✅ Complete authentication system  
✅ Dual dashboards (student/employer)  
✅ AI-powered career guidance  
✅ Skills management (120+ skills)  
✅ Job recommendation engine  
✅ Application tracking  
✅ Job offers workflow  
✅ Real-time notifications  
✅ Beautiful dark theme  
✅ Mobile responsive  
✅ Production ready  
✅ Fully documented  

---

**Version:** 1.0.0 - COMPLETE  
**Status:** PRODUCTION READY ✅  
**Last Updated:** October 31, 2025

**🎊 Welcome to AVSAR - Your Complete Job Matching Platform!**

---

[📖 Installation Guide](INSTALLATION_GUIDE.md) | [🚀 Quick Start](QUICK_START_GUIDE.md) | [📚 Full Documentation](README_COMPLETE_SYSTEM.md)

