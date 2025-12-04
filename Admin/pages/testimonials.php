<?php
// Admin Dashboard - Testimonials Management
session_start();
require_once 'includes/auth.php';
checkAdminAccess();

$pageTitle = "Testimonials Management";
include 'includes/header.php';
?>

<div class="dashboard-container">
    <div class="sidebar">
        <?php include 'includes/sidebar.php'; ?>
    </div>
    
    <div class="main-content">
        <div class="content-header">
            <h1><i class="fas fa-quote-left"></i> Testimonials Management</h1>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="openAddTestimonialModal()">
                    <i class="fas fa-plus"></i> Add Testimonial
                </button>
            </div>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard.php">Dashboard</a> / 
            <a href="testimonials.php">Testimonials</a>
        </div>
        
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon bg-blue">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-info">
                    <h3>24</h3>
                    <p>Active Testimonials</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3>4.8</h3>
                    <p>Average Rating</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-orange">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3>5</h3>
                    <p>Pending Review</p>
                </div>
            </div>
        </div>
        
        <div class="content-card">
            <div class="card-header">
                <h2>All Testimonials</h2>
                <div class="filter-options">
                    <select id="filterStatus">
                        <option value="all">All Status</option>
                        <option value="approved">Approved</option>
                        <option value="pending">Pending</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Testimonial</th>
                            <th>Rating</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $testimonials = [
                            [
                                'id' => 1,
                                'name' => 'John Doe',
                                'company' => 'Tech Corp',
                                'content' => 'Excellent service! Highly recommended.',
                                'rating' => 5,
                                'date' => '2024-01-15',
                                'status' => 'approved'
                            ],
                            [
                                'id' => 2,
                                'name' => 'Jane Smith',
                                'company' => 'Design Studio',
                                'content' => 'Great experience working with them.',
                                'rating' => 4,
                                'date' => '2024-01-14',
                                'status' => 'pending'
                            ],
                        ];
                        
                        foreach ($testimonials as $testimonial):
                        ?>
                        <tr>
                            <td>#<?php echo $testimonial['id']; ?></td>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar"><?php echo strtoupper(substr($testimonial['name'], 0, 1)); ?></div>
                                    <div>
                                        <strong><?php echo htmlspecialchars($testimonial['name']); ?></strong>
                                        <small><?php echo htmlspecialchars($testimonial['company']); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td class="truncate"><?php echo htmlspecialchars($testimonial['content']); ?></td>
                            <td>
                                <div class="rating">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? 'filled' : ''; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </td>
                            <td><?php echo $testimonial['date']; ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $testimonial['status']; ?>">
                                    <?php echo ucfirst($testimonial['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon btn-approve" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn-icon btn-edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon btn-delete" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Testimonial Modal -->
<div id="testimonialModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Testimonial</h3>
            <button class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="testimonialForm">
                <div class="form-group">
                    <label>Customer Name</label>
                    <input type="text" required>
                </div>
                <div class="form-group">
                    <label>Company/Position</label>
                    <input type="text">
                </div>
                <div class="form-group">
                    <label>Testimonial Content</label>
                    <textarea rows="4" required></textarea>
                </div>
                <div class="form-group">
                    <label>Rating</label>
                    <div class="rating-input">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star" data-rating="<?php echo $i; ?>"></i>
                        <?php endfor; ?>
                    </div>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal">Cancel</button>
            <button class="btn btn-primary">Save Testimonial</button>
        </div>
    </div>
</div>

<script>
function openAddTestimonialModal() {
    document.getElementById('testimonialModal').style.display = 'block';
}
</script>

<?php include 'includes/footer.php'; ?>
