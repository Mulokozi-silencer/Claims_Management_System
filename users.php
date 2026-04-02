<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle  = 'User Management';
$breadcrumb = 'ClaimsPro <span>/</span> Admin <span>/</span> Users';
include __DIR__ . '/includes/layout.php';
requireRole('admin');

$pdo     = getDB();
$error   = '';
$success = '';

// ── Handle Actions ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name  = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';
        $role  = $_POST['role'] ?? 'claimant';
        $phone = trim($_POST['phone'] ?? '');

        if (!$name || !$email || !$pass) {
            $error = 'Name, email, and password are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address.';
        } else {
            $existing = $pdo->prepare("SELECT id FROM users WHERE email=?");
            $existing->execute([$email]);
            if ($existing->fetch()) {
                $error = 'Email already exists.';
            } else {
                $hash = password_hash($pass, PASSWORD_BCRYPT);
                $pdo->prepare("INSERT INTO users (full_name,email,password,role,phone) VALUES (?,?,?,?,?)")->execute([$name,$email,$hash,$role,$phone]);
                $success = "User '$name' created successfully.";
            }
        }
    }

    if ($action === 'toggle_status') {
        $uid2 = (int)$_POST['user_id'];
        if ($uid2 !== $user['id']) { // prevent self-deactivation
            $pdo->prepare("UPDATE users SET status = IF(status='active','inactive','active') WHERE id=?")->execute([$uid2]);
            $success = 'User status updated.';
        }
    }

    if ($action === 'change_role') {
        $uid2  = (int)$_POST['user_id'];
        $newRole = $_POST['new_role'] ?? '';
        if ($uid2 !== $user['id'] && in_array($newRole, ['admin','adjuster','claimant'])) {
            $pdo->prepare("UPDATE users SET role=? WHERE id=?")->execute([$newRole,$uid2]);
            $success = 'Role updated.';
        }
    }
}

// Fetch users
$search = trim($_GET['search'] ?? '');
$roleFilter = $_GET['role'] ?? '';
$where = [];
$params = [];
if ($search) { $where[] = '(full_name LIKE ? OR email LIKE ?)'; $params = array_merge($params, ["%$search%","%$search%"]); }
if ($roleFilter) { $where[] = 'role=?'; $params[] = $roleFilter; }
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$users = $pdo->prepare("SELECT u.*, (SELECT COUNT(*) FROM claims c WHERE c.claimant_id=u.id) AS claim_count FROM users u $whereSQL ORDER BY u.created_at DESC");
$users->execute($params);
$users = $users->fetchAll();
?>

