<?php // includes/layout-end.php ?>
</div><!-- .page-content -->
</div><!-- .main-content -->

</div><!-- .app-wrapper -->

<!-- ── GLOBAL JAVASCRIPT ───────────────────────────────────── -->
<script>
(function() {
  // Notification panel toggle
  const btn   = document.getElementById('notifBtn');
  const panel = document.getElementById('notifPanel');
  if (btn && panel) {
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      panel.classList.toggle('open');
    });
    document.addEventListener('click', () => panel.classList.remove('open'));
    panel.addEventListener('click', (e) => e.stopPropagation());
  }

  // Mobile sidebar toggle
  const sidebar    = document.getElementById('sidebar');
  const menuToggle = document.getElementById('menuToggle');
  if (menuToggle && sidebar) {
    menuToggle.addEventListener('click', () => sidebar.classList.toggle('open'));
  }

  // Auto-dismiss alerts
  document.querySelectorAll('.alert[data-auto-dismiss]').forEach(el => {
    setTimeout(() => el.style.display = 'none', 4000);
  });

  // Confirm dialogs on delete buttons
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', (e) => {
      if (!confirm(el.dataset.confirm || 'Are you sure?')) {
        e.preventDefault();
      }
    });
  });

  // Modal helpers
  window.openModal  = (id) => document.getElementById(id)?.classList.add('open');
  window.closeModal = (id) => document.getElementById(id)?.classList.remove('open');
  document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', (e) => { if (e.target === m) m.classList.remove('open'); });
  });
})();
</script>
</body>
</html>
