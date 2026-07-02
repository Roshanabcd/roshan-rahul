<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

if (!isAdmin()) {
    redirect('../index.php');
}

if (isset($_GET['action']) && $_GET['action'] == 'export') {
    // Database backup
    $tables = [];
    $result = mysqli_query($conn, "SHOW TABLES");
    while ($row = mysqli_fetch_row($result)) {
        $tables[] = $row[0];
    }
    
    $output = "-- Database Backup: " . date('Y-m-d H:i:s') . "\n\n";
    
    foreach ($tables as $table) {
        $result = mysqli_query($conn, "SELECT * FROM $table");
        $num_fields = mysqli_num_fields($result);
        
        $output .= "DROP TABLE IF EXISTS $table;\n";
        $row2 = mysqli_fetch_row(mysqli_query($conn, "SHOW CREATE TABLE $table"));
        $output .= $row2[1] . ";\n\n";
        
        while ($row = mysqli_fetch_row($result)) {
            $output .= "INSERT INTO $table VALUES(";
            for ($j = 0; $j < $num_fields; $j++) {
                $row[$j] = addslashes($row[$j]);
                $output .= '"' . $row[$j] . '"';
                if ($j < ($num_fields - 1)) $output .= ',';
            }
            $output .= ");\n";
        }
        $output .= "\n";
    }
    
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename=backup_' . date('Y-m-d') . '.sql');
    echo $output;
    exit;
}

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
                    <li class="nav-item"><a class="nav-link text-white" href="reports.php"><i class="fas fa-chart-line"></i> Reports</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                    <li class="nav-item"><a class="nav-link text-white active" href="backup.php"><i class="fas fa-database"></i> Backup</a></li>
                </ul>
            </div>
        </nav>
        
        <main class="col-md-10 ms-sm-auto px-md-4">
            <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Database Backup</h1>
            </div>
            
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-database fa-4x text-primary mb-3"></i>
                    <h4>Database Backup</h4>
                    <p class="text-muted mb-4">Download a complete backup of your database including all tables and data.</p>
                    <a href="?action=export" class="btn btn-primary btn-lg">
                        <i class="fas fa-download me-2"></i>Download Backup
                    </a>
                </div>
            </div>
            
            <div class="alert alert-info mt-4">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Note:</strong> This backup includes all data from your database. Keep it in a safe place.
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>