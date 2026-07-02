<?php
/**
 * Admin Dashboard - Main Entry Point
 * 
 * Access: Admin users only
 */

// Include header from parent directory
require_once '../includes/header.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isLoggedIn()) {
    $_SESSION['error'] = "Please login first.";
    redirect('../login.php');
}

if (!isAdmin()) {
    $_SESSION['error'] = "Access denied. Admin privileges required.";
    redirect('../dashboard.php');
}

$pdo = getDBConnection();

// Get statistics
try {
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $totalUsers = $stmt->fetchColumn();
    
    // Total students
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'");
    $totalStudents = $stmt->fetchColumn();
    
    // Total notes
    $stmt = $pdo->query("SELECT COUNT(*) FROM notes");
    $totalNotes = $stmt->fetchColumn();
    
    // Active notes
    $stmt = $pdo->query("SELECT COUNT(*) FROM notes WHERE status = 'active'");
    $activeNotes = $stmt->fetchColumn();
    
    // Total downloads
    $stmt = $pdo->query("SELECT COALESCE(SUM(download_count), 0) FROM notes");
    $totalDownloads = $stmt->fetchColumn();
    
    // Total ratings
    $stmt = $pdo->query("SELECT COUNT(*) FROM ratings");
    $totalRatings = $stmt->fetchColumn();
    
    // Average rating
    $stmt = $pdo->query("SELECT COALESCE(AVG(stars), 0) FROM ratings");
    $avgRating = round($stmt->fetchColumn(), 1);
    
    // Recent notes (last 5)
    $stmt = $pdo->query("SELECT n.*, u.name as uploader_name 
                         FROM notes n 
                         JOIN users u ON n.user_id = u.user_id 
                         ORDER BY n.created_at DESC LIMIT 5");
    $recentNotes = $stmt->fetchAll();
    
    // Recent users (last 5)
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
    $recentUsers = $stmt->fetchAll();
    
    // Notes per semester
    $stmt = $pdo->query("SELECT semester, COUNT(*) as count 
                         FROM notes 
                         GROUP BY semester 
                         ORDER BY semester");
    $notesPerSemester = $stmt->fetchAll();
    
    // Top uploaders
    $stmt = $pdo->query("SELECT u.name, COUNT(n.note_id) as note_count 
                         FROM users u 
                         LEFT JOIN notes n ON u.user_id = n.user_id 
                         WHERE u.role = 'student' 
                         GROUP BY u.user_id 
                         ORDER BY note_count DESC LIMIT 5");
    $topUploaders = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Admin dashboard error: " . $e->getMessage());
    $error = "Error loading dashboard data.";
}
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse" id="sidebarMenu">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active text-white" href="index.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white-50" href="users.php">
                            <i class="fas fa-users me-2"></i>Manage Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white-50" href="notes.php">
                            <i class="fas fa-sticky-note me-2"></i>Manage Notes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white-50" href="../index.php">
                            <i class="fas fa-home me-2"></i>View Site
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white-50" href="../logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-cog me-2"></i>Admin Dashboard
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <span class="text-muted">
                        Welcome, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>
                    </span>
                </div>
            </div>
            
            <!-- Flash Messages -->
            <?php echo showFlashMessage('success'); ?>
            <?php echo showFlashMessage('error'); ?>
            
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Users</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalUsers; ?></div>
                                    <small class="text-muted"><?php echo $totalStudents; ?> students</small>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-users fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Notes</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalNotes; ?></div>
                                    <small class="text-muted"><?php echo $activeNotes; ?> active</small>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-sticky-note fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Downloads</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($totalDownloads); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-download fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Avg Rating</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo $avgRating; ?> / 5
                                    </div>
                                    <small class="text-muted"><?php echo $totalRatings; ?> ratings</small>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-star fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Recent Notes -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Notes</h6>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($recentNotes)): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Uploader</th>
                                                <th>Date</th>
                                                <th>Views</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentNotes as $note): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars(substr($note['title'], 0, 30)); ?>...</td>
                                                    <td><?php echo htmlspecialchars($note['uploader_name']); ?></td>
                                                    <td><small><?php echo date('M d', strtotime($note['created_at'])); ?></small></td>
                                                    <td><span class="badge bg-info"><?php echo $note['download_count']; ?></span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center">No notes yet.</p>
                            <?php endif; ?>
                            <a href="notes.php" class="btn btn-sm btn-outline-primary mt-2">View All Notes</a>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Users -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="fas fa-user-plus me-2"></i>Recent Users</h6>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($recentUsers)): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Joined</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentUsers as $user): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                                    <td><small><?php echo htmlspecialchars($user['email']); ?></small></td>
                                                    <td><span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : 'primary'; ?>"><?php echo $user['role']; ?></span></td>
                                                    <td><small><?php echo date('M d', strtotime($user['created_at'])); ?></small></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center">No users yet.</p>
                            <?php endif; ?>
                            <a href="users.php" class="btn btn-sm btn-outline-success mt-2">View All Users</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Notes Per Semester -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Notes Per Semester</h6>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($notesPerSemester)): ?>
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Semester</th>
                                            <th>Notes</th>
                                            <th>Progress</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $maxNotes = max(array_column($notesPerSemester, 'count'));
                                        foreach ($notesPerSemester as $item): 
                                            $percentage = $maxNotes > 0 ? ($item['count'] / $maxNotes) * 100 : 0;
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['semester']); ?></td>
                                                <td><strong><?php echo $item['count']; ?></strong></td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar" style="width: <?php echo $percentage; ?>%;">
                                                            <?php echo round($percentage); ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p class="text-muted text-center">No data available.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Top Uploaders -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0"><i class="fas fa-trophy me-2"></i>Top Uploaders</h6>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($topUploaders) && $topUploaders[0]['note_count'] > 0): ?>
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $rank = 1;
                                        foreach ($topUploaders as $uploader): 
                                            if ($uploader['note_count'] == 0) break;
                                        ?>
                                            <tr>
                                                <td>
                                                    <?php if ($rank == 1): ?>🥇
                                                    <?php elseif ($rank == 2): ?>🥈
                                                    <?php elseif ($rank == 3): ?>🥉
                                                    <?php else: ?><?php echo $rank; ?><?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($uploader['name']); ?></td>
                                                <td><span class="badge bg-success"><?php echo $uploader['note_count']; ?></span></td>
                                            </tr>
                                        <?php 
                                            $rank++;
                                        endforeach; 
                                        ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p class="text-muted text-center">No uploads yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
        </main>
    </div>
</div>

<!-- Admin Styles -->
<style>
.sidebar {
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    z-index: 100;
    padding: 48px 0 0;
    box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
}

.sidebar .nav-link {
    font-weight: 500;
    padding: 10px 20px;
}

.sidebar .nav-link:hover {
    background: rgba(255,255,255,0.1);
}

.border-left-primary { border-left: 4px solid #4e73df; }
.border-left-success { border-left: 4px solid #1cc88a; }
.border-left-info { border-left: 4px solid #36b9cc; }
.border-left-warning { border-left: 4px solid #f6c23e; }

@media (max-width: 767.98px) {
    .sidebar {
        position: relative;
        padding: 0;
    }
}
</style>

<?php require_once '../includes/footer.php'; ?>