<?php
require_once '../includes/auth.php';
requireRole('user');

$user = getCurrentUser();
$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $skills_json = $_POST['skills_json'] ?? '[]';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Invalid request. Please try again.';
    } else {
        // Convert JSON array to comma-separated string
        $skills_array = json_decode($skills_json, true);
        if ($skills_array === null) {
            $error = 'Invalid skills data format.';
        } else {
            // Store as comma-separated string for backward compatibility
            $skills_string = implode(', ', $skills_array);
            
            try {
                // Update skills in database
                $stmt = $pdo->prepare("UPDATE users SET skills = ? WHERE id = ?");
                $stmt->execute([$skills_string, $user['id']]);
                
                // Verify the save was successful
                $verify_stmt = $pdo->prepare("SELECT skills FROM users WHERE id = ?");
                $verify_stmt->execute([$user['id']]);
                $saved_skills = $verify_stmt->fetchColumn();
                
                if ($saved_skills === $skills_string) {
                    $success = 'Your skills have been saved successfully to your profile!';
                } else {
                    $success = 'Your skills have been updated successfully!';
                }
                
                // Refresh user data from database
                $user = getCurrentUser();
            } catch (PDOException $e) {
                // If column doesn't exist, try to add it
                if (strpos($e->getMessage(), "Unknown column 'skills'") !== false) {
                    try {
                        $pdo->exec("ALTER TABLE users ADD COLUMN skills TEXT NULL");
                        $stmt = $pdo->prepare("UPDATE users SET skills = ? WHERE id = ?");
                        $stmt->execute([$skills_string, $user['id']]);
                        $success = 'Your skills have been updated successfully!';
                        $user = getCurrentUser();
                    } catch (PDOException $e2) {
                        $error = 'Failed to update skills. Please try again.';
                        error_log("Skills update error: " . $e2->getMessage());
                    }
                } else {
                    $error = 'Failed to update skills. Please try again.';
                    error_log("Skills update error: " . $e->getMessage());
                }
            }
        }
    }
}

// Get current skills and convert to array
$current_skills_string = $user['skills'] ?? '';
$current_skills_array = [];
if (!empty($current_skills_string)) {
    // Split by comma and clean up
    $current_skills_array = array_map('trim', explode(',', $current_skills_string));
    $current_skills_array = array_filter($current_skills_array); // Remove empty values
    $current_skills_array = array_values($current_skills_array); // Re-index
}

// Predefined list of common skills
$common_skills = [
    // Programming Languages
    'Python', 'JavaScript', 'Java', 'C++', 'C#', 'PHP', 'Ruby', 'Go', 'Swift', 'Kotlin', 'TypeScript',
    'HTML', 'CSS', 'SQL', 'R', 'MATLAB', 'Scala', 'Perl', 'Rust', 'Dart',
    
    // Web Development
    'React', 'Angular', 'Vue.js', 'Node.js', 'Express.js', 'Django', 'Flask', 'Laravel', 'Spring Boot',
    'jQuery', 'Bootstrap', 'Tailwind CSS', 'SASS', 'LESS', 'REST API', 'GraphQL', 'WebSocket',
    
    // Mobile Development
    'React Native', 'Flutter', 'Android Development', 'iOS Development', 'Xamarin',
    
    // Databases
    'MySQL', 'PostgreSQL', 'MongoDB', 'Redis', 'SQLite', 'Oracle', 'Microsoft SQL Server', 'Firebase',
    
    // Cloud & DevOps
    'AWS', 'Azure', 'Google Cloud', 'Docker', 'Kubernetes', 'Jenkins', 'CI/CD', 'Git', 'GitHub', 'GitLab',
    'Linux', 'Bash Scripting', 'Terraform', 'Ansible',
    
    // Data Science & Analytics
    'Data Analysis', 'Machine Learning', 'Deep Learning', 'TensorFlow', 'PyTorch', 'Pandas', 'NumPy',
    'Tableau', 'Power BI', 'Excel', 'Data Visualization',
    
    // Design & UI/UX
    'UI/UX Design', 'Figma', 'Adobe XD', 'Sketch', 'Photoshop', 'Illustrator', 'InDesign',
    'User Research', 'Wireframing', 'Prototyping',
    
    // Testing & QA
    'Software Testing', 'Manual Testing', 'Automated Testing', 'Selenium', 'JUnit', 'TestNG',
    'Quality Assurance', 'Bug Tracking',
    
    // Project Management
    'Agile', 'Scrum', 'Kanban', 'Project Management', 'JIRA', 'Trello', 'Asana',
    
    // Soft Skills
    'Communication', 'Teamwork', 'Problem Solving', 'Leadership', 'Time Management',
    'Critical Thinking', 'Adaptability', 'Creativity', 'Collaboration', 'Attention to Detail',
    
    // Business Skills
    'Business Analysis', 'Marketing', 'Digital Marketing', 'SEO', 'Content Writing',
    'Sales', 'Customer Service', 'Financial Analysis',
    
    // Other Technical Skills
    'Network Administration', 'Cybersecurity', 'Blockchain', 'IoT', 'Microservices',
    'System Design', 'Algorithm Design', 'Data Structures'
];

