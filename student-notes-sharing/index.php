<?php
require_once 'includes/header.php';
$pdo = getDBConnection();

// Get search parameters
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$semester = isset($_GET['semester']) ? sanitize($_GET['semester']) : '';

// Get notes
$notes = getAllNotes($pdo, $search, $semester);
$semesters = getDistinctSemesters($pdo);
?>

<!-- Hero Section -->
<?php if (!isLoggedIn()): ?>
<section class="hero-section text-center">
    <div class="container">
        <h1 class="display-4 mb-4">Welcome to Student Notes Sharing Platform</h1>
        <p class="lead mb-4">A centralized platform for students to share, discover, and download academic notes.</p>
        <div class="mt-4">
            <a href="register.php" class="btn btn-light btn-lg me-3">
                <i class="fas fa-user-plus me-2"></i>Get Started
            </a>
            <a href="login.php" class="btn btn-outline-light btn-lg">
                <i class="fas fa-sign-in-alt me-2"></i>Login
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Search Section -->
<section class="search-container">
    <form method="GET" action="index.php" class="row g-3">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" name="search" id="searchInput" class="form-control" 
                       placeholder="Search by title, subject..." value="<?php echo $search; ?>">
            </div>
        </div>
        <div class="col-md-4">
            <select name="semester" class="form-select">
                <option value="">All Semesters</option>
                <?php foreach ($semesters as $sem): ?>
                    <option value="<?php echo $sem; ?>" <?php echo ($semester == $sem) ? 'selected' : ''; ?>>
                        <?php echo $sem; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-filter me-2"></i>Search & Filter
            </button>
        </div>
    </form>
</section>

<!-- Notes Grid -->
<section>
    <h3 class="mb-4">
        <i class="fas fa-sticky-note me-2"></i>
        <?php echo !empty($search) || !empty($semester) ? 'Search Results' : 'All Notes'; ?>
        <span class="badge bg-primary ms-2"><?php echo count($notes); ?></span>
    </h3>
    
    <?php if (count($notes) > 0): ?>
        <div class="row">
            <?php foreach ($notes as $note): ?>
                <?php
                // Determine file type
                $ext = pathinfo($note['file_name'], PATHINFO_EXTENSION);
                $fileTypeClass = 'file-type-' . $ext;
                $fileIcon = 'fa-file';
                if ($ext == 'pdf') $fileIcon = 'fa-file-pdf';
                elseif ($ext == 'docx' || $ext == 'doc') $fileIcon = 'fa-file-word';
                elseif ($ext == 'ppt' || $ext == 'pptx') $fileIcon = 'fa-file-powerpoint';
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card note-card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><?php echo sanitize($note['title']); ?></h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text text-muted mb-3">
                                <?php echo !empty($note['description']) ? sanitize(substr($note['description'], 0, 100)) . '...' : 'No description provided.'; ?>
                            </p>
                            
                            <!-- Star Rating Display -->
                            <div class="mb-2">
                                <span class="star-rating-display" data-rating="<?php echo $note['avg_rating']; ?>"></span>
                                <small class="text-muted">(<?php echo $note['rating_count']; ?> ratings)</small>
                            </div>
                            
                            <div class="mb-3">
                                <span class="file-type-badge <?php echo $fileTypeClass; ?>">
                                    <i class="fas <?php echo $fileIcon; ?> me-1"></i><?php echo strtoupper($ext); ?>
                                </span>
                                <span class="badge bg-info ms-1"><?php echo sanitize($note['subject']); ?></span>
                                <span class="badge bg-secondary ms-1"><?php echo sanitize($note['semester']); ?></span>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-user me-1"></i><?php echo sanitize($note['uploader_name']); ?>
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i><?php echo date('M d, Y', strtotime($note['created_at'])); ?>
                                </small>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="download-badge">
                                    <i class="fas fa-download me-1"></i><?php echo $note['download_count']; ?> downloads
                                </span>
                                
                                <div class="btn-group">
                                    <a href="download.php?id=<?php echo $note['note_id']; ?>" class="btn btn-success btn-sm">
                                        <i class="fas fa-download me-1"></i>Download
                                    </a>
                                    <?php if (isLoggedIn()): ?>
                                        <button class="btn btn-outline-warning btn-sm rate-btn" 
                                                data-note-id="<?php echo $note['note_id']; ?>"
                                                data-bs-toggle="modal" data-bs-target="#rateModal">
                                            <i class="fas fa-star"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>No notes found. <?php if (!isLoggedIn()): ?><a href="login.php">Login</a> or <a href="register.php">Register</a> to upload notes.<?php endif; ?>
        </div>
    <?php endif; ?>
</section>

<!-- Rate Modal -->
<?php if (isLoggedIn()): ?>
<div class="modal fade" id="rateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rate This Note</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="rate.php" method="POST">
                <div class="modal-body text-center">
                    <input type="hidden" name="note_id" id="modal_note_id">
                    <input type="hidden" name="rating" id="rating_value">
                    <div id="rateYo" class="mb-3"></div>
                    <p class="text-muted">Click the stars to rate this note (1-5 stars)</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check me-1"></i>Submit Rating
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>