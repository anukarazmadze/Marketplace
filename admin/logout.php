<?php
require_once '../session_manager.php';

// Destroy the session
session_unset();
session_destroy();

header('Location: /admin/index.php');
exit();
?>
