<?php
// Set PHP settings for large file uploads
ini_set('upload_max_filesize', '500M');
ini_set('post_max_size', '500M');
ini_set('max_execution_time', 300);
ini_set('max_input_time', 300);
ini_set('memory_limit', '512M');
ini_set('max_file_uploads', 20);
ini_set('max_input_vars', 3000);

echo "<h2>PHP Upload Configuration Test</h2>";
echo "<p><strong>upload_max_filesize:</strong> " . ini_get('upload_max_filesize') . "</p>";
echo "<p><strong>post_max_size:</strong> " . ini_get('post_max_size') . "</p>";
echo "<p><strong>memory_limit:</strong> " . ini_get('memory_limit') . "</p>";
echo "<p><strong>max_execution_time:</strong> " . ini_get('max_execution_time') . "</p>";
echo "<p><strong>max_input_time:</strong> " . ini_get('max_input_time') . "</p>";
echo "<p><strong>max_file_uploads:</strong> " . ini_get('max_file_uploads') . "</p>";

// Convert to bytes for comparison
function convertToBytes($value) {
    $value = trim($value);
    $last = strtolower($value[strlen($value)-1]);
    $value = (int)$value;
    switch($last) {
        case 'g':
            $value *= 1024;
        case 'm':
            $value *= 1024;
        case 'k':
            $value *= 1024;
    }
    return $value;
}

$uploadMax = convertToBytes(ini_get('upload_max_filesize'));
$postMax = convertToBytes(ini_get('post_max_size'));

echo "<h3>Size Comparison (in bytes):</h3>";
echo "<p><strong>upload_max_filesize:</strong> " . number_format($uploadMax) . " bytes</p>";
echo "<p><strong>post_max_size:</strong> " . number_format($postMax) . " bytes</p>";

if ($postMax >= $uploadMax) {
    echo "<p style='color: green;'>✓ Configuration is correct: post_max_size >= upload_max_filesize</p>";
} else {
    echo "<p style='color: red;'>✗ Configuration error: post_max_size must be >= upload_max_filesize</p>";
}

echo "<h3>Test Upload Form:</h3>";
echo "<form method='post' enctype='multipart/form-data'>";
echo "<input type='file' name='test_file' accept='*/*'>";
echo "<input type='submit' value='Test Upload'>";
echo "</form>";

if ($_FILES) {
    echo "<h3>Upload Result:</h3>";
    if (isset($_FILES['test_file']) && $_FILES['test_file']['error'] === UPLOAD_ERR_OK) {
        echo "<p style='color: green;'>✓ File uploaded successfully!</p>";
        echo "<p><strong>File name:</strong> " . $_FILES['test_file']['name'] . "</p>";
        echo "<p><strong>File size:</strong> " . number_format($_FILES['test_file']['size']) . " bytes</p>";
    } else {
        echo "<p style='color: red;'>✗ Upload failed!</p>";
        echo "<p><strong>Error code:</strong> " . $_FILES['test_file']['error'] . "</p>";
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
?> 