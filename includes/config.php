<?php
// ============================================================
// DATABASE CONFIGURATION
// ============================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');          // Change to your MySQL username
define('DB_PASS', '');              // Change to your MySQL password
define('DB_NAME', 'claims_db');
define('DB_CHARSET', 'utf8mb4');

// Application Settings
define('APP_NAME', 'ClaimsPro');
define('APP_VERSION', '2.0');
define('APP_URL', 'http://localhost/claims-system');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', APP_URL . '/uploads/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_EXTENSIONS', ['pdf','jpg','jpeg','png','gif','doc','docx','xls','xlsx']);

// Session
define('SESSION_NAME', 'claims_session');
define('SESSION_LIFETIME', 3600); // 1 hour

// ============================================================
// DATABASE CONNECTION (PDO)
// ============================================================
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}

// ============================================================
// SESSION INITIALIZATION
// ============================================================
function initSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'secure'   => false, // Set true in production with HTTPS
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        session_start();
    }
}

// ============================================================
// AUTHENTICATION HELPERS
// ============================================================
function isLoggedIn(): bool {
    initSession();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    return [
        'id'        => $_SESSION['user_id'],
        'name'      => $_SESSION['user_name'],
        'email'     => $_SESSION['user_email'],
        'role'      => $_SESSION['user_role'],
    ];
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/index.php');
        exit;
    }
}

function requireRole(string ...$roles): void {
    requireLogin();
    $user = currentUser();
    if (!in_array($user['role'], $roles)) {
        header('Location: ' . APP_URL . '/dashboard.php?error=unauthorized');
        exit;
    }
}

function isAdmin(): bool {
    $u = currentUser();
    return $u && $u['role'] === 'admin';
}

function isAdjuster(): bool {
    $u = currentUser();
    return $u && in_array($u['role'], ['admin','adjuster']);
}

// ============================================================
// UTILITY FUNCTIONS
// ============================================================
function sanitize(string $input): string {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function generateClaimNumber(): string {
    $year = date('Y');
    $pdo = getDB();
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM claims");
    $count = $stmt->fetch()['cnt'] + 1;
    return 'CLM-' . $year . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
}

function formatCurrency(float $amount): string {
    return '$' . number_format($amount, 2);
}

function formatDate(?string $date): string {
    if (!$date) return 'N/A';
    return date('M d, Y', strtotime($date));
}

function formatDateTime(?string $dt): string {
    if (!$dt) return 'N/A';
    return date('M d, Y H:i', strtotime($dt));
}

function getStatusBadge(string $status): string {
    $map = [
        'draft'        => 'badge-draft',
        'submitted'    => 'badge-submitted',
        'under_review' => 'badge-review',
        'approved'     => 'badge-approved',
        'rejected'     => 'badge-rejected',
        'settled'      => 'badge-settled',
        'closed'       => 'badge-closed',
    ];
    $label = ucwords(str_replace('_', ' ', $status));
    $cls   = $map[$status] ?? 'badge-draft';
    return "<span class=\"badge $cls\">$label</span>";
}

function getPriorityBadge(string $priority): string {
    $map = [
        'low'    => 'priority-low',
        'medium' => 'priority-medium',
        'high'   => 'priority-high',
        'urgent' => 'priority-urgent',
    ];
    $label = ucfirst($priority);
    $cls   = $map[$priority] ?? 'priority-medium';
    return "<span class=\"priority-badge $cls\">$label</span>";
}

function getUnreadNotifications(int $userId): array {
    $pdo  = getDB();
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getUnreadCount(int $userId): int {
    $pdo  = getDB();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$userId]);
    return (int)$stmt->fetchColumn();
}

function logActivity(int $claimId, int $userId, string $type, string $message, ?string $old = null, ?string $new = null): void {
    $pdo  = getDB();
    $stmt = $pdo->prepare("INSERT INTO claim_activities (claim_id, user_id, activity_type, message, old_value, new_value) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$claimId, $userId, $type, $message, $old, $new]);
}

function sendNotification(int $userId, ?int $claimId, string $title, string $message): void {
    $pdo  = getDB();
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, claim_id, title, message) VALUES (?,?,?,?)");
    $stmt->execute([$userId, $claimId, $title, $message]);
}
