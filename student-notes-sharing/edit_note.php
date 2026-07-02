<?php
require_once 'includes/header.php';
require_once 'includes/auth_check.php';

$pdo = getDBConnection();
$userId = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    redirect('dashboard.php');
}

$noteId = intval($_GET['id']);

// Get note
$stmt = $pdo->prepare("SELECT * FROM notes WHERE note_id = :note_id AND user_id = :user_id");
$stmt->execute(['note_id' => $noteId, 'user_id' => $userId]);
$note = $stmt->fetch();

if (!$note) {
    $_SESSION['error'] = "Note not found.";
    redirect('dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $subject = sanitize($_POST['subject']);
    $semester = sanitize($_POST['semester']);
    
    if (empty($title)) $errors[] = "Title is required.";
    if (empty($subject)) $errors[] = "Subject is required.";
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE notes SET title = :title, description = :description, 
                               subject = :subject, semester = :semester WHERE note_id = :note_id");
        $stmt->execute([
            'title' => $title,
            'description' => $description,
            'subject' => $subject,
            'semester' => $semester,
            'note_id' => $noteId
        ]);
        
        $_SESSION['success'] = "Note updated successfully!";
        redirect('dashboard.php');
    }
}
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="upload-form">
                <h3 class="mb-4"><i class="fas fa-edit me-2"></i>Edit Note</h3>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" 
                               value="<?php echo sanitize($note['title']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"><?php echo sanitize($note['description']); ?></textarea>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control" 
                                   value="<?php echo sanitize($note['subject']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Semester <span class="text-danger">*</span></label>
                            <select name="semester" class="form-select" required>
                                <?php for ($i = 1; $i <= 8; $i++): ?>
                                    <option value="<?php echo $i; ?>th" 
                                        <?php echo ($note['semester'] == $i.'th') ? 'selected' : ''; ?>>
                                        <?php echo $i; ?>th Semester
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    
                    <p class="text-muted mb-3">
                        <i class="fas fa-file me-1"></i>Current file: <?php echo sanitize($note['file_name']); ?>
                        <br><small>To change the file, delete this note and upload a new one.</small>
                    </p>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Note
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>