<?php
// Admin Dashboard - Analytics
session_start();
require_once 'includes/auth.php';
checkAdminAccess();

$pageTitle = "Analytics Dashboard";
include 'includes/header.php';
?>

<div class="dashboard-container">
    <div class="sidebar">
        <?php include 'includes/sidebar.php'; ?>
    </div>
    
    <div class="main-content">
        <div class="content-header">
            <h1><i class="fas fa-chart-line"></i> Analytics Dashboard</h1>
            <div class="header-actions">
                <select id="timeRange">
                    <option value="7">Last 7 Days</option>
                    <option value="30" selected>Last 30 Days</option>
                    <option value="90">Last 90 Days</option>
                    <option value="365">Last Year</option>
                </select>
                <button class="btn btn-secondary">
                    <i class="fas fa-download"></i> Export Report
                </button>
            </div>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard.php">Dashboard</a> / 
            <a href="analytics.php">Analytics</a>
        </div>
        
        <!-- Key Metrics -->
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-header">
                    <h3>Total Visitors</h3>
                    <span class="metric-change positive">+12.5%</span>
                </div>
                <div class="metric-value">24,589</div>
                <div class="metric-chart">
                    <canvas id="visitorsChart"></canvas>
                </div>
            </div>
            
            <div class="metric-card">
                <div class="metric-header">
                    <h3>Page Views</h3>
                    <span class="metric-change positive">+8.3%</span>
                </div>
                <div class="metric-value">124,876</div>
                <div class="metric-chart">
                    <canvas id="pageViewsChart"></canvas>
                </div>
            </div>
            
            <div class="metric-card">
                <div class="metric-header">
                    <h3>Avg. Session</h3>
                    <span class="metric-change negative">-2.1%</span>
                </div>
                <div class="metric-value">4m 32s</div>
                <div class="metric-chart">
                    <canvas id="sessionChart"></canvas>
                </div>
            </div>
            
            <div class="metric-card">
                <div class="metric-header">
                    <h3>Bounce Rate</h3>
                    <span class="metric-change positive">-5.7%</span>
                </div>
                <div class="metric-value">32.4%</div>
                <div class="metric-chart">
                    <canvas id="bounceRateChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Main Charts -->
        <div class="charts-grid">
            <div class="chart-card wide">
                <div class="chart-header">
                    <h3>Website Traffic Overview</h3>
                    <div class="chart-legend">
                        <span class="legend-item"><i class="legend-dot organic"></i> Organic</span>
                        <span class="legend-item"><i class="legend-dot direct"></i> Direct</span>
                        <span class="legend-item"><i class="legend-dot social"></i> Social</span>
                        <span class="legend-item"><i class="legend-dot referral"></i> Referral</span>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="trafficChart"></canvas>
                </div>
            </div>
            
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Top Pages</h3>
                </div>
                <div class="chart-container">
                    <canvas id="topPagesChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Data Tables -->
        <div class="tables-grid">
            <div class="table-card">
                <div class="table-header">
                    <h3>Top Referrers</h3>
                </div>
                <div class="table-responsive">
                    <table class="compact-table">
                        <thead>
                            <tr>
                                <th>Source</th>
                                <th>Visitors</th>
                                <th>% Change</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <i class="fab fa-google"></i>
                                    <span>Google</span>
                                </td>
                                <td>12,458</td>
                                <td><span class="positive">+15%</span></td>
                            </tr>
                            <tr>
                                <td>
                                    <i class="fab fa-facebook"></i>
                                    <span>Facebook</span>
                                </td>
                                <td>5,234</td>
                                <td><span class="positive">+8%</span></td>
                            </tr>
                            <tr>
                                <td>
                                    <i class="fab fa-twitter"></i>
                                    <span>Twitter</span>
                                </td>
                                <td>3,567</td>
                                <td><span class="negative">-3%</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="table-card">
                <div class="table-header">
                    <h3>Device Breakdown</h3>
                </div>
                <div class="chart-container">
                    <canvas id="deviceChart"></canvas>
                </div>
            </div>
            
            <div class="table-card">
                <div class="table-header">
                    <h3>Geographic Data</h3>
                </div>
                <div class="table-responsive">
                    <table class="compact-table">
                        <thead>
                            <tr>
                                <th>Country</th>
                                <th>Visitors</th>
                                <th>Sessions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>United States</td>
                                <td>8,456</td>
                                <td>12,345</td>
                            </tr>
                            <tr>
                                <td>United Kingdom</td>
                                <td>3,567</td>
                                <td>5,678</td>
                            </tr>
                            <tr>
                                <td>India</td>
                                <td>2,890</td>
                                <td>4,123</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="chart.js"></script>

<?php include 'includes/footer.php'; ?>
