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

// Handle form submission for adding a new product
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productName = isset($_POST['product_name']) ? htmlspecialchars($_POST['product_name']) : '';
    $about = isset($_POST['about']) ? htmlspecialchars($_POST['about']) : '';
    $currency = isset($_POST['currency']) ? htmlspecialchars($_POST['currency']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0.0;
    $oldPrice = isset($_POST['old_price']) ? floatval($_POST['old_price']) : 0.0;

    // Insert new product
    $stmt = $conn->prepare("INSERT INTO products (product_name, about, currency, price, old_price) VALUES (?, ?, ?, ?, ?)");
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    // Binding parameters
    $stmt->bind_param("sssdd", $productName, $about, $currency, $price, $oldPrice);

    $result = $stmt->execute();
    if ($result === false) {
        die('Execute failed: ' . htmlspecialchars($stmt->error));
    }

    $productId = $stmt->insert_id; // Get the newly inserted product ID
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
                        $statusMsg .= "The file {$fileName} has been uploaded and added to the database.<br>";
                    } else {
                        $statusMsg .= "Failed to add file information to the database: " . $stmt->error . "<br>";
                    }
                    $stmt->close();
                } else {
                    $statusMsg .= "Sorry, there was an error uploading your file {$fileName}.<br>";
                }
            } else {
                $statusMsg .= "Sorry, only JPG, JPEG, PNG, GIF files are allowed for upload.<br>";
            }
        }
        // Display status messages
        if (!empty($statusMsg)) {
            echo $statusMsg;
        }
    }

    header('Location: /admin/welcome.php'); // Redirect after successful add
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
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
        <h1 class="text-2xl font-bold mb-6">Add Product</h1>
        <form action="/admin/AddProduct.php" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow">
            <div class="mb-4">
                <label for="product_name" class="block text-gray-700">Product Name</label>
                <input type="text" id="product_name" name="product_name" class="w-full p-2 border border-gray-300 rounded mt-1" required>
            </div>
            <div class="mb-4">
                <label for="about" class="block text-gray-700">About</label>
                <textarea id="about" name="about" class="w-full p-2 border border-gray-300 rounded mt-1"></textarea>
            </div>
            <div class="mb-4">
                <label for="currency" class="block text-gray-700">Currency</label>
                <select id="currency" name="currency" class="w-full p-2 border border-gray-300 rounded mt-1" required>
                    <option value="USD">USD</option>
                    <option value="GEL">GEL</option>
                    <option value="EUR">EUR</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="price" class="block text-gray-700">Price</label>
                <input type="number" step="0.1" id="price" name="price" class="w-full p-2 border border-gray-300 rounded mt-1" required>
            </div>
            <div class="mb-4">
                <label for="old_price" class="block text-gray-700">Old Price</label>
                <input type="number" step="0.1" id="old_price" name="old_price" class="w-full p-2 border border-gray-300 rounded mt-1">
            </div>
            <div class="mt-4 grid grid-cols-3 gap-4">
                <label class="block text-gray-700">Upload Images</label>
                <input type="file" id="product_images" name="product_images[]" accept="image/*" multiple onchange="handleFileInputChange(event)">
                <div id="fileList" class="mt-2 text-gray-500"></div>
            </div>

            <div class="mt-4 flex justify-end">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700 mr-2">Add Product</button>
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