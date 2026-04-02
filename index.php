<?php
require_once __DIR__ . '/includes/config.php';
initSession();

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . APP_URL . '/dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (!$email || !$password) {
        $error = 'Please enter your email and password.';
    } else {
        $pdo  = getDB();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Default password for demo: "password"
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_name']  = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role']  = $user['role'];

            // Update last login
            $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);

            header('Location: ' . APP_URL . '/dashboard.php');
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In — ClaimsPro</title>
  <link rel="stylesheet" href="<?= APP_URL ?>/css/style.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-bg"></div>

  <div class="auth-card fade-up">
    <div class="auth-logo">
      <div class="logo-icon">🛡️</div>
      <div class="auth-logo-text">ClaimsPro</div>
      <div class="auth-tagline">Claims Management System</div>
    </div>

    <h2 class="auth-title">Welcome back</h2>

    <?php if ($error): ?>
      <div class="alert alert-danger">⚠️ <?= sanitize($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="form-group mb-16">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control"
               placeholder="you@company.com"
               value="<?= sanitize($_POST['email'] ?? '') ?>" required autofocus>
      </div>

      <div class="form-group mb-16">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control"
               placeholder="••••••••" required>
      </div>

      <div class="flex align-center gap-8 mb-24" style="justify-content:space-between;">
        <label class="flex align-center gap-8 text-sm" style="cursor:pointer;">
          <a href="register.php" class="text-sm text-gold">Don't have an account</a>
        </label>
        <a href="#" class="text-sm text-gold">Forgot password?</a>
      </div>

      <button type="submit" class="btn btn-gold btn-full btn-lg">Sign In</button>
    </form>
    </div>
  </div>
</div>
</body>
</html>
