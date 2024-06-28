<?php
require_once '../session_manager.php';
require_once '../functions.php';

// Check if user is logged in and has admin role
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') { 
  header('Location: /admin/welcome.php');
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

$registerError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $registerError = 'Invalid email format';
    } elseif (strlen($password) < 7 || strlen($password) > 25) {
        $registerError = 'Password must be between 7 and 25 characters';
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($conn->error));
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            $stmt->close();

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $role = 'admin'; // Admin role

            $stmt = $conn->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
            if ($stmt === false) {
                die('Prepare failed: ' . htmlspecialchars($conn->error));
            }
            $stmt->bind_param("sss", $email, $hashedPassword, $role);
            if ($stmt->execute()) {
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $role;
                header('Location: /admin/welcome.php');
                exit();
            } else {
                $registerError = 'Registration failed';
            }
        } else {
            $registerError = 'Email is already taken';
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <style>
        body, html {
            height: 100%;
        }
        .centered-form {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
        }
        .error {
            color: red;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="centered-form">
        <div class="w-full max-w-lg">
        <button id="toggleFormButton" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline mb-4" onclick="window.location.href='/admin/login.php'">Switch to Log In</button>
            <form id="registerForm" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4" action="/admin/register.php" method="POST">
                <h2 class="mb-6 text-center text-2xl font-bold">Register</h2>
                <?php if (!empty($registerError)) echo "<p class='error'>{$registerError}</p>"; ?>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="registerEmail">
                        Email
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="registerEmail" name="email" type="email" placeholder="Email" required>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="registerPassword">
                        Password
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" id="registerPassword" name="password" type="password" placeholder="Password" required minlength="7" maxlength="25">
                </div>
                <div class="flex items-center justify-between">
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                        Register
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

