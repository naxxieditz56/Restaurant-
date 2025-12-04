<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Get user permissions
$permissions = getUserPermissions();

// Get user notifications
$notifications = [];
$notificationCount = 0;

// Get pending reservations count
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM reservations WHERE status = 'pending'");
$stmt->execute();
$pendingReservations = $stmt->fetch()['count'];
$notificationCount += $pendingReservations;

// Get pending testimonials count
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM testimonials WHERE approved = 0");
$stmt->execute();
$pendingTestimonials = $stmt->fetch()['count'];
$notificationCount += $pendingTestimonials;

// Get current page
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | Jack Fry's</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="stylesheet" href="assets/css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
</head>
<body class="admin-body">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <!-- Sidebar Header -->
        <div class="sidebar-header">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-utensils"></i>
                </div>
                <div class="logo-text">
                    <span>Jack Fry's</span>
                    <small>Admin</small>
                </div>
            </div>
            <button class="toggle-sidebar" id="toggleSidebar">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        
        <!-- User Profile -->
        <div class="user-profile">
            <div class="user-avatar">
                <?php if ($_SESSION['admin_avatar']): ?>
                    <img src="<?php echo htmlspecialchars($_SESSION['admin_avatar']); ?>" alt="<?php echo htmlspecialchars($_SESSION['admin_name']); ?>">
                <?php else: ?>
                    <i class="fas fa-user"></i>
                <?php endif; ?>
            </div>
            <div class="user-info">
                <h4><?php echo htmlspecialchars($_SESSION['admin_name']); ?></h4>
                <p class="user-role"><?php echo ucfirst($_SESSION['admin_role']); ?></p>
            </div>
            <button class="user-menu-toggle" id="userMenuToggle">
                <i class="fas fa-chevron-down"></i>
            </button>
            
            <!-- User Dropdown Menu -->
            <div class="user-dropdown" id="userDropdown">
                <a href="profile.php" class="dropdown-item">
                    <i class="fas fa-user"></i> My Profile
                </a>
                <a href="change-password.php" class="dropdown-item">
                    <i class="fas fa-key"></i> Change Password
                </a>
                <div class="dropdown-divider"></div>
                <a href="logout.php" class="dropdown-item logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        
        <!-- Sidebar Navigation -->
        <nav class="sidebar-nav">
            <ul class="nav-list">
                <!-- Dashboard -->
                <li class="nav-item <?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>">
                    <a href="dashboard.php" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
                
                <!-- Content Management -->
                <li class="nav-section">
                    <span class="section-label">Content</span>
                </li>
                
                <?php if ($permissions['menu']): ?>
                <li class="nav-item <?php echo $currentPage == 'menu-management.php' ? 'active' : ''; ?>">
                    <a href="menu-management.php" class="nav-link">
                        <i class="fas fa-utensils"></i>
                        <span class="nav-text">Menu Management</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if ($permissions['pages']): ?>
                <li class="nav-item <?php echo $currentPage == 'pages-content.php' ? 'active' : ''; ?>">
                    <a href="pages-content.php" class="nav-link">
                        <i class="fas fa-file-alt"></i>
                        <span class="nav-text">Page Content</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if ($permissions['gallery']): ?>
                <li class="nav-item <?php echo $currentPage == 'gallery.php' ? 'active' : ''; ?>">
                    <a href="gallery.php" class="nav-link">
                        <i class="fas fa-images"></i>
                        <span class="nav-text">Gallery</span>
                        <?php if ($notificationCount > 0): ?>
                            <span class="nav-badge"><?php echo $notificationCount; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php endif; ?>
                
                <!-- Operations -->
                <li class="nav-section">
                    <span class="section-label">Operations</span>
                </li>
                
                <?php if ($permissions['reservations']): ?>
                <li class="nav-item <?php echo strpos($currentPage, 'reservations') !== false ? 'active' : ''; ?>">
                    <a href="reservations.php" class="nav-link">
                        <i class="fas fa-calendar-check"></i>
                        <span class="nav-text">Reservations</span>
                        <?php if ($pendingReservations > 0): ?>
                            <span class="nav-badge"><?php echo $pendingReservations; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if ($permissions['testimonials']): ?>
                <li class="nav-item <?php echo $currentPage == 'testimonials.php' ? 'active' : ''; ?>">
                    <a href="testimonials.php" class="nav-link">
                        <i class="fas fa-star"></i>
                        <span class="nav-text">Testimonials</span>
                        <?php if ($pendingTestimonials > 0): ?>
                            <span class="nav-badge"><?php echo $pendingTestimonials; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php endif; ?>
                
                <!-- Team Management -->
                <li class="nav-section">
                    <span class="section-label">Team</span>
                </li>
                
                <?php if ($permissions['staff']): ?>
                <li class="nav-item <?php echo $currentPage == 'staff.php' ? 'active' : ''; ?>">
                    <a href="staff.php" class="nav-link">
                        <i class="fas fa-users"></i>
                        <span class="nav-text">Staff Management</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if ($permissions['users']): ?>
                <li class="nav-item <?php echo $currentPage == 'users.php' ? 'active' : ''; ?>">
                    <a href="users.php" class="nav-link">
                        <i class="fas fa-user-cog"></i>
                        <span class="nav-text">Admin Users</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <!-- Analytics & Reports -->
                <li class="nav-section">
                    <span class="section-label">Analytics</span>
                </li>
                
                <?php if ($permissions['analytics']): ?>
                <li class="nav-item <?php echo $currentPage == 'analytics.php' ? 'active' : ''; ?>">
                    <a href="analytics.php" class="nav-link">
                        <i class="fas fa-chart-line"></i>
                        <span class="nav-text">Analytics</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if ($permissions['reports']): ?>
                <li class="nav-item">
                    <a href="reports.php" class="nav-link">
                        <i class="fas fa-file-chart-column"></i>
                        <span class="nav-text">Reports</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <!-- System -->
                <li class="nav-section">
                    <span class="section-label">System</span>
                </li>
                
                <?php if ($permissions['settings']): ?>
                <li class="nav-item <?php echo $currentPage == 'settings.php' ? 'active' : ''; ?>">
                    <a href="settings.php" class="nav-link">
                        <i class="fas fa-cog"></i>
                        <span class="nav-text">Settings</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <li class="nav-item">
                    <a href="backup.php" class="nav-link">
                        <i class="fas fa-database"></i>
                        <span class="nav-text">Backup</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="activity-log.php" class="nav-link">
                        <i class="fas fa-history"></i>
                        <span class="nav-text">Activity Log</span>
                    </a>
                </li>
            </ul>
        </nav>
        
        <!-- Sidebar Footer -->
        <div class="sidebar-footer">
            <div class="system-status">
                <div class="status-indicator online"></div>
                <span>System Online</span>
            </div>
            <div class="quick-actions">
                <a href="<?php echo SITE_URL; ?>" target="_blank" class="btn-view-site" title="View Live Site">
                    <i class="fas fa-external-link-alt"></i>
                </a>
                <button class="btn-help" title="Help" onclick="showHelpModal()">
                    <i class="fas fa-question-circle"></i>
                </button>
            </div>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="main-wrapper" id="mainWrapper">
        <!-- Top Bar -->
        <header class="top-bar">
            <div class="top-bar-left">
                <button class="mobile-menu-toggle" id="mobileMenuToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="page-title">
                    <h1 id="pageTitle">Dashboard</h1>
                    <p class="page-subtitle" id="pageSubtitle">Overview & Analytics</p>
                </div>
            </div>
            
            <div class="top-bar-right">
                <!-- Search Bar -->
                <div class="search-container">
                    <button class="search-toggle" id="searchToggle">
                        <i class="fas fa-search"></i>
                    </button>
                    <div class="search-box" id="searchBox">
                        <input type="text" placeholder="Search..." id="searchInput">
                        <button class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Notifications -->
                <div class="notification-dropdown">
                    <button class="notification-btn" id="notificationBtn">
                        <i class="fas fa-bell"></i>
                        <?php if ($notificationCount > 0): ?>
                            <span class="notification-badge"><?php echo $notificationCount; ?></span>
                        <?php endif; ?>
                    </button>
                    <div class="notification-panel" id="notificationPanel">
                        <div class="notification-header">
                            <h3>Notifications</h3>
                            <button class="mark-all-read" id="markAllRead">Mark all as read</button>
                        </div>
                        <div class="notification-list">
                            <?php if ($pendingReservations > 0): ?>
                            <a href="reservations.php?status=pending" class="notification-item unread">
                                <div class="notification-icon reservation">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <div class="notification-content">
                                    <p class="notification-title">New Reservations</p>
                                    <p class="notification-text"><?php echo $pendingReservations; ?> pending reservation(s)</p>
                                    <span class="notification-time">Just now</span>
                                </div>
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($pendingTestimonials > 0): ?>
                            <a href="testimonials.php?status=pending" class="notification-item unread">
                                <div class="notification-icon testimonial">
                                    <i class="fas fa-star"></i>
                                </div>
                                <div class="notification-content">
                                    <p class="notification-title">New Testimonials</p>
                                    <p class="notification-text"><?php echo $pendingTestimonials; ?> testimonial(s) awaiting approval</p>
                                    <span class="notification-time">Today</span>
                                </div>
                            </a>
                            <?php endif; ?>
                            
                            <!-- More notifications would be loaded dynamically -->
                        </div>
                        <div class="notification-footer">
                            <a href="notifications.php" class="view-all">View All Notifications</a>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="quick-action-buttons">
                    <button class="quick-action" onclick="window.location.href='reservations.php?action=add'" title="Add Reservation">
                        <i class="fas fa-plus"></i>
                    </button>
                    <button class="quick-action" onclick="window.location.href='menu-management.php?action=add'" title="Add Menu Item">
                        <i class="fas fa-utensils"></i>
                    </button>
                    <button class="quick-action" id="refreshBtn" title="Refresh">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                
                <!-- Time Display -->
                <div class="time-display" id="timeDisplay">
                    <i class="fas fa-clock"></i>
                    <span id="currentTime"><?php echo date('h:i A'); ?></span>
                </div>
            </div>
        </header>
        
        <!-- Main Content Area -->
        <main class="main-content" id="mainContent">
            <!-- Content will be loaded here -->
            <?php
            // Display success/error messages from session
            if (isset($_SESSION['success'])) {
                echo '<div class="alert alert-success alert-dismissible">
                        <i class="fas fa-check-circle"></i>
                        ' . htmlspecialchars($_SESSION['success']) . '
                        <button class="alert-close">&times;</button>
                      </div>';
                unset($_SESSION['success']);
            }
            
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-error alert-dismissible">
                        <i class="fas fa-exclamation-circle"></i>
                        ' . htmlspecialchars($_SESSION['error']) . '
                        <button class="alert-close">&times;</button>
                      </div>';
                unset($_SESSION['error']);
            }
            
            if (isset($_SESSION['warning'])) {
                echo '<div class="alert alert-warning alert-dismissible">
                        <i class="fas fa-exclamation-triangle"></i>
                        ' . htmlspecialchars($_SESSION['warning']) . '
                        <button class="alert-close">&times;</button>
                      </div>';
                unset($_SESSION['warning']);
            }
            
            if (isset($_SESSION['info'])) {
                echo '<div class="alert alert-info alert-dismissible">
                        <i class="fas fa-info-circle"></i>
                        ' . htmlspecialchars($_SESSION['info']) . '
                        <button class="alert-close">&times;</button>
                      </div>';
                unset($_SESSION['info']);
            }
            ?>
