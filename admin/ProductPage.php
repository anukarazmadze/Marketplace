<?php
require_once '../session_manager.php';
require_once '../functions.php';

isAdmin();

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

// Fetch product details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    // Edit product logic
    $productId = $_POST['product_id'];
    $productName = $_POST['product_name'] ?? null;
    $about = $_POST['about'] ?? null;
    $currency = $_POST['currency'] ?? null;
    $price = $_POST['price'] ?? null;
    $oldPrice = $_POST['old_price'] ?? null;

    $stmt = $conn->prepare("UPDATE products SET product_name = ?, about = ?, currency = ?, price = ?, old_price = ? WHERE id = ?");
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    // Binding parameters
    $stmt->bind_param("sssddi", $productName, $about, $currency, $price, $oldPrice, $productId);

    $result = $stmt->execute();
    if ($result === false) {
        die('Execute failed: ' . htmlspecialchars($stmt->error));
    }

    $stmt->close();

    // Handle image upload
    if (!empty($_FILES['product_images']['name'][0])) {
        $targetDir = "../uploads/";
        $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
        $statusMsg = '';

        foreach ($_FILES['product_images']['name'] as $key => $val) {
            $fileName = basename($_FILES['product_images']['name'][$key]);
            $targetFilePath = $targetDir . $fileName;
            $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

            if (in_array($fileType, $allowTypes)) {
                if (move_uploaded_file($_FILES['product_images']['tmp_name'][$key], $targetFilePath)) {
                    // Insert file path into the database
                    $stmt = $conn->prepare("INSERT INTO product_images (product_id, image_path) VALUES (?, ?)");
                    $stmt->bind_param("is", $productId, $targetFilePath);

                    if ($stmt->execute()) {
                        $statusMsg .= "The file {$fileName} has been uploaded and added to database.<br>";
                    } else {
                        $statusMsg .= "Failed to add file information to database: " . $stmt->error . "<br>";
                    }
                    $stmt->close();
                } else {
                    $statusMsg .= "Sorry, there was an error uploading your file {$fileName}.<br>";
                }
            } else {
                $statusMsg .= "Sorry, only JPG, JPEG, PNG, GIF files are allowed for upload.<br>";
            }
        }
        echo $statusMsg;
    }

    header('Location: /admin/welcome.php'); // Redirect after successful edit
    exit();
} elseif ($productId !== null) {
    // Fetch product details for editing
    $stmt = $conn->prepare("SELECT product_name, about, currency, price, old_price FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $stmt->bind_result($productName, $about, $currency, $price, $oldPrice);
    $stmt->fetch();
    $stmt->close();

    // Fetch uploaded images for the product
    $stmtImages = $conn->prepare("SELECT id, image_path FROM product_images WHERE product_id = ?");
    $stmtImages->bind_param("i", $productId);
    $stmtImages->execute();
    $stmtImages->bind_result($imageId, $imagePath);

    $images = [];
    while ($stmtImages->fetch()) {
        $images[] = [
            'id' => $imageId,
            'path' => $imagePath
        ];
    }
    $stmtImages->close();
} else {
    // Redirect if no product ID is provided
    header('Location: /admin/welcome.php');
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">

    <style>
        .image-placeholder {
            position: relative;
            height: 200px;
            /* Adjust height as needed */
            border: 2px dashed #ccc;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .image-placeholder input[type="file"] {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 0;
            cursor: pointer;
        }

        .image-placeholder img {
            max-width: 100%;
            max-height: 100%;
            display: block;
            object-fit: cover;
        }

        .delete-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background-color: rgba(255, 255, 255, 0.7);
            padding: 2px 6px;
            border-radius: 50%;
            cursor: pointer;
            text-decoration: none;
            color: black;
        }

        .delete-image:hover {
            background-color: rgba(255, 0, 0, 0.7);
            color: white;
        }
    </style>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto py-8">
        <div class="mt-4 flex justify-between" style="align-items: center;">
            <h1 class="text-2xl font-bold mb-6">Edit Product</h1>
            <a href="/admin/delete_product.php?id=<?php echo htmlspecialchars($productId); ?>" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-700 ml-2">Delete Product</a>
        </div>
        <form action="/admin/ProductPage.php" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow">
            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($productId); ?>">
            <div class="mb-4">
                <label for="product_name" class="block text-gray-700">Product Name</label>
                <input type="text" id="product_name" name="product_name" class="w-full p-2 border border-gray-300 rounded mt-1" value="<?php echo htmlspecialchars($productName); ?>" required>
            </div>
            <div class="mb-4">
                <label for="about" class="block text-gray-700">About</label>
                <textarea id="about" name="about" class="w-full p-2 border border-gray-300 rounded mt-1"><?php echo htmlspecialchars($about); ?></textarea>
            </div>
            <div class="mb-4">
                <label for="currency" class="block text-gray-700">Currency</label>
                <select id="currency" name="currency" class="w-full p-2 border border-gray-300 rounded mt-1" required>
                    <option value="USD" <?php echo ($currency === 'USD') ? 'selected' : ''; ?>>USD</option>
                    <option value="GEL" <?php echo ($currency === 'GEL') ? 'selected' : ''; ?>>GEL</option>
                    <option value="EUR" <?php echo ($currency === 'EUR') ? 'selected' : ''; ?>>EUR</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="price" class="block text-gray-700">Price</label>
                <input type="number" step="0.1" id="price" name="price" class="w-full p-2 border border-gray-300 rounded mt-1" value="<?php echo htmlspecialchars($price); ?>" required>
            </div>
            <div class="mb-4">
                <label for="old_price" class="block text-gray-700">Old Price</label>
                <input type="number" step="0.1" id="old_price" name="old_price" class="w-full p-2 border border-gray-300 rounded mt-1" value="<?php echo htmlspecialchars($oldPrice); ?>">
            </div>

            <div class="mt-4 grid grid-cols-3 gap-4">
                <?php foreach ($images as $image) : ?>
                    <div class="relative image-placeholder">
                        <?php if (!empty($image['path']) && file_exists($image['path'])) : ?>
                            <img src="<?php echo htmlspecialchars($image['path']); ?>" alt="Product Image" class="h-full w-full object-cover">
                            <a href="/admin/delete_image.php?id=<?php echo $image['id']; ?>&product_id=<?php echo $productId; ?>" class="delete-image">X</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-4">
                <label class="block text-gray-700">Upload Images</label>
                <input type="file" id="product_images" name="product_images[]" accept="image/*" multiple onchange="handleFileInputChange(event)">
                <div id="fileList" class="mt-2 text-gray-500"></div>
            </div>

            <div class="mt-4 flex justify-end">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700 mr-2">Save Changes</button>
                <a href="/admin/welcome.php" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        function handleFileInputChange(event) {
            const input = event.target;
            const fileList = document.getElementById('fileList');
            fileList.innerHTML = ''; // Clear previous file names

            if (input.files && input.files.length > 0) {
                for (let i = 0; i < input.files.length; i++) {
                    const fileName = input.files[i].name;
                    fileList.innerHTML += `<div>${fileName}</div>`;
                }
            }
        }
    </script>
</body>

</html>