$page_title = 'My Skills';
include '../includes/header.php';
?>

<style>
.skills-container {
    border: 2px dashed #d0d0d0;
    border-radius: 8px;
    padding: 1rem;
    min-height: 100px;
    background-color: #f5f5f5;
}

.skill-tag {
    display: inline-flex;
    align-items: center;
    background: #ffffff;
    color: #1a1a1a;
    border: 2px solid #1a1a1a;
    padding: 0.4rem 0.8rem;
    border-radius: 4px;
    margin: 0.25rem;
    font-size: 0.9rem;
    font-weight: 700;
    gap: 0.5rem;
}

.skill-tag .remove-skill {
    cursor: pointer;
    background: #1a1a1a;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    transition: all 0.2s;
}

.skill-tag .remove-skill:hover {
    background: #2d2d2d;
    transform: scale(1.1);
}

.skills-autocomplete {
    position: relative;
    width: 100%;
}

.skills-input {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #d0d0d0;
    border-radius: 4px;
    font-size: 1rem;
    color: #1a1a1a;
}

.skills-input:focus {
    outline: none;
    border-color: #1a1a1a;
    box-shadow: 0 0 0 2px rgba(26, 26, 26, 0.1);
}

.skills-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 2px solid #d0d0d0;
    border-radius: 4px;
    max-height: 300px;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    display: none;
    margin-top: 4px;
}

.skills-dropdown.show {
    display: block;
}

.skill-option {
    padding: 0.75rem 1rem;
    cursor: pointer;
    transition: background 0.2s;
    border-bottom: 2px solid #e0e0e0;
    color: #1a1a1a;
}

.skill-option:hover,
.skill-option.highlighted {
    background-color: #f5f5f5;
    border-left: 4px solid #1a1a1a;
}

.skill-option:last-child {
    border-bottom: none;
}

.skill-option.add-custom {
    font-weight: 700;
    color: #1a1a1a;
    border-top: 2px solid #1a1a1a;
}

