<?php
/**
 * Admin Users Management
 * PHP 8.4 Pure Functional Script
 */

// Include configuration
require_once __DIR__ . '/../config/config.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    set_flash_message('error', 'You do not have permission to access the admin area');
    redirect(site_url());
}

// Handle actions
$action = isset($_GET['action']) ? $_GET['action'] : '';
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('error', 'Invalid security token. Please try again.');
    } else {
        switch ($action) {
            case 'add':
                $result = handle_add_user();
                break;
                
            case 'edit':
                $result = handle_edit_user($user_id);
                break;
                
            case 'delete':
                $result = handle_delete_user($user_id);
                break;
                
            case 'status':
                $result = handle_change_status($user_id, $_POST['status'] ?? '');
                break;
        }
    }
}

// Handle search query
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Handle role filter
$role_filter = isset($_GET['role']) ? (int)$_GET['role'] : 0;

// Handle status filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Pagination
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Build query
$query = "SELECT id, name, email, role_id, status, verified_at, created_at, last_login 
          FROM users
          WHERE 1=1";
$params = [];

// Add search condition if provided
if (!empty($search)) {
    $query .= " AND (name LIKE ? OR email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
}

// Add role filter if provided
if ($role_filter > 0) {
    $query .= " AND role_id = ?";
    $params[] = $role_filter;
}

// Add status filter if provided
if (!empty($status_filter)) {
    $query .= " AND status = ?";
    $params[] = $status_filter;
}

// Add order by and pagination
$query .= " ORDER BY created_at DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $per_page;

// Get users
$users = db_query($query, $params);

// Count total users for pagination
$count_query = "SELECT COUNT(*) as count FROM users WHERE 1=1";
$count_params = [];

if (!empty($search)) {
    $count_query .= " AND (name LIKE ? OR email LIKE ?)";
    $count_params[] = $search_param;
    $count_params[] = $search_param;
}

if ($role_filter > 0) {
    $count_query .= " AND role_id = ?";
    $count_params[] = $role_filter;
}

if (!empty($status_filter)) {
    $count_query .= " AND status = ?";
    $count_params[] = $status_filter;
}

$total_users = db_query_row($count_query, $count_params)['count'] ?? 0;
$total_pages = ceil($total_users / $per_page);

// Get user stats
$user_stats = [
    'total' => db_query_row("SELECT COUNT(*) as count FROM users")['count'] ?? 0,
    'admin' => db_query_row("SELECT COUNT(*) as count FROM users WHERE role_id = ?", [ROLE_ADMIN])['count'] ?? 0,
    'user' => db_query_row("SELECT COUNT(*) as count FROM users WHERE role_id = ?", [ROLE_USER])['count'] ?? 0,
    'active' => db_query_row("SELECT COUNT(*) as count FROM users WHERE status = 'active'")['count'] ?? 0,
    'inactive' => db_query_row("SELECT COUNT(*) as count FROM users WHERE status = 'inactive'")['count'] ?? 0,
    'banned' => db_query_row("SELECT COUNT(*) as count FROM users WHERE status = 'banned'")['count'] ?? 0
];

// Get user to edit if in edit mode
$user_to_edit = null;
if ($action === 'edit' && $user_id > 0) {
    $user_to_edit = db_query_row("SELECT id, name, email, role_id, status FROM users WHERE id = ?", [$user_id]);
    
    if (!$user_to_edit) {
        set_flash_message('error', 'User not found');
        redirect(site_url('admin/users.php'));
    }
}

// Page title
$page_title = 'Manage Users';

