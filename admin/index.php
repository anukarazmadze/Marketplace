<?php
require_once '../session_manager.php';


// Call the function to check if the user is already logged in
checkLoggedIn();

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Auth Forms</title>
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
    .hidden {
      display: none;
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
      <button id="toggleFormButton" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline mb-4">Switch to Log In</button>

      <!-- Registration Form -->
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

      <!-- Login Form -->
      <form id="loginForm" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 hidden" action="/admin/login.php" method="POST">
        <h2 class="mb-6 text-center text-2xl font-bold">Log In</h2>
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
            Log In
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    const toggleFormButton = document.getElementById('toggleFormButton');
    const registerForm = document.getElementById('registerForm');
    const loginForm = document.getElementById('loginForm');

    toggleFormButton.addEventListener('click', () => {
      if (registerForm.classList.contains('hidden')) {
        registerForm.classList.remove('hidden');
        loginForm.classList.add('hidden');
        toggleFormButton.textContent = 'Switch to Log In';
      } else {
        registerForm.classList.add('hidden');
        loginForm.classList.remove('hidden');
        toggleFormButton.textContent = 'Switch to Register';
      }
    });
  </script>
</body>
</html>

