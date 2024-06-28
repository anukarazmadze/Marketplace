<?php
require_once 'session_manager.php';

// Redirect to welcome page if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: /welcome');
    exit();
}

$registerError = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validate inputs 
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $registerError = 'Invalid email format';
    } elseif (strlen($password) < 7 || strlen($password) > 25) {
        $registerError = 'Password must be between 7 and 25 characters';
    } else {
        // Database connection
        $host = '127.0.0.1'; // Change as needed
        $db = 'marketplace'; // Change as needed
        $user = 'root'; // Change as needed
        $pass = ''; // Change as needed

        $conn = new mysqli($host, $user, $pass, $db);
        if ($conn->connect_error) {
            die('Connection failed: ' . $conn->connect_error);
        }

        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($conn->error));
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            $stmt->close();

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $role = 'user'; // Set role as 'user' for regular registration

            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
            if ($stmt === false) {
                die('Prepare failed: ' . htmlspecialchars($conn->error));
            }
            $stmt->bind_param("sss", $email, $hashedPassword, $role);
            if ($stmt->execute()) {
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $role; // Set the user's role in session
                $stmt->close();
                $conn->close();
                header('Location: /welcome');
                exit();
            } else {
                $registerError = 'Registration failed';
            }
        } else {
            $registerError = 'Email is already taken';
        }

        $stmt->close();
        $conn->close();
    }
}
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
            <form id="registerForm" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4" action="/register" method="POST">
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
                <div class="mt-4 flex justify-between">
                  <p>Already have an account?
                  <a href="/login" class="text-blue-500 hover:text-blue-700">Login</a>
                  </p>
                    <a href="/home" class="text-blue-500 hover:text-blue-700">Back to Main Page</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

