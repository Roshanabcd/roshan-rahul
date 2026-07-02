<?php
/**
 * Upload Note Page
 * 
 * Allows logged-in users to upload notes
 */

// Include header first
require_once 'includes/header.php';

// Check authentication
if (!isLoggedIn()) {
    $_SESSION['error'] = "Please login to upload notes.";
    redirect('login.php');
}

$pdo = getDBConnection();
$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid security token. Please try again.";
    } else {
        // Get and sanitize inputs
        $title = sanitize($_POST['title'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $subject = sanitize($_POST['subject'] ?? '');
        $semester = sanitize($_POST['semester'] ?? '');
        
        // Validation
        if (empty($title)) {
            $errors[] = "Title is required.";
        }
        
        if (empty($subject)) {
            $errors[] = "Subject is required.";
        }
        
        if (empty($semester)) {
            $errors[] = "Semester is required.";
        }
        
        // File upload validation
        $allowedTypes = [
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/msword',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation'
        ];
        
        $maxSize = 10 * 1024 * 1024; // 10MB
        
        $fileValidation = validateFileUpload($_FILES['noteFile'] ?? null, $allowedTypes, $maxSize);
        
        if (!$fileValidation['valid']) {
            $errors[] = $fileValidation['error'];
        }
        
        // If no errors, proceed with upload
        if (empty($errors)) {
            try {
                $file = $_FILES['noteFile'];
                $extension = getFileExtension($file['name']);
                $newFileName = generateUniqueFilename($file['name']);
                $uploadDir = __DIR__ . '/uploads/';
                
                // Create uploads directory if not exists
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $filePath = $uploadDir . $newFileName;
                
                // Move uploaded file
                if (move_uploaded_file($file['tmp_name'], $filePath)) {
                    // Save to database
                    $dbFilePath = 'uploads/' . $newFileName;
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO notes (user_id, title, description, subject, semester, file_name, file_path) 
                        VALUES (:user_id, :title, :description, :subject, :semester, :file_name, :file_path)
                    ");
                    
                    $stmt->execute([
                        'user_id' => $_SESSION['user_id'],
                        'title' => $title,
                        'description' => $description,
                        'subject' => $subject,
                        'semester' => $semester,
                        'file_name' => $file['name'],
                        'file_path' => $dbFilePath
                    ]);
                    
                    // Set success message and redirect
                    $_SESSION['success'] = "Note uploaded successfully!";
                    redirect('dashboard.php');
                } else {
                    $errors[] = "Failed to save uploaded file. Please try again.";
                }
            } catch (PDOException $e) {
                error_log("Upload error: " . $e->getMessage());
                $errors[] = "Database error occurred. Please try again.";
                
                // Clean up uploaded file if database fails
                if (isset($filePath) && file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }
    }
}
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Upload Note</li>
                </ol>
            </nav>
            
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-upload me-2"></i>Upload New Note
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Error Messages -->
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Please fix the following errors:</h6>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Upload Form -->
                    <form method="POST" action="" enctype="multipart/form-data">
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <!-- Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label">
                                Note Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   name="title" 
                                   id="title" 
                                   class="form-control <?php echo (isset($errors) && !empty($errors) && empty($_POST['title'])) ? 'is-invalid' : ''; ?>" 
                                   value="<?php echo isset($_POST['title']) ? sanitize($_POST['title']) : ''; ?>" 
                                   placeholder="e.g., Data Structures and Algorithms - Chapter 1"
                                   maxlength="255"
                                   required>
                            <div class="form-text">Choose a clear, descriptive title for your note.</div>
                        </div>
                        
                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" 
                                      id="description" 
                                      class="form-control" 
                                      rows="4" 
                                      placeholder="Brief description of the note content, key topics covered..."
                                      maxlength="1000"><?php echo isset($_POST['description']) ? sanitize($_POST['description']) : ''; ?></textarea>
                            <div class="form-text">Optional. Provide a brief summary to help others understand the content.</div>
                        </div>
                        
                        <!-- Subject and Semester Row -->
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="subject" class="form-label">
                                    Subject <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="subject" 
                                       id="subject" 
                                       class="form-control" 
                                       value="<?php echo isset($_POST['subject']) ? sanitize($_POST['subject']) : ''; ?>" 
                                       placeholder="e.g., Data Structures"
                                       required>
                            </div>
                            <div class="col-md-6">
                                <label for="semester" class="form-label">
                                    Semester <span class="text-danger">*</span>
                                </label>
                                <select name="semester" id="semester" class="form-select" required>
                                    <option value="">-- Select Semester --</option>
                                    <?php for ($i = 1; $i <= 8; $i++): ?>
                                        <option value="<?php echo $i; ?>th" 
                                            <?php echo (isset($_POST['semester']) && $_POST['semester'] == $i.'th') ? 'selected' : ''; ?>>
                                            <?php echo $i; ?>th Semester
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        
                        <!-- File Upload -->
                        <div class="mb-4">
                            <label for="noteFile" class="form-label">
                                Choose File <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="file" 
                                       name="noteFile" 
                                       id="noteFile" 
                                       class="form-control" 
                                       accept=".pdf,.doc,.docx,.ppt,.pptx"
                                       required>
                                <label class="input-group-text" for="noteFile">
                                    <i class="fas fa-file-upload"></i>
                                </label>
                            </div>
                            <div id="file-name-display" class="mt-2"></div>
                            <div class="form-text">
                                <strong>Supported formats:</strong> PDF, DOCX, PPT<br>
                                <strong>Maximum file size:</strong> 10MB
                            </div>
                        </div>
                        
                        <!-- Submit Buttons -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="dashboard.php" class="btn btn-secondary me-md-2">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-cloud-upload-alt me-1"></i>Upload Note
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Show selected filename
document.getElementById('noteFile').addEventListener('change', function(e) {
    var fileName = e.target.files[0]?.name || '';
    var display = document.getElementById('file-name-display');
    
    if (fileName) {
        var ext = fileName.split('.').pop().toUpperCase();
        var icon = '📄';
        if (ext === 'PDF') icon = '📕';
        else if (ext === 'DOCX' || ext === 'DOC') icon = '📘';
        else if (ext === 'PPT' || ext === 'PPTX') icon = '📙';
        
        display.innerHTML = '<span class="text-success">' + icon + ' Selected: <strong>' + fileName + '</strong> (' + ext + ')</span>';
    } else {
        display.innerHTML = '';
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>