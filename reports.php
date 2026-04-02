<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle  = 'Reports & Analytics';
$breadcrumb = 'ClaimsPro <span>/</span> Admin <span>/</span> Reports';
include __DIR__ . '/includes/layout.php';
requireRole('admin', 'adjuster');

$pdo = getDB();

// ── Analytics Queries ──────────────────────────────────────────
// By Status
$byStatus = $pdo->query("SELECT status, COUNT(*) as cnt, COALESCE(SUM(claimed_amount),0) as total FROM claims GROUP BY status")->fetchAll();

// By Type
$byType = $pdo->query("SELECT claim_type, COUNT(*) as cnt, COALESCE(SUM(claimed_amount),0) as total, COALESCE(SUM(approved_amount),0) as approved FROM claims GROUP BY claim_type ORDER BY cnt DESC")->fetchAll();

// By Priority
$byPriority = $pdo->query("SELECT priority, COUNT(*) as cnt FROM claims GROUP BY priority ORDER BY FIELD(priority,'urgent','high','medium','low')")->fetchAll();

// Monthly trend (last 6 months)
$trend = $pdo->query("SELECT DATE_FORMAT(created_at,'%Y-%m') as month, COUNT(*) as cnt, COALESCE(SUM(claimed_amount),0) as total FROM claims WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY month ORDER BY month")->fetchAll();

// Totals
$totals = $pdo->query("SELECT COUNT(*) as total_claims, COALESCE(SUM(claimed_amount),0) as total_claimed, COALESCE(SUM(approved_amount),0) as total_approved, AVG(claimed_amount) as avg_claimed, (SELECT COUNT(*) FROM users) as total_users, (SELECT COUNT(*) FROM claim_documents) as total_docs FROM claims")->fetch();

// Top Claimants
$topClaimants = $pdo->query("SELECT u.full_name, COUNT(c.id) as claim_count, COALESCE(SUM(c.claimed_amount),0) as total_amount FROM users u JOIN claims c ON c.claimant_id=u.id GROUP BY u.id ORDER BY claim_count DESC LIMIT 5")->fetchAll();

$statusColors = ['draft'=>'#5c6478','submitted'=>'#5bc8f5','under_review'=>'#f0a030','approved'=>'#2ecc88','rejected'=>'#e05252','settled'=>'#c9a84c','closed'=>'#9b72cf'];
$typeColors   = ['auto'=>'#3d7fff','health'=>'#e05252','property'=>'#2ecc88','life'=>'#c9a84c','travel'=>'#5bc8f5','liability'=>'#9b72cf','other'=>'#5c6478'];
?>

