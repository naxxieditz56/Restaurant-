// Analytics Charts for Admin Dashboard

// Initialize all charts when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Website Traffic Overview Chart
    const trafficCtx = document.getElementById('trafficChart').getContext('2d');
    const trafficChart = new Chart(trafficCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
            datasets: [
                {
                    label: 'Organic',
                    data: [12000, 19000, 15000, 25000, 22000, 30000, 28000],
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Direct',
                    data: [8000, 12000, 10000, 15000, 18000, 22000, 20000],
                    borderColor: '#2ecc71',
                    backgroundColor: 'rgba(46, 204, 113, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Social',
                    data: [5000, 8000, 6000, 9000, 12000, 15000, 14000],
                    borderColor: '#e74c3c',
                    backgroundColor: 'rgba(231, 76, 60, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Referral',
                    data: [3000, 5000, 4000, 7000, 9000, 12000, 11000],
                    borderColor: '#9b59b6',
                    backgroundColor: 'rgba(155, 89, 182, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    },
                    ticks: {
                        callback: function(value) {
                            return value >= 1000 ? (value/1000) + 'k' : value;
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Top Pages Chart
    const topPagesCtx = document.getElementById('topPagesChart').getContext('2d');
    const topPagesChart = new Chart(topPagesCtx, {
        type: 'doughnut',
        data: {
            labels: ['Home', 'About', 'Services', 'Contact', 'Blog'],
            datasets: [{
                data: [35, 25, 20, 12, 8],
                backgroundColor: [
                    '#3498db',
                    '#2ecc71',
                    '#e74c3c',
                    '#f39c12',
                    '#9b59b6'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            },
            cutout: '70%'
        }
    });

    // Device Breakdown Chart
    const deviceCtx = document.getElementById('deviceChart').getContext('2d');
    const deviceChart = new Chart(deviceCtx, {
        type: 'pie',
        data: {
            labels: ['Desktop', 'Mobile', 'Tablet'],
            datasets: [{
                data: [55, 35, 10],
                backgroundColor: [
                    '#3498db',
                    '#2ecc71',
                    '#f39c12'
                ],
                borderWidth: 3,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Mini Charts for Metrics
    const miniChartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: { enabled: false }
        },
        scales: {
            x: { display: false },
            y: { display: false }
        },
        elements: {
            line: {
                tension: 0.4,
                borderWidth: 2
            },
            point: {
                radius: 0
            }
        }
    };

    // Visitors Mini Chart
    const visitorsCtx = document.getElementById('visitorsChart').getContext('2d');
    new Chart(visitorsCtx, {
        type: 'line',
        data: {
            labels: Array(7).fill(''),
            datasets: [{
                data: [30, 45, 40, 60, 55, 75, 70],
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                fill: true
            }]
        },
        options: miniChartOptions
    });

    // Page Views Mini Chart
    const pageViewsCtx = document.getElementById('pageViewsChart').getContext('2d');
    new Chart(pageViewsCtx, {
        type: 'line',
        data: {
            labels: Array(7).fill(''),
            datasets: [{
                data: [40, 50, 45, 65, 70, 85, 90],
                borderColor: '#2ecc71',
                backgroundColor: 'rgba(46, 204, 113, 0.1)',
                fill: true
            }]
        },
        options: miniChartOptions
    });

    // Session Duration Mini Chart
    const sessionCtx = document.getElementById('sessionChart').getContext('2d');
    new Chart(sessionCtx, {
        type: 'line',
        data: {
            labels: Array(7).fill(''),
            datasets: [{
                data: [70, 65, 68, 62, 58, 55, 52],
                borderColor: '#f39c12',
                backgroundColor: 'rgba(243, 156, 18, 0.1)',
                fill: true
            }]
        },
        options: miniChartOptions
    });

    // Bounce Rate Mini Chart
    const bounceCtx = document.getElementById('bounceRateChart').getContext('2d');
    new Chart(bounceCtx, {
        type: 'line',
        data: {
            labels: Array(7).fill(''),
            datasets: [{
                data: [50, 48, 45, 42, 40, 38, 35],
                borderColor: '#e74c3c',
                backgroundColor: 'rgba(231, 76, 60, 0.1)',
                fill: true
            }]
        },
        options: miniChartOptions
    });

    // Time Range Filter
    const timeRangeSelect = document.getElementById('timeRange');
    if (timeRangeSelect) {
        timeRangeSelect.addEventListener('change', function() {
            const days = parseInt(this.value);
            updateChartData(days);
        });
    }

    function updateChartData(days) {
        // This function would update chart data based on selected time range
        console.log('Updating charts for last', days, 'days');
        
        // In a real application, you would:
        // 1. Fetch new data from server via AJAX
        // 2. Update all charts with new data
        // 3. Refresh the charts
        
        // Example update (simulated):
        const newLabels = generateLabels(days);
        trafficChart.data.labels = newLabels;
        trafficChart.update();
    }

    function generateLabels(days) {
        const labels = [];
        const now = new Date();
        
        for (let i = days - 1; i >= 0; i--) {
            const date = new Date(now);
            date.setDate(date.getDate() - i);
            
            if (days <= 7) {
                labels.push(date.toLocaleDateString('en-US', { weekday: 'short' }));
            } else if (days <= 30) {
                labels.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
            } else {
                labels.push(date.toLocaleDateString('en-US', { month: 'short' }));
            }
        }
        
        return labels;
    }

    // Export functionality
    const exportBtn = document.querySelector('.btn-secondary');
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            exportChartsData();
        });
    }

    function exportChartsData() {
        const data = {
            trafficData: trafficChart.data,
            topPagesData: topPagesChart.data,
            deviceData: deviceChart.data,
            timestamp: new Date().toISOString()
        };
        
        const dataStr = JSON.stringify(data, null, 2);
        const dataBlob = new Blob([dataStr], { type: 'application/json' });
        const url = URL.createObjectURL(dataBlob);
        
        const a = document.createElement('a');
        a.href = url;
        a.download = `analytics-export-${new Date().toISOString().split('T')[0]}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        
        // Show success message
        showNotification('Data exported successfully!', 'success');
    }

    function showNotification(message, type) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        `;
        
        // Add to body
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => notification.classList.add('show'), 10);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    // Add notification styles
    const style = document.createElement('style');
    style.textContent = `
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 10px;
            transform: translateX(150%);
            transition: transform 0.3s ease;
            z-index: 9999;
        }
        
        .notification.show {
            transform: translateX(0);
        }
        
        .notification-success {
            border-left: 4px solid #2ecc71;
        }
        
        .notification-error {
            border-left: 4px solid #e74c3c;
        }
        
        .notification i {
            font-size: 1.2rem;
        }
        
        .notification-success i {
            color: #2ecc71;
        }
        
        .notification-error i {
            color: #e74c3c;
        }
    `;
    document.head.appendChild(style);
});

// Chart Update Functions
function updateTrafficChart(newData) {
    // Function to update traffic chart with new data
    const chart = Chart.getChart('trafficChart');
    if (chart) {
        chart.data.datasets.forEach((dataset, index) => {
            if (newData[index]) {
                dataset.data = newData[index];
            }
        });
        chart.update();
    }
}

function refreshAllCharts() {
    // Refresh all charts
    Chart.getChart('trafficChart')?.update();
    Chart.getChart('topPagesChart')?.update();
    Chart.getChart('deviceChart')?.update();
}

// Utility function for generating random data (for demo purposes)
function generateRandomData(count, min, max) {
    return Array.from({length: count}, () => 
        Math.floor(Math.random() * (max - min + 1)) + min
    );
                  }
