<?php
require_once 'session_manager.php';
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /');
    exit();
}

$host = '127.0.0.1'; 
$db = 'marketplace'; 
$user = 'root'; 
$pass = ''; 

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['product_id'], $_GET['product_name'], $_GET['price'])) {
    $productId = intval($_GET['product_id']);
    $productName = urldecode($_GET['product_name']);
    $productPrice = floatval($_GET['price']);

    // Add or update product in session basket
    if (!isset($_SESSION['basket'])) {
        $_SESSION['basket'] = [];
    }

    if (isset($_SESSION['basket'][$productId])) {
        $_SESSION['basket'][$productId]['quantity'] += 1;
    } else {
        $_SESSION['basket'][$productId] = [
            'name' => $productName,
            'price' => $productPrice,
            'quantity' => 1
        ];
    }

    // Insert into user_baskets table
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];

        // Check if the product is already in the basket and update the quantity
        $stmt = $conn->prepare("SELECT quantity FROM user_baskets WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $userId, $productId);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Update the quantity
            $stmt->bind_result($quantity);
            $stmt->fetch();
            $newQuantity = $quantity + 1;
            $stmt->close();

            $updateStmt = $conn->prepare("UPDATE user_baskets SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $updateStmt->bind_param("iii", $newQuantity, $userId, $productId);
            $updateStmt->execute();
            $updateStmt->close();
        } else {
            // Insert new entry
            $stmt->close();
            $insertStmt = $conn->prepare("INSERT INTO user_baskets (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $insertStmt->bind_param("iii", $userId, $productId, $_SESSION['basket'][$productId]['quantity']);
            $insertStmt->execute();
            $insertStmt->close();
        }
    }

    $conn->close();

    // Redirect back to the welcome page with a success message
    header('Location: /welcome?product_added=1');
    exit();
}

$conn->close();

// Redirect back to welcome page if the request is not valid
header('Location: /welcome');
exit();
?>

