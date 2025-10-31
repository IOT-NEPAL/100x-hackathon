<?php
require_once '../includes/auth.php';
requireRole('user');

$user = getCurrentUser();

// Get user's skills
$user_skills = $user['skills'] ?? '';
$skills_array = [];
if (!empty($user_skills)) {
    $skills_array = array_map('trim', explode(',', $user_skills));
    $skills_array = array_filter($skills_array);
}

// Get available jobs from database
$jobs_stmt = $pdo->prepare("
    SELECT o.*, u.org_name, u.name as organizer_name 
    FROM opportunities o 
    LEFT JOIN users u ON o.organizer_id = u.id 
    WHERE o.is_active = 1 
    ORDER BY o.date_posted DESC 
    LIMIT 50
");
$jobs_stmt->execute();
$available_jobs = $jobs_stmt->fetchAll();

// Format jobs for AI context
$jobs_context = '';
foreach ($available_jobs as $job) {
    $jobs_context .= "Job: {$job['title']} | Type: {$job['type']} | Location: " . ($job['location'] ?? 'Not specified') . 
                     " | Requirements: " . ($job['requirements'] ?? 'Not specified') . " | ID: {$job['id']}\n";
}

$page_title = 'Career Guidance AI';
include '../includes/header.php';
?>

<style>
.chat-container {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 200px);
    max-height: 700px;
    border: 1px solid #dee2e6;
    border-radius: 12px;
    background: #ffffff;
    overflow: hidden;
}

.chat-header {
    background: #1a1a1a;
    color: white;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    border-bottom: 3px solid #ffff00;
}

.chat-header h5 {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;
    background: #f5f5f5;
}

.message {
    margin-bottom: 1.5rem;
    display: flex;
    gap: 0.75rem;
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.message.user {
    flex-direction: row-reverse;
}

.message-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1.2rem;
}

.message.ai .message-avatar {
    background: #1a1a1a;
    color: white;
    border: 2px solid #e0e0e0;
}

.message.user .message-avatar {
    background: #2d2d2d;
    color: white;
    border: 2px solid #e0e0e0;
}

.message-content {
    max-width: 70%;
    padding: 0.875rem 1.125rem;
    border-radius: 18px;
    line-height: 1.5;
}

.message.ai .message-content {
    background: #ffffff;
    border: 2px solid #d0d0d0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    color: #1a1a1a;
}

.message.user .message-content {
    background: #1a1a1a;
    color: white;
    border: 2px solid #2d2d2d;
}

.message-content p {
    margin: 0 0 0.5rem 0;
}

.message-content p:last-child {
    margin-bottom: 0;
}

.message-content ul, .message-content ol {
    margin: 0.5rem 0;
    padding-left: 1.5rem;
}

.job-recommendation {
    background: #f5f5f5;
    border: 2px solid #d0d0d0;
    border-left: 4px solid #1a1a1a;
    padding: 1rem;
    margin-top: 0.75rem;
    border-radius: 4px;
}

.job-recommendation h6 {
    color: #1a1a1a;
    margin-bottom: 0.5rem;
    font-weight: 700;
}

.job-recommendation a {
    color: #ffffff;
    text-decoration: none;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    margin-top: 0.5rem;
    padding: 0.5rem 1rem;
    background: #1a1a1a;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.job-recommendation a:hover {
    background: #2d2d2d;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.chat-input-area {
    border-top: 2px solid #d0d0d0;
    padding: 1rem;
    background: #ffffff;
}

.chat-input-wrapper {
    display: flex;
    gap: 0.75rem;
    align-items: flex-end;
}

.chat-input {
    flex: 1;
    padding: 0.75rem 1rem;
    border: 2px solid #d0d0d0;
    border-radius: 4px;
    resize: none;
    max-height: 120px;
    font-size: 0.95rem;
    line-height: 1.5;
    color: #1a1a1a;
}

.chat-input:focus {
    outline: none;
    border-color: #1a1a1a;
    box-shadow: 0 0 0 2px rgba(26, 26, 26, 0.1);
}

