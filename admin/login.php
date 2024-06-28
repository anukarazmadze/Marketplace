<?php
require_once '../session_manager.php';
require_once '../functions.php';

// Check if user is logged in and has admin role
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') { 
  header('Location: /admin/welcome.php');
  exit();
}

$host = '127.0.0.1'; // Change as needed
$db = 'marketplace'; // Change as needed
$user = 'root'; // Change as needed
$pass = ''; // Change as needed

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
  die('Connection failed: ' . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email']);
  $password = trim($_POST['password']);

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $loginError = 'Invalid email format';
    $_SESSION['login_error'] = $loginError;
    header('Location: /admin/index.php');
    exit();
  } else {
    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
      $stmt->bind_result($id, $hashedPassword, $role);
      $stmt->fetch();

      if (password_verify($password, $hashedPassword)) {
        $_SESSION['user_id'] = $id;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = $role; // Store user role in session
        if ($role === 'admin') {
          header('Location: /admin/welcome.php'); // Redirect admin users to admin welcome page
        } else {
          header('Location: /welcome'); // Redirect regular users to regular welcome page
        }
        exit();
      } else {
        $loginError = 'Incorrect password';
      }
    } else {
      $loginError = 'No user found with that email';
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
  <title>Log In</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
  <style>
    body,
    html {
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
    <button id="toggleFormButton" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline mb-4" onclick="window.location.href='/admin/register.php'">Switch to Register</button>
      <form id="loginForm" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4" action="/admin/login.php" method="POST">
        <h2 class="mb-6 text-center text-2xl font-bold">Log In</h2>
        <?php if (isset($loginError)) echo "<p class='error'>{$loginError}</p>"; ?>
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
            Log In
          </button>
        </div>
      </form>
    </div>
  </div>
</body>

</html>