<?php
require_once 'includes/header.php';

// Check permissions
if (!canAccessModule('gallery')) {
    header('Location: dashboard.php');
    exit();
}

// Handle actions
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_images'])) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!validateCSRFToken($csrf_token)) {
        $_SESSION['error'] = 'Invalid CSRF token.';
        header('Location: gallery.php');
        exit();
    }
    
    $title = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $category = sanitize($_POST['category'] ?? 'food');
    $display_order = (int)($_POST['display_order'] ?? 0);
    
    // Handle multiple file uploads
    if (isset($_FILES['images']) && count($_FILES['images']['name']) > 0) {
        $uploaded_count = 0;
        $failed_count = 0;
        
        for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $_FILES['images']['name'][$i],
                    'type' => $_FILES['images']['type'][$i],
                    'tmp_name' => $_FILES['images']['tmp_name'][$i],
                    'error' => $_FILES['images']['error'][$i],
                    'size' => $_FILES['images']['size'][$i]
                ];
                
                $upload = uploadFile($file, 'gallery');
                
                if ($upload['success']) {
                    try {
                        $stmt = $pdo->prepare("INSERT INTO gallery 
                            (title, description, image_path, category, display_order, uploaded_by) 
                            VALUES (?, ?, ?, ?, ?, ?)");
                        
                        $stmt->execute([
                            $title ?: pathinfo($file['name'], PATHINFO_FILENAME),
                            $description,
                            $upload['file_path'],
                            $category,
                            $display_order,
                            $_SESSION['admin_id']
                        ]);
                        
                        $uploaded_count++;
                        logActivity('upload_image', "Uploaded gallery image: " . $upload['file_name']);
                    } catch (PDOException $e) {
                        $failed_count++;
                    }
                } else {
                    $failed_count++;
                }
            }
        }
        
        if ($uploaded_count > 0) {
            $_SESSION['success'] = "Successfully uploaded $uploaded_count image(s)" . 
                                  ($failed_count > 0 ? ". Failed to upload $failed_count image(s)." : ".");
        } else {
            $_SESSION['error'] = "Failed to upload images. Please try again.";
        }
    }
    
    header('Location: gallery.php');
    exit();
}

// Handle image update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_image'])) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!validateCSRFToken($csrf_token)) {
        $_SESSION['error'] = 'Invalid CSRF token.';
        header('Location: gallery.php');
        exit();
    }
    
    $image_id = (int)$_POST['image_id'];
    $title = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $category = sanitize($_POST['category'] ?? 'food');
    $display_order = (int)($_POST['display_order'] ?? 0);
    $active = isset($_POST['active']) ? 1 : 0;
    
    // Handle new image upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFile($_FILES['image'], 'gallery');
        if ($upload['success']) {
            $image_path = $upload['file_path'];
            
            // Get old image path to delete
            $stmt = $pdo->prepare("SELECT image_path FROM gallery WHERE id = ?");
            $stmt->execute([$image_id]);
            $old_image = $stmt->fetch();
            
            if ($old_image && file_exists(UPLOAD_PATH . $old_image['image_path'])) {
                unlink(UPLOAD_PATH . $old_image['image_path']);
            }
        }
    }
    
    try {
        if ($image_path) {
            $stmt = $pdo->prepare("UPDATE gallery SET 
                title = ?, description = ?, image_path = ?, category = ?, 
                display_order = ?, active = ?, updated_at = NOW() 
                WHERE id = ?");
            
            $stmt->execute([
                $title, $description, $image_path, $category,
                $display_order, $active, $image_id
            ]);
        } else {
            $stmt = $pdo->prepare("UPDATE gallery SET 
                title = ?, description = ?, category = ?, 
                display_order = ?, active = ?, updated_at = NOW() 
                WHERE id = ?");
            
            $stmt->execute([
                $title, $description, $category,
                $display_order, $active, $image_id
            ]);
        }
        
        $_SESSION['success'] = 'Image updated successfully.';
        logActivity('update_image', "Updated gallery image ID: $image_id");
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error updating image: ' . $e->getMessage();
    }
    
    header('Location: gallery.php');
    exit();
}

// Handle delete
if ($action === 'delete' && $id > 0) {
    // Get image path first
    $stmt = $pdo->prepare("SELECT image_path FROM gallery WHERE id = ?");
    $stmt->execute([$id]);
    $image = $stmt->fetch();
    
    if ($image) {
        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = ?");
        $stmt->execute([$id]);
        
        // Delete file
        if (file_exists(UPLOAD_PATH . $image['image_path'])) {
            unlink(UPLOAD_PATH . $image['image_path']);
        }
        
        $_SESSION['success'] = 'Image deleted successfully.';
        logActivity('delete_image', "Deleted gallery image ID: $id");
    }
    
    header('Location: gallery.php');
    exit();
}

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!validateCSRFToken($csrf_token)) {
        $_SESSION['error'] = 'Invalid CSRF token.';
        header('Location: gallery.php');
        exit();
    }
    
    $bulk_action = $_POST['bulk_action'];
    $selected_ids = $_POST['selected_ids'] ?? [];
    
    if (empty($selected_ids)) {
        $_SESSION['error'] = 'No images selected.';
        header('Location: gallery.php');
        exit();
    }
    
    $placeholders = str_repeat('?,', count($selected_ids) - 1) . '?';
    
    switch ($bulk_action) {
        case 'delete':
            // Get image paths first
            $stmt = $pdo->prepare("SELECT image_path FROM gallery WHERE id IN ($placeholders)");
            $stmt->execute($selected_ids);
            $images = $stmt->fetchAll();
            
            // Delete files
            foreach ($images as $image) {
                if (file_exists(UPLOAD_PATH . $image['image_path'])) {
                    unlink(UPLOAD_PATH . $image['image_path']);
                }
            }
            
            // Delete from database
            $stmt = $pdo->prepare("DELETE FROM gallery WHERE id IN ($placeholders)");
            $stmt->execute($selected_ids);
            
            $_SESSION['success'] = count($selected_ids) . ' image(s) deleted successfully.';
            logActivity('bulk_delete', "Deleted " . count($selected_ids) . " gallery images");
            break;
            
        case 'activate':
            $stmt = $pdo->prepare("UPDATE gallery SET active = 1 WHERE id IN ($placeholders)");
            $stmt->execute($selected_ids);
            $_SESSION['success'] = count($selected_ids) . ' image(s) activated.';
            break;
            
        case 'deactivate':
            $stmt = $pdo->prepare("UPDATE gallery SET active = 0 WHERE id IN ($placeholders)");
            $stmt->execute($selected_ids);
            $_SESSION['success'] = count($selected_ids) . ' image(s) deactivated.';
            break;
            
        case 'update_category':
            $new_category = sanitize($_POST['bulk_category'] ?? 'food');
            $stmt = $pdo->prepare("UPDATE gallery SET category = ? WHERE id IN ($placeholders)");
            $stmt->execute(array_merge([$new_category], $selected_ids));
            $_SESSION['success'] = count($selected_ids) . ' image(s) category updated.';
            break;
    }
    
    header('Location: gallery.php');
    exit();
}