<!-- Summary KPIs -->
<div class="stats-grid mb-24 fade-up">
  <div class="stat-card gold">
    <div class="stat-icon">📋</div>
    <div class="stat-value"><?= number_format($totals['total_claims']) ?></div>
    <div class="stat-label">Total Claims</div>
  </div>
  <div class="stat-card blue">
    <div class="stat-icon">💵</div>
    <div class="stat-value" style="font-size:1.2rem;"><?= formatCurrency($totals['total_claimed']) ?></div>
    <div class="stat-label">Total Claimed</div>
  </div>
  <div class="stat-card green">
    <div class="stat-icon">✅</div>
    <div class="stat-value" style="font-size:1.2rem;"><?= formatCurrency($totals['total_approved']) ?></div>
    <div class="stat-label">Total Approved</div>
  </div>
  <div class="stat-card purple">
    <div class="stat-icon">📊</div>
    <div class="stat-value" style="font-size:1.2rem;"><?= formatCurrency($totals['avg_claimed']) ?></div>
    <div class="stat-label">Avg Claim Amount</div>
  </div>
  <div class="stat-card red">
    <div class="stat-icon">👥</div>
    <div class="stat-value"><?= $totals['total_users'] ?></div>
    <div class="stat-label">Total Users</div>
  </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:24px;">

  <!-- Claims by Status (bar) -->
  <div class="card fade-up">
    <div class="card-header"><div class="card-title"><span class="icon">📊</span> Claims by Status</div></div>
    <div class="card-body">
      <?php foreach ($byStatus as $row):
        $pct = $totals['total_claims'] > 0 ? round(($row['cnt']/$totals['total_claims'])*100) : 0;
        $color = $statusColors[$row['status']] ?? '#5c6478';
        $label = ucwords(str_replace('_',' ',$row['status']));
      ?>
        <div class="mb-16">
          <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
            <span style="font-size:0.82rem; color:var(--text-secondary);"><?= $label ?></span>
            <span style="font-size:0.82rem; color:var(--text-primary); font-weight:600;"><?= $row['cnt'] ?> (<?= $pct ?>%)</span>
          </div>
          <div style="height:8px; background:var(--bg-elevated); border-radius:4px; overflow:hidden;">
            <div style="height:100%; width:<?= $pct ?>%; background:<?= $color ?>; border-radius:4px; transition:width 0.6s ease;"></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Claims by Type -->
  <div class="card fade-up">
    <div class="card-header"><div class="card-title"><span class="icon">🗂️</span> Claims by Type</div></div>
    <div class="card-body">
      <table>
        <thead><tr><th>Type</th><th>Count</th><th>Claimed</th><th>Approved</th></tr></thead>
        <tbody>
          <?php foreach ($byType as $row):
            $color = $typeColors[$row['claim_type']] ?? '#5c6478';
          ?>
            <tr>
              <td style="display:flex; align-items:center; gap:8px;">
                <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:<?= $color ?>;"></span>
                <span style="text-transform:capitalize; color:var(--text-primary);"><?= $row['claim_type'] ?></span>
              </td>
              <td class="text-gold fw-600"><?= $row['cnt'] ?></td>
              <td class="text-sm"><?= formatCurrency($row['total']) ?></td>
              <td class="text-sm" style="color:var(--success);"><?= $row['approved'] > 0 ? formatCurrency($row['approved']) : '—' ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<div style="display:grid; grid-template-columns:2fr 1fr; gap:24px; margin-bottom:24px;">

  <!-- Monthly Trend -->
  <div class="card fade-up">
    <div class="card-header"><div class="card-title"><span class="icon">📈</span> Monthly Claim Trend (Last 6 Months)</div></div>
    <div class="card-body">
      <?php if (empty($trend)): ?>
        <div class="empty-state" style="padding:24px 0;"><div class="empty-icon">📉</div><div class="empty-title">No data yet</div></div>
      <?php else:
        $maxCnt = max(array_column($trend, 'cnt')) ?: 1;
        foreach ($trend as $row):
          $pct = round(($row['cnt']/$maxCnt)*100);
          $monthLabel = date('M Y', strtotime($row['month'].'-01'));
      ?>
          <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px;">
            <div style="width:80px; font-size:0.8rem; color:var(--text-muted); flex-shrink:0;"><?= $monthLabel ?></div>
            <div style="flex:1; height:28px; background:var(--bg-elevated); border-radius:4px; overflow:hidden; position:relative;">
              <div style="position:absolute; inset:0; width:<?= $pct ?>%; background:linear-gradient(90deg, var(--gold-dim), var(--gold)); border-radius:4px; transition:width 0.8s ease;"></div>
              <div style="position:absolute; inset:0; display:flex; align-items:center; padding:0 10px; font-size:0.78rem; font-weight:600; color:var(--text-primary); z-index:1;"><?= $row['cnt'] ?> claims</div>
            </div>
            <div style="width:100px; text-align:right; font-size:0.8rem; color:var(--gold);"><?= formatCurrency($row['total']) ?></div>
          </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

  <!-- Top Claimants -->
  <div class="card fade-up">
    <div class="card-header"><div class="card-title"><span class="icon">🏆</span> Top Claimants</div></div>
    <div class="card-body">
      <?php if (empty($topClaimants)): ?>
        <div class="text-center text-muted text-sm">No data</div>
      <?php else: foreach ($topClaimants as $i => $row):
        $medals = ['🥇','🥈','🥉','4️⃣','5️⃣'];
      ?>
        <div style="display:flex; align-items:center; gap:12px; padding:10px 0; border-bottom:1px solid var(--border);">
          <span style="font-size:1.2rem;"><?= $medals[$i] ?? ($i+1) ?></span>
          <div style="flex:1; min-width:0;">
            <div style="font-size:0.85rem; color:var(--text-primary); font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= sanitize($row['full_name']) ?></div>
            <div style="font-size:0.72rem; color:var(--text-muted);"><?= formatCurrency($row['total_amount']) ?> total</div>
          </div>
          <span class="text-gold fw-600"><?= $row['claim_count'] ?></span>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

</div>

<!-- Priority Distribution -->
<div class="card fade-up mb-24">
  <div class="card-header"><div class="card-title"><span class="icon">🚨</span> Priority Distribution</div></div>
  <div class="card-body" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:16px;">
    <?php
    $priorityColors = ['urgent'=>'var(--danger)','high'=>'var(--warning)','medium'=>'var(--info)','low'=>'var(--success)'];
    $priorityIcons  = ['urgent'=>'🔴','high'=>'🟠','medium'=>'🔵','low'=>'🟢'];
    $totalPri = array_sum(array_column($byPriority,'cnt')) ?: 1;
    foreach ($byPriority as $row):
      $pct = round(($row['cnt']/$totalPri)*100);
      $color = $priorityColors[$row['priority']] ?? 'var(--text-muted)';
    ?>
      <div style="text-align:center; padding:16px; background:var(--bg-elevated); border-radius:var(--radius-lg); border:1px solid var(--border);">
        <div style="font-size:1.8rem; margin-bottom:6px;"><?= $priorityIcons[$row['priority']] ?? '●' ?></div>
        <div style="font-family:var(--font-display); font-size:1.8rem; font-weight:700; color:<?= $color ?>;"><?= $row['cnt'] ?></div>
        <div style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.08em; color:var(--text-muted); margin-top:2px;"><?= ucfirst($row['priority']) ?></div>
        <div style="font-size:0.78rem; color:var(--text-muted); margin-top:4px;"><?= $pct ?>%</div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php include __DIR__ . '/includes/layout-end.php'; ?>