.btn-send {
    padding: 0.75rem 1.5rem;
    background: #1a1a1a;
    color: white;
    border: 2px solid #1a1a1a;
    border-radius: 4px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-send:hover:not(:disabled) {
    background: #2d2d2d;
    border-color: #2d2d2d;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.btn-send:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.typing-indicator {
    display: none;
    padding: 0.875rem 1.125rem;
    background: #ffffff;
    border: 2px solid #d0d0d0;
    border-radius: 4px;
    width: fit-content;
}

.typing-indicator.active {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.typing-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #1a1a1a;
    animation: typing 1.4s infinite;
}

.typing-dot:nth-child(2) {
    animation-delay: 0.2s;
}

.typing-dot:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes typing {
    0%, 60%, 100% { transform: translateY(0); opacity: 0.7; }
    30% { transform: translateY(-10px); opacity: 1; }
}

.user-skills-badge {
    background: #ffffff;
    color: #1a1a1a;
    padding: 0.25rem 0.75rem;
    border: 2px solid #1a1a1a;
    border-radius: 4px;
    font-size: 0.85rem;
    font-weight: 700;
    display: inline-block;
    margin: 0.25rem;
}
</style>

<div class="container mt-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/inclusify/">Home</a></li>
                    <li class="breadcrumb-item"><a href="user-dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Career Guidance AI</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold">
                <i class="fas fa-robot me-3"></i>Career Guidance AI
            </h1>
            <p class="lead text-muted">Get personalized career advice based on your skills and available opportunities</p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-9">
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="chat-container">
                        <div class="chat-header">
                            <h5>
                                <i class="fas fa-robot"></i>
                                Career Guidance AI Assistant
                            </h5>
                            <span class="badge bg-light text-dark ms-auto">
                                <?php echo count($skills_array); ?> skills detected
                            </span>
                        </div>
                        
                        <div class="chat-messages" id="chatMessages">
                            <div class="message ai">
                                <div class="message-avatar">
                                    <i class="fas fa-robot"></i>
                                </div>
                                <div class="message-content">
                                    <p><strong>Hello <?php echo escape($user['name']); ?>! 👋</strong></p>
                                    <p>I'm your Career Guidance AI assistant. I can help you with:</p>
                                    <ul>
                                        <li>Career path recommendations</li>
                                        <li>Skill gap analysis</li>
                                        <li>Job recommendations based on your profile</li>
                                        <li>Learning roadmaps</li>
                                    </ul>
                                    <?php if (!empty($skills_array)): ?>
                                        <p><strong>Your current skills:</strong></p>
                                        <div>
                                            <?php foreach (array_slice($skills_array, 0, 10) as $skill): ?>
                                                <span class="user-skills-badge"><?php echo escape($skill); ?></span>
                                            <?php endforeach; ?>
                                            <?php if (count($skills_array) > 10): ?>
                                                <span class="user-skills-badge">+<?php echo count($skills_array) - 10; ?> more</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <p class="mt-3"><strong>What career path are you interested in? Or what would you like to know?</strong></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="typing-indicator" id="typingIndicator">
                            <div class="typing-dot"></div>
                            <div class="typing-dot"></div>
                            <div class="typing-dot"></div>
                        </div>
                        
                        <div class="chat-input-area">
                            <div class="chat-input-wrapper">
                                <textarea 
                                    class="chat-input form-control" 
                                    id="userInput" 
                                    placeholder="Type your message here..."
                                    rows="1"
                                ></textarea>
                                <button class="btn btn-send" id="sendButton">
                                    <span>Send</span>
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3">
            <div class="card shadow-sm mb-3">
                <div class="card-header" style="background: #1a1a1a; color: white; border-bottom: 3px solid #ffff00; font-weight: 700;">
                    <h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Tips</h6>
                </div>
                <div class="card-body" style="background: #ffffff;">
                    <ul class="small mb-0" style="color: #1a1a1a;">
                        <li>Ask about career paths</li>
                        <li>Get job recommendations</li>
                        <li>Learn about skill requirements</li>
                        <li>Get learning roadmaps</li>
                    </ul>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header" style="background: #1a1a1a; color: white; border-bottom: 3px solid #ffff00; font-weight: 700;">
                    <h6 class="mb-0"><i class="fas fa-briefcase me-2"></i>Your Skills</h6>
                </div>
                <div class="card-body" style="background: #ffffff;">
                    <?php if (!empty($skills_array)): ?>
                        <div class="d-flex flex-wrap gap-1">
                            <?php foreach ($skills_array as $skill): ?>
                                <span class="badge" style="background: #ffffff !important; color: #1a1a1a !important; border: 2px solid #1a1a1a; padding: 0.375rem 0.75rem; border-radius: 4px; font-weight: 700;"><?php echo escape($skill); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="small mb-0" style="color: #666666;">No skills added yet. <a href="my-skills.php" style="color: #1a1a1a; font-weight: 700;">Add your skills</a></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const API_KEY = 'AIzaSyDNmkp0npHbiH66BAao_gEn4lOR8JusaDs';
const API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

const chatMessages = document.getElementById('chatMessages');
const userInput = document.getElementById('userInput');
const sendButton = document.getElementById('sendButton');
const typingIndicator = document.getElementById('typingIndicator');

const userSkills = <?php echo json_encode($skills_array); ?>;
const availableJobs = <?php echo json_encode($available_jobs); ?>;
const userName = <?php echo json_encode($user['name']); ?>;
const jobsContext = <?php echo json_encode($jobs_context); ?>;

let conversationHistory = [];

// Auto-resize textarea
userInput.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
});

