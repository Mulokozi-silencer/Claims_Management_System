<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle  = 'New Claim';
$breadcrumb = 'ClaimsPro <span>/</span> Claims <span>/</span> New Claim';
include __DIR__ . '/includes/layout.php';

$pdo    = getDB();
$uid    = $user['id'];
$editId = (int)($_GET['edit'] ?? 0);
$claim  = null;
$error  = '';
$success = '';

// Load existing claim for editing
if ($editId && $user['role'] === 'claimant') {
    $stmt = $pdo->prepare("SELECT * FROM claims WHERE id = ? AND claimant_id = ? AND status = 'draft'");
    $stmt->execute([$editId, $uid]);
    $claim = $stmt->fetch();
    if ($claim) {
        $pageTitle  = 'Edit Claim';
        $breadcrumb = 'ClaimsPro <span>/</span> Claims <span>/</span> Edit Claim';
    }
}

// ── Handle Submission ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action          = $_POST['action'] ?? 'save_draft';
    $claim_type      = $_POST['claim_type'] ?? '';
    $title           = trim($_POST['title'] ?? '');
    $description     = trim($_POST['description'] ?? '');
    $incident_date   = $_POST['incident_date'] ?? '';
    $incident_location = trim($_POST['incident_location'] ?? '');
    $claimed_amount  = (float)($_POST['claimed_amount'] ?? 0);
    $priority        = $_POST['priority'] ?? 'medium';
    $notes           = trim($_POST['notes'] ?? '');

    if (!$claim_type || !$title || !$description || !$incident_date || $claimed_amount <= 0) {
        $error = 'Please fill in all required fields.';
    } else {
        $status      = ($action === 'submit') ? 'submitted' : 'draft';
        $submitted_at = ($action === 'submit') ? date('Y-m-d H:i:s') : null;

        if ($claim) {
            // Update
            $stmt = $pdo->prepare("UPDATE claims SET claim_type=?, title=?, description=?, incident_date=?, incident_location=?, claimed_amount=?, priority=?, notes=?, status=?, submitted_at=?, updated_at=NOW() WHERE id=? AND claimant_id=?");
            $stmt->execute([$claim_type,$title,$description,$incident_date,$incident_location,$claimed_amount,$priority,$notes,$status,$submitted_at,$claim['id'],$uid]);
            $claimId = $claim['id'];
        } else {
            $claimNumber = generateClaimNumber();
            $stmt = $pdo->prepare("INSERT INTO claims (claim_number,claimant_id,claim_type,title,description,incident_date,incident_location,claimed_amount,priority,notes,status,submitted_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$claimNumber,$uid,$claim_type,$title,$description,$incident_date,$incident_location,$claimed_amount,$priority,$notes,$status,$submitted_at]);
            $claimId = $pdo->lastInsertId();
        }

        if ($action === 'submit') {
            logActivity($claimId, $uid, 'status_change', 'Claim submitted for review.', 'draft', 'submitted');
            // Notify all adjusters
            $adjusters = $pdo->query("SELECT id FROM users WHERE role IN ('admin','adjuster') AND status='active'")->fetchAll();
            foreach ($adjusters as $adj) {
                sendNotification($adj['id'], $claimId, 'New Claim Submitted', "Claim $claimNumber has been submitted and needs review.");
            }
            header('Location: ' . APP_URL . '/claim-detail.php?id=' . $claimId . '&msg=submitted');
            exit;
        } else {
            $success = 'Claim saved as draft.';
            if (!$claim) {
                header('Location: ' . APP_URL . '/new-claim.php?edit=' . $claimId . '&msg=saved');
                exit;
            }
        }
    }
}

$f = $claim ?: ($_POST ?: []);
?>

<div style="max-width:860px;">