// Include header
require_once __DIR__ . '/../partials/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-2 mb-4">
            <!-- Admin Sidebar -->
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Admin Panel</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="<?= site_url('admin/dashboard.php') ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                    <a href="<?= site_url('admin/products.php') ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-box me-2"></i> Products
                    </a>
                    <a href="<?= site_url('admin/categories.php') ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-tags me-2"></i> Categories
                    </a>
                    <a href="<?= site_url('admin/orders.php') ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-bag me-2"></i> Orders
                    </a>
                    <a href="<?= site_url('admin/users.php') ?>" class="list-group-item list-group-item-action active">
                        <i class="bi bi-people me-2"></i> Users
                    </a>
                    <a href="<?= site_url('admin/settings.php') ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-gear me-2"></i> Settings
                    </a>
                    <a href="<?= site_url() ?>" class="list-group-item list-group-item-action text-primary">
                        <i class="bi bi-shop me-2"></i> View Shop
                    </a>
                    <a href="<?= site_url('logout.php') ?>" class="list-group-item list-group-item-action text-danger">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-lg-10">
            <h2 class="mb-4">Manage Users</h2>
            
            <?php if (isset($_SESSION['flash_message'])): ?>
                <div class="alert alert-<?= $_SESSION['flash_message']['type'] ?> alert-dismissible fade show" role="alert">
                    <?= $_SESSION['flash_message']['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['flash_message']); ?>
            <?php endif; ?>
            
            <!-- User Stats -->
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <h6 class="card-title">Total Users</h6>
                            <h2 class="card-text"><?= $user_stats['total'] ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <h6 class="card-title">Admins</h6>
                            <h2 class="card-text"><?= $user_stats['admin'] ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <h6 class="card-title">Customers</h6>
                            <h2 class="card-text"><?= $user_stats['user'] ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <h6 class="card-title">Active</h6>
                            <h2 class="card-text text-success"><?= $user_stats['active'] ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <h6 class="card-title">Inactive</h6>
                            <h2 class="card-text text-warning"><?= $user_stats['inactive'] ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <h6 class="card-title">Banned</h6>
                            <h2 class="card-text text-danger"><?= $user_stats['banned'] ?></h2>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- User Management Section -->
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">User Management</h5>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="bi bi-plus-circle me-1"></i> Add New User
                    </button>
                </div>
                <div class="card-body">
                    <!-- Search and Filter -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <form method="get" action="<?= site_url('admin/users.php') ?>" class="d-flex">
                                <input type="text" name="search" class="form-control me-2" placeholder="Search users..." value="<?= h($search) ?>">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="bi bi-search"></i>
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6 d-flex justify-content-end">
                            <div class="btn-group me-2">
                                <a href="<?= site_url('admin/users.php') ?>" class="btn btn-outline-secondary <?= empty($role_filter) && empty($status_filter) ? 'active' : '' ?>">All</a>
                                <a href="<?= site_url('admin/users.php?role=' . ROLE_ADMIN) ?>" class="btn btn-outline-secondary <?= $role_filter === ROLE_ADMIN ? 'active' : '' ?>">Admins</a>
                                <a href="<?= site_url('admin/users.php?role=' . ROLE_USER) ?>" class="btn btn-outline-secondary <?= $role_filter === ROLE_USER ? 'active' : '' ?>">Customers</a>
                            </div>
                            <div class="btn-group">
                                <a href="<?= site_url('admin/users.php?status=active') ?>" class="btn btn-outline-success <?= $status_filter === 'active' ? 'active' : '' ?>">Active</a>
                                <a href="<?= site_url('admin/users.php?status=inactive') ?>" class="btn btn-outline-warning <?= $status_filter === 'inactive' ? 'active' : '' ?>">Inactive</a>
                                <a href="<?= site_url('admin/users.php?status=banned') ?>" class="btn btn-outline-danger <?= $status_filter === 'banned' ? 'active' : '' ?>">Banned</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Users Table -->
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Verified</th>
                                    <th>Registered</th>
                                    <th>Last Login</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4">No users found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?= $user['id'] ?></td>
                                            <td><?= h($user['name']) ?></td>
                                            <td><?= h($user['email']) ?></td>
                                            <td>
                                                <?php if ($user['role_id'] == ROLE_ADMIN): ?>
                                                    <span class="badge bg-primary">Admin</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Customer</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $status_class = '';
                                                switch ($user['status']) {
                                                    case 'active':
                                                        $status_class = 'bg-success';
                                                        break;
                                                    case 'inactive':
                                                        $status_class = 'bg-warning text-dark';
                                                        break;
                                                    case 'banned':
                                                        $status_class = 'bg-danger';
                                                        break;
                                                    default:
                                                        $status_class = 'bg-secondary';
                                                }
                                                ?>
                                                <span class="badge <?= $status_class ?>"><?= ucfirst($user['status']) ?></span>
                                            </td>
                                            <td>
                                                <?php if ($user['verified_at']): ?>
                                                    <span class="text-success"><i class="bi bi-check-circle-fill"></i> Yes</span>
                                                <?php else: ?>
                                                    <span class="text-danger"><i class="bi bi-x-circle-fill"></i> No</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                            <td><?= $user['last_login'] ? date('M j, Y', strtotime($user['last_login'])) : 'Never' ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            data-bs-toggle="modal" data-bs-target="#editUserModal" 
                                                            data-user-id="<?= $user['id'] ?>"
                                                            data-user-name="<?= h($user['name']) ?>"
                                                            data-user-email="<?= h($user['email']) ?>"
                                                            data-user-role="<?= $user['role_id'] ?>"
                                                            data-user-status="<?= $user['status'] ?>">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    
                                                    <?php if ($user['id'] != $_SESSION['user_id']): // Can't change own status ?>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                            Status
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li>
                                                                <form method="post" action="<?= site_url('admin/users.php?action=status&id=' . $user['id']) ?>">
                                                                    <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
                                                                    <input type="hidden" name="status" value="active">
                                                                    <button type="submit" class="dropdown-item text-success">
                                                                        <i class="bi bi-check-circle me-1"></i> Set Active
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <form method="post" action="<?= site_url('admin/users.php?action=status&id=' . $user['id']) ?>">
                                                                    <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
                                                                    <input type="hidden" name="status" value="inactive">
                                                                    <button type="submit" class="dropdown-item text-warning">
                                                                        <i class="bi bi-pause-circle me-1"></i> Set Inactive
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <form method="post" action="<?= site_url('admin/users.php?action=status&id=' . $user['id']) ?>">
                                                                    <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
                                                                    <input type="hidden" name="status" value="banned">
                                                                    <button type="submit" class="dropdown-item text-danger">
                                                                        <i class="bi bi-ban me-1"></i> Ban User
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        </ul>
                                                        
                                                        <?php if ($user['id'] != 1): // Can't delete the main admin ?>
                                                            <form method="post" action="<?= site_url('admin/users.php?action=delete&id=' . $user['id']) ?>" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                                                <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
                                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php
                                $query_params = [];
                                if (!empty($search)) $query_params[] = "search=" . urlencode($search);
                                if ($role_filter > 0) $query_params[] = "role=" . $role_filter;
                                if (!empty($status_filter)) $query_params[] = "status=" . $status_filter;
                                
                                $query_string = !empty($query_params) ? '&' . implode('&', $query_params) : '';
                                
                                // Previous button
                                if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= site_url('admin/users.php?page=' . ($page - 1) . $query_string) ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item disabled">
                                        <a class="page-link" href="#" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <!-- Page numbers -->
                                <?php
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);
                                
                                for ($i = $start_page; $i <= $end_page; $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= site_url('admin/users.php?page=' . $i . $query_string) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <!-- Next button -->
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= site_url('admin/users.php?page=' . ($page + 1) . $query_string) ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item disabled">
                                        <a class="page-link" href="#" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="<?= site_url('admin/users.php?action=add') ?>" id="addUserForm">
                    <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="form-text">Password must be at least 8 characters with uppercase, lowercase, and number.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role_id" required>
                            <option value="<?= ROLE_USER ?>">Customer</option>
                            <option value="<?= ROLE_ADMIN ?>">Admin</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="banned">Banned</option>
                        </select>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="verified" name="verified" checked>
                        <label class="form-check-label" for="verified">
                            Mark as verified (skip email verification)
                        </label>
                    </div>
                    
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="<?= site_url('admin/users.php?action=edit') ?>" id="editUserForm">
                    <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
                    <input type="hidden" name="user_id" id="edit_user_id" value="">
                    
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="edit_password" name="password">
                        <div class="form-text">Leave blank to keep current password.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_role" class="form-label">Role</label>
                        <select class="form-select" id="edit_role" name="role_id" required>
                            <option value="<?= ROLE_USER ?>">Customer</option>
                            <option value="<?= ROLE_ADMIN ?>">Admin</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <select class="form-select" id="edit_status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="banned">Banned</option>
                        </select>
                    </div>
                    
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Edit User Modal
document.addEventListener('DOMContentLoaded', function() {
    // Populate edit user modal with data
    const editUserModal = document.getElementById('editUserModal');
    if (editUserModal) {
        editUserModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const userId = button.getAttribute('data-user-id');
            const userName = button.getAttribute('data-user-name');
            const userEmail = button.getAttribute('data-user-email');
            const userRole = button.getAttribute('data-user-role');
            const userStatus = button.getAttribute('data-user-status');
            
            const modal = this;
            modal.querySelector('#edit_user_id').value = userId;
            modal.querySelector('#edit_name').value = userName;
            modal.querySelector('#edit_email').value = userEmail;
            modal.querySelector('#edit_password').value = '';
            
            const roleSelect = modal.querySelector('#edit_role');
            for (let i = 0; i < roleSelect.options.length; i++) {
                if (roleSelect.options[i].value == userRole) {
                    roleSelect.options[i].selected = true;
                    break;
                }
            }
            
            const statusSelect = modal.querySelector('#edit_status');
            for (let i = 0; i < statusSelect.options.length; i++) {
                if (statusSelect.options[i].value == userStatus) {
                    statusSelect.options[i].selected = true;
                    break;
                }
            }
        });
    }
    
    // Form validations
    const addUserForm = document.getElementById('addUserForm');
    if (addUserForm) {
        addUserForm.addEventListener('submit', function(event) {
            const passwordInput = document.getElementById('password');
            if (passwordInput.value.length < 8) {
                alert('Password must be at least 8 characters long');
                event.preventDefault();
                return false;
            }
            
            if (!/[A-Z]/.test(passwordInput.value)) {
                alert('Password must contain at least one uppercase letter');
                event.preventDefault();
                return false;
            }
            
            if (!/[a-z]/.test(passwordInput.value)) {
                alert('Password must contain at least one lowercase letter');
                event.preventDefault();
                return false;
            }
            
            if (!/[0-9]/.test(passwordInput.value)) {
                alert('Password must contain at least one number');
                event.preventDefault();
                return false;
            }
        });
    }
    
    const editUserForm = document.getElementById('editUserForm');
    if (editUserForm) {
        editUserForm.addEventListener('submit', function(event) {
            const passwordInput = document.getElementById('edit_password');
            // Only validate password if it's not empty (changing password)
            if (passwordInput.value.length > 0) {
                if (passwordInput.value.length < 8) {
                    alert('Password must be at least 8 characters long');
                    event.preventDefault();
                    return false;
                }
                
                if (!/[A-Z]/.test(passwordInput.value)) {
                    alert('Password must contain at least one uppercase letter');
                    event.preventDefault();
                    return false;
                }
                
                if (!/[a-z]/.test(passwordInput.value)) {
                    alert('Password must contain at least one lowercase letter');
                    event.preventDefault();
                    return false;
                }
                
                if (!/[0-9]/.test(passwordInput.value)) {
                    alert('Password must contain at least one number');
                    event.preventDefault();
                    return false;
                }
            }
        });
    }
});
</script>

