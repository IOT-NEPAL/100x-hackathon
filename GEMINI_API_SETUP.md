# Google Gemini AI API Setup Guide

## 🚀 Quick Setup (5 Minutes)

### Step 1: Get Your API Key

1. Go to **Google AI Studio**: https://makersuite.google.com/app/apikey

2. Click **"Get API Key"** or **"Create API Key"**

3. Select **"Create API key in new project"** (or use existing project)

4. Copy the generated API key (starts with `AIza...`)

### Step 2: Configure in Your Project

1. Open `db_config.php` in your code editor

2. Find line 9:
```php
define('GEMINI_API_KEY', 'YOUR_GEMINI_API_KEY_HERE');
```

3. Replace `YOUR_GEMINI_API_KEY_HERE` with your actual API key:
```php
define('GEMINI_API_KEY', 'AIzaSyD...your-actual-key-here');
```

4. Save the file

### Step 3: Test the AI Chatbot

1. Start XAMPP (Apache + MySQL)

2. Navigate to: `http://localhost/avsar/`

3. **Sign up** as a student

4. **Add some skills** (e.g., "Python", "JavaScript")

5. Go to **AI Career Guide** in navigation

6. Type a message like: **"What jobs can I get?"**

7. The AI should respond with personalized recommendations!

---

## 🔍 Troubleshooting

### Error: "API request failed"
- ✅ Check if API key is correctly copied (no extra spaces)
- ✅ Verify API key is valid at Google AI Studio
- ✅ Check browser console (F12) for detailed error

### Error: "Invalid API key"
- ✅ Generate a new API key
- ✅ Make sure you copied the entire key
- ✅ Check for typos in `db_config.php`

### AI responses are generic/not using job data
- ✅ Make sure you have active jobs in database
- ✅ Check that jobs have `is_active = 1`
- ✅ Verify `requirements` field has text

---

## 💡 API Features Used

### Gemini 2.0 Flash
- **Model:** `gemini-2.0-flash`
- **Endpoint:** `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent`
- **Authentication:** API Key in header

### Context Sent to AI:
1. Student's actual skills from database
2. All active job listings with requirements
3. Specific matching instructions
4. Response formatting guidelines

### API Request Structure:
```json
{
  "contents": [
    {
      "role": "user",
      "parts": [
        {
          "text": "[CONTEXT] + User message"
        }
      ]
    }
  ]
}
```

---

## 📊 API Usage & Limits

### Free Tier:
- ✅ **15 requests per minute**
- ✅ **1,500 requests per day**
- ✅ **1 million tokens per minute**

### Best for:
- Development and testing
- Small to medium applications
- Educational projects

### If you exceed limits:
- Wait 1 minute for rate limit reset
- Consider upgrading to paid tier
- Implement request caching

---

## 🎯 How It Works

### 1. Context Building
When student opens chatbot:
```php
// Gets from database:
$userSkills = "Python, JavaScript, HTML, CSS"
$availableJobs = [
  "Web Developer - Requires: HTML, CSS, JavaScript",
  "Python Developer - Requires: Python, Django",
  // ... all active jobs
]
```

### 2. Prompt Construction
```
[SYSTEM CONTEXT]
Student's Skills: Python, JavaScript, HTML, CSS
Available Jobs: [Full list with requirements]

RULES:
- ALWAYS match skills to job requirements
- NEVER recommend jobs not in database
- IDENTIFY skill gaps
```

### 3. AI Response
AI analyzes the context and responds with:
- Matched jobs from actual database
- Skill gap analysis
- Learning recommendations
- Career path advice

### 4. Job Extraction
JavaScript scans AI response for job titles and creates clickable cards.

---

## 🔒 Security Notes

### API Key Security:
- ✅ Store in `db_config.php` (not tracked in git)
- ✅ Never commit API key to public repos
- ✅ Add `db_config.php` to `.gitignore`
- ✅ Regenerate key if accidentally exposed

### Recommended .gitignore:
```
db_config.php
.env
uploads/*
!uploads/.gitkeep
```

---

## 🌟 Advanced Configuration

### Customize AI Behavior

Edit `user/career-guidance-ai.php` around line 150:

```javascript
function buildContextPrompt() {
    return `
    === YOUR CUSTOM INSTRUCTIONS ===
    - Be more/less formal
    - Focus on specific industries
    - Include salary information
    - Provide skill roadmaps
    `;
}
```

### Add More Context

Include additional data:
```php
// Get user's applications
$stmt = $pdo->prepare("SELECT * FROM applications WHERE user_id = ?");
// Include in context prompt
```

---

## 📞 Support Resources

### Official Documentation:
- Google AI Studio: https://makersuite.google.com/
- Gemini API Docs: https://ai.google.dev/docs
- API Pricing: https://ai.google.dev/pricing

### Common Issues:
1. **Quota exceeded:** Wait 1 minute
2. **Invalid key:** Regenerate in AI Studio
3. **No response:** Check browser console
4. **Generic answers:** Verify job data in database

---

## ✨ Tips for Best Results

### 1. Add Skills
Students should add 5-10 relevant skills for better matching.

### 2. Detailed Job Requirements
When posting jobs, include specific skills in the requirements field.

### 3. Clear Questions
Ask specific questions like:
- "What jobs match my Python skills?"
- "What skills do I need for Web Developer role?"
- "Give me a learning roadmap for Data Science"

### 4. Context Persistence
The chatbot remembers conversation history, so you can ask follow-up questions.

---

## 🎓 Example Conversations

### Example 1: Job Recommendations
```
User: "What jobs can I apply for?"

AI: "Based on your skills (Python, JavaScript, HTML, CSS), 
you're a great match for these positions:

**Web Developer** - ABC Company
You have: HTML, CSS, JavaScript ✓
Required: HTML, CSS, JavaScript, React
Missing: React (consider learning!)

**Python Developer** - XYZ Corp
You have: Python ✓
Required: Python, Django
Missing: Django (2-week learning curve)"
```

### Example 2: Skill Gap Analysis
```
User: "I want to become a Full Stack Developer"

AI: "For Full Stack Developer roles in our database:

Current Skills: Python, HTML, CSS
Skills to Add:
1. JavaScript frameworks (React/Vue)
2. Node.js for backend
3. Database (MySQL/MongoDB)
4. Git version control

Recommended Learning Path:
Week 1-2: JavaScript fundamentals
Week 3-4: React basics
..."
```

---

## 🎯 Success Checklist

- ✅ API key added to `db_config.php`
- ✅ Student account created
- ✅ Skills added to profile
- ✅ Jobs posted in database
- ✅ Chatbot responds to messages
- ✅ Job recommendations appear
- ✅ No console errors

---

**Ready to Go!** Your AI Career Chatbot is now fully functional. 🚀

For questions or issues, refer to the main `README_COMPLETE_SYSTEM.md` file.

