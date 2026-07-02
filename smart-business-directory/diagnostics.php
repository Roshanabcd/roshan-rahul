<?php
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostics</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 24px; background: #f8fafc; }
        .card { max-width: 800px; margin: 0 auto; background: white; padding: 24px; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.08); }
        code { background: #f3f4f6; padding: 2px 6px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Database Diagnostics</h1>
        <p><strong>Host:</strong> <?php echo htmlspecialchars(DB_HOST); ?></p>
        <p><strong>User:</strong> <?php echo htmlspecialchars(DB_USER); ?></p>
        <p><strong>Database:</strong> <?php echo htmlspecialchars(DB_NAME); ?></p>
        <p><strong>Status:</strong> <?php echo $conn ? 'Connected' : 'Not connected'; ?></p>
        <?php if ($conn): ?>
            <p><strong>Connection test:</strong> Successful</p>
        <?php else: ?>
            <p><strong>Connection test:</strong> Failed</p>
        <?php endif; ?>
    </div>
</body>
</html>
