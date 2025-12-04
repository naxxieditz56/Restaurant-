<?php
// Admin Dashboard - Pages Content Management
session_start();
require_once 'includes/auth.php';
checkAdminAccess();

$pageTitle = "Pages Content Management";
include 'includes/header.php';
?>

<div class="dashboard-container">
    <div class="sidebar">
        <?php include 'includes/sidebar.php'; ?>
    </div>
    
    <div class="main-content">
        <div class="content-header">
            <h1><i class="fas fa-file-alt"></i> Pages Content Management</h1>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="openAddPageModal()">
                    <i class="fas fa-plus"></i> Add New Page
                </button>
            </div>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard.php">Dashboard</a> / 
            <a href="pages-content.php">Pages</a>
        </div>
        
        <div class="content-card">
            <div class="card-header">
                <h2>All Pages</h2>
                <div class="search-box">
                    <input type="text" id="searchPages" placeholder="Search pages...">
                    <i class="fas fa-search"></i>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Page Name</th>
                            <th>Slug</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Sample data - In real app, fetch from database
                        $pages = [
                            ['id' => 1, 'name' => 'Home', 'slug' => 'home', 'status' => 'active', 'updated' => '2024-01-15'],
                            ['id' => 2, 'name' => 'About Us', 'slug' => 'about-us', 'status' => 'active', 'updated' => '2024-01-14'],
                            ['id' => 3, 'name' => 'Services', 'slug' => 'services', 'status' => 'draft', 'updated' => '2024-01-13'],
                            ['id' => 4, 'name' => 'Contact', 'slug' => 'contact', 'status' => 'active', 'updated' => '2024-01-12'],
                            ['id' => 5, 'name' => 'Blog', 'slug' => 'blog', 'status' => 'active', 'updated' => '2024-01-11'],
                        ];
                        
                        foreach ($pages as $page):
                        ?>
                        <tr>
                            <td>#<?php echo $page['id']; ?></td>
                            <td>
                                <div class="page-info">
                                    <strong><?php echo htmlspecialchars($page['name']); ?></strong>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($page['slug']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $page['status']; ?>">
                                    <?php echo ucfirst($page['status']); ?>
                                </span>
                            </td>
                            <td><?php echo $page['updated']; ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon btn-edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon btn-preview" title="Preview">
                                        <i class="fas fa-eye"></i>
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
            
            <div class="card-footer">
                <div class="pagination">
                    <span>Showing 1-5 of 5 pages</span>
                    <div class="pagination-controls">
                        <button disabled><i class="fas fa-chevron-left"></i></button>
                        <button class="active">1</button>
                        <button><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Page Editor Modal -->
        <div id="pageEditorModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Edit Page Content</h3>
                    <button class="close-modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="pageForm">
                        <div class="form-group">
                            <label>Page Title</label>
                            <input type="text" id="pageTitle" required>
                        </div>
                        <div class="form-group">
                            <label>Page Slug</label>
                            <input type="text" id="pageSlug" required>
                        </div>
                        <div class="form-group">
                            <label>Content</label>
                            <textarea id="pageContent" rows="10"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select id="pageStatus">
                                <option value="draft">Draft</option>
                                <option value="active">Active</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary close-modal">Cancel</button>
                    <button class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openAddPageModal() {
    document.getElementById('pageEditorModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('pageEditorModal').style.display = 'none';
}

// Close modal on clicking X or outside
document.querySelectorAll('.close-modal').forEach(btn => {
    btn.addEventListener('click', closeModal);
});

window.onclick = function(event) {
    const modal = document.getElementById('pageEditorModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
