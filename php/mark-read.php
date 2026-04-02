<?php
// php/mark-read.php
require_once __DIR__ . '/../includes/config.php';
initSession();
if (isLoggedIn()) {
    $pdo = getDB();
    $pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([$_SESSION['user_id']]);
}
header('Location: ' . APP_URL . '/notifications.php');
exit;