<?php
/**
 * Handle adding a new user
 * 
 * @return array Status and message
 */
function handle_add_user() {
    // Validate input
    $validation_rules = [
        'name' => [
            ['rule' => 'validate_required', 'field_name' => 'name'],
            ['rule' => 'validate_min_length', 'params' => [3], 'field_name' => 'name']
        ],
        'email' => [
            ['rule' => 'validate_required', 'field_name' => 'email'],
            ['rule' => 'validate_email']
        ],
        'password' => [
            ['rule' => 'validate_required', 'field_name' => 'password'],
            ['rule' => 'validate_password']
        ],
        'role_id' => [
            ['rule' => 'validate_required', 'field_name' => 'role'],
        ],
        'status' => [
            ['rule' => 'validate_required', 'field_name' => 'status'],
        ]
    ];
    
    $errors = validate_form($_POST, $validation_rules);
    
    if (!empty($errors)) {
        set_flash_message('error', reset($errors));
        return [
            'status' => false,
            'message' => reset($errors)
        ];
    }
    
    // Check if email already exists
    $existing_user = db_query_row("SELECT id FROM users WHERE email = ?", [$_POST['email']]);
    if ($existing_user) {
        set_flash_message('error', 'Email is already registered');
        return [
            'status' => false,
            'message' => 'Email is already registered'
        ];
    }
    
    // Get form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role_id = (int)$_POST['role_id'];
    $status = $_POST['status'];
    $verified = isset($_POST['verified']) ? true : false;
    
    // Set verification data
    $verify_token = null;
    $verified_at = null;
    
    if ($verified) {
        $verified_at = date('Y-m-d H:i:s');
    } else {
        $verify_token = generate_token();
    }
    
    // Insert user
    $result = db_execute(
        "INSERT INTO users (name, email, password, role_id, status, verify_token, verified_at, created_at) 
         VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
        [$name, $email, $password, $role_id, $status, $verify_token, $verified_at]
    );
    
    if (!$result) {
        set_flash_message('error', 'Failed to add user. Please try again.');
        return [
            'status' => false,
            'message' => 'Failed to add user. Please try again.'
        ];
    }
    
    $user_id = db_last_insert_id();
    
    // Send verification email if not marked as verified
    if (!$verified && $verify_token) {
        $verify_url = SITE_URL . '/verify.php?token=' . $verify_token;
        
        $email_content = get_verification_email($name, $verify_token);
        $email_sent = send_html_email($email, SITE_NAME . ' - Verify Your Email', $email_content['html'], $email_content['text']);
    }
    
    set_flash_message('success', 'User added successfully');
    
    return [
        'status' => true,
        'message' => 'User added successfully'
    ];
}

