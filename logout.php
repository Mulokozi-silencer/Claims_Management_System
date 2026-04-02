<?php
// logout.php
require_once __DIR__ . '/includes/config.php';
initSession();
session_unset();
session_destroy();
header('Location: ' . APP_URL . '/index.php');
exit;
