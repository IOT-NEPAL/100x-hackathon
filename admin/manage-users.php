<?php
require_once '../includes/auth.php';
requireRole('admin');

$user = getCurrentUser();

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_user'])) {
        $user_id = intval($_POST['user_id']);
        
        // Don't allow deleting yourself
        if ($user_id != $user['id']) {
            try {
                // Delete related records first
                $stmt = $pdo->prepare("DELETE FROM applications WHERE user_id = ?");
                $stmt->execute([$user_id]);
                
                $stmt = $pdo->prepare("DELETE FROM opportunities WHERE organizer_id = ?");
                $stmt->execute([$user_id]);
                
                $stmt = $pdo->prepare("DELETE FROM activity_logs WHERE user_id = ?");
                $stmt->execute([$user_id]);
                
                // Delete the user
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                
                $success_message = "User deleted successfully!";
            } catch (PDOException $e) {
                $error_message = "Error deleting user: " . $e->getMessage();
            }
        } else {
            $error_message = "You cannot delete yourself!";
        }
    }
}

// Get filter parameters
$role_filter = $_GET['role'] ?? '';
$status_filter = $_GET['status'] ?? '';
$search_query = trim($_GET['search'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Build query conditions
$where_conditions = ['1=1'];
$params = [];

if ($role_filter) {
    $where_conditions[] = 'role = ?';
    $params[] = $role_filter;
}

if ($status_filter !== '') {
    $where_conditions[] = 'is_active = ?';
    $params[] = $status_filter === 'active' ? 1 : 0;
}

if ($search_query) {
    $where_conditions[] = '(name LIKE ? OR email LIKE ? OR phone LIKE ? OR org_name LIKE ?)';
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
}

$where_clause = implode(' AND ', $where_conditions);

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM users WHERE $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_users = $count_stmt->fetch()['total'];
$total_pages = ceil($total_users / $per_page);

// Get users
$sql = "
    SELECT * FROM users 
    WHERE $where_clause 
    ORDER BY created_at DESC 
    LIMIT $per_page OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

$page_title = 'Manage Users';
include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="admin-dashboard.php">Admin Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Manage Users</li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-users me-2"></i>Manage Users</h2>
                <a href="admin-dashboard.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo escape($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo escape($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Filters and Search -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search Users</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?php echo escape($search_query); ?>" 
                                   placeholder="Search by name, email, phone...">
                        </div>
                        <div class="col-md-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role">
                                <option value="">All Roles</option>
                                <option value="user" <?php echo $role_filter === 'user' ? 'selected' : ''; ?>>Job Seekers</option>
                                <option value="organizer" <?php echo $role_filter === 'organizer' ? 'selected' : ''; ?>>Organizers</option>
                                <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Administrators</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i>Search
                            </button>
                            <a href="manage-users.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Summary Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h4 class="text-primary"><?php echo number_format($total_users); ?></h4>
                            <p class="card-text">Total Users</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h4 class="text-success">
                                <?php 
                                $active_count = 0;
                                foreach ($users as $usr) {
                                    if ($usr['is_active']) $active_count++;
                                }
                                echo $active_count;
                                ?>
                            </h4>
                            <p class="card-text">Active Users</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h4 class="text-info">
                                <?php 
                                $organizer_count = 0;
                                foreach ($users as $usr) {
                                    if ($usr['role'] === 'organizer') $organizer_count++;
                                }
                                echo $organizer_count;
                                ?>
                            </h4>
                            <p class="card-text">Organizers</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h4 class="text-warning">
                                <?php 
                                $new_users = 0;
                                foreach ($users as $usr) {
                                    if (strtotime($usr['created_at']) > strtotime('-7 days')) $new_users++;
                                }
                                echo $new_users;
                                ?>
                            </h4>
                            <p class="card-text">New This Week</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Users Table -->
            <?php if (empty($users)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h5>No Users Found</h5>
                        <p class="text-muted">No users match your current search criteria.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Users List</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Contact</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $usr): ?>
                                    <tr class="<?php echo !$usr['is_active'] ? 'table-secondary' : ''; ?>">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if ($usr['profile_pic']): ?>
                                                    <img src="../<?php echo escape($usr['profile_pic']); ?>" 
                                                         alt="Profile" class="rounded-circle me-3" width="40" height="40">
                                                <?php else: ?>
                                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                         style="width: 40px; height: 40px;">
                                                        <i class="fas fa-user text-white"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <strong><?php echo escape($usr['name']); ?></strong>
                                                    <?php if ($usr['role'] === 'organizer' && $usr['org_name']): ?>
                                                        <br><small class="text-muted"><?php echo escape($usr['org_name']); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo match($usr['role']) {
                                                    'admin' => 'danger',
                                                    'organizer' => 'info',
                                                    'user' => 'success',
                                                    default => 'secondary'
                                                };
                                            ?>">
                                                <?php echo ucfirst($usr['role']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small>
                                                <i class="fas fa-envelope me-1"></i>
                                                <a href="mailto:<?php echo escape($usr['email']); ?>">
                                                    <?php echo escape($usr['email']); ?>
                                                </a>
                                                <?php if ($usr['phone']): ?>
                                                    <br><i class="fas fa-phone me-1"></i>
                                                    <a href="tel:<?php echo escape($usr['phone']); ?>">
                                                        <?php echo escape($usr['phone']); ?>
                                                    </a>
                                                <?php endif; ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $usr['is_active'] ? 'success' : 'secondary'; ?>">
                                                <?php echo $usr['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small><?php echo date('M j, Y', strtotime($usr['created_at'])); ?></small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <!-- View Profile -->
                                                <a href="view-user-profile.php?id=<?php echo $usr['id']; ?>" 
                                                   class="btn btn-outline-info" title="View Profile">
                                                    <i class="fas fa-eye me-1"></i>View Profile
                                                </a>
                                                
                                                <!-- Delete User -->
                                                <?php if ($usr['id'] != $user['id']): ?>
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            onclick="confirmDelete(<?php echo $usr['id']; ?>, '<?php echo escape($usr['name']); ?>')"
                                                            title="Delete User">
                                                        <i class="fas fa-trash me-1"></i>Delete
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Users pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search_query); ?>&role=<?php echo urlencode($role_filter); ?>&status=<?php echo urlencode($status_filter); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>


<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm User Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this user?</p>
                <p><strong id="deleteUserName"></strong></p>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone. All user data, applications, and opportunities will be permanently deleted.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" id="deleteForm" class="d-inline">
                    <input type="hidden" name="user_id" id="deleteUserId">
                    <input type="hidden" name="delete_user" value="1">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Delete User
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(userId, userName) {
    document.getElementById('deleteUserId').value = userId;
    document.getElementById('deleteUserName').textContent = userName;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}
</script>

<?php include '../includes/footer.php'; ?>
