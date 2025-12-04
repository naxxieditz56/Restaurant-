<?php
require_once 'includes/header.php';

// Get dashboard statistics
$stats = [];

// Total Reservations
$stmt = $pdo->query("SELECT COUNT(*) as total FROM reservations");
$stats['total_reservations'] = $stmt->fetch()['total'];

// Today's Reservations
$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM reservations WHERE reservation_date = ?");
$stmt->execute([$today]);
$stats['today_reservations'] = $stmt->fetch()['total'];

// Pending Reservations
$stmt = $pdo->query("SELECT COUNT(*) as total FROM reservations WHERE status = 'pending'");
$stats['pending_reservations'] = $stmt->fetch()['total'];

// Total Menu Items
$stmt = $pdo->query("SELECT COUNT(*) as total FROM menu_items WHERE active = 1");
$stats['total_menu_items'] = $stmt->fetch()['total'];

// Total Testimonials
$stmt = $pdo->query("SELECT COUNT(*) as total FROM testimonials WHERE approved = 1");
$stats['total_testimonials'] = $stmt->fetch()['total'];

// Pending Testimonials
$stmt = $pdo->query("SELECT COUNT(*) as total FROM testimonials WHERE approved = 0");
$stats['pending_testimonials'] = $stmt->fetch()['total'];

// Revenue (estimated)
$stmt = $pdo->query("SELECT SUM(price * party_size * 0.3) as revenue FROM reservations WHERE status = 'completed' AND reservation_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$stats['monthly_revenue'] = $stmt->fetch()['revenue'] ?? 0;

// Recent Reservations (last 5)
$stmt = $pdo->prepare("SELECT * FROM reservations ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$recent_reservations = $stmt->fetchAll();

// Recent Activity
$stmt = $pdo->prepare("SELECT al.*, au.username, au.full_name 
                       FROM activity_log al 
                       LEFT JOIN admin_users au ON al.user_id = au.id 
                       ORDER BY al.created_at DESC LIMIT 8");
$stmt->execute();
$recent_activity = $stmt->fetchAll();

// Reservations by status for chart
$stmt = $pdo->query("SELECT status, COUNT(*) as count FROM reservations GROUP BY status");
$reservations_by_status = $stmt->fetchAll();

// Popular menu items
$stmt = $pdo->query("SELECT name, price, featured FROM menu_items WHERE active = 1 ORDER BY featured DESC, created_at DESC LIMIT 5");
$popular_items = $stmt->fetchAll();

// Get monthly reservations for chart
$monthly_data = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM reservations WHERE DATE_FORMAT(reservation_date, '%Y-%m') = ?");
    $stmt->execute([$month]);
    $count = $stmt->fetch()['count'];
    $monthly_data[] = [
        'month' => date('M Y', strtotime($month)),
        'count' => $count
    ];
}

// Get today's schedule
$stmt = $pdo->prepare("SELECT * FROM reservations WHERE reservation_date = ? ORDER BY reservation_time");
$stmt->execute([$today]);
$today_schedule = $stmt->fetchAll();
?>

<script>
    document.getElementById('pageTitle').textContent = 'Dashboard';
    document.getElementById('pageSubtitle').textContent = 'Welcome back, <?php echo $_SESSION['admin_name']; ?>!';
</script>

<div class="dashboard-content">
    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon reservation">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['total_reservations']); ?></h3>
                <p>Total Reservations</p>
            </div>
            <a href="reservations.php" class="stat-link">
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon today">
                <i class="fas fa-calendar-day"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['today_reservations']); ?></h3>
                <p>Today's Bookings</p>
            </div>
            <a href="reservations.php?date=<?php echo $today; ?>" class="stat-link">
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon pending">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['pending_reservations']); ?></h3>
                <p>Pending Actions</p>
            </div>
            <a href="reservations.php?status=pending" class="stat-link">
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon revenue">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-content">
                <h3>$<?php echo number_format($stats['monthly_revenue'], 2); ?></h3>
                <p>Monthly Revenue</p>
            </div>
            <a href="analytics.php" class="stat-link">
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Charts & Graphs -->
    <div class="charts-grid">
        <!-- Reservations Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3>Reservations Overview</h3>
                <select class="chart-period" id="reservationsPeriod">
                    <option value="7">Last 7 Days</option>
                    <option value="30" selected>Last 30 Days</option>
                    <option value="90">Last 90 Days</option>
                </select>
            </div>
            <div class="chart-body">
                <canvas id="reservationsChart"></canvas>
            </div>
        </div>
        
        <!-- Revenue Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3>Revenue Trend</h3>
                <select class="chart-period" id="revenuePeriod">
                    <option value="monthly" selected>Monthly</option>
                    <option value="weekly">Weekly</option>
                    <option value="daily">Daily</option>
                </select>
            </div>
            <div class="chart-body">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Main Content Grid -->
    <div class="content-grid">
        <!-- Today's Schedule -->
        <div class="content-card">
            <div class="card-header">
                <h3>Today's Schedule</h3>
                <a href="reservations.php?date=<?php echo $today; ?>" class="btn-link">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($today_schedule)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <p>No reservations for today</p>
                    </div>
                <?php else: ?>
                    <div class="schedule-list">
                        <?php foreach ($today_schedule as $reservation): ?>
                        <div class="schedule-item <?php echo $reservation['status']; ?>">
                            <div class="schedule-time">
                                <?php echo date('g:i A', strtotime($reservation['reservation_time'])); ?>
                            </div>
                            <div class="schedule-details">
                                <h4><?php echo htmlspecialchars($reservation['customer_name']); ?></h4>
                                <p><?php echo $reservation['party_size']; ?> people • Table <?php echo $reservation['table_number'] ?? 'TBD'; ?></p>
                            </div>
                            <div class="schedule-status">
                                <span class="status-badge status-<?php echo $reservation['status']; ?>">
                                    <?php echo ucfirst($reservation['status']); ?>
                                </span>
                            </div>
                            <div class="schedule-actions">
                                <a href="reservations.php?action=view&id=<?php echo $reservation['id']; ?>" class="action-btn" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Recent Reservations -->
        <div class="content-card">
            <div class="card-header">
                <h3>Recent Reservations</h3>
                <a href="reservations.php" class="btn-link">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_reservations as $reservation): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($reservation['customer_name']); ?></strong>
                                    <small><?php echo $reservation['party_size']; ?> people</small>
                                </td>
                                <td>
                                    <?php echo date('M d', strtotime($reservation['reservation_date'])); ?>
                                </td>
                                <td>
                                    <?php echo date('g:i A', strtotime($reservation['reservation_time'])); ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $reservation['status']; ?>">
                                        <?php echo ucfirst($reservation['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="reservations.php?action=view&id=<?php echo $reservation['id']; ?>" 
                                           class="btn-icon" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="reservations.php?action=edit&id=<?php echo $reservation['id']; ?>" 
                                           class="btn-icon" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Popular Items -->
        <div class="content-card">
            <div class="card-header">
                <h3>Popular Menu Items</h3>
                <a href="menu-management.php" class="btn-link">Manage Menu</a>
            </div>
            <div class="card-body">
                <div class="menu-items-list">
                    <?php foreach ($popular_items as $item): ?>
                    <div class="menu-item-card">
                        <div class="menu-item-image">
                            <?php if ($item['image']): ?>
                                <img src="<?php echo SITE_URL . $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <?php else: ?>
                                <div class="image-placeholder">
                                    <i class="fas fa-utensils"></i>
                                </div>
                            <?php endif; ?>
                            <?php if ($item['featured']): ?>
                                <span class="featured-badge">Featured</span>
                            <?php endif; ?>
                        </div>
                        <div class="menu-item-content">
                            <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                            <p class="price">$<?php echo number_format($item['price'], 2); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="content-card">
            <div class="card-header">
                <h3>Recent Activity</h3>
                <a href="activity-log.php" class="btn-link">View Log</a>
            </div>
            <div class="card-body">
                <div class="activity-list">
                    <?php foreach ($recent_activity as $activity): ?>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-<?php echo getActivityIcon($activity['action']); ?>"></i>
                        </div>
                        <div class="activity-content">
                            <p>
                                <strong><?php echo htmlspecialchars($activity['full_name'] ?? 'System'); ?></strong>
                                <?php echo htmlspecialchars($activity['action']); ?>
                            </p>
                            <small><?php echo time_ago($activity['created_at']); ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Stats -->
    <div class="quick-stats-grid">
        <div class="quick-stat">
            <div class="stat-label">
                <i class="fas fa-utensils"></i>
                <span>Menu Items</span>
            </div>
            <div class="stat-value"><?php echo number_format($stats['total_menu_items']); ?></div>
        </div>
        
        <div class="quick-stat">
            <div class="stat-label">
                <i class="fas fa-star"></i>
                <span>Testimonials</span>
            </div>
            <div class="stat-value"><?php echo number_format($stats['total_testimonials']); ?></div>
        </div>
        
        <div class="quick-stat">
            <div class="stat-label">
                <i class="fas fa-user-check"></i>
                <span>Staff Members</span>
            </div>
            <div class="stat-value">
                <?php 
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM staff WHERE active = 1");
                echo number_format($stmt->fetch()['total']);
                ?>
            </div>
        </div>
        
        <div class="quick-stat">
            <div class="stat-label">
                <i class="fas fa-image"></i>
                <span>Gallery Images</span>
            </div>
            <div class="stat-value">
                <?php 
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM gallery WHERE active = 1");
                echo number_format($stmt->fetch()['total']);
                ?>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="quick-actions-grid">
        <h3>Quick Actions</h3>
        <div class="actions-grid">
            <a href="reservations.php?action=add" class="quick-action">
                <div class="action-icon">
                    <i class="fas fa-calendar-plus"></i>
                </div>
                <div class="action-content">
                    <h4>Add Reservation</h4>
                    <p>Create a new booking</p>
                </div>
            </a>
            
            <a href="menu-management.php?action=add" class="quick-action">
                <div class="action-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <div class="action-content">
                    <h4>Add Menu Item</h4>
                    <p>Add new dish to menu</p>
                </div>
            </a>
            
            <a href="testimonials.php?action=add" class="quick-action">
                <div class="action-icon">
                    <i class="fas fa-comment-medical"></i>
                </div>
                <div class="action-content">
                    <h4>Add Testimonial</h4>
                    <p>Add customer review</p>
                </div>
            </a>
            
            <a href="gallery.php?action=upload" class="quick-action">
                <div class="action-icon">
                    <i class="fas fa-cloud-upload-alt"></i>
                </div>
                <div class="action-content">
                    <h4>Upload Images</h4>
                    <p>Add to gallery</p>
                </div>
            </a>
        </div>
    </div>
</div>

<?php
// Helper function to get activity icon
function getActivityIcon($action) {
    $icons = [
        'login' => 'sign-in-alt',
        'logout' => 'sign-out-alt',
        'create' => 'plus-circle',
        'update' => 'edit',
        'delete' => 'trash',
        'view' => 'eye',
        'download' => 'download',
        'upload' => 'upload',
        'approve' => 'check-circle',
        'reject' => 'times-circle',
        'reservation' => 'calendar-check',
        'menu' => 'utensils',
        'testimonial' => 'star',
        'gallery' => 'images',
        'settings' => 'cog',
        'user' => 'user',
        'default' => 'history'
    ];
    
    foreach ($icons as $key => $icon) {
        if (stripos($action, $key) !== false) {
            return $icon;
        }
    }
    
    return $icons['default'];
}
?>

<!-- JavaScript -->
<script src="assets/js/chart.js"></script>
<script>
    // Initialize real-time clock
    function updateClock() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
        document.getElementById('currentTime').textContent = timeString;
    }
    
    setInterval(updateClock, 1000);
    
    // Initialize charts
    document.addEventListener('DOMContentLoaded', function() {
        // Reservations Chart
        const reservationsCtx = document.getElementById('reservationsChart').getContext('2d');
        const reservationsChart = new Chart(reservationsCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($monthly_data, 'month')); ?>,
                datasets: [{
                    label: 'Reservations',
                    data: <?php echo json_encode(array_column($monthly_data, 'count')); ?>,
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#4f46e5',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
         
