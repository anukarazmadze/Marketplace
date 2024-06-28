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

$productId = $_GET['id'] ?? null;

if ($productId !== null) {
    // Step 1: Fetch images associated with the product
    $stmtImages = $conn->prepare("SELECT id, image_path FROM product_images WHERE product_id = ?");
    $stmtImages->bind_param("i", $productId);
    $stmtImages->execute();
    $stmtImages->bind_result($imageId, $imagePath);

    // Array to store image IDs and paths
    $imageIds = [];
    while ($stmtImages->fetch()) {
        $imageIds[] = ['id' => $imageId, 'path' => $imagePath];
    }
    $stmtImages->close();

    // Step 2: Delete each image file and record
    foreach ($imageIds as $image) {
        $imageId = $image['id'];
        $imagePath = $image['path'];

        // Delete the image file from the directory
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }

        // Delete the image record from the database
        $stmtDeleteImage = $conn->prepare("DELETE FROM product_images WHERE id = ?");
        $stmtDeleteImage->bind_param("i", $imageId);
        $stmtDeleteImage->execute();
        $stmtDeleteImage->close();
    }

    // Step 3: Delete the product from the products table
    $stmtProduct = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmtProduct->bind_param("i", $productId);
    $resultProduct = $stmtProduct->execute();
    $stmtProduct->close();

    if ($resultProduct) {
        // Redirect to a success page or back to product list
        header('Location: /admin/welcome.php');
        exit();
    } else {
        die('Failed to delete product.');
    }
} else {
    die('Product ID not provided.');
}

$conn->close();
?>





