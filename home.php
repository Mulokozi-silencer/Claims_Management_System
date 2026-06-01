<?php
require_once __DIR__ . '/includes/config.php';
initSession();

// If user is already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: ' . APP_URL . '/dashboard.php');
    exit;
}

// Fetch real-time statistics for the home page
$pdo = getDB();
$totalClaims = (int)$pdo->query("SELECT COUNT(*) FROM claims")->fetchColumn();
$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'claimant'")->fetchColumn();
$totalApproved = (float)$pdo->query("SELECT COALESCE(SUM(approved_amount),0) FROM claims WHERE status IN ('approved','settled')")->fetchColumn();

// Get recent claims for the "live activity" section (optional)
$recentClaims = $pdo->query("SELECT claim_number, claim_type, status, created_at FROM claims ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>ClaimsPro — Intelligent Claims Management</title>
    <link rel="stylesheet" href="<?= APP_URL ?>/css/style.css">
    <style>
        /* Additional home-specific styles */
        .hero {
            padding: 100px 0 60px;
            background: radial-gradient(ellipse 150% 80% at 20% 30%, rgba(201,168,76,0.08), transparent);
            border-bottom: 1px solid var(--border);
        }
        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
        }
        .hero-badge {
            display: inline-block;
            background: var(--gold-glow);
            color: var(--gold);
            padding: 4px 12px;
            border-radius: 40px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            margin-bottom: 24px;
            backdrop-filter: blur(4px);
        }
        .hero h1 {
            font-family: var(--font-display);
            font-size: 3.5rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #fff, var(--gold-light));
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
        }
        .hero p {
            font-size: 1.1rem;
            color: var(--text-secondary);
            max-width: 600px;
            margin: 0 auto 32px;
        }
        .cta-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 32px;
            margin: 60px 0;
        }
        .feature-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 30px 24px;
            transition: all 0.3s ease;
            text-align: center;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            border-color: var(--border-gold);
            box-shadow: var(--shadow-gold);
        }
        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        .feature-card h3 {
            font-family: var(--font-display);
            font-size: 1.3rem;
            margin-bottom: 12px;
        }
        .stats-showcase {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 40px;
            background: var(--bg-elevated);
            border-radius: var(--radius-xl);
            padding: 40px 20px;
            margin: 60px 0;
            border: 1px solid var(--border-gold);
        }
        .stat-item {
            text-align: center;
        }
        .stat-number {
            font-family: var(--font-display);
            font-size: 2.8rem;
            font-weight: 700;
            color: var(--gold);
            line-height: 1;
        }
        .stat-label {
            font-size: 0.85rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-top: 8px;
        }
        .steps {
            display: flex;
            flex-wrap: wrap;
            gap: 24px;
            margin: 50px 0;
        }
        .step {
            flex: 1;
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            padding: 24px;
            text-align: center;
            border: 1px solid var(--border);
        }
        .step-number {
            width: 48px;
            height: 48px;
            background: var(--gold-glow);
            color: var(--gold);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0 auto 16px;
        }
        .testimonial {
            background: var(--bg-elevated);
            border-radius: var(--radius-lg);
            padding: 32px;
            margin: 20px 0;
            border-left: 4px solid var(--gold);
        }
        .footer-cta {
            text-align: center;
            padding: 60px 20px;
            background: linear-gradient(135deg, var(--bg-surface), var(--bg-card));
            border-radius: var(--radius-xl);
            margin-top: 40px;
        }
        @media (max-width: 768px) {
            .hero h1 { font-size: 2.2rem; }
            .stats-showcase { flex-direction: column; align-items: center; gap: 24px; }
            .steps { flex-direction: column; }
        }
        .activity-feed {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            padding: 20px;
            margin-top: 40px;
        }
        .activity-item-sm {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
<div class="app-wrapper" style="display: block;">

    <!-- Simplified header (no sidebar) -->
    <header class="header" style="left:0; background: rgba(10,12,16,0.95); backdrop-filter: blur(12px);">
        <div class="header-left">
            <div class="logo-mark" style="display: flex; align-items: center; gap: 12px;">
                <div class="logo-icon">🛡️</div>
                <div class="logo-text">ClaimsPro</div>
            </div>
        </div>
        <div class="header-actions">
            <a href="<?= APP_URL ?>/home.php" class="btn btn-ghost btn-sm">Home</a>
            <a href="<?= APP_URL ?>/home.php#features" class="btn btn-ghost btn-sm">Features</a>
            <a href="<?= APP_URL ?>/index.php" class="btn btn-ghost btn-sm">Sign In</a>
            <a href="<?= APP_URL ?>/register.php" class="btn btn-gold btn-sm">Get Started</a>
        </div>
    </header>

    <div class="main-content" style="margin-left:0; padding-top: var(--header-h);">
        <div class="page-content" style="max-width: 1200px; margin: 0 auto;">

            <!-- Hero Section -->
            <div class="hero fade-up">
                <div class="hero-content">
                    <div class="hero-badge">✨ AI-Powered Insurance Claims Management</div>
                    <h1>Streamline Claims.<br>Accelerate Settlements.</h1>
                    <p>Enterprise-grade platform for insurers, adjusters, and claimants. Submit, track, and resolve claims with transparency and speed.</p>
                    <div class="cta-buttons">
                        <a href="<?= APP_URL ?>/register.php" class="btn btn-gold btn-lg">Start Free Trial</a>
                        <a href="#features" class="btn btn-ghost btn-lg">Explore Features</a>
                    </div>
                </div>
            </div>

            <!-- Live Stats -->
            <div class="stats-showcase fade-up">
                <div class="stat-item">
                    <div class="stat-number"><?= number_format($totalClaims) ?></div>
                    <div class="stat-label">Claims Processed</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= number_format($totalUsers) ?></div>
                    <div class="stat-label">Active Claimants</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= formatCurrency($totalApproved) ?></div>
                    <div class="stat-label">Total Paid Out</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">99.8%</div>
                    <div class="stat-label">Satisfaction Rate</div>
                </div>
            </div>

            <!-- Features Section -->
            <div id="features">
                <h2 style="text-align: center; font-family: var(--font-display); margin-bottom: 20px;">Everything you need in one platform</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">📝</div>
                        <h3>Smart Claim Intake</h3>
                        <p>Multi‑step forms with conditional logic, document uploads, and real‑time validation.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">⚙️</div>
                        <h3>Adjuster Workbench</h3>
                        <p>Assign claims, update statuses, approve/reject with notes, and manage payouts.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">📎</div>
                        <h3>Document Management</h3>
                        <p>Secure uploads of evidence, medical reports, photos – auto‑converted to PDF.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">🔔</div>
                        <h3>Real‑time Notifications</h3>
                        <p>Email & in‑app alerts for status changes, new messages, and deadline reminders.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">📊</div>
                        <h3>Analytics Dashboard</h3>
                        <p>Live metrics, claim trends, adjuster performance, and financial reports.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">🛡️</div>
                        <h3>Enterprise Security</h3>
                        <p>Role‑based access, audit logs, encrypted storage, and GDPR readiness.</p>
                    </div>
                </div>
            </div>

            <!-- How It Works -->
            <div style="margin: 60px 0;">
                <h2 style="text-align: center; font-family: var(--font-display); margin-bottom: 40px;">How ClaimsPro works</h2>
                <div class="steps">
                    <div class="step">
                        <div class="step-number">1</div>
                        <h3>File a Claim</h3>
                        <p>Claimant fills out the digital form, adds supporting docs, and submits.</p>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <h3>Assign & Review</h3>
                        <p>Admin assigns to an adjuster who investigates and updates status.</p>
                    </div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <h3>Decision & Payout</h3>
                        <p>Approve, reject, or request more info – approved amounts are paid out.</p>
                    </div>
                </div>
            </div>

            <!-- Recent Activity (Dynamic) -->
            <div class="activity-feed fade-up">
                <h3 style="margin-bottom: 16px;">🔄 Recent claims activity</h3>
                <?php if (empty($recentClaims)): ?>
                    <div class="text-muted text-sm">No recent claims yet.</div>
                <?php else: ?>
                    <?php foreach ($recentClaims as $rc): ?>
                        <div class="activity-item-sm">
                            <span class="td-mono"><?= sanitize($rc['claim_number']) ?></span>
                            <span><?= ucfirst($rc['claim_type']) ?></span>
                            <span><?= getStatusBadge($rc['status']) ?></span>
                            <span class="text-muted text-xs"><?= formatDate($rc['created_at']) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Testimonial -->
            <div class="testimonial">
                <div style="font-size: 2rem; color: var(--gold); margin-bottom: 8px;">“</div>
                <p style="font-size: 1rem; color: var(--text-secondary); font-style: italic;">ClaimsPro reduced our claim processing time by 62%. The dashboard is intuitive and the automation features are game‑changers.</p>
                <div style="margin-top: 16px;">
                    <strong>— Winfrida Willium</strong><br>
                    <span class="text-muted text-xs">Senior Adjuster, InsureCorp</span>
                </div>
            </div>

            <!-- Final CTA -->
            <div class="footer-cta fade-up">
                <h2 style="font-family: var(--font-display);">Ready to transform claims management?</h2>
                <p style="color: var(--text-secondary); margin-bottom: 24px;">Join 500+ insurance companies using ClaimsPro.</p>
                <div class="cta-buttons">
                    <a href="<?= APP_URL ?>/register.php" class="btn btn-gold btn-lg">Create Free Account</a>
                    <a href="<?= APP_URL ?>/index.php" class="btn btn-primary btn-lg">Sign In</a>
                </div>
                <p class="text-muted text-xs" style="margin-top: 24px;">No credit card required · Free 14‑day trial</p>
            </div>

        </div>
    </div>

    <!-- Simple Footer -->
    <footer style="border-top: 1px solid var(--border); padding: 32px; text-align: center; color: var(--text-muted); font-size: 0.75rem;">
        © <?= date('Y') ?> ClaimsPro — Intelligent Claims Management System. All rights reserved.
    </footer>
</div>

<script>
    // Simple fade-up animation trigger (re-use existing if needed)
    document.querySelectorAll('.fade-up').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        observer.observe(el);
    });
</script>
</body>
</html>