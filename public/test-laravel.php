<?php
// Laravel test file
require_once __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Set PHP settings
ini_set('upload_max_filesize', '500M');
ini_set('post_max_size', '500M');
ini_set('max_execution_time', 300);
ini_set('memory_limit', '512M');

echo "<h2>Laravel Configuration Test</h2>";
echo "<p><strong>upload_max_filesize:</strong> " . ini_get('upload_max_filesize') . "</p>";
echo "<p><strong>post_max_size:</strong> " . ini_get('post_max_size') . "</p>";
echo "<p><strong>memory_limit:</strong> " . ini_get('memory_limit') . "</p>";
echo "<p><strong>max_execution_time:</strong> " . ini_get('max_execution_time') . "</p>";

// Test middleware
try {
    $middleware = new \App\Http\Middleware\CustomValidatePostSize();
    echo "<p style='color: green;'>✓ CustomValidatePostSize middleware loaded successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error loading middleware: " . $e->getMessage() . "</p>";
}

// Test upload form
echo "<h3>Test Upload Form:</h3>";
echo "<form method='post' enctype='multipart/form-data'>";
echo "<input type='file' name='test_file' accept='*/*'>";
echo "<input type='submit' value='Test Upload'>";
echo "</form>";

if ($_FILES) {
    echo "<h3>Upload Result:</h3>";
    if (isset($_FILES['test_file'])) {
        if ($_FILES['test_file']['error'] === UPLOAD_ERR_OK) {
            echo "<p style='color: green;'>✓ Upload successful! File size: " . number_format($_FILES['test_file']['size']) . " bytes</p>";
        } else {
            echo "<p style='color: red;'>✗ Upload failed! Error code: " . $_FILES['test_file']['error'] . "</p>";
            $errors = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
            ];
            if (isset($errors[$_FILES['test_file']['error']])) {
                echo "<p><strong>Error:</strong> " . $errors[$_FILES['test_file']['error']] . "</p>";
            }
        }
    }
}
?> 