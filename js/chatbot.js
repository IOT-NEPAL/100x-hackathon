/**
 * Inclusify AI Chatbot
 * Provides help and answers questions about the website
 */

class InclusifyChatbot {
    constructor() {
        this.isOpen = false;
        this.isListening = false;
        this.apiKey = 'AIzaSyA4i2zrFWLURe6skdHqFvgveJsTYx1U7QI';
        this.synthesis = window.speechSynthesis;
        this.localKnowledge = this.buildLocalKnowledge();
        this.chatHistory = [];
        
        this.init();
    }
    
    buildLocalKnowledge() {
        return [
            // Website Overview
            {
                keywords: ['what is inclusify', 'about inclusify', 'website purpose', 'what does inclusify do'],
                answer: "Inclusify is a platform dedicated to creating inclusive employment opportunities for people with disabilities. We connect job seekers with employers committed to diversity and accessibility, providing a barrier-free environment for career development."
            },
            {
                keywords: ['mission', 'goal', 'purpose'],
                answer: "Our mission is to bridge the gap between talented individuals with disabilities and inclusive employers, creating a more accessible and equitable job market for everyone."
            },
            
            // Features
            {
                keywords: ['features', 'what can i do', 'capabilities', 'functionality'],
                answer: "Inclusify offers job searching, opportunity posting, application management, accessibility features like voice commands and high contrast mode, user profiles, and comprehensive admin tools for managing the platform."
            },
            {
                keywords: ['voice commands', 'voice control', 'speech navigation'],
                answer: "Our voice command system allows you to navigate the entire website hands-free. You can say commands like 'go home', 'login', 'opportunities', 'fill form', 'read page', and many more. Enable it from the accessibility menu!"
            },
            
            {
                keywords: ['job seeker', 'user account', 'applicant'],
                answer: "As a job seeker, you can browse opportunities, apply for positions, track your applications, upload your resume, and manage your profile with accessibility information and skills."
            },
            {
                keywords: ['employer', 'organizer', 'post jobs', 'hiring'],
                answer: "Employers and organizers can post opportunities, manage applications, review candidates, and track the success of their postings. We provide tools to help you find the right talent."
            },
            
            // Opportunities and Scholarships
            {
                keywords: ['opportunities', 'jobs', 'positions', 'employment'],
                answer: "We offer various types of opportunities including full-time employment, internships, volunteer positions, training programs, and scholarships. All are focused on inclusivity and accessibility."
            },
            {
                keywords: ['scholarships', 'education funding', 'financial aid', 'educational support', 'student funding'],
                answer: "Inclusify offers a dedicated scholarships section where students with disabilities can find educational funding opportunities. Organizations provide scholarships ranging from $2,500 to $5,000+ for STEM, law, social work, and other fields. Visit the Scholarships page to explore available funding!"
            },
            {
                keywords: ['scholarship application', 'how to apply scholarship', 'scholarship requirements', 'scholarship process'],
                answer: "To apply for scholarships: 1) Browse the Scholarships page, 2) Review requirements (typically include GPA minimums, essays, recommendation letters), 3) Click 'Apply Now' and fill out the application form, 4) Upload required documents. Each scholarship has specific deadlines and criteria."
            },
            {
                keywords: ['scholarship types', 'scholarship categories', 'what scholarships available', 'scholarship fields'],
                answer: "We offer scholarships for various fields including STEM degrees, computer science, law, social work, assistive technology, and disability rights advocacy. Amounts range from $2,500 to $5,000+, with some being merit-based and others need-based."
            },
            {
                keywords: ['organization scholarships', 'provide scholarship', 'offer scholarship', 'create scholarship'],
                answer: "Organizations can offer scholarships through Inclusify! Register as an organizer, post your scholarship opportunity with details about amount, requirements, and deadlines. Help break down financial barriers to education for students with disabilities."
            },
            {
                keywords: ['track application', 'application status', 'my applications'],
                answer: "You can track your applications in your user dashboard. Applications can have statuses: Applied (new), Under Review, Accepted, or Rejected. You'll see updates as organizers review your applications."
            },
            
            // Technical Help
            {
                keywords: ['login', 'sign in', 'password', 'forgot password'],
                answer: "To login, click the 'Login' link and enter your email and password. If you forgot your password, use the 'Forgot Password' link. For demo purposes, you can also contact support."
            },
            {
                keywords: ['profile', 'edit profile', 'update information'],
                answer: "You can edit your profile by going to your dashboard and clicking 'Edit Profile'. Update your personal information, skills, accessibility needs, and upload a profile picture."
            },
            {
                keywords: ['upload resume', 'resume', 'cv', 'file upload'],
                answer: "You can upload your resume when applying for opportunities. We accept PDF, DOC, and DOCX files up to 5MB. Your resume will be securely stored and shared only with employers you apply to."
            },
            
            // Navigation
            {
                keywords: ['navigate', 'how to use', 'getting around', 'navigation'],
                answer: "Use the top navigation menu to access different sections. The website is fully keyboard navigable and works with screen readers. You can also use our voice commands for hands-free navigation."
            },
            {
                keywords: ['dashboard', 'my dashboard', 'user panel'],
                answer: "Your dashboard is your personal control center where you can view your applications, edit your profile, track progress, and access all your account features. Find it in the top navigation after logging in."
            },
            
            // Support and Contact
            {
                keywords: ['help', 'support', 'contact', 'assistance'],
                answer: "We're here to help! You can contact our support team through the Contact page, use this chatbot for instant answers, or explore our comprehensive accessibility features designed to make your experience seamless."
            },
            {
                keywords: ['technical issues', 'problems', 'bugs', 'not working'],
                answer: "If you're experiencing technical issues, try refreshing the page, checking your internet connection, or using a different browser. For persistent problems, please contact our support team with details about the issue."
            },
            
            // Privacy and Security
            {
                keywords: ['privacy', 'data protection', 'personal information'],
                answer: "We take your privacy seriously. Your personal information is securely stored and only shared with employers when you apply to their opportunities. You can export or delete your data anytime from your profile settings."
            },
            {
                keywords: ['security', 'safe', 'data safety'],
                answer: "Our platform uses industry-standard security measures including encrypted connections, secure password storage, and regular security updates to protect your information."
            },
            
            // Specific Features
            {
                keywords: ['high contrast', 'contrast mode', 'visibility'],
                answer: "High contrast mode makes text and buttons more visible by using stronger color combinations. You can toggle it from the accessibility menu in the top navigation."
            },
            {
                keywords: ['font size', 'text size', 'bigger text', 'smaller text'],
                answer: "You can adjust the font size using the controls in the accessibility menu. This helps make text more readable according to your preferences."
            },
            {
                keywords: ['read aloud', 'text to speech', 'speech', 'audio'],
                answer: "Our text-to-speech feature can read page content aloud. Use the 'Read Page Content' option in the accessibility menu or voice commands to activate it."
            }
        ];
    }
    
