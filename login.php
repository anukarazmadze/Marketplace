<?php
require_once 'session_manager.php';

// Redirect to welcome page if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: /welcome');
    exit();
}

$loginError = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email']);
  $password = $_POST['password'];

  // Validate inputs 
  if (empty($email) || empty($password)) {
      $loginError = 'Email and password are required';
  } else {
      // Database connection
      $host = '127.0.0.1'; 
      $db = 'marketplace'; 
      $user = 'root'; 
      $pass = ''; 

      $conn = new mysqli($host, $user, $pass, $db);
      if ($conn->connect_error) {
          die('Connection failed: ' . $conn->connect_error);
      }

      // Prepare SQL statement
      $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email = ?");
      if ($stmt === false) {
          die('Prepare failed: ' . htmlspecialchars($conn->error));
      }

      // Bind parameters and execute query
      $stmt->bind_param("s", $email);
      $stmt->execute();
      $stmt->bind_result($userId, $hashedPassword, $role); // Fetch role along with user_id and password

      // Fetch the user record
      if ($stmt->fetch()) {
          // Verify password
          if (password_verify($password, $hashedPassword)) {
              $_SESSION['user_id'] = $userId;
              $_SESSION['email'] = $email;
              $_SESSION['role'] = $role; // Set the user's role in the session

              $stmt->close();
              $conn->close();
              header('Location: /welcome');
              exit();
          } else {
              $loginError = 'Invalid email or password';
          }
      } else {
          $loginError = 'Invalid email or password';
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
    <title>Login</title>
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
            <form id="loginForm" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4" action="/login" method="POST">
                <h2 class="mb-6 text-center text-2xl font-bold">Login</h2>
                <?php if (!empty($loginError)) echo "<p class='error'>{$loginError}</p>"; ?>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="loginEmail">
                        Email
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="loginEmail" name="email" type="email" placeholder="Email" required>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="loginPassword">
                        Password
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" id="loginPassword" name="password" type="password" placeholder="Password" required minlength="7" maxlength="25">
                </div>
                <div class="flex items-center justify-between">
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                        Login
                    </button>
                </div>
                <div class="mt-4 flex justify-between">
                  <p>Don't have an account?  <a href="/register" class="text-blue-500 hover:text-blue-700">Register</a>
                  </p>
                    <a href="/home" class="text-blue-500 hover:text-blue-700">Back to Main Page</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

