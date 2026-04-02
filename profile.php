<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle  = 'My Profile';
$breadcrumb = 'ClaimsPro <span>/</span> Profile';
include __DIR__ . '/includes/layout.php';

$pdo     = getDB();
$uid     = $user['id'];
$error   = '';
$success = '';

// Fetch full profile
$me = $pdo->prepare("SELECT * FROM users WHERE id=?");
$me->execute([$uid]);
$me = $me->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $name  = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        if (!$name) { $error = 'Name is required.'; }
        else {
            $pdo->prepare("UPDATE users SET full_name=?, phone=? WHERE id=?")->execute([$name,$phone,$uid]);
            $_SESSION['user_name'] = $name;
            $success = 'Profile updated.';
            $me['full_name'] = $name;
            $me['phone'] = $phone;
        }
    }

    if ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (!password_verify($current, $me['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new) < 6) {
            $error = 'New password must be at least 6 characters.';
        } elseif ($new !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            $hash = password_hash($new, PASSWORD_BCRYPT);
            $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hash,$uid]);
            $success = 'Password changed successfully.';
        }
    }
}

$initials = implode('', array_map(fn($w)=>strtoupper($w[0]), array_slice(explode(' ',$me['full_name']),0,2)));
$claimCount = (int)$pdo->prepare("SELECT COUNT(*) FROM claims WHERE claimant_id=?")->execute([$uid]) ? $pdo->prepare("SELECT COUNT(*) FROM claims WHERE claimant_id=?")->execute([$uid]) : 0;
$stmt = $pdo->prepare("SELECT COUNT(*) FROM claims WHERE claimant_id=?"); $stmt->execute([$uid]); $claimCount = (int)$stmt->fetchColumn();
?>

<?php if ($error):   ?><div class="alert alert-danger mb-16" data-auto-dismiss>⚠️ <?= sanitize($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success mb-16" data-auto-dismiss>✅ <?= sanitize($success) ?></div><?php endif; ?>

<div style="max-width:720px; display:grid; gap:24px;">

  <!-- Profile Card -->
  <div class="card fade-up">
    <div class="card-body" style="display:flex; align-items:center; gap:24px; flex-wrap:wrap;">
      <div style="width:72px; height:72px; border-radius:50%; background:linear-gradient(135deg, var(--accent), var(--purple)); display:flex; align-items:center; justify-content:center; font-size:1.5rem; font-weight:700; border:3px solid var(--border-gold); flex-shrink:0;">
        <?= $initials ?>
      </div>
      <div>
        <h3 style="font-family:var(--font-display); font-size:1.3rem; color:var(--text-primary);"><?= sanitize($me['full_name']) ?></h3>
        <div style="color:var(--gold); font-size:0.85rem; text-transform:capitalize;"><?= ucfirst($me['role']) ?></div>
        <div style="color:var(--text-muted); font-size:0.82rem; margin-top:4px;"><?= sanitize($me['email']) ?> · Member since <?= date('M Y', strtotime($me['created_at'])) ?></div>
      </div>
      <div style="margin-left:auto; text-align:right;">
        <div style="font-family:var(--font-display); font-size:2rem; color:var(--gold);"><?= $claimCount ?></div>
        <div class="text-xs text-muted" style="text-transform:uppercase; letter-spacing:0.08em;">Total Claims</div>
      </div>
    </div>
  </div>

  <!-- Edit Profile -->
  <div class="card fade-up">
    <div class="card-header"><div class="card-title"><span class="icon">✏️</span> Edit Profile</div></div>
    <div class="card-body">
      <form method="POST">
        <input type="hidden" name="action" value="update_profile">
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Full Name *</label>
            <input type="text" name="full_name" class="form-control" required value="<?= sanitize($me['full_name']) ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" value="<?= sanitize($me['phone']??'') ?>" placeholder="+1-555-0100">
          </div>
          <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" value="<?= sanitize($me['email']) ?>" disabled>
            <div class="form-hint">Contact admin to change email</div>
          </div>
          <div class="form-group">
            <label class="form-label">Role</label>
            <input type="text" class="form-control" value="<?= ucfirst($me['role']) ?>" disabled>
          </div>
        </div>
        <div class="mt-16">
          <button type="submit" class="btn btn-gold">💾 Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Change Password -->
  <div class="card fade-up">
    <div class="card-header"><div class="card-title"><span class="icon">🔒</span> Change Password</div></div>
    <div class="card-body">
      <form method="POST">
        <input type="hidden" name="action" value="change_password">
        <div class="form-grid">
          <div class="form-group form-full">
            <label class="form-label">Current Password *</label>
            <input type="password" name="current_password" class="form-control" required placeholder="••••••••">
          </div>
          <div class="form-group">
            <label class="form-label">New Password *</label>
            <input type="password" name="new_password" class="form-control" required minlength="6" placeholder="Min 6 characters">
          </div>
          <div class="form-group">
            <label class="form-label">Confirm New Password *</label>
            <input type="password" name="confirm_password" class="form-control" required placeholder="Repeat new password">
          </div>
        </div>
        <div class="mt-16">
          <button type="submit" class="btn btn-primary">🔒 Update Password</button>
        </div>
      </form>
    </div>
  </div>

</div>

<?php include __DIR__ . '/includes/layout-end.php'; ?>