/**
 * Handle editing a user
 * 
 * @param int $user_id User ID
 * @return array Status and message
 */
function handle_edit_user($user_id) {
    // Validate user ID
    if ($user_id <= 0) {
        set_flash_message('error', 'Invalid user ID');
        return [
            'status' => false,
            'message' => 'Invalid user ID'
        ];
    }
    
    // Check if user exists
    $user = db_query_row("SELECT id, email FROM users WHERE id = ?", [$user_id]);
    if (!$user) {
        set_flash_message('error', 'User not found');
        return [
            'status' => false,
            'message' => 'User not found'
        ];
    }
    
    // Validate input
    $validation_rules = [
        'name' => [
            ['rule' => 'validate_required', 'field_name' => 'name'],
            ['rule' => 'validate_min_length', 'params' => [3], 'field_name' => 'name']
        ],
        'email' => [
            ['rule' => 'validate_required', 'field_name' => 'email'],
            ['rule' => 'validate_email']
        ],
        'role_id' => [
            ['rule' => 'validate_required', 'field_name' => 'role'],
        ],
        'status' => [
            ['rule' => 'validate_required', 'field_name' => 'status'],
        ]
    ];
    
    $errors = validate_form($_POST, $validation_rules);
    
    // Validate password if provided
    if (!empty($_POST['password'])) {
        $password_error = validate_password($_POST['password']);
        if ($password_error) {
            $errors['password'] = $password_error;
        }
    }
    
    if (!empty($errors)) {
        set_flash_message('error', reset($errors));
        return [
            'status' => false,
            'message' => reset($errors)
        ];
    }
    
    // Check if email is already used by another user
    if ($_POST['email'] !== $user['email']) {
        $existing_user = db_query_row("SELECT id FROM users WHERE email = ? AND id != ?", [$_POST['email'], $user_id]);
        if ($existing_user) {
            set_flash_message('error', 'Email is already registered by another user');
            return [
                'status' => false,
                'message' => 'Email is already registered by another user'
            ];
        }
    }
    
    // Get form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role_id = (int)$_POST['role_id'];
    $status = $_POST['status'];
    
    // Update user
    $query = "UPDATE users SET name = ?, email = ?, role_id = ?, status = ?";
    $params = [$name, $email, $role_id, $status];
    
    // Add password to update if provided
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $query .= ", password = ?";
        $params[] = $password;
    }
    
    $query .= " WHERE id = ?";
    $params[] = $user_id;
    
    $result = db_execute($query, $params);
    
    if (!$result) {
        set_flash_message('error', 'Failed to update user. Please try again.');
        return [
            'status' => false,
            'message' => 'Failed to update user. Please try again.'
        ];
    }
    
    set_flash_message('success', 'User updated successfully');
    
    return [
        'status' => true,
        'message' => 'User updated successfully'
    ];
}