// Get all gallery images
$category_filter = $_GET['category'] ?? 'all';
$search_query = $_GET['search'] ?? '';

$query = "SELECT g.*, au.username as uploaded_by_name FROM gallery g 
          LEFT JOIN admin_users au ON g.uploaded_by = au.id 
          WHERE 1=1";
$params = [];

if ($category_filter !== 'all') {
    $query .= " AND g.category = ?";
    $params[] = $category_filter;
}

if ($search_query) {
    $query .= " AND (g.title LIKE ? OR g.description LIKE ?)";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
}

$query .= " ORDER BY g.display_order, g.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$gallery_images = $stmt->fetchAll();

// Get category statistics
$stmt = $pdo->query("SELECT category, COUNT(*) as count FROM gallery GROUP BY category");
$category_stats = $stmt->fetchAll();

// Get total counts
$stmt = $pdo->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN active = 1 THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN active = 0 THEN 1 ELSE 0 END) as inactive
    FROM gallery");
$total_stats = $stmt->fetch();
?>

<script>
    document.getElementById('pageTitle').textContent = 'Gallery Management';
    document.getElementById('pageSubtitle').textContent = 'Manage restaurant photos & images';
</script>

<div class="gallery-management">
    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-images"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($total_stats['total'] ?? 0); ?></h3>
                <p>Total Images</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($total_stats['active'] ?? 0); ?></h3>
                <p>Active Images</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($total_stats['inactive'] ?? 0); ?></h3>
                <p>Inactive Images</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-folder"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo count($category_stats); ?></h3>
                <p>Categories</p>
            </div>
        </div>
    </div>
    
    <!-- Category Filter -->
    <div class="category-filter">
        <div class="filter-tabs">
            <a href="?category=all" class="filter-tab <?php echo $category_filter === 'all' ? 'active' : ''; ?>">
                All Images
                <span class="tab-count"><?php echo $total_stats['total'] ?? 0; ?></span>
            </a>
            
            <?php foreach ($category_stats as $stat): ?>
            <a href="?category=<?php echo urlencode($stat['category']); ?>" 
               class="filter-tab <?php echo $category_filter === $stat['category'] ? 'active' : ''; ?>">
                <?php echo ucfirst($stat['category']); ?>
                <span class="tab-count"><?php echo $stat['count']; ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Action Bar -->
    <div class="action-bar">
        <div class="action-bar-left">
            <button class="btn btn-primary" onclick="showUploadModal()">
                <i class="fas fa-cloud-upload-alt"></i> Upload Images
            </button>
            <button class="btn btn-secondary" onclick="toggleBulkActions()">
                <i class="fas fa-tasks"></i> Bulk Actions
            </button>
        </div>
        
        <div class="action-bar-right">
            <form method="GET" class="search-form">
                <div class="search-box">
                    <input type="text" name="search" placeholder="Search images..." 
                           value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
            
            <div class="view-options">
                <button class="view-option active" data-view="grid" title="Grid View">
                    <i class="fas fa-th"></i>
                </button>
                <button class="view-option" data-view="list" title="List View">
                    <i class="fas fa-list"></i>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Bulk Actions Panel -->
    <div class="bulk-actions-panel" id="bulkActionsPanel" style="display: none;">
        <div class="bulk-panel-header">
            <h4>Bulk Actions</h4>
            <button class="btn-close" onclick="toggleBulkActions()">&times;</button>
        </div>
        <form method="POST" class="bulk-actions-form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="selected_ids" id="bulkSelectedIds">
            
            <div class="bulk-action-group">
                <select name="bulk_action" id="bulkAction" class="form-control" required>
                    <option value="">Select Action</option>
                    <option value="activate">Activate Selected</option>
                    <option value="deactivate">Deactivate Selected</option>
                    <option value="update_category">Update Category</option>
                    <option value="delete">Delete Selected</option>
                </select>
                
                <div class="bulk-category-field" id="bulkCategoryField" style="display: none;">
                    <select name="bulk_category" class="form-control">
                        <option value="food">Food</option>
                        <option value="interior">Interior</option>
                        <option value="bar">Bar</option>
                        <option value="historic">Historic</option>
                        <option value="events">Events</option>
                        <option value="team">Team</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-danger" id="applyBulkAction">
                    Apply
                </button>
            </div>
            
            <p class="bulk-info" id="bulkInfo">No images selected</p>
        </form>
    </div>
    
    <!-- Gallery Grid -->
    <div class="gallery-container" id="galleryContainer">
        <div class="gallery-grid" id="galleryGrid">
            <?php if (empty($gallery_images)): ?>
                <div class="empty-state">
                    <i class="fas fa-images"></i>
                    <h3>No images found</h3>
                    <p>Upload your first image to get started</p>
                    <button class="btn btn-primary" onclick="showUploadModal()">
                        <i class="fas fa-cloud-upload-alt"></i> Upload Images
                    </button>
                </div>
            <?php else: ?>
                <?php foreach ($gallery_images as $image): ?>
                <div class="gallery-item" data-id="<?php echo $image['id']; ?>">
                    <div class="gallery-item-checkbox">
                        <input type="checkbox" class="image-checkbox" value="<?php echo $image['id']; ?>">
                    </div>
                    
                    <div class="gallery-item-image">
                        <img src="<?php echo SITE_URL . $image['image_path']; ?>" 
                             alt="<?php echo htmlspecialchars($image['title']); ?>"
                             loading="lazy">
                        
                        <div class="image-overlay">
                            <div class="overlay-content">
                                <h4><?php echo htmlspecialchars($image['title']); ?></h4>
                                <span class="image-category"><?php echo ucfirst($image['category']); ?></span>
                            </div>
                            <div class="overlay-actions">
                                <a href="<?php echo SITE_URL . $image['image_path']; ?>" 
                                   target="_blank" class="action-btn" title="View Full Size">
                                    <i class="fas fa-expand"></i>
                                </a>
                                <a href="gallery.php?action=edit&id=<?php echo $image['id']; ?>" 
                                   class="action-btn" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </div>
                        
                        <?php if (!$image['active']): ?>
                            <div class="inactive-badge">Inactive</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="gallery-item-info">
                        <h4><?php echo htmlspecialchars($image['title']); ?></h4>
                        <p class="image-description"><?php echo htmlspecialchars($image['description']); ?></p>
                        
                        <div class="image-meta">
                            <span class="meta-item">
                                <i class="fas fa-folder"></i>
                                <?php echo ucfirst($image['category']); ?>
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-sort-numeric-down"></i>
                                Order: <?php echo $image['display_order']; ?>
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-user"></i>
                                <?php echo htmlspecialchars($image['uploaded_by_name']); ?>
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-calendar"></i>
                                <?php echo date('M d, Y', strtotime($image['created_at'])); ?>
                            </span>
                        </div>
                        
                        <div class="image-actions">
                            <a href="gallery.php?action=edit&id=<?php echo $image['id']; ?>" 
                               class="btn btn-sm btn-outline">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="gallery.php?action=delete&id=<?php echo $image['id']; ?>" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Are you sure you want to delete 
