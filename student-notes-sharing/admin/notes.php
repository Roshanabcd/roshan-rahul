<?php
require_once '../includes/header.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error'] = "Access denied.";
    redirect('../login.php');
}

$pdo = getDBConnection();

// Handle note deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        // Get file path before deleting
        $stmt = $pdo->prepare("SELECT file_path FROM notes WHERE note_id = :note_id");
        $stmt->execute(['note_id' => intval($_GET['delete'])]);
        $note = $stmt->fetch();
        
        if ($note) {
            // Delete file
            $fullPath = __DIR__ . '/../' . $note['file_path'];
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            
            // Delete from database
            $stmt = $pdo->prepare("DELETE FROM notes WHERE note_id = :note_id");
            $stmt->execute(['note_id' => intval($_GET['delete'])]);
            
            $_SESSION['success'] = "Note deleted successfully.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting note.";
    }
    redirect('notes.php');
}

// Get all notes
$notes = getAllNotes($pdo);
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-white-50" href="index.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white-50" href="users.php">
                            <i class="fas fa-users me-2"></i>Manage Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active text-white" href="notes.php">
                            <i class="fas fa-sticky-note me-2"></i>Manage Notes
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
                <h1 class="h2"><i class="fas fa-sticky-note me-2"></i>Manage Notes</h1>
                <span class="badge bg-primary">Total: <?php echo count($notes); ?></span>
            </div>
            
            <?php echo showFlashMessage('success'); ?>
            <?php echo showFlashMessage('error'); ?>
            
            <div class="card shadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Uploader</th>
                                    <th>Subject</th>
                                    <th>Semester</th>
                                    <th>File</th>
                                    <th>Downloads</th>
                                    <th>Rating</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($notes as $note): ?>
                                    <tr>
                                        <td><?php echo $note['note_id']; ?></td>
                                        <td><?php echo htmlspecialchars(substr($note['title'], 0, 40)); ?>...</td>
                                        <td><?php echo htmlspecialchars($note['uploader_name']); ?></td>
                                        <td><span class="badge bg-info"><?php echo htmlspecialchars($note['subject']); ?></span></td>
                                        <td><?php echo htmlspecialchars($note['semester']); ?></td>
                                        <td>
                                            <?php 
                                            $ext = strtolower(pathinfo($note['file_name'], PATHINFO_EXTENSION));
                                            $badgeClass = $ext == 'pdf' ? 'danger' : ($ext == 'docx' ? 'primary' : 'warning');
                                            ?>
                                            <span class="badge bg-<?php echo $badgeClass; ?>"><?php echo strtoupper($ext); ?></span>
                                        </td>
                                        <td><?php echo $note['download_count']; ?></td>
                                        <td>
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= round($note['avg_rating']) ? 'text-warning' : 'text-muted'; ?>"></i>
                                            <?php endfor; ?>
                                        </td>
                                        <td><small><?php echo date('M d, Y', strtotime($note['created_at'])); ?></small></td>
                                        <td>
                                            <a href="../download.php?id=<?php echo $note['note_id']; ?>" 
                                               class="btn btn-sm btn-success" title="Download">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <a href="?delete=<?php echo $note['note_id']; ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Delete this note? This cannot be undone.');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
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

<style>
.sidebar { position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; padding: 48px 0 0; }
.sidebar .nav-link { font-weight: 500; padding: 10px 20px; }
.sidebar .nav-link:hover { background: rgba(255,255,255,0.1); }
@media (max-width: 767.98px) { .sidebar { position: relative; padding: 0; } }
</style>

<?php require_once '../includes/footer.php';?>