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
    $name     = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (!$name || !$email || !$password || !$confirm) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $pdo = getDB();

        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = 'Email already registered.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO users (full_name, email, password, role, status, created_at)
                VALUES (?, ?, ?, 'user', 'active', NOW())
            ");

            if ($stmt->execute([$name, $email, $hashedPassword])) {
                // $success = 'Account created successfully. You can now login.';
                header('Location: ' . APP_URL . '/index.php');
            } else {
                $error = 'Something went wrong. Try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register — ClaimsPro</title>
  <link rel="stylesheet" href="<?= APP_URL ?>/css/style.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-bg"></div>

  <div class="auth-card fade-up">
    <div class="auth-logo">
      <div class="logo-icon">🛡️</div>
      <div class="auth-logo-text">ClaimsPro</div>
      <div class="auth-tagline">Create your account</div>
    </div>

    <h2 class="auth-title">Register</h2>

    <?php if ($error): ?>
      <div class="alert alert-danger">⚠️ <?= sanitize($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success">✅ <?= sanitize($success) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="form-group mb-16">
        <label class="form-label">Full Name</label>
        <input type="text" name="full_name" class="form-control"
               value="<?= sanitize($_POST['full_name'] ?? '') ?>" required>
      </div>

      <div class="form-group mb-16">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control"
               value="<?= sanitize($_POST['email'] ?? '') ?>" required>
      </div>

      <div class="form-group mb-16">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>

      <div class="form-group mb-24">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="confirm_password" class="form-control" required>
      </div>

      <button type="submit" class="btn btn-gold btn-full btn-lg">Register</button>
    </form>

    <div class="text-sm mt-16" style="text-align:center;">
      Already have an account?
      <a href="index.php" class="text-gold">Sign In</a>
    </div>
  </div>
</div>
</body>
</html>
