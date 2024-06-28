<?php
require_once 'session_manager.php';
require_once 'functions.php';

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


// Function to fetch the main product image
function getMainImage($productId, $conn) {
    $stmt = $conn->prepare("SELECT image_path FROM product_images WHERE product_id = ? LIMIT 1");
    $imagePath = '';
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $stmt->bind_result($imagePath);
    $stmt->fetch();
    $stmt->close();
    return $imagePath;
}

// Function to display basket items with main product photo
function displayBasket($conn) {
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];

        // Fetch user's basket items from database
        $stmt = $conn->prepare("SELECT b.product_id, b.quantity, p.product_name, p.price FROM user_baskets b JOIN products p ON b.product_id = p.id WHERE b.user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo '<div class="grid grid-cols-1 gap-4">';
            while ($row = $result->fetch_assoc()) {
                $productId = $row['product_id'];
                $item = [
                    'name' => $row['product_name'],
                    'price' => $row['price'],
                    'quantity' => $row['quantity']
                ];
                $mainImage = getMainImage($productId, $conn);
                echo '<div class="flex items-center border p-4 rounded-md relative">';
                echo '<div class="absolute top-0 right-0 cursor-pointer" onclick="deleteProduct(' . $productId . ')"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></div>';
                echo '<div class="mr-4">';
                echo '<img src="' . htmlspecialchars($mainImage) . '" alt="Product Image" class="w-24 h-24 object-cover">';
                echo '</div>';
                echo '<div class="flex-1">';
                echo '<h3 class="text-lg font-semibold">' . htmlspecialchars($item['name']) . '</h3>';
                echo '<p class="text-gray-600">$<span class="price">' . number_format($item['price'], 2) . '</span></p>';
                echo '</div>';
                echo '<div class="ml-4">';
                echo '<input type="number" class="quantity w-16 p-2 border rounded-md" value="' . $item['quantity'] . '" min="1" data-product-id="' . $productId . '">';
                echo '</div>';
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo '<p>Your basket is empty.</p>';
        }

        $stmt->close();
    } else {
        echo '<p>Your basket is empty.</p>';
    }
}

// Function to calculate total price of items in basket
function calculateTotal($conn) {
    $total = 0.0;

    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];

        // Fetch total price from database
        $stmt = $conn->prepare("SELECT SUM(b.quantity * p.price) AS total FROM user_baskets b JOIN products p ON b.product_id = p.id WHERE b.user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $total = $row['total'];
        }

        $stmt->close();
    }

    return $total !== null ? $total : 0.0; // Handle null case by returning 0.0
}

/// Function to add/update basket in database
function addToBasket($userId, $productId, $quantity, $conn) {
    // Check if the product already exists in the user's basket
    $stmt = $conn->prepare("SELECT id, quantity FROM user_baskets WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Product already exists, update quantity
        $row = $result->fetch_assoc();
        $currentQuantity = $row['quantity'];
        $newQuantity = $currentQuantity + $quantity;

        $stmtUpdate = $conn->prepare("UPDATE user_baskets SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmtUpdate->bind_param("iii", $newQuantity, $userId, $productId);
        $stmtUpdate->execute();
        $stmtUpdate->close();
    } else {
        // Product does not exist, insert new record
        $stmtInsert = $conn->prepare("INSERT INTO user_baskets (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmtInsert->bind_param("iii", $userId, $productId, $quantity);
        $stmtInsert->execute();
        $stmtInsert->close();
    }

    $stmt->close();
}


// Function to handle AJAX requests to update basket quantities or delete products
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_quantity') {
        $productId = $_POST['product_id'];
        $quantity = intval($_POST['quantity']);

        if (isset($_SESSION['basket'][$productId])) {
            $_SESSION['basket'][$productId]['quantity'] = $quantity;
        }

        if (isset($_SESSION['user_id'])) {
            addToBasket($_SESSION['user_id'], $productId, $quantity, $conn);
        }

        $total = calculateTotal($conn); // Pass $conn argument to calculateTotal
        echo json_encode(['total' => $total]);
        exit();
    } elseif ($_POST['action'] === 'clear_basket') {
        unset($_SESSION['basket']);

        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];

            // Clear user's basket from database
            $stmt = $conn->prepare("DELETE FROM user_baskets WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->close();
        }

        header("Location: /basket");
        exit();
    } elseif ($_POST['action'] === 'delete_product') {
        $productId = $_POST['product_id'];

        if (isset($_SESSION['basket'][$productId])) {
            unset($_SESSION['basket'][$productId]);
        }

        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];

            // Delete product from user's basket in database
            $stmt = $conn->prepare("DELETE FROM user_baskets WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $userId, $productId);
            $stmt->execute();
            $stmt->close();
        }

        $total = calculateTotal($conn); // Pass $conn argument to calculateTotal
        echo json_encode(['total' => $total]);
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Basket</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const quantityInputs = document.querySelectorAll('.quantity');
            quantityInputs.forEach(input => {
                input.addEventListener('change', function () {
                    const productId = this.dataset.productId;
                    const quantity = this.value;

                    fetch('/basket', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'action=update_quantity&product_id=' + productId + '&quantity=' + quantity
                    })
                        .then(response => response.json())
                        .then(data => {
                            document.getElementById('total').textContent = '$' + data.total.toFixed(2);
                        });
                });
            });
        });

        function deleteProduct(productId) {
            if (confirm('Are you sure you want to remove this product from your basket?')) {
                fetch('/basket', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'action=delete_product&product_id=' + productId
                })
                    .then(response => response.json())
                    .then(data => {
                        window.location.reload(); // Example: reload the page after deletion
                    });
            }
        }
    </script>
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto py-8">
        <h1 class="text-2xl font-bold mb-6">Shopping Basket</h1>
        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-xl font-bold mb-4">Products in Basket</h2>
            <?php displayBasket($conn); ?>
            <h3 class="text-lg font-bold mt-6">Total: $<span id="total"><?php echo number_format(calculateTotal($conn), 2); ?></span></h3>
            <form action="#" method="POST" class="mt-4">
                <input type="hidden" name="action" value="checkout">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">Proceed to Checkout</button>
            </form>
            <form action="/basket" method="POST" class="mt-2">
                <input type="hidden" name="action" value="clear_basket">
                <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-700">Clear Basket</button>
            </form>
            <div class="mt-4 flex justify-end">
                <a href="/welcome" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">Back to Main Page</a>
            </div>
        </div>
    </div>
</body>

</html>

<?php
$conn->close(); // Close database connection
?>

