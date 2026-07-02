<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

// Check if user is admin
if (!isAdmin()) {
    $_SESSION['error'] = 'Access denied';
    redirect('../index.php');
}

// Get statistics
$stats = getDashboardStats();
$recent_activities = getRecentActivities(10);
$chart_data = getChartData('month');

include '../includes/navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-md-block bg-dark text-white sidebar">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link text-white active" href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="users.php"><i class="fas fa-users"></i> Users</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="businesses.php"><i class="fas fa-store"></i> Businesses</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="reviews.php"><i class="fas fa-star"></i> Reviews</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="reports.php"><i class="fas fa-chart-line"></i> Reports</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="backup.php"><i class="fas fa-database"></i> Backup</a></li>
                </ul>
            </div>
        </nav>
        
        <main class="col-md-10 ms-sm-auto px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Admin Dashboard</h1>
                <span class="text-muted">Welcome, <?php echo $_SESSION['fullname']; ?>!</span>
            </div>
            
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-primary h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6>Total Users</h6>
                                    <h2 class="mb-0"><?php echo number_format($stats['total_users']); ?></h2>
                                </div>
                                <i class="fas fa-users fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-success h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6>Total Businesses</h6>
                                    <h2 class="mb-0"><?php echo number_format($stats['total_businesses']); ?></h2>
                                </div>
                                <i class="fas fa-store fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-warning h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6>Pending Approval</h6>
                                    <h2 class="mb-0"><?php echo number_format($stats['pending_businesses']); ?></h2>
                                </div>
                                <i class="fas fa-clock fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-info h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6>Total Reviews</h6>
                                    <h2 class="mb-0"><?php echo number_format($stats['total_reviews']); ?></h2>
                                </div>
                                <i class="fas fa-star fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Charts -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h6 class="mb-0">User Growth (Last 30 Days)</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="userChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h6 class="mb-0">Business Growth (Last 30 Days)</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="businessChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Recent Activities -->
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activities</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Activity</th>
                                            <th>Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($recent_activities as $activity): ?>
                                        <tr>
                                            <td><?php echo $activity['activity']; ?></td>
                                            <td><?php echo timeAgo($activity['time']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// User Growth Chart
const userCtx = document.getElementById('userChart').getContext('2d');
new Chart(userCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($chart_data['users'], 'label')); ?>,
        datasets: [{
            label: 'New Users',
            data: <?php echo json_encode(array_column($chart_data['users'], 'count')); ?>,
            borderColor: '#4361ee',
            backgroundColor: 'rgba(67, 97, 238, 0.1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: { responsive: true, maintainAspectRatio: true }
});

// Business Growth Chart
const bizCtx = document.getElementById('businessChart').getContext('2d');
new Chart(bizCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($chart_data['businesses'], 'label')); ?>,
        datasets: [{
            label: 'New Businesses',
            data: <?php echo json_encode(array_column($chart_data['businesses'], 'count')); ?>,
            borderColor: '#28a745',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: { responsive: true, maintainAspectRatio: true }
});
</script>

<?php include '../includes/footer.php'; ?>