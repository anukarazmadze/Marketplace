<?php
require_once '../session_manager.php';
require_once '../functions.php';

ensureLoggedIn();

// Database connection
$host = '127.0.0.1';
$db = 'marketplace';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$imageId = $_GET['id'] ?? null;
$productId = $_GET['product_id'] ?? null;

if ($imageId && $productId) {
    // Fetch the image path
    $stmt = $conn->prepare("SELECT image_path FROM product_images WHERE id = ? AND product_id = ?");
    $stmt->bind_param("ii", $imageId, $productId);
    $stmt->execute();
    $stmt->bind_result($imagePath);
    $stmt->fetch();
    $stmt->close();

    if ($imagePath) {
        // Delete the image file from the directory
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }

        // Delete the image record from the database
        $stmt = $conn->prepare("DELETE FROM product_images WHERE id = ? AND product_id = ?");
        $stmt->bind_param("ii", $imageId, $productId);
        $stmt->execute();
        $stmt->close();
    }
}

$conn->close();
header('Location: /admin/ProductPage.php?id=' . $productId);
exit();
?>


