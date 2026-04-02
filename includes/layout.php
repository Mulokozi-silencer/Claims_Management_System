<?php
// includes/layout.php — shared header + sidebar
require_once __DIR__ . '/config.php';
requireLogin();
$user         = currentUser();
$unreadCount  = getUnreadCount($user['id']);
$notifications = getUnreadNotifications($user['id']);
$initials     = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $user['name']), 0, 2)));

$currentPage  = basename($_SERVER['PHP_SELF'], '.php');

function navItem(string $href, string $icon, string $label, string $page, string $current, ?int $badge = null): string {
    $active = ($page === $current) ? 'active' : '';
    $badgeHtml = $badge ? "<span class=\"nav-badge\">$badge</span>" : '';
    return "<a href=\"$href\" class=\"nav-item $active\"><span class=\"nav-icon\">$icon</span>$label$badgeHtml</a>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? $pageTitle . ' — ' : '' ?>ClaimsPro</title>
  <link rel="stylesheet" href="<?= APP_URL ?>/css/style.css">
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🛡️</text></svg>">
</head>
<body>
<div class="app-wrapper">

<!-- ── SIDEBAR ─────────────────────────────────────────────── -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <div class="logo-mark">
      <div class="logo-icon">🛡️</div>
      <div>
        <div class="logo-text">ClaimsPro</div>
        <div class="logo-sub">Management System</div>
      </div>
    </div>
  </div>

  <div class="sidebar-user">
    <div class="user-avatar"><?= $initials ?></div>
    <div class="user-info-text">
      <div class="user-name-sm"><?= sanitize($user['name']) ?></div>
      <div class="user-role-sm"><?= ucfirst($user['role']) ?></div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-title">Main</div>
    <?= navItem(APP_URL . '/dashboard.php', '📊', 'Dashboard', 'dashboard', $currentPage) ?>
    <?= navItem(APP_URL . '/claims.php', '📋', 'All Claims', 'claims', $currentPage) ?>
    <?= navItem(APP_URL . '/new-claim.php', '➕', 'New Claim', 'new-claim', $currentPage) ?>

    <?php if (isAdjuster()): ?>
    <div class="nav-section-title">Management</div>
    <?= navItem(APP_URL . '/claims.php?status=submitted', '📥', 'Incoming Claims', 'incoming', $currentPage) ?>
    <?= navItem(APP_URL . '/claims.php?status=under_review', '🔍', 'Under Review', 'review', $currentPage) ?>
    <?php endif; ?>

    <?php if (isAdmin()): ?>
    <div class="nav-section-title">Admin</div>
    <?= navItem(APP_URL . '/users.php', '👥', 'Users', 'users', $currentPage) ?>
    <?= navItem(APP_URL . '/reports.php', '📈', 'Reports', 'reports', $currentPage) ?>
    <?php endif; ?>

    <div class="nav-section-title">Account</div>
    <?= navItem(APP_URL . '/notifications.php', '🔔', 'Notifications', 'notifications', $currentPage, $unreadCount ?: null) ?>
    <?= navItem(APP_URL . '/profile.php', '👤', 'My Profile', 'profile', $currentPage) ?>
  </nav>

  <div class="sidebar-footer">
    <a href="<?= APP_URL ?>/logout.php" class="nav-item" style="color: var(--danger);">
      <span class="nav-icon">🚪</span>Sign Out
    </a>
  </div>
</aside>

<!-- ── HEADER ──────────────────────────────────────────────── -->
<header class="header">
  <div class="header-left">
    <div class="page-title"><?= $pageTitle ?? 'Dashboard' ?></div>
    <div class="breadcrumb"><?= $breadcrumb ?? 'ClaimsPro <span>/</span> ' . ($pageTitle ?? 'Dashboard') ?></div>
  </div>

  <div class="header-actions" style="position:relative;">
    <!-- Notifications -->
    <div style="position:relative;">
      <button class="header-btn" id="notifBtn" title="Notifications">
        🔔
        <?php if ($unreadCount > 0): ?>
          <span class="notif-count"><?= $unreadCount > 9 ? '9+' : $unreadCount ?></span>
        <?php endif; ?>
      </button>

      <div class="notif-panel" id="notifPanel">
        <div class="notif-panel-header">
          <span class="notif-panel-title">Notifications</span>
          <?php if ($unreadCount): ?>
            <a href="<?= APP_URL ?>/php/mark-read.php" class="text-sm text-gold">Mark all read</a>
          <?php endif; ?>
        </div>
        <div class="notif-list">
          <?php if (empty($notifications)): ?>
            <div style="padding:20px; text-align:center; color:var(--text-muted); font-size:0.85rem;">No new notifications</div>
          <?php else: ?>
            <?php foreach ($notifications as $n): ?>
              <a href="<?= $n['claim_id'] ? APP_URL . '/claim-detail.php?id=' . $n['claim_id'] : '#' ?>" class="notif-item unread" style="display:block; text-decoration:none;">
                <div class="notif-item-title"><?= sanitize($n['title']) ?></div>
                <div class="notif-item-msg"><?= sanitize($n['message']) ?></div>
                <div class="notif-time"><?= formatDateTime($n['created_at']) ?></div>
              </a>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <div class="notif-panel-footer">
          <a href="<?= APP_URL ?>/notifications.php">View all notifications</a>
        </div>
      </div>
    </div>

    <a href="<?= APP_URL ?>/new-claim.php" class="btn btn-gold btn-sm">
      ➕ New Claim
    </a>

    <a href="<?= APP_URL ?>/profile.php" class="header-btn" title="Profile">👤</a>
  </div>
</header>

<div class="main-content">
<div class="page-content">
