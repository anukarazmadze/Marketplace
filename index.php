<?php
require_once 'session_manager.php';
require_once 'functions.php';


// Parse the requested URL
$requestUri = $_SERVER['REQUEST_URI'];

// Remove query string from URL if exists
$requestUri = strtok($requestUri, '?');

// Remove leading and trailing slashes
$requestUri = trim($requestUri, '/');

// Route requests
switch ($requestUri) {
    case '':
    case 'home':
        require 'home.php'; // Default home page
        break;
    case 'login':
        require 'login.php';
        break;
    case 'welcome':
        require 'welcome.php';
        break;
    case 'logout':
        require 'logout.php';
        break;
    case 'register':
        require 'register.php';
        break;
    case 'basket':
        require 'basket.php';
        break;
    case 'add_to_basket':
        require 'add_to_basket.php';
        break;
    default:
        http_response_code(404);
        require '404.php'; // Page not found
        break;
}