    init() {
        this.createChatbotHTML();
        this.attachEventListeners();
        this.addGreeting();
    }
    
    createChatbotHTML() {
        const chatbotHTML = `
            <!-- Chatbot Widget -->
            <div id="chatbot-widget" class="chatbot-widget">
                <!-- Chat Button -->
                <div id="chatbot-toggle" class="chatbot-toggle" title="Chat with Inclusify Assistant">
                    <i class="fas fa-comments"></i>
                    <span class="chatbot-notification" id="chatbot-notification">1</span>
                </div>
                
                <!-- Chat Window -->
                <div id="chatbot-window" class="chatbot-window">
                    <div class="chatbot-header">
                        <div class="chatbot-header-info">
                            <div class="chatbot-avatar">
                                <i class="fas fa-robot"></i>
                            </div>
                            <div class="chatbot-title">
                                <h6>Inclusify Assistant</h6>
                                <small>Here to help you navigate</small>
                            </div>
                        </div>
                        <div class="chatbot-controls">
                            <button id="chatbot-voice-toggle" class="chatbot-voice-btn" title="Toggle Voice Reading">
                                <i class="fas fa-volume-up"></i>
                            </button>
                            <button id="chatbot-minimize" class="chatbot-close-btn">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div id="chatbot-messages" class="chatbot-messages">
                        <!-- Messages will be populated here -->
                    </div>
                    
                    <div class="chatbot-input-area">
                        <div class="chatbot-quick-actions">
                            <button class="quick-action-btn" onclick="inclusifyBot.sendMessage('What is Inclusify?')">About Inclusify</button>
                            <button class="quick-action-btn" onclick="inclusifyBot.sendMessage('Tell me about scholarships')">Scholarships</button>
                            <button class="quick-action-btn" onclick="inclusifyBot.sendMessage('What opportunities are available?')">Opportunities</button>
                        </div>
                        <div class="chatbot-input-container">
                            <input type="text" id="chatbot-input" placeholder="Ask me anything about Inclusify..." maxlength="500">
                            <button id="chatbot-send" class="chatbot-send-btn">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                        <div class="chatbot-footer">
                            <small>Powered by AI • <a href="#" onclick="inclusifyBot.showHelp()">Help</a></small>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', chatbotHTML);
    }
    
    attachEventListeners() {
        // Toggle chatbot
        document.getElementById('chatbot-toggle').addEventListener('click', () => {
            this.toggleChatbot();
        });
        
        // Minimize chatbot
        document.getElementById('chatbot-minimize').addEventListener('click', () => {
            this.toggleChatbot();
        });
        
        // Send message
        document.getElementById('chatbot-send').addEventListener('click', () => {
            this.handleSendMessage();
        });
        
        // Enter key to send
        document.getElementById('chatbot-input').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.handleSendMessage();
            }
        });
        
        // Voice toggle
        document.getElementById('chatbot-voice-toggle').addEventListener('click', () => {
            this.toggleVoiceReading();
        });
        
        // Auto-resize input
        document.getElementById('chatbot-input').addEventListener('input', (e) => {
            // Simple auto-resize logic could go here
        });
    }
    
    toggleChatbot() {
        const widget = document.getElementById('chatbot-widget');
        const notification = document.getElementById('chatbot-notification');
        
        this.isOpen = !this.isOpen;
        widget.classList.toggle('open', this.isOpen);
        
        if (this.isOpen) {
            notification.style.display = 'none';
            document.getElementById('chatbot-input').focus();
            this.markMessagesAsRead();
        }
    }
    
    toggleVoiceReading() {
        const btn = document.getElementById('chatbot-voice-toggle');
        const icon = btn.querySelector('i');
        
        this.isListening = !this.isListening;
        
        if (this.isListening) {
            icon.className = 'fas fa-volume-off';
            btn.title = 'Disable Voice Reading';
            this.speak("Voice reading enabled. I'll read my responses aloud.");
        } else {
            icon.className = 'fas fa-volume-up';
            btn.title = 'Enable Voice Reading';
            this.synthesis.cancel();
        }
    }
    
    addGreeting() {
        const greeting = `
            <div class="chatbot-message bot-message">
                <div class="message-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="message-content">
                    <div class="message-text">
                        Hello! I'm your Inclusify assistant. I'm here to help you navigate our platform and answer any questions about our inclusive employment opportunities. 
                        <br><br>
                        You can ask me about:
                        <ul>
                            <li>What Inclusify is and our mission</li>
                            <li>Available scholarships and opportunities</li>
                            <li>How to use voice commands</li>
                            <li>Posting opportunities</li>
                            <li>Anything else about Inclusify!</li>
                        </ul>
                        How can I help you today?
                    </div>
                    <div class="message-time">${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
                </div>
            </div>
        `;
        
        document.getElementById('chatbot-messages').innerHTML = greeting;
        this.showNotification();
    }
    
    handleSendMessage() {
        const input = document.getElementById('chatbot-input');
        const message = input.value.trim();
        
        if (message) {
            this.sendMessage(message);
            input.value = '';
        }
    }
    
    async sendMessage(message) {
        // Add user message
        this.addMessage(message, 'user');
        
        // Show typing indicator
        this.showTypingIndicator();
        
        // Get response
        const response = await this.getResponse(message);
        
        // Remove typing indicator and add bot response
        this.hideTypingIndicator();
        this.addMessage(response, 'bot');
        
        // Read aloud if voice is enabled
        if (this.isListening) {
            this.speak(response);
        }
    }
    
    async getResponse(message) {
        // First try local knowledge
        const localResponse = this.searchLocalKnowledge(message);
        if (localResponse) {
            return localResponse;
        }
        
        // Fall back to Gemini AI
        try {
            const aiResponse = await this.getGeminiResponse(message);
            return aiResponse;
        } catch (error) {
            console.error('Error getting AI response:', error);
            return "I apologize, but I'm having trouble accessing my AI capabilities right now. For immediate assistance, please check our Help section or contact our support team. In the meantime, try using our voice commands or accessibility features to navigate the site!";
        }
    }
    
    searchLocalKnowledge(message) {
        const lowerMessage = message.toLowerCase();
        
        for (const item of this.localKnowledge) {
            for (const keyword of item.keywords) {
                if (lowerMessage.includes(keyword.toLowerCase())) {
                    return item.answer;
                }
            }
        }
        
        return null;
    }
    
    async getGeminiResponse(message) {
        const context = `You are an AI assistant for Inclusify, a platform dedicated to creating inclusive employment opportunities for people with disabilities. Be helpful, positive, and encouraging. Keep responses concise but informative. The user asked: "${message}"`;
        
        const response = await fetch('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-goog-api-key': this.apiKey
            },
            body: JSON.stringify({
                contents: [{
                    parts: [{
                        text: context
                    }]
                }],
                generationConfig: {
                    maxOutputTokens: 200,
                    temperature: 0.7
                }
            })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        return data.candidates[0].content.parts[0].text;
    }
    
    addMessage(text, sender) {
        const messagesContainer = document.getElementById('chatbot-messages');
        const isBot = sender === 'bot';
        const time = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        
        const messageHTML = `
            <div class="chatbot-message ${isBot ? 'bot-message' : 'user-message'}">
                ${isBot ? `
                    <div class="message-avatar">
                        <i class="fas fa-robot"></i>
                    </div>
                ` : ''}
                <div class="message-content">
                    <div class="message-text">${this.formatMessage(text)}</div>
                    <div class="message-time">${time}</div>
                </div>
            </div>
        `;
        
        messagesContainer.insertAdjacentHTML('beforeend', messageHTML);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        
        // Store in chat history
        this.chatHistory.push({ text, sender, time });
        
        // Show notification if chatbot is closed
        if (!this.isOpen && isBot) {
            this.showNotification();
        }
    }
    
    formatMessage(text) {
        // Convert line breaks and format lists
        return text
            .replace(/\n/g, '<br>')
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>');
    }
    
    showTypingIndicator() {
        const messagesContainer = document.getElementById('chatbot-messages');
        const typingHTML = `
            <div id="typing-indicator" class="chatbot-message bot-message">
                <div class="message-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="message-content">
                    <div class="typing-indicator">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
        `;
        
        messagesContainer.insertAdjacentHTML('beforeend', typingHTML);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    hideTypingIndicator() {
        const typingIndicator = document.getElementById('typing-indicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
    }
    
    showNotification() {
        const notification = document.getElementById('chatbot-notification');
        notification.style.display = 'block';
    }
    
    markMessagesAsRead() {
        const notification = document.getElementById('chatbot-notification');
        notification.style.display = 'none';
    }
    
    speak(text) {
        if (this.synthesis && this.isListening) {
            this.synthesis.cancel();
            
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.rate = 0.9;
            utterance.pitch = 1;
            utterance.volume = 0.8;
            
            this.synthesis.speak(utterance);
        }
    }
    
    showHelp() {
        const helpMessage = `
            <strong>How to use the Inclusify Assistant:</strong><br><br>
            
            <strong>Voice Reading:</strong> Click the volume icon to enable/disable voice reading of responses.<br><br>
            
            <strong>Quick Actions:</strong> Use the suggested buttons for common questions.<br><br>
            
            <strong>Sample Questions:</strong>
            <ul>
                <li>"What is Inclusify?"</li>
                <li>"Tell me about scholarships"</li>
                <li>"What opportunities are available?"</li>
                <li>"Can I use voice commands?"</li>
                <li>"How do I post opportunities?"</li>
            </ul>
            
            I'm powered by local knowledge about Inclusify and AI for general questions. Feel free to ask me anything!
        `;
        
        this.addMessage(helpMessage, 'bot');
        if (this.isListening) {
            this.speak("Here's how to use the Inclusify Assistant. You can ask me about Inclusify, scholarships, opportunities, and much more!");
        }
    }
}

// Initialize chatbot when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.inclusifyBot = new InclusifyChatbot();
});
