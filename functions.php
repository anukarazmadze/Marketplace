<?php
// Check if the user is logged in, if so redirect to welcome page
function checkLoggedIn()
{
    if (isset($_SESSION['user_id'])) {
        header('Location: /admin/welcome.php');
        exit();
    }
}

// Ensure the user is logged in, if not redirect to login page
function ensureLoggedIn()
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: /admin/index.php');
        exit();
    }
}

function isAdmin()
{
    // Check if user is logged in and has admin role
    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        return true; // User is admin
    } else {
        // Redirect to non-admin page or handle accordingly
        header('Location: /404.php');
        exit();
    }
}