/**
 * Handle deleting a user
 * 
 * @param int $user_id User ID
 * @return array Status and message
 */
function handle_delete_user($user_id) {
    // Validate user ID
    if ($user_id <= 0) {
        set_flash_message('error', 'Invalid user ID');
        return [
            'status' => false,
            'message' => 'Invalid user ID'
        ];
    }
    
    // Check if user exists
    $user = db_query_row("SELECT id FROM users WHERE id = ?", [$user_id]);
    if (!$user) {
        set_flash_message('error', 'User not found');
        return [
            'status' => false,
            'message' => 'User not found'
        ];
    }
    
    // Prevent deleting self
    if ($user_id == $_SESSION['user_id']) {
        set_flash_message('error', 'You cannot delete your own account');
        return [
            'status' => false,
            'message' => 'You cannot delete your own account'
        ];
    }
    
    // Prevent deleting main admin (ID 1)
    if ($user_id == 1) {
        set_flash_message('error', 'You cannot delete the main administrator account');
        return [
            'status' => false,
            'message' => 'You cannot delete the main administrator account'
        ];
    }
    
    // Delete user
    $result = db_execute("DELETE FROM users WHERE id = ?", [$user_id]);
    
    if (!$result) {
        set_flash_message('error', 'Failed to delete user. Please try again.');
        return [
            'status' => false,
            'message' => 'Failed to delete user. Please try again.'
        ];
    }
    
    set_flash_message('success', 'User deleted successfully');
    
    return [
        'status' => true,
        'message' => 'User deleted successfully'
    ];
}

