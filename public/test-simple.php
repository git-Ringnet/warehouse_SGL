<?php
// Simple PHP configuration test
echo "<h2>PHP Configuration Test</h2>";
echo "<p><strong>upload_max_filesize:</strong> " . ini_get('upload_max_filesize') . "</p>";
echo "<p><strong>post_max_size:</strong> " . ini_get('post_max_size') . "</p>";
echo "<p><strong>memory_limit:</strong> " . ini_get('memory_limit') . "</p>";
echo "<p><strong>max_execution_time:</strong> " . ini_get('max_execution_time') . "</p>";

// Test if we can set values
ini_set('upload_max_filesize', '500M');
ini_set('post_max_size', '500M');
ini_set('memory_limit', '512M');

echo "<h3>After setting values:</h3>";
echo "<p><strong>upload_max_filesize:</strong> " . ini_get('upload_max_filesize') . "</p>";
echo "<p><strong>post_max_size:</strong> " . ini_get('post_max_size') . "</p>";
echo "<p><strong>memory_limit:</strong> " . ini_get('memory_limit') . "</p>";

// Simple upload form
echo "<h3>Test Upload:</h3>";
echo "<form method='post' enctype='multipart/form-data'>";
echo "<input type='file' name='test_file'>";
echo "<input type='submit' value='Upload'>";
echo "</form>";

if ($_FILES) {
    echo "<h3>Upload Result:</h3>";
    if (isset($_FILES['test_file'])) {
        if ($_FILES['test_file']['error'] === UPLOAD_ERR_OK) {
            echo "<p style='color: green;'>✓ Upload successful! Size: " . number_format($_FILES['test_file']['size']) . " bytes</p>";
        } else {
            echo "<p style='color: red;'>✗ Upload failed! Error code: " . $_FILES['test_file']['error'] . "</p>";
        }
    }
}
?> 