<?php if ($error):   ?><div class="alert alert-danger mb-16" data-auto-dismiss>⚠️ <?= sanitize($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success mb-16" data-auto-dismiss>✅ <?= sanitize($success) ?></div><?php endif; ?>

<div style="display:flex; gap:16px; margin-bottom:24px; flex-wrap:wrap; align-items:flex-start;">

  <!-- User List -->
  <div class="card fade-up" style="flex:1; min-width:0;">
    <div class="filter-bar">
      <form method="GET" style="display:contents;">
        <div class="search-input-wrap">
          <span class="search-icon">🔍</span>
          <input type="text" name="search" class="form-control search-input" placeholder="Search users…" value="<?= sanitize($search) ?>">
        </div>
        <select name="role" class="filter-select" onchange="this.form.submit()">
          <option value="">All Roles</option>
          <option value="admin" <?= $roleFilter==='admin'?'selected':''?>>Admin</option>
          <option value="adjuster" <?= $roleFilter==='adjuster'?'selected':''?>>Adjuster</option>
          <option value="claimant" <?= $roleFilter==='claimant'?'selected':''?>>Claimant</option>
        </select>
        <button type="submit" class="btn btn-gold btn-sm">Filter</button>
        <a href="<?= APP_URL ?>/users.php" class="btn btn-ghost btn-sm">Reset</a>
        <button type="button" class="btn btn-primary btn-sm" onclick="openModal('createUserModal')">➕ Add User</button>
      </form>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>User</th>
            <th>Role</th>
            <th>Phone</th>
            <th>Claims</th>
            <th>Last Login</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($users)): ?>
            <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">👥</div><div class="empty-title">No users found</div></div></td></tr>
          <?php else: ?>
            <?php foreach ($users as $u):
              $initials = implode('', array_map(fn($w)=>strtoupper($w[0]), array_slice(explode(' ',$u['full_name']),0,2)));
              $statusColor = $u['status']==='active' ? 'var(--success)' : 'var(--danger)';
            ?>
              <tr>
                <td>
                  <div class="flex align-center gap-8">
                    <div class="user-avatar" style="width:34px;height:34px;font-size:0.75rem;"><?= $initials ?></div>
                    <div>
                      <div class="td-primary" style="font-size:0.875rem;"><?= sanitize($u['full_name']) ?></div>
                      <div class="text-xs text-muted"><?= sanitize($u['email']) ?></div>
                    </div>
                  </div>
                </td>
                <td>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="change_role">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <select name="new_role" class="filter-select" style="min-width:110px; padding:4px 8px; font-size:0.78rem;" onchange="this.form.submit()" <?= $u['id']===$user['id']?'disabled':'' ?>>
                      <option value="admin"     <?= $u['role']==='admin'?'selected':''?>>🔑 Admin</option>
                      <option value="adjuster"  <?= $u['role']==='adjuster'?'selected':''?>>🔍 Adjuster</option>
                      <option value="claimant"  <?= $u['role']==='claimant'?'selected':''?>>👤 Claimant</option>
                    </select>
                  </form>
                </td>
                <td class="text-sm"><?= sanitize($u['phone'] ?: '—') ?></td>
                <td><span class="text-gold fw-600"><?= $u['claim_count'] ?></span></td>
                <td class="text-sm text-muted"><?= formatDateTime($u['last_login']) ?></td>
                <td>
                  <span style="display:inline-flex;align-items:center;gap:4px;font-size:0.78rem;font-weight:600;color:<?= $statusColor ?>;">
                    ● <?= ucfirst($u['status']) ?>
                  </span>
                </td>
                <td>
                  <?php if ($u['id'] !== $user['id']): ?>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="toggle_status">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <button type="submit" class="btn btn-ghost btn-xs" data-confirm="Toggle status for <?= sanitize($u['full_name']) ?>?">
                      <?= $u['status']==='active' ? '🚫 Deactivate' : '✅ Activate' ?>
                    </button>
                  </form>
                  <?php else: ?>
                    <span class="text-muted text-xs">— You —</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Create User Modal -->
<div class="modal-overlay" id="createUserModal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">➕ Add New User</div>
      <button class="modal-close" onclick="closeModal('createUserModal')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="create">
      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group form-full">
            <label class="form-label">Full Name *</label>
            <input type="text" name="full_name" class="form-control" required placeholder="John Doe">
          </div>
          <div class="form-group form-full">
            <label class="form-label">Email Address *</label>
            <input type="email" name="email" class="form-control" required placeholder="john@example.com">
          </div>
          <div class="form-group">
            <label class="form-label">Password *</label>
            <input type="password" name="password" class="form-control" required placeholder="Min 8 characters" minlength="6">
          </div>
          <div class="form-group">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" placeholder="+1-555-0100">
          </div>
          <div class="form-group form-full">
            <label class="form-label">Role</label>
            <select name="role" class="form-control">
              <option value="claimant">👤 Claimant</option>
              <option value="adjuster">🔍 Adjuster</option>
              <option value="admin">🔑 Admin</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('createUserModal')">Cancel</button>
        <button type="submit" class="btn btn-gold">Create User</button>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/includes/layout-end.php'; ?>
