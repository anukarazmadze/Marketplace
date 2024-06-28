<?php
require_once '../session_manager.php';
require_once '../functions.php';

isAdmin();

$targetDir = "../uploads/";
$allowTypes = array('jpg', 'png', 'jpeg', 'gif');
$statusMsg = '';

if (!empty(array_filter($_FILES['product_images']['name']))) {
    foreach ($_FILES['product_images']['name'] as $key => $val) {
        $fileName = basename($_FILES['product_images']['name'][$key]);
        $targetFilePath = $targetDir . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

        if (in_array($fileType, $allowTypes)) {
            if (move_uploaded_file($_FILES['product_images']['tmp_name'][$key], $targetFilePath)) {
                $statusMsg .= "The file {$fileName} has been uploaded successfully.<br>";
                
                // Insert file path into the database
                $conn = new mysqli($host, $user, $pass, $db);
                if ($conn->connect_error) {
                    die('Connection failed: ' . $conn->connect_error);
                }

                $productId = $_POST['product_id']; // Get the associated product ID
                $stmt = $conn->prepare("INSERT INTO product_images (product_id, image_path) VALUES (?, ?)");
                $stmt->bind_param("is", $productId, $targetFilePath);

                if ($stmt->execute()) {
                    $statusMsg .= "File information added to database.<br>";
                } else {
                    $statusMsg .= "Failed to add file information to database: " . $stmt->error . "<br>";
                }
                $stmt->close();
                $conn->close();
            } else {
                $statusMsg .= "Sorry, there was an error uploading your file.<br>";
            }
        } else {
            $statusMsg .= "Sorry, only JPG, JPEG, PNG, GIF files are allowed.<br>";
        }
    }
} else {
    $statusMsg = 'Please select a file to upload.';
}

echo $statusMsg;
?>





