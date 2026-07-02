<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

if (!isAdmin()) {
    redirect('../index.php');
}

// Get dashboard stats
$stats = getDashboardStats();
$chart_data = getChartData('month');

include '../includes/navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-md-block bg-dark text-white sidebar">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link text-white" href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="users.php"><i class="fas fa-users"></i> Users</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="businesses.php"><i class="fas fa-store"></i> Businesses</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="reviews.php"><i class="fas fa-star"></i> Reviews</a></li>
                    <li class="nav-item"><a class="nav-link text-white active" href="reports.php"><i class="fas fa-chart-line"></i> Reports</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                </ul>
            </div>
        </nav>
        
        <main class="col-md-10 ms-sm-auto px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Analytics Reports</h1>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h6>Total Users</h6>
                            <h2><?php echo number_format($stats['total_users']); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h6>Total Businesses</h6>
                            <h2><?php echo number_format($stats['total_businesses']); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h6>Total Reviews</h6>
                            <h2><?php echo number_format($stats['total_reviews']); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h6>Views Today</h6>
                            <h2><?php echo number_format($stats['total_views_today']); ?></h2>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>User Growth</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="userGrowthChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Business Growth</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="businessGrowthChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5>Recent Activities</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Activity</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $activities = getRecentActivities(20); ?>
                                <?php foreach($activities as $activity): ?>
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
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// User Growth Chart
const userCtx = document.getElementById('userGrowthChart').getContext('2d');
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
    options: { responsive: true, maintainAspectRatio: false }
});

// Business Growth Chart
const bizCtx = document.getElementById('businessGrowthChart').getContext('2d');
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
    options: { responsive: true, maintainAspectRatio: false }
});
</script>

<?php include '../includes/footer.php'; ?>