.empty-state {
    text-align: center;
    padding: 2rem;
    color: #666666;
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
                    <li class="breadcrumb-item active">My Skills</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold">
                <i class="fas fa-code me-3"></i>My Skills
            </h1>
            <p class="lead text-muted">Add and manage your skills. Start typing to search or add a custom skill.</p>
        </div>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo escape($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo escape($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header" style="background: #1a1a1a; color: white; border-bottom: 3px solid #ffff00; font-weight: 700;">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Add Your Skills</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" id="skillsForm">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="skills_json" id="skills_json" value='[]'>
                        
                        <div class="mb-4">
                            <label for="skill-input" class="form-label fw-bold">Search and Add Skills *</label>
                            <div class="skills-autocomplete">
                                <input 
                                    type="text" 
                                    class="skills-input form-control" 
                                    id="skill-input" 
                                    placeholder="Type to search skills or add a custom skill..."
                                    autocomplete="off"
                                >
                                <div class="skills-dropdown" id="skills-dropdown"></div>
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Type to search from common skills or press Enter to add a custom skill.
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Your Skills</label>
                            <div class="skills-container" id="skills-container">
                                <?php if (empty($current_skills_array)): ?>
                                    <div class="empty-state">
                                        <i class="fas fa-plus-circle fa-2x mb-2 text-muted"></i>
                                        <p class="text-muted mb-0">No skills added yet. Start typing above to add skills.</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($current_skills_array as $skill): ?>
                                        <span class="skill-tag" data-skill="<?php echo escape($skill); ?>">
                                            <?php echo escape($skill); ?>
                                            <span class="remove-skill" onclick="removeSkill('<?php echo escape(addslashes($skill)); ?>')">×</span>
                                        </span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Save Skills
                            </button>
                            <a href="user-dashboard.php" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Help Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background: #1a1a1a; color: white; border-bottom: 3px solid #ffff00; font-weight: 700;">
                    <h5 class="mb-0"><i class="fas fa-question-circle me-2"></i>How to Add Skills</h5>
                </div>
                <div class="card-body">
                    <ol class="mb-0">
                        <li class="mb-2">Type in the search box to see matching skills</li>
                        <li class="mb-2">Click on a skill from the dropdown to add it</li>
                        <li class="mb-2">Type a custom skill and press Enter to add it</li>
                        <li>Click the × button on a skill tag to remove it</li>
                    </ol>
                </div>
            </div>
            
            <!-- Tips Card -->
            <div class="card shadow-sm">
                <div class="card-header" style="background: #1a1a1a; color: white; border-bottom: 3px solid #ffff00; font-weight: 700;">
                    <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Best Practices</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li class="mb-2">Add 5-10 relevant skills</li>
                        <li class="mb-2">Include both technical and soft skills</li>
                        <li class="mb-2">Be honest about your proficiency level</li>
                        <li>Update regularly as you learn new skills</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Skills array
const allSkills = <?php echo json_encode($common_skills); ?>;
const selectedSkills = <?php echo json_encode($current_skills_array); ?>;

let highlightedIndex = -1;

// Get DOM elements
const skillInput = document.getElementById('skill-input');
const skillsDropdown = document.getElementById('skills-dropdown');
const skillsContainer = document.getElementById('skills-container');
const skillsForm = document.getElementById('skillsForm');
const skillsJsonInput = document.getElementById('skills_json');

// Filter skills based on input
function filterSkills(query) {
    if (!query || query.trim().length === 0) {
        return [];
    }
    
    query = query.toLowerCase().trim();
    const filtered = allSkills.filter(skill => 
        skill.toLowerCase().includes(query) && 
        !selectedSkills.includes(skill)
    );
    
    // Add custom option if query doesn't match exactly
    if (query && !allSkills.some(s => s.toLowerCase() === query) && !selectedSkills.includes(query)) {
        filtered.push(query);
    }
    
    return filtered.slice(0, 10); // Limit to 10 results
}

// Display filtered skills in dropdown
function showDropdown(skills, customSkill = null) {
    if (skills.length === 0 && !customSkill) {
        skillsDropdown.classList.remove('show');
        return;
    }
    
    skillsDropdown.innerHTML = '';
    
    skills.forEach((skill, index) => {
        const option = document.createElement('div');
        option.className = 'skill-option';
        option.textContent = skill;
        if (index === highlightedIndex) {
            option.classList.add('highlighted');
        }
        option.onclick = () => addSkill(skill);
        skillsDropdown.appendChild(option);
    });
    
    // Add custom skill option at the end
    if (customSkill && !allSkills.some(s => s.toLowerCase() === customSkill.toLowerCase()) && !selectedSkills.includes(customSkill)) {
        const customOption = document.createElement('div');
        customOption.className = 'skill-option add-custom';
        customOption.innerHTML = `<i class="fas fa-plus me-2"></i>Add "${customSkill}" as custom skill`;
        customOption.onclick = () => addSkill(customSkill);
        skillsDropdown.appendChild(customOption);
    }
    
    skillsDropdown.classList.add('show');
}

// Add skill to selected list
function addSkill(skill) {
    skill = skill.trim();
    if (!skill || selectedSkills.includes(skill)) {
        return;
    }
    
    selectedSkills.push(skill);
    updateSkillsDisplay();
    updateSkillsJson();
    skillInput.value = '';
    skillsDropdown.classList.remove('show');
    skillInput.focus();
    highlightedIndex = -1;
}

// Remove skill from selected list
function removeSkill(skill) {
    const index = selectedSkills.indexOf(skill);
    if (index > -1) {
        selectedSkills.splice(index, 1);
        updateSkillsDisplay();
        updateSkillsJson();
    }
}

// Update skills display
function updateSkillsDisplay() {
    if (selectedSkills.length === 0) {
        skillsContainer.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-plus-circle fa-2x mb-2 text-muted"></i>
                <p class="text-muted mb-0">No skills added yet. Start typing above to add skills.</p>
            </div>
        `;
        return;
    }
    
    skillsContainer.innerHTML = selectedSkills.map(skill => `
        <span class="skill-tag" data-skill="${skill.replace(/"/g, '&quot;')}">
            ${skill.replace(/</g, '&lt;').replace(/>/g, '&gt;')}
            <span class="remove-skill" onclick="removeSkill('${skill.replace(/'/g, "\\'")}')">×</span>
        </span>
    `).join('');
}

// Update hidden JSON input
function updateSkillsJson() {
    skillsJsonInput.value = JSON.stringify(selectedSkills);
}

// Handle input
skillInput.addEventListener('input', function(e) {
    const query = e.target.value;
    if (query.length > 0) {
        const filtered = filterSkills(query);
        const customSkill = query.trim();
        highlightedIndex = -1;
        showDropdown(filtered, customSkill);
    } else {
        skillsDropdown.classList.remove('show');
    }
});

// Handle keyboard navigation
skillInput.addEventListener('keydown', function(e) {
    const options = skillsDropdown.querySelectorAll('.skill-option');
    
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        highlightedIndex = Math.min(highlightedIndex + 1, options.length - 1);
        options.forEach((opt, idx) => {
            opt.classList.toggle('highlighted', idx === highlightedIndex);
        });
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        highlightedIndex = Math.max(highlightedIndex - 1, -1);
        options.forEach((opt, idx) => {
            opt.classList.toggle('highlighted', idx === highlightedIndex);
        });
    } else if (e.key === 'Enter') {
        e.preventDefault();
        if (highlightedIndex >= 0 && options[highlightedIndex]) {
            options[highlightedIndex].click();
        } else if (skillInput.value.trim()) {
            // Add custom skill
            addSkill(skillInput.value.trim());
        }
    } else if (e.key === 'Escape') {
        skillsDropdown.classList.remove('show');
        highlightedIndex = -1;
    }
});

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (!skillInput.contains(e.target) && !skillsDropdown.contains(e.target)) {
        skillsDropdown.classList.remove('show');
        highlightedIndex = -1;
    }
});

// Initialize
updateSkillsDisplay();
updateSkillsJson();

// Form validation
skillsForm.addEventListener('submit', function(e) {
    if (selectedSkills.length === 0) {
        e.preventDefault();
        alert('Please add at least one skill.');
        skillInput.focus();
        return false;
    }
});
</script>

<?php include '../includes/footer.php'; ?>