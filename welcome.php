<?php
require_once 'session_manager.php';
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /');
    exit();
}

// Initialize basket session if not already set
if (!isset($_SESSION['basket'])) {
    $_SESSION['basket'] = [];
}

$host = '127.0.0.1'; // Change as needed
$db = 'marketplace'; // Change as needed
$user = 'root'; // Change as needed
$pass = ''; // Change as needed

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Fetch all products
$stmt = $conn->prepare("SELECT id, product_name, about, currency, price FROM products");
$stmt->execute();
$stmt->bind_result($id, $productName, $about, $currency, $price);

$products = [];
while ($stmt->fetch()) {
    $products[] = [
        'id' => $id,
        'product_name' => $productName,
        'about' => $about,
        'currency' => $currency,
        'price' => $price
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
    <title>Welcome</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <style>
        .carousel-container {
            position: relative;
        }

        .carousel-slide {
            display: none;
        }

        .carousel-slide.active {
            display: block;
        }

        .carousel-button {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: black;
            color: white;
            padding: 0.5rem;
            cursor: pointer;
            z-index: 10;
        }

        .product-image {
            height: 350px;
            width: 100%;
            object-fit: cover;
        }

        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            background-color: black;
            padding: 10px;
            border-radius: 50%;
        }

        .product-card {
            position: relative;
            z-index: 1;
        }

        .product-card .carousel-control-prev,
        .product-card .carousel-control-next {
            z-index: 12000 !important;
        }

        .product-card .group-hover\:opacity-75 {
            z-index: 2;
        }

        .add {
            position: relative;
            z-index: 2000000 !important;
        }

        .cart-icon {
            position: fixed;
            top: 100px;
            right: 20px;
            background-color: #4CAF50;
            color: white;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            z-index: 10000;
        }

        .cart-icon:hover {
            background-color: #45a049;
        }

        .notification {
            position: fixed;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            background-color: lightgreen;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            display: none;
            z-index: 10001;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="bg-white">
        <div class="flex justify-between items-center p-4">
            <h1 class="text-xl font-bold">Marketplace</h1>
            <form action="/logout" method="POST">
                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Logout
                </button>
            </form>
        </div>

        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">Products</h2>
            <div class="mt-6 grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-4 xl:gap-x-8">
                <?php foreach ($products as $product) : ?>
                    <div class="group relative product-card bg-white p-4 rounded shadow">
                        <div class="aspect-h-1 aspect-w-1 w-full overflow-hidden rounded-md bg-gray-200 lg:aspect-none group-hover:opacity-75 lg:h-80">
                            <?php if (!empty($productImages[$product['id']])) : ?>
                                <div id="carousel<?php echo $product['id']; ?>" class="carousel slide" data-ride="carousel">
                                    <div class="carousel-inner">
                                        <?php foreach ($productImages[$product['id']] as $index => $imagePath) : ?>
                                            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                                <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Product Image" class="d-block w-100 product-image">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php if (count($productImages[$product['id']]) > 1) : ?>
                                        <a class="carousel-control-prev" href="#carousel<?php echo $product['id']; ?>" role="button" data-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="sr-only">Previous</span>
                                        </a>
                                        <a class="carousel-control-next" href="#carousel<?php echo $product['id']; ?>" role="button" data-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="sr-only">Next</span>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="add mt-4 flex justify-between">
                            <div>
                                <h3 class="text-sm text-gray-700">
                                    <span aria-hidden="true" class="absolute inset-0"></span>
                                    <p class="text-lg font-bold"><?php echo htmlspecialchars($product['product_name']); ?></p>
                                </h3>
                                <p class="mt-1 text-sm text-gray-500"><?php echo htmlspecialchars($product['about']); ?></p>
                            </div>
                            <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($product['currency']) . ' ' . htmlspecialchars($product['price']); ?></p>
                        </div>
                        <a href="/add_to_basket?product_id=<?php echo $product['id']; ?>&product_name=<?php echo urlencode($product['product_name']); ?>&price=<?php echo urlencode($product['price']); ?>" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-700 mt-4 inline-block">Add</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="cart-icon" onclick="window.location.href='/basket'">
        🛒
    </div>
    <div class="notification" id="notification">
        <?php if (isset($_GET['product_added'])) echo 'The product has been added to the basket.'; ?>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const notification = document.getElementById('notification');
            if (notification.innerText) {
                notification.style.display = 'block';
                setTimeout(() => {
                    notification.style.display = 'none';
                }, 3000);
            }
        });
    </script>
</body>

</html>