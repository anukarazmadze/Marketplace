<?php
require_once '../session_manager.php';
require_once '../functions.php';

isAdmin();

// Database connection setup
$host = '127.0.0.1';
$db = 'marketplace';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Fetch all products
$stmt = $conn->prepare("SELECT id, product_name, about, currency, price, old_price, created_at FROM products");
$stmt->execute();
$stmt->bind_result($id, $productName, $about, $currency, $price, $oldPrice, $createdAt);

$products = [];
while ($stmt->fetch()) {
    $products[] = [
        'id' => $id,
        'product_name' => $productName,
        'about' => $about,
        'currency' => $currency,
        'price' => $price,
        'old_price' => $oldPrice,
        'created_at' => $createdAt
    ];
}
$stmt->close();

// Fetch product images
$productImages = [];
foreach ($products as $product) {
    $productId = $product['id'];
    $imageStmt = $conn->prepare("SELECT image_path FROM product_images WHERE product_id = ?");
    $imageStmt->bind_param("i", $productId);
    $imageStmt->execute();
    $imageResult = $imageStmt->get_result();
    while ($imageRow = $imageResult->fetch_assoc()) {
        $productImages[$productId][] = $imageRow['image_path'];
    }
    $imageStmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Welcome</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto py-8">
        <h1 class="text-2xl font-bold mb-6">Welcome, <?php echo htmlspecialchars($_SESSION['email']); ?></h1>
        <a href="/admin/AddProduct.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">Add Product</a>
        <div class="text-center mt-4">
            <a href="/admin/logout.php" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Log Out</a>
        </div>
        <div class="mt-8">
            <h2 class="text-xl font-semibold">All Products</h2>
            <div class="grid grid-cols-1 gap-6 mt-4">
                <?php foreach ($products as $product) : ?>
                    <div class="bg-white p-6 rounded shadow">
                        <h3 class="text-lg font-bold"><?php echo htmlspecialchars($product['product_name']); ?></h3>
                        <p><?php echo htmlspecialchars($product['about']); ?></p>
                        <p class="mt-2">Price: <?php echo htmlspecialchars($product['currency']); ?> <?php echo htmlspecialchars($product['price']); ?></p>
                        <p>Old Price: <?php echo htmlspecialchars($product['currency']); ?> <?php echo htmlspecialchars($product['old_price']); ?></p>
                        <p class="text-sm text-gray-500 mt-2">Added on: <?php echo htmlspecialchars($product['created_at']); ?></p>
                        <a href="/admin/ProductPage.php?id=<?php echo $product['id']; ?>" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-700 mt-4 inline-block">Edit</a>
                        
                        <!-- Product Images -->
                        <?php if (!empty($productImages[$product['id']])) : ?>
                            <div class="grid grid-cols-3 gap-4 mt-4">
                                <?php foreach ($productImages[$product['id']] as $imagePath) : ?>
                                    <div class="relative">
                                        <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Product Image" class="h-32 w-auto object-cover">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>