<?php if ($error): ?>
  <div class="alert alert-danger mb-16" data-auto-dismiss>⚠️ <?= sanitize($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
  <div class="alert alert-success mb-16" data-auto-dismiss>✅ <?= sanitize($success) ?></div>
<?php endif; ?>

<!-- Claim Progress Steps -->
<div class="card mb-24 fade-up">
  <div class="card-body" style="padding:20px 28px;">
    <div class="progress-steps">
      <?php
      $currentStatus = $claim['status'] ?? 'draft';
      $steps = ['draft'=>'Draft','submitted'=>'Submitted','under_review'=>'Under Review','approved'=>'Decision','settled'=>'Settled'];
      $order = array_keys($steps);
      $currentIdx = array_search($currentStatus, $order) ?: 0;
      foreach ($steps as $key => $label):
          $idx = array_search($key, $order);
          $cls = $idx < $currentIdx ? 'done' : ($idx === $currentIdx ? 'active' : '');
      ?>
        <div class="step <?= $cls ?>">
          <div class="step-dot"><?= $idx < $currentIdx ? '✓' : ($idx+1) ?></div>
          <div class="step-label"><?= $label ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<form method="POST" action="" id="claimForm">

  <!-- Basic Information -->
  <div class="card mb-24 fade-up">
    <div class="card-header">
      <div class="card-title"><span class="icon">📋</span> Claim Information</div>
      <?php if ($claim): ?><span class="td-mono text-sm"><?= sanitize($claim['claim_number']) ?></span><?php endif; ?>
    </div>
    <div class="card-body">
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Claim Type <span class="text-danger">*</span></label>
          <select name="claim_type" class="form-control" required>
            <option value="">Select type…</option>
            <?php foreach (['auto'=>'🚗 Auto / Vehicle','health'=>'🏥 Health / Medical','property'=>'🏠 Property / Home','life'=>'❤️ Life Insurance','travel'=>'✈️ Travel','liability'=>'⚖️ Liability','other'=>'📁 Other'] as $v => $l): ?>
              <option value="<?= $v ?>" <?= ($f['claim_type']??'')===$v?'selected':'' ?>><?= $l ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Priority</label>
          <select name="priority" class="form-control">
            <?php foreach (['low'=>'🟢 Low','medium'=>'🔵 Medium','high'=>'🟠 High','urgent'=>'🔴 Urgent'] as $v => $l): ?>
              <option value="<?= $v ?>" <?= ($f['priority']??'medium')===$v?'selected':'' ?>><?= $l ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group form-full">
          <label class="form-label">Claim Title <span class="text-danger">*</span></label>
          <input type="text" name="title" class="form-control" required maxlength="255"
                 placeholder="Brief description of the claim"
                 value="<?= sanitize($f['title']??'') ?>">
        </div>

        <div class="form-group form-full">
          <label class="form-label">Detailed Description <span class="text-danger">*</span></label>
          <textarea name="description" class="form-control" required rows="5"
                    placeholder="Describe the incident in detail — what happened, when, witnesses, etc."><?= sanitize($f['description']??'') ?></textarea>
        </div>
      </div>
    </div>
  </div>

  <!-- Incident Details -->
  <div class="card mb-24 fade-up">
    <div class="card-header">
      <div class="card-title"><span class="icon">📍</span> Incident Details</div>
    </div>
    <div class="card-body">
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Incident Date <span class="text-danger">*</span></label>
          <input type="date" name="incident_date" class="form-control" required
                 max="<?= date('Y-m-d') ?>"
                 value="<?= sanitize($f['incident_date']??'') ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Claimed Amount (USD) <span class="text-danger">*</span></label>
          <input type="number" name="claimed_amount" class="form-control" required
                 min="0.01" step="0.01" placeholder="0.00"
                 value="<?= $f['claimed_amount']??'' ?>">
        </div>

        <div class="form-group form-full">
          <label class="form-label">Incident Location</label>
          <input type="text" name="incident_location" class="form-control"
                 placeholder="City, State or specific address"
                 value="<?= sanitize($f['incident_location']??'') ?>">
        </div>

        <div class="form-group form-full">
          <label class="form-label">Additional Notes</label>
          <textarea name="notes" class="form-control" rows="3"
                    placeholder="Any other relevant information…"><?= sanitize($f['notes']??'') ?></textarea>
        </div>
      </div>
    </div>
  </div>

  <!-- Document Upload (after creation) -->
  <?php if ($claim): ?>
  <div class="card mb-24 fade-up">
    <div class="card-header">
      <div class="card-title"><span class="icon">📎</span> Supporting Documents</div>
      <a href="<?= APP_URL ?>/claim-detail.php?id=<?= $claim['id'] ?>#documents" class="btn btn-ghost btn-sm">Manage Documents</a>
    </div>
    <div class="card-body">
      <?php
      $docs = $pdo->prepare("SELECT * FROM claim_documents WHERE claim_id = ? ORDER BY created_at DESC");
      $docs->execute([$claim['id']]);
      $docList = $docs->fetchAll();
      ?>
      <?php if (empty($docList)): ?>
        <div class="upload-zone" onclick="window.location='<?= APP_URL ?>/claim-detail.php?id=<?= $claim['id'] ?>#upload'">
          <div class="upload-icon">📁</div>
          <div class="upload-text">Click to go to <strong>document upload</strong></div>
          <div class="upload-hint">PDF, JPG, PNG, DOC, DOCX, XLS up to 10MB</div>
        </div>
      <?php else: ?>
        <?php foreach ($docList as $doc): ?>
          <div class="doc-item">
            <div class="doc-icon">📄</div>
            <div>
              <div class="doc-name"><?= sanitize($doc['original_name']) ?></div>
              <div class="doc-meta"><?= ucfirst($doc['document_type']) ?> · <?= round($doc['file_size']/1024) ?> KB · <?= formatDate($doc['created_at']) ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Form Actions -->
  <div class="card fade-up">
    <div class="card-body" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
      <div>
        <div class="text-sm text-muted">
          <?php if ($claim && $claim['status']==='draft'): ?>
            💾 Last saved: <?= formatDateTime($claim['updated_at']) ?>
          <?php else: ?>
            💡 You can save as draft and come back later to complete
          <?php endif; ?>
        </div>
      </div>
      <div class="flex gap-12">
        <a href="<?= APP_URL ?>/claims.php" class="btn btn-ghost">Cancel</a>
        <button type="submit" name="action" value="save_draft" class="btn btn-ghost">
          💾 Save Draft
        </button>
        <button type="submit" name="action" value="submit" class="btn btn-gold"
                onclick="return confirm('Submit this claim for review? You cannot edit it after submission.')">
          🚀 Submit Claim
        </button>
      </div>
    </div>
  </div>

</form>
</div>

<?php include __DIR__ . '/includes/layout-end.php'; ?>