/**
 * Handle changing user status
 * 
 * @param int $user_id User ID
 * @param string $status New status
 * @return array Status and message
 */
function handle_change_status($user_id, $status) {
    // Validate user ID
    if ($user_id <= 0) {
        set_flash_message('error', 'Invalid user ID');
        return [
            'status' => false,
            'message' => 'Invalid user ID'
        ];
    }
    
    // Check if user exists
    $user = db_query_row("SELECT id FROM users WHERE id = ?", [$user_id]);
    if (!$user) {
        set_flash_message('error', 'User not found');
        return [
            'status' => false,
            'message' => 'User not found'
        ];
    }
    
    // Prevent changing own status
    if ($user_id == $_SESSION['user_id']) {
        set_flash_message('error', 'You cannot change your own status');
        return [
            'status' => false,
            'message' => 'You cannot change your own status'
        ];
    }
    
    // Validate status
    $valid_statuses = ['active', 'inactive', 'banned'];
    if (!in_array($status, $valid_statuses)) {
        set_flash_message('error', 'Invalid status');
        return [
            'status' => false,
            'message' => 'Invalid status'
        ];
    }
    
    // Update user status
    $result = db_execute(
        "UPDATE users SET status = ? WHERE id = ?",
        [$status, $user_id]
    );
    
    if (!$result) {
        set_flash_message('error', 'Failed to update user status. Please try again.');
        return [
            'status' => false,
            'message' => 'Failed to update user status. Please try again.'
        ];
    }
    
    set_flash_message('success', 'User status updated successfully');
    
    return [
        'status' => true,
        'message' => 'User status updated successfully'
    ];
}

// Include footer
require_once __DIR__ . '/../partials/footer.php';
?>