<?php
// Specify the upload directory
$uploadDir = 'uploads/';

// Check if the request is a POST request and contains a file
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image'])) {
    $image = $_FILES['image'];

    // Check if there was an error during upload
    if ($image['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'error' => 'File upload error.']);
        exit;
    }

    // Get the file details
    $fileTmpPath = $image['tmp_name'];
    $fileName = basename($image['name']);
    $fileType = $image['type'];

    // Generate a unique name for the image to avoid overwriting
    $fileName = uniqid() . '_' . $fileName;

    // Specify the file path where the image will be saved
    $filePath = $uploadDir . $fileName;

    // Check if the file is an image (optional validation step)
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($fileType, $allowedMimeTypes)) {
        echo json_encode(['success' => false, 'error' => 'Invalid file type.']);
        exit;
    }

    // Move the file to the uploads directory
    if (move_uploaded_file($fileTmpPath, $filePath)) {
        // Return success with the file name
        echo json_encode(['success' => true, 'file_name' => $fileName]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to move uploaded file.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No file uploaded.']);
}
?>
