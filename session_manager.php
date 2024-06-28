<?php
// Include the custom session handler class
require_once 'session_handler.php';
require_once 'functions.php';


// Database credentials
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'marketplace';

// Create an instance of the custom session handler
$session_handler = new CustomSessionHandler($db_host, $db_user, $db_pass, $db_name);

// Set the custom session handler
session_set_save_handler($session_handler, true);

// Register the session handler
register_shutdown_function('session_write_close');

// Start the session if it hasn't been started already
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


?>





