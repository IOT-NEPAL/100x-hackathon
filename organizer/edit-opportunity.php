<?php
require_once '../includes/auth.php';
requireRole('organizer');

$user = getCurrentUser();
$opportunity_id = intval($_GET['id'] ?? 0);

// Get the opportunity
$stmt = $pdo->prepare("SELECT * FROM opportunities WHERE id = ? AND organizer_id = ?");
$stmt->execute([$opportunity_id, $user['id']]);
$opportunity = $stmt->fetch();

if (!$opportunity) {
    header("Location: my-opportunities.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $type = $_POST['type'] ?? '';
    $description = trim($_POST['description'] ?? '');
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
    $location = trim($_POST['location'] ?? '');
    $salary_range = trim($_POST['salary_range'] ?? '');
    $application_deadline = $_POST['application_deadline'] ?? '';
    $contact_email = trim($_POST['contact_email'] ?? '');
    $contact_phone = trim($_POST['contact_phone'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Validate CSRF token
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Invalid request. Please try again.';
    } elseif (empty($title) || empty($type) || empty($description) || empty($location)) {
        $error = 'Please fill in all required fields.';
    } elseif (!in_array($type, ['employment', 'internship'])) {
        $error = 'Please select a valid opportunity type.';
    } elseif (!empty($application_deadline) && strtotime($application_deadline) < time()) {
        $error = 'Application deadline must be in the future.';
    } elseif (!empty($contact_email) && !filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid contact email address.';
    } else {
        try {
            // Handle file upload if new file is provided
            $file_path = $opportunity['file_path'];
            if (isset($_FILES['opportunity_file']) && $_FILES['opportunity_file']['error'] === UPLOAD_ERR_OK) {
                $allowed_types = ['pdf', 'doc', 'docx', 'txt'];
                $file_info = pathinfo($_FILES['opportunity_file']['name']);
                $file_extension = strtolower($file_info['extension']);
                
                if (!in_array($file_extension, $allowed_types)) {
                    throw new Exception('Invalid file type. Please upload PDF, DOC, DOCX, or TXT files only.');
                }
                
                if ($_FILES['opportunity_file']['size'] > 5 * 1024 * 1024) { // 5MB limit
                    throw new Exception('File size too large. Maximum size is 5MB.');
                }
                
                // Create upload directory if it doesn't exist
                $upload_dir = '../uploads/opportunity_files/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                // Generate unique filename
                $filename = uniqid() . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['opportunity_file']['tmp_name'], $upload_path)) {
                    // Delete old file if it exists
                    if ($file_path && file_exists('../' . $file_path)) {
                        unlink('../' . $file_path);
                    }
                    $file_path = 'uploads/opportunity_files/' . $filename;
                } else {
                    throw new Exception('Failed to upload file. Please try again.');
                }
            }
            
            // Update opportunity
            $stmt = $pdo->prepare("
                UPDATE opportunities SET 
                    title = ?, type = ?, description = ?, requirements = ?, 
                    location = ?, salary_range = ?, application_deadline = ?, 
                    contact_email = ?, contact_phone = ?, file_path = ?, is_active = ?
                WHERE id = ? AND organizer_id = ?
            ");
            
            $stmt->execute([
                $title, $type, $description, $requirements, $location, 
                $salary_range ?: null, 
                $application_deadline ?: null, 
                $contact_email ?: null, 
                $contact_phone ?: null, 
                $file_path,
                $is_active,
                $opportunity_id, 
                $user['id']
            ]);
            
            // Log activity
            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, 'opportunity_update', ?, ?)");
            $stmt->execute([$user['id'], "Updated opportunity: $title", $_SERVER['REMOTE_ADDR'] ?? '']);
            
            $success = 'Opportunity updated successfully!';
            
            // Refresh opportunity data
            $stmt = $pdo->prepare("SELECT * FROM opportunities WHERE id = ? AND organizer_id = ?");
            $stmt->execute([$opportunity_id, $user['id']]);
            $opportunity = $stmt->fetch();
            
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Predefined list of common skills for autocomplete
$common_skills = [
    // Programming Languages
    'Python', 'JavaScript', 'Java', 'C++', 'C#', 'PHP', 'Ruby', 'Go', 'Swift', 'Kotlin', 'TypeScript',
    'HTML', 'CSS', 'SQL', 'R', 'MATLAB', 'Scala', 'Perl', 'Rust', oft 'Dart',
    
    // Web Development
    'React', 'Angular', 'Vue.js does', 'Node.js', 'Express.js', 'Django', 'Flask', 'Laravel', 'Spring Boot',
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

// Get current requirements and convert to array
$current_requirements_string = $opportunity['requirements'] ?? '';
$current_requirements_array = [];
if (!empty($current_requirements_string)) {
    $current_requirements_array = array_map('trim', explode(',', $current_requirements_string));
    $current_requirements_array = array_filter($current_requirements_array);
    $current_requirements_array = array_values($current_requirements_array);
}

$page_title = 'Edit Opportunity';
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
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="organizer-dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="my-opportunities.php">My Opportunities</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Opportunity</li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-edit me-2"></i>Edit Opportunity</h2>
                <div>
                    <a href="../view-opportunity.php?id=<?php echo $opportunity['id']; ?>" class="btn btn-outline-info me-2">
                        <i class="fas fa-eye me-2"></i>Preview
                    </a>
                    <a href="my-opportunities.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Back to My Opportunities
                    </a>
                </div>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo escape($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo escape($success); ?>
                </div>
            <?php endif; ?>
            
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">Opportunity Details</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Opportunity Title *</label>
                                    <input type="text" class="form-control" id="title" name="title" 
                                           value="<?php echo escape($opportunity['title']); ?>" required>
                                    <div class="invalid-feedback">Please enter an opportunity title.</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Type *</label>
                                    <select class="form-select" id="type" name="type" required>
                                        <option value="">Choose type...</option>
                                        <option value="employment" <?php echo $opportunity['type'] === 'employment' ? 'selected' : ''; ?>>Employment (Full-time/Part-time)</option>
                                        <option value="internship" <?php echo $opportunity['type'] === 'internship' ? 'selected' : ''; ?>>Internship</option>
                                    </select>
                                    <div class="invalid-feedback">Please select an opportunity type.</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea class="form-control" id="description" name="description" rows="5" required><?php echo escape($opportunity['description']); ?></textarea>
                            <div class="form-text">Provide a detailed description of the opportunity, responsibilities, and what you're looking for.</div>
                            <div class="invalid-feedback">Please enter a description.</div>
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
                                    <?php if (empty($current_requirements_array)): ?>
                                        <div class="empty-state">
                                            <i class="fas fa-plus-circle fa-2x mb-2 text-muted"></i>
                                            <p class="text-muted mb-0">No requirements added yet. Start typing above to add requirements.</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($current_requirements_array as $req): ?>
                                            <span class="skill-tag" data-requirement="<?php echo escape($req); ?>">
                                                <?php echo escape($req); ?>
                                                <span class="remove-skill" onclick="removeRequirement('<?php echo escape(addslashes($req)); ?>')">×</span>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="location" class="form-label">Location *</label>
                                    <input type="text" class="form-control" id="location" name="location" 
                                           value="<?php echo escape($opportunity['location']); ?>" required>
                                    <div class="form-text">e.g., "Remote", "New York, NY", "Hybrid - San Francisco"</div>
                                    <div class="invalid-feedback">Please enter a location.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="salary_range" class="form-label">Salary/Compensation</label>
                                    <input type="text" class="form-control" id="salary_range" name="salary_range" 
                                           value="<?php echo escape($opportunity['salary_range']); ?>">
                                    <div class="form-text">e.g., "$50,000 - $70,000", "NPR 40,000 - 60,000", "Competitive"</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="contact_email" class="form-label">Contact Email</label>
                                    <input type="email" class="form-control" id="contact_email" name="contact_email" 
                                           value="<?php echo escape($opportunity['contact_email']); ?>">
                                    <div class="form-text">Leave blank to use your profile email</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="contact_phone" class="form-label">Contact Phone</label>
                                    <input type="tel" class="form-control" id="contact_phone" name="contact_phone" 
                                           value="<?php echo escape($opportunity['contact_phone']); ?>">
                                    <div class="form-text">Optional contact phone number</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="application_deadline" class="form-label">Application Deadline</label>
                            <input type="date" class="form-control" id="application_deadline" name="application_deadline" 
                                   value="<?php echo $opportunity['application_deadline'] ? date('Y-m-d', strtotime($opportunity['application_deadline'])) : ''; ?>" 
                                   min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                            <div class="form-text">Optional deadline for applications</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="opportunity_file" class="form-label">Attachment</label>
                            <?php if ($opportunity['file_path']): ?>
                                <div class="mb-2">
                                    <div class="alert alert-info">
                                        <i class="fas fa-file me-2"></i>
                                        Current file: <a href="../<?php echo escape($opportunity['file_path']); ?>" target="_blank">
                                            <?php echo basename($opportunity['file_path']); ?>
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="opportunity_file" name="opportunity_file" 
                                   accept=".pdf,.doc,.docx,.txt">
                            <div class="form-text">
                                Upload a file with additional details (PDF, DOC, DOCX, TXT - Max 5MB)
                                <?php if ($opportunity['file_path']): ?>
                                    <br><strong>Note:</strong> Uploading a new file will replace the current one.
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                       <?php echo $opportunity['is_active'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_active">
                                    <strong>Active</strong> - This opportunity is visible to users and accepting applications
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="my-opportunities.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Opportunity
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="card mt-4">
                <div class="card-body">
                    <h6><i class="fas fa-chart-bar me-2"></i>Opportunity Statistics</h6>
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="border-end">
                                <h5 class="text-primary"><?php echo $opportunity['views_count']; ?></h5>
                                <small class="text-muted">Views</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <h5 class="text-success">
                                    <?php 
                                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE opportunity_id = ?");
                                    $stmt->execute([$opportunity['id']]);
                                    echo $stmt->fetchColumn();
                                    ?>
                                </h5>
                                <small class="text-muted">Applications</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <h5 class="text-info">
                                    <?php echo floor((time() - strtotime($opportunity['date_posted'])) / 86400); ?>
                                </h5>
                                <small class="text-muted">Days Active</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h5 class="text-warning">
                                <?php 
                                $stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE opportunity_id = ? AND status = 'applied'");
                                $stmt->execute([$opportunity['id']]);
                                echo $stmt->fetchColumn();
                                ?>
                            </h5>
                            <small class="text-muted">New Applications</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

افته<script>
// Skills array
const allSkills = <?php echo json_encode($common_skills); ?>;
const selectedRequirements = <?php echo json_encode($current_requirements_array); ?>;

let highlightedIndex = -1;

// Get DOM elements
const reqInput = document.getElementById('requirements-input');
const reqDropdown = document.getElementById('requirements-dropdown');
const reqContainer = document.getElementById('requirements-container');
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
    
    return filtered.slice(0, 10);
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
 ange   `;
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
reqInput.addEventListener('keydown',競賽 function(e) {
    const options = reqDropdown.querySelectorAll('.skill-option');
    
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        highlightedIndex = Math.min(highlightedIndex + 1, options.length - 1);
        options.forEach((opt, idx) => {
            opt.classList.toggle('highlighted', idx === highlightedIndex);
        });
    }.switch else if (e.key === 'ArrowUp') {
        e.preventDefault();
        highlightedIndex = Math.max(highlightedIndex - 1, -1);
        options.forEach((opt, idx) => {
            opt.classList.toggle('highlighted gamme', idx === highlightedIndex);
        });
    } else if (e.key === 'Enter') {
        e.preventDefault();
        if (highlightedIndex >= 0 && options[highlightedIndex]) {
            options[highlightedIndex].click();
        } else if (reqInput.value.trim()) {
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
 dext </script>

<?php include '../includes/footer.php'; ?>
