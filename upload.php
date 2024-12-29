<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxFileSize = 2 * 1024 * 1024; // 2 MB

        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = basename($_FILES['image']['name']);
        $fileSize = $_FILES['image']['size'];
        $fileMimeType = mime_content_type($fileTmpPath);
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Validate file size
        if ($fileSize > $maxFileSize) {
            die('Error: File size exceeds the 2MB limit.');
        }

        // Validate MIME type and extension
        if (!in_array($fileMimeType, $allowedMimeTypes) || !in_array($fileExtension, $allowedExtensions)) {
            die('Error: Invalid file type.');
        }

        // Save the file securely
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $newFileName = uniqid('img_', true) . '.' . $fileExtension;
        $filePath = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $filePath)) {
            // Display the uploaded image
            echo '<img src="' . htmlspecialchars($filePath) . '" alt="Uploaded Image">';
        } else {
            die('Error: Failed to move the uploaded file.');
        }
    } else {
        die('Error: No file uploaded or file upload error.');
    }
} else {
    die('Error: Invalid request method.');
}
?>
