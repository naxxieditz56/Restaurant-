<div class="sidebar-header">
    <h2>Admin Panel</h2>
    <p>Welcome, Admin</p>
</div>

<nav class="sidebar-nav">
    <ul>
        <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="pages-content.php"><i class="fas fa-file-alt"></i> Pages Content</a></li>
        <li><a href="testimonials.php"><i class="fas fa-quote-left"></i> Testimonials</a></li>
        <li><a href="staff.php"><i class="fas fa-users"></i> Staff Management</a></li>
        <li><a href="analytics.php" class="active"><i class="fas fa-chart-line"></i> Analytics</a></li>
        <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</nav>

<script>
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('active');
}
</script>
