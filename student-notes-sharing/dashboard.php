<?php
/**
 * User Dashboard
 * 
 * Shows user's uploaded notes and stats
 */

require_once 'includes/header.php';

// Check authentication
if (!isLoggedIn()) {
    $_SESSION['error'] = "Please login to access the dashboard.";
    redirect('login.php');
}

$pdo = getDBConnection();
$userId = $_SESSION['user_id'];

// Get user's notes
$notes = getNotesByUser($pdo, $userId);

?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-tachometer-alt me-2"></i>My Dashboard</h2>
        <a href="upload.php" class="btn btn-primary">
            <i class="fas fa-upload me-2"></i>Upload New Note
        </a>
    </div>

    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-info text-white shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Total Notes</h5>
                    <h2 class="mb-0"><?php echo count($notes); ?></h2>
                </div>
            </div>
        </div>
        <?php
        $totalDownloads = 0;
        foreach ($notes as $n) {
            $totalDownloads += $n['download_count'];
        }
        ?>
        <div class="col-md-4">
            <div class="card bg-success text-white shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Total Downloads</h5>
                    <h2 class="mb-0"><?php echo $totalDownloads; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Notes List -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>My Uploaded Notes</h5>
        </div>
        <div class="card-body">
            <?php if (count($notes) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Subject</th>
                                <th>Semester</th>
                                <th>Downloads</th>
                                <th>Rating</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($notes as $note): ?>
                                <tr>
                                    <td>
                                        <a href="download.php?id=<?php echo $note['note_id']; ?>" class="text-decoration-none text-dark fw-bold">
                                            <?php echo htmlspecialchars($note['title']); ?>
                                        </a>
                                    </td>
                                    <td><span class="badge bg-info"><?php echo htmlspecialchars($note['subject']); ?></span></td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($note['semester']); ?></span></td>
                                    <td><?php echo $note['download_count']; ?></td>
                                    <td>
                                        <span class="text-warning">
                                            <?php echo number_format($note['avg_rating'], 1); ?> <i class="fas fa-star"></i>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($note['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="edit_note.php?id=<?php echo $note['note_id']; ?>" class="btn btn-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete_note.php?id=<?php echo $note['note_id']; ?>" 
                                               class="btn btn-danger" 
                                               title="Delete"
                                               onclick="return confirm('Are you sure you want to delete this note?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle me-2"></i>You haven't uploaded any notes yet. 
                    <a href="upload.php" class="alert-link">Upload your first note!</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
