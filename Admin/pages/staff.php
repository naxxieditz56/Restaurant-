<?php
// Admin Dashboard - Staff Management
session_start();
require_once 'includes/auth.php';
checkAdminAccess();

$pageTitle = "Staff Management";
include 'includes/header.php';
?>

<div class="dashboard-container">
    <div class="sidebar">
        <?php include 'includes/sidebar.php'; ?>
    </div>
    
    <div class="main-content">
        <div class="content-header">
            <h1><i class="fas fa-users"></i> Staff Management</h1>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="openAddStaffModal()">
                    <i class="fas fa-user-plus"></i> Add Staff Member
                </button>
            </div>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard.php">Dashboard</a> / 
            <a href="staff.php">Staff</a>
        </div>
        
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon bg-blue">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="stat-info">
                    <h3>15</h3>
                    <p>Total Staff</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-green">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-info">
                    <h3>12</h3>
                    <p>Active</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-orange">
                    <i class="fas fa-user-clock"></i>
                </div>
                <div class="stat-info">
                    <h3>2</h3>
                    <p>On Leave</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-red">
                    <i class="fas fa-user-slash"></i>
                </div>
                <div class="stat-info">
                    <h3>1</h3>
                    <p>Inactive</p>
                </div>
            </div>
        </div>
        
        <div class="content-card">
            <div class="card-header">
                <h2>Staff Members</h2>
                <div class="filter-options">
                    <select id="filterDepartment">
                        <option value="all">All Departments</option>
                        <option value="it">IT</option>
                        <option value="sales">Sales</option>
                        <option value="support">Support</option>
                        <option value="management">Management</option>
                    </select>
                </div>
            </div>
            
            <div class="staff-grid">
                <?php
                $staffMembers = [
                    [
                        'id' => 1,
                        'name' => 'Alex Johnson',
                        'position' => 'Web Developer',
                        'department' => 'IT',
                        'email' => 'alex@example.com',
                        'phone' => '+1 (555) 123-4567',
                        'status' => 'active',
                        'avatar' => 'AJ'
                    ],
                    [
                        'id' => 2,
                        'name' => 'Sarah Williams',
                        'position' => 'Sales Manager',
                        'department' => 'Sales',
                        'email' => 'sarah@example.com',
                        'phone' => '+1 (555) 234-5678',
                        'status' => 'active',
                        'avatar' => 'SW'
                    ],
                    [
                        'id' => 3,
                        'name' => 'Mike Chen',
                        'position' => 'Support Specialist',
                        'department' => 'Support',
                        'email' => 'mike@example.com',
                        'phone' => '+1 (555) 345-6789',
                        'status' => 'on-leave',
                        'avatar' => 'MC'
                    ],
                    [
                        'id' => 4,
                        'name' => 'Lisa Rodriguez',
                        'position' => 'Project Manager',
                        'department' => 'Management',
                        'email' => 'lisa@example.com',
                        'phone' => '+1 (555) 456-7890',
                        'status' => 'active',
                        'avatar' => 'LR'
                    ],
                ];
                
                foreach ($staffMembers as $staff):
                ?>
                <div class="staff-card">
                    <div class="staff-header">
                        <div class="staff-avatar"><?php echo $staff['avatar']; ?></div>
                        <div class="staff-info">
                            <h3><?php echo htmlspecialchars($staff['name']); ?></h3>
                            <p><?php echo htmlspecialchars($staff['position']); ?></p>
                            <span class="department-badge department-<?php echo strtolower($staff['department']); ?>">
                                <?php echo $staff['department']; ?>
                            </span>
                        </div>
                        <span class="staff-status status-<?php echo $staff['status']; ?>"></span>
                    </div>
                    
                    <div class="staff-details">
                        <div class="detail-item">
                            <i class="fas fa-envelope"></i>
                            <span><?php echo htmlspecialchars($staff['email']); ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-phone"></i>
                            <span><?php echo htmlspecialchars($staff['phone']); ?></span>
                        </div>
                    </div>
                    
                    <div class="staff-actions">
                        <button class="btn-icon" title="View Profile">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn-icon" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-icon" title="Message">
                            <i class="fas fa-comment"></i>
                        </button>
                        <button class="btn-icon btn-danger" title="Deactivate">
                            <i class="fas fa-user-slash"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Staff Modal -->
<div id="staffModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Staff Member</h3>
            <button class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="staffForm">
                <div class="form-row">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Position</label>
                        <input type="text" required>
                    </div>
                    <div class="form-group">
                        <label>Department</label>
                        <select required>
                            <option value="">Select Department</option>
                            <option value="it">IT</option>
                            <option value="sales">Sales</option>
                            <option value="support">Support</option>
                            <option value="management">Management</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel">
                </div>
                
                <div class="form-group">
                    <label>Role</label>
                    <select>
                        <option value="staff">Staff</option>
                        <option value="manager">Manager</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal">Cancel</button>
            <button class="btn btn-primary">Add Staff</button>
        </div>
    </div>
</div>

<script>
function openAddStaffModal() {
    document.getElementById('staffModal').style.display = 'block';
}
</script>

<?php include 'includes/footer.php'; ?>
