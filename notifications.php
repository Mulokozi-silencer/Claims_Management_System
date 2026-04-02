<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle  = 'Notifications';
$breadcrumb = 'ClaimsPro <span>/</span> Notifications';
include __DIR__ . '/includes/layout.php';

$pdo = getDB();
$uid = $user['id'];

// Mark all read
if (isset($_GET['markall'])) {
    $pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([$uid]);
    header('Location: ' . APP_URL . '/notifications.php');
    exit;
}

$notifs = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 50");
$notifs->execute([$uid]);
$notifs = $notifs->fetchAll();

// Mark shown as read
$pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([$uid]);
?>

<div style="max-width:700px;">
  <div class="flex align-center gap-12 mb-16" style="justify-content:space-between;">
    <span class="text-muted text-sm"><?= count($notifs) ?> notification(s)</span>
    <a href="?markall=1" class="btn btn-ghost btn-sm">✔ Mark all read</a>
  </div>

  <?php if (empty($notifs)): ?>
    <div class="card"><div class="card-body">
      <div class="empty-state"><div class="empty-icon">🔔</div><div class="empty-title">No notifications</div><div class="empty-msg">You're all caught up!</div></div>
    </div></div>
  <?php else: ?>
    <div class="card fade-up">
      <?php foreach ($notifs as $n):
        $unreadStyle = !$n['is_read'] ? 'border-left:3px solid var(--gold);' : '';
      ?>
        <a href="<?= $n['claim_id'] ? APP_URL.'/claim-detail.php?id='.$n['claim_id'] : '#' ?>" style="display:block; text-decoration:none; padding:16px 20px; border-bottom:1px solid var(--border); transition:var(--transition); <?= $unreadStyle ?>" onmouseover="this.style.background='var(--bg-elevated)'" onmouseout="this.style.background=''">
          <div style="display:flex; gap:12px; align-items:flex-start;">
            <span style="font-size:1.5rem;">🔔</span>
            <div>
              <div style="font-weight:600; font-size:0.9rem; color:var(--text-primary);"><?= sanitize($n['title']) ?></div>
              <div style="font-size:0.82rem; color:var(--text-secondary); margin-top:3px;"><?= sanitize($n['message']) ?></div>
              <div style="font-size:0.72rem; color:var(--text-muted); margin-top:6px;">🕐 <?= formatDateTime($n['created_at']) ?></div>
            </div>
            <?php if (!$n['is_read']): ?>
              <span style="margin-left:auto; width:8px; height:8px; background:var(--gold); border-radius:50%; flex-shrink:0; margin-top:4px;"></span>
            <?php endif; ?>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/layout-end.php'; ?>
