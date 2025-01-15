<?php
// Set upload directory and constraints
$uploadDir = "uploads/";
$allowedFileTypes = ['image/jpeg', 'image/png', 'image/gif'];
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
$maxFileSize = 5 * 1024 * 1024; // 5MB
$maxFilesPerId = 100; // Limit the number of files per ID

// Get and sanitize the ID
// Get and sanitize the ID
$id = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['id'] ?? 'unknown');

// Continue handling the image upload as usual


// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if a file was uploaded without errors
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpName = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileSize = $_FILES['image']['size'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Validate MIME type using finfo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileType = finfo_file($finfo, $fileTmpName);
        finfo_close($finfo);

        if (!in_array($fileType, $allowedFileTypes)) {
            echo "Error: Invalid MIME type.";
            exit;
        }

        // Validate file extension
        if (!in_array($fileExtension, $allowedExtensions)) {
            echo "Error: Invalid file extension.";
            exit;
        }

        // Validate file size
        if ($fileSize > $maxFileSize) {
            echo "Error: File size exceeds the maximum limit of 5MB.";
            exit;
        }

        // Validate if the file is an image
        if (!getimagesize($fileTmpName)) {
            echo "Error: The uploaded file is not a valid image.";
            exit;
        }

        // Create directory for this ID if it doesn't exist
        $idDir = $uploadDir . $id . '/';
        if (!is_dir($idDir)) {
            mkdir($idDir, 0755, true);
        }

        // Check file count limit
        $existingFiles = glob($idDir . '*');
        if (count($existingFiles) >= $maxFilesPerId) {
            echo "Error: Maximum file limit reached for this ID.";
            exit;
        }

        // Create a unique file name
        $imageIndex = count($existingFiles) + 1;
        $newFileName = $id . '_' . $imageIndex . '.' . $fileExtension;
        $uploadPath = $idDir . $newFileName;

        // Move the uploaded file
        if (move_uploaded_file($fileTmpName, $uploadPath)) {
            // echo "Success: File uploaded.<br>";
            // echo "<img src='" . htmlspecialchars($uploadPath) . "' alt='Uploaded Image' style='max-width: 300px;'>";

            // Send the file to Flask app
            $url = "http://localhost:5000/upload";
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, [
                'image' => new CURLFile($uploadPath), // Ensure $uploadPath is the full path to the image file
                'id' => $id // The ID to send
            ]);

            // Set cURL timeout and SSL verification
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                echo 'Error: ' . curl_error($ch);
            } else {
                $responseData = json_decode($response, true);
                if (isset($responseData['status']) && $responseData['status'] === 'success') {
                    echo "Flask Response: " . $responseData['message'];
                } else {
                    echo "Flask Error: "  ?? 'Unknown error.';
                }
            }

            curl_close($ch);
        } else {
            echo "Error: Failed to move uploaded file.";
        }
    } else {
        echo "Error: No file uploaded or there was an error during the upload.";
    }
} else {
    echo "Error: Invalid request method.";
}
