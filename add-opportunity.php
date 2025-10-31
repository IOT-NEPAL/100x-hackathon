<?php
require_once '../includes/auth.php';
requireRole('organizer');

$user = getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $type = $_POST['type'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $deadline = $_POST['deadline'] ?? '';
    
    // Handle requirements as JSON array (from tag system)
    $requirements_json = $_POST['requirements_json'] ?? '[]';
    $requirements_array = json_decode($requirements_json, true);
    $requirements = '';
    if ($requirements_array && is_array($requirements_array)) {
        $requirements = implode(', ', $requirements_array);
    } else {
        // Fallback to text if JSON parsing fails
    $requirements = trim($_POST['requirements'] ?? '');
    }
    
    $contact_info = trim($_POST['contact_info'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Invalid request. Please try again.';
    } elseif (empty($title) || empty($description) || empty($type)) {
        $error = 'Please fill in all required fields.';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO opportunities (title, description, type, organizer_id, location, application_deadline, requirements, contact_email)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $title,
                $description,
                $type,
                $user['id'],
                $location ?: null,
                $deadline ?: null,
                $requirements ?: null,
                $contact_info ?: null
            ]);
            
            $opportunity_id = $pdo->lastInsertId();
            
            // Log activity
            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, 'opportunity_posted', 'Posted new opportunity: " . $title . "', ?)");
            $stmt->execute([$user['id'], $_SERVER['REMOTE_ADDR'] ?? '']);
            
            header("Location: ../view-opportunity.php?id=$opportunity_id&success=created");
            exit;
            
        } catch (PDOException $e) {
            $error = 'Failed to create opportunity. Please try again.';
            error_log("Create opportunity error: " . $e->getMessage());
        }
    }
}

// Predefined list of common skills for autocomplete
$common_skills = [
    // Programming Languages
    'Python', 'JavaScript', 'Java', 'C++', 'C#', 'PHP', 'Ruby', 'Go', 'Swift', 'Kotlin', 'TypeScript',
    'HTML', 'CSS', 'SQL', 'R', 'MATLAB', 'Scala', 'Perl', 'Rust', 'D老人的',
    
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
    'System Design', 'Algorithm Design', 'Data Structures',
    
    // Qualifications
    'Bachelor Degree', 'Master Degree', 'Diploma', 'Certification', '1-2 years experience',
    'Fresher Welcome', 'Entry Level', 'Mid Level', 'Senior Level'
];

$page_title = 'Post New Opportunity';
include '../includes/header.php';
?>

<style>
.skills-container {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 1rem;
    min-height: 100px;
    background-color: #f8f9fa;
}

.skill-tag {
    display: inline-flex;
    align-items: center;
    background: #0d6efd;
    color: white;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    margin: 0.25rem;
    font-size: 0.9rem;
    gap: 0.5rem;
}

.skill-tag .remove-skill {
    cursor: pointer;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    transition: background 0.2s;
}

.skill-tag .remove-skill:hover {
    background: rgba(255, 255, 255, 0.5);
}

.skills-autocomplete {
    position: relative;
    width: 100%;
}

.skills-input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ced4da;
    border-radius: 6px;
    font-size: 1rem;
}

.skills-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ced4da;
    border-radius: 6px;
    max-height: 300px;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
    border-bottom: 1px solid #f0f0f0;
}

.skill-option:hover,
.skill-option.highlighted {
    background-color: #e7f3ff;
}

.skill-option:last-child {
    border-bottom: none;
}

.skill-option.add-custom {
    font-weight: 600;
    color: #0d6efd;
    border-top: 2px solid #0d6efd;
}

.empty-state {
    text-align: center;
    padding: 2rem;
    color: #6c757d;
}
</style>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="organizer-dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Post New Opportunity</li>
                </ol>
            </nav>
            
            <div class="card shadow">
                <div class="card-header">
                    <h3 class="mb-0">
                        <i class="fas fa-plus me-2"></i>Post New Opportunity
                    </h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo escape($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Opportunity Title *</label>
                                    <input type="text" class="form-control" id="title" name="title" 
                                           value="<?php echo escape($_POST['title'] ?? ''); ?>" required>
                                    <div class="invalid-feedback">Please provide a title for this opportunity.</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Type *</label>
                                    <select class="form-select" id="type" name="type" required>
                                        <option value="">Select Type</option>
                                        <option value="employment" <?php echo ($_POST['type'] ?? $_GET['type'] ?? '') === 'employment' ? 'selected' : ''; ?>>Employment (Full-time/Part-time)</option>
                                        <option value="internship" <?php echo ($_POST['type'] ?? $_GET['type'] ?? '') === 'internship' ? 'selected' : ''; ?>>Internship</option>
                                    </select>
                                    <div class="invalid-feedback">Please select an opportunity type.</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea class="form-control" id="description" name="description" rows="6" required
                                      placeholder="Provide a detailed description of this opportunity..."><?php echo escape($_POST['description'] ?? ''); ?></textarea>
                            <div class="invalid-feedback">Please provide a description.</div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="location" class="form-label">Location</label>
                                    <input type="text" class="form-control" id="location" name="location" 
                                           value="<?php echo escape($_POST['location'] ?? ''); ?>"
                                           placeholder="e.g., Remote, New York, NY">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="deadline" class="form-label">Application Deadline</label>
                                    <input type="date" class="form-control" id="deadline" name="deadline" 
                                           value="<?php echo escape($_POST['deadline'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="requirements-input" class="form-label fw-bold">Required Skills & Qualifications</label>
                            <div class="skills-autocomplete">
                                <input 
                                    type="text" 
                                    class="skills-input form-control" 
                                    id="requirements-input" 
                                    placeholder="Type to search skills or add a custom requirement..."
                                    autocomplete="off"
                                >
                                <div class="skills-dropdown" id="requirements-dropdown"></div>
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Type to search from common skills or press Enter to add a custom requirement.
                            </div>
                            <input type="hidden" name="requirements_json" id="requirements_json" value='[]'>
                            <input type="hidden" name="requirements" id="requirements" value="">
                            
                            <div class="mt-3">
                                <label class="form-label fw-bold">Selected Requirements</label>
                                <div class="skills-container" id="requirements-container">
                                    <div class="empty-state">
                                        <i class="fas fa-plus-circle fa-2x mb-2 text-muted"></i>
                                        <p class="text-muted mb-0">No requirements added yet. Start typing above to add requirements.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="contact_info" class="form-label">Contact Email</label>
                            <input type="email" class="form-control" id="contact_info" name="contact_info" 
                                   value="<?php echo escape($_POST['contact_info'] ?? ''); ?>"
                                   placeholder="Enter contact email for applications">
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="organizer-dashboard.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-plus me-2"></i>Post Opportunity
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Skills array
const allSkills = <?php echo json_encode($common_skills); ?>;
const selectedRequirements = [];

let highlightedIndex = -1;

// Get DOM elements
const reqInput = document.getElementById('requirements-input');
const reqDropdown = document.getElementById('requirements-dropdown');
const reqContainer = document.getElementById('requirements-container');
const reqForm = document.querySelector('form');
const reqJsonInput = document.getElementById('requirements_json');
const reqHiddenInput = document.getElementById('requirements');

// Filter skills based on input
function filterRequirements(query) {
    if (!query || query.trim().length === 0) {
        return [];
    }
    
    query = query.toLowerCase().trim();
    const filtered = allSkills.filter(skill => 
        skill.toLowerCase().includes(query) && 
        !selectedRequirements.includes(skill)
    );
    
    // Add custom option if query doesn't match exactly
    if (query && !allSkills.some(s => s.toLowerCase() === query) && !selectedRequirements.includes(query)) {
        filtered.push(query);
    }
    
    return filtered.slice(0, 10); // Limit to 10 results
}

// Display filtered skills in dropdown
function showDropdown(skills, customSkill = null) {
    if (skills.length === 0 && !customSkill) {
        reqDropdown.classList.remove('show');
        return;
    }
    
    reqDropdown.innerHTML = '';
    
    skills.forEach((skill, index) => {
        const option = document.createElement('div');
        option.className = 'skill-option';
        option.textContent = skill;
        if (index === highlightedIndex) {
            option.classList.add('highlighted');
        }
        option.onclick = () => addRequirement(skill);
        reqDropdown.appendChild(option);
    });
    
    // Add custom skill option at the end
    if (customSkill && !allSkills.some(s => s.toLowerCase() === customSkill.toLowerCase()) && !selectedRequirements.includes(customSkill)) {
        const customOption = document.createElement('div');
        customOption.className = 'skill-option add-custom';
        customOption.innerHTML = `<i class="fas fa-plus me-2"></i>Add "${customSkill}" as custom requirement`;
        customOption.onclick = () => addRequirement(customSkill);
        reqDropdown.appendChild(customOption);
    }
    
    reqDropdown.classList.add('show');
}

// Add requirement to selected list
function addRequirement(requirement) {
    requirement = requirement.trim();
    if (!requirement || selectedRequirements.includes(requirement)) {
        return;
    }
    
    selectedRequirements.push(requirement);
    updateRequirementsDisplay();
    updateRequirementsJson();
    reqInput.value = '';
    reqDropdown.classList.remove('show');
    reqInput.focus();
    highlightedIndex = -1;
}

// Remove requirement from selected list
function removeRequirement(requirement) {
    const index = selectedRequirements.indexOf(requirement);
    if (index > -1) {
        selectedRequirements.splice(index, 1);
        updateRequirementsDisplay();
        updateRequirementsJson();
    }
}

// Update requirements display
function updateRequirementsDisplay() {
    if (selectedRequirements.length === 0) {
        reqContainer.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-plus-circle fa-2x mb-2 text-muted"></i>
                <p class="text-muted mb-0">No requirements added yet. Start typing above to add requirements.</p>
            </div>
        `;
        return;
    }
    
    reqContainer.innerHTML = selectedRequirements.map(req => `
        <span class="skill-tag" data-requirement="${req.replace(/"/g, '&quot;')}">
            ${req.replace(/</g, '&lt;').replace(/>/g, '&gt;')}
            <span class="remove-skill" onclick="removeRequirement('${req.replace(/'/g, "\\'")}')">×</span>
        </span>
    `).join('');
}

// Update hidden JSON and text inputs
function updateRequirementsJson() {
    reqJsonInput.value = JSON.stringify(selectedRequirements);
    reqHiddenInput.value = selectedRequirements.join(', ');
}

// Handle input
reqInput.addEventListener('input', function(e) {
    const query = e.target.value;
    if (query.length > 0) {
        const filtered = filterRequirements(query);
        const customSkill = query.trim();
        highlightedIndex = -1;
        showDropdown(filtered, customSkill);
    } else {
        reqDropdown.classList.remove('show');
    }
});

// Handle keyboard navigation
reqInput.addEventListener('keydown', function(e) {
    const options = reqDropdown.querySelectorAll('.skill-option');
    
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
        } else if (reqInput.value.trim()) {
            // Add custom requirement
            addRequirement(reqInput.value.trim());
        }
    } else if (e.key === 'Escape') {
        reqDropdown.classList.remove('show');
        highlightedIndex = -1;
    }
});

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (!reqInput.contains(e.target) && !reqDropdown.contains(e.target)) {
        reqDropdown.classList.remove('show');
        highlightedIndex = -1;
    }
});

// Initialize
updateRequirementsDisplay();
updateRequirementsJson();
</script>

<?php include '../includes/footer.php'; ?>