// Send message on Enter (but allow Shift+Enter for new line)
userInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});

sendButton.addEventListener('click', sendMessage);

async function sendMessage() {
    const message = userInput.value.trim();
    if (!message) return;
    
    // Disable input
    userInput.disabled = true;
    sendButton.disabled = true;
    
    // Add user message to chat
    addMessage('user', message);
    userInput.value = '';
    userInput.style.height = 'auto';
    
    // Show typing indicator
    showTypingIndicator();
    
    try {
        // Get the context prompt (includes student skills and job data)
        const contextPrompt = buildContextPrompt();
        
        // Build API contents: ALWAYS start with context prompt on every request
        let apiContents = [];
        
        if (conversationHistory.length === 0) {
            // First message: Context + user message combined in one user message
            apiContents = [
                {
                    role: 'user',
                    parts: [{ text: contextPrompt + '\n\n---\n\n' + message }]
                }
            ];
        } else {
            // Rebuild entire conversation with context at the start
            // Start with context + first user message
            const firstUserMsg = conversationHistory[0].parts[0].text;
            apiContents = [
                {
                    role: 'user',
                    parts: [{ text: contextPrompt + '\n\n---\n\n' + firstUserMsg }]
                }
            ];
            
            // Add all model responses and subsequent user messages
            for (let i = 1; i < conversationHistory.length; i++) {
                apiContents.push(conversationHistory[i]);
            }
            
            // Add current message
            apiContents.push({
                role: 'user',
                parts: [{ text: message }]
            });
        }
        
        // Debug: Log what we're sending to the API
        console.log('📤 Sending to AI:', {
            studentSkills: userSkills,
            jobCount: availableJobs.length,
            messageCount: apiContents.length
        });
        
        // Call Gemini API (using header format as per curl example)
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-goog-api-key': API_KEY
            },
            body: JSON.stringify({
                contents: apiContents
            })
        });
        
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            console.error('API Error Response:', errorData);
            throw new Error(`API error: ${response.status} - ${errorData.error?.message || response.statusText}`);
        }
        
        const data = await response.json();
        console.log('API Response:', data);
        
        // Extract AI response
        let aiResponse = '';
        if (data.candidates && data.candidates[0] && data.candidates[0].content && data.candidates[0].content.parts) {
            aiResponse = data.candidates[0].content.parts[0].text || '';
        }
        
        if (!aiResponse) {
            console.error('No response text found:', data);
            throw new Error('No response from AI. Response structure: ' + JSON.stringify(data));
        }
        
        // Hide typing indicator
        hideTypingIndicator();
        
        // Add AI response to chat
        addMessage('ai', aiResponse);
        
        // Add user message to conversation history (always add, even on first message)
        conversationHistory.push({
            role: 'user',
            parts: [{ text: message }]
        });
        
        // Add AI response to conversation history
        conversationHistory.push({
            role: 'model',
            parts: [{ text: aiResponse }]
        });
        
        // Extract and display job recommendations if any
        extractJobRecommendations(aiResponse);
        
    } catch (error) {
        console.error('Error:', error);
        hideTypingIndicator();
        const errorMessage = error.message || 'Unknown error occurred';
        addMessage('ai', `Sorry, I encountered an error: ${errorMessage}. Please check the browser console for more details or try again.`);
    } finally {
        // Re-enable input
        userInput.disabled = false;
        sendButton.disabled = false;
        userInput.focus();
    }
}

function buildContextPrompt() {
    // Format skills clearly
    const skillsList = userSkills.length > 0 ? userSkills.join(', ') : 'NONE';
    
    // Count available jobs
    const jobCount = availableJobs.length;
    
    let prompt = `[SYSTEM CONTEXT - YOU MUST USE THIS DATA]

You are the Career Guidance Chatbot for ${userName} in the Awasar Nepal Student Dashboard.

=== STUDENT'S CURRENT SKILLS ===
${skillsList}

=== AVAILABLE JOB LISTINGS (${jobCount} jobs) ===
${jobsContext || 'No jobs currently available in the database.'}

=== YOUR ROLE ===
1. **ANALYZE** the student's skills listed above
2. **MATCH** those skills with the job requirements from the job listings above
3. **RECOMMEND** jobs where student's skills match the requirements
4. **IDENTIFY** skill gaps when there's no perfect match
5. **SUGGEST** specific skills to learn based on the job requirements listed above

=== CRITICAL RULES ===
❌ NEVER say you don't have access to their skills - they are listed above as: ${skillsList}
❌ NEVER recommend jobs not listed in the database above
❌ NEVER make up skills or qualifications
✅ ALWAYS reference the student's actual skills listed above
✅ ALWAYS match against the real job requirements shown above
✅ ALWAYS be specific about which jobs from the list above match their profile

=== RESPONSE STYLE ===
- Short, practical paragraphs
- Use bullet points for clarity
- Bold important job titles like **Job Title**
- Compare student's skills vs job requirements explicitly

=== EXAMPLE INTERACTION ===
If student asks "What jobs can I get?" and they have HTML, CSS skills:
1. Check ALL job listings above for requirements
2. Find jobs that require HTML and/or CSS (or no skills)
3. List those specific job titles from the database
4. If no matches, suggest 1-2 close jobs and what skills to add

Remember: You HAVE the student's skills (${skillsList}) and you HAVE the job listings (shown above). Use them!`;

    return prompt;
}

function addMessage(role, content) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${role}`;
    
    const avatar = document.createElement('div');
    avatar.className = 'message-avatar';
    avatar.innerHTML = role === 'ai' ? '<i class="fas fa-robot"></i>' : '<i class="fas fa-user"></i>';
    
    const contentDiv = document.createElement('div');
    contentDiv.className = 'message-content';
    
    // Convert markdown-like formatting to HTML
    content = content.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    content = content.replace(/\*(.*?)\*/g, '<em>$1</em>');
    content = content.replace(/\n/g, '<br>');
    
    // Format lists
    content = content.replace(/^\d+\.\s+(.+)$/gm, '<li>$1</li>');
    content = content.replace(/^-\s+(.+)$/gm, '<li>$1</li>');
    
    contentDiv.innerHTML = content;
    
    messageDiv.appendChild(avatar);
    messageDiv.appendChild(contentDiv);
    chatMessages.appendChild(messageDiv);
    
    // Scroll to bottom
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function showTypingIndicator() {
    typingIndicator.classList.add('active');
    chatMessages.appendChild(typingIndicator);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function hideTypingIndicator() {
    typingIndicator.classList.remove('active');
    if (typingIndicator.parentNode) {
        typingIndicator.parentNode.removeChild(typingIndicator);
    }
}

function extractJobRecommendations(aiResponse) {
    // Look for job titles in the response and create clickable links
    availableJobs.forEach(job => {
        if (aiResponse.toLowerCase().includes(job.title.toLowerCase())) {
            const jobLink = document.createElement('div');
            jobLink.className = 'job-recommendation';
            jobLink.innerHTML = `
                <h6><i class="fas fa-briefcase me-2"></i>Recommended Job</h6>
                <p><strong>${escapeHtml(job.title)}</strong> - ${escapeHtml(job.type)}</p>
                ${job.location ? `<p><i class="fas fa-map-marker-alt me-1"></i>${escapeHtml(job.location)}</p>` : ''}
                <a href="../view-opportunity.php?id=${job.id}" target="_blank">
                    View Job Details <i class="fas fa-external-link-alt ms-1"></i>
                </a>
            `;
            
            const lastMessage = chatMessages.querySelector('.message:last-child .message-content');
            if (lastMessage && !lastMessage.querySelector('.job-recommendation')) {
                lastMessage.appendChild(jobLink);
            }
        }
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Focus input on load
userInput.focus();
</script>

<?php include '../includes/footer.php'; ?>
