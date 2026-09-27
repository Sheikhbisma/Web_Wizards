</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const sidebar = document.getElementById('aSidebar');
  const toggle = document.getElementById('aToggle');
  const overlay = document.getElementById('aOverlay');
  const closeMenu = () => {
    if (!sidebar || !toggle || !overlay) return;
    sidebar.classList.remove('open');
    overlay.classList.remove('show');
    overlay.style.opacity = '0';
    overlay.style.visibility = 'hidden';
    toggle.setAttribute('aria-expanded', 'false');
  };
  if (toggle && sidebar && overlay) {
    toggle.setAttribute('aria-expanded', 'false');
    toggle.addEventListener('click', () => {
      const isOpen = sidebar.classList.toggle('open');
      overlay.style.opacity = isOpen ? '1' : '0';
      overlay.style.visibility = isOpen ? 'visible' : 'hidden';
      toggle.setAttribute('aria-expanded', String(isOpen));
    });
    overlay.addEventListener('click', closeMenu);
    sidebar.querySelectorAll('a').forEach(link => link.addEventListener('click', closeMenu));
    document.addEventListener('keydown', event => { if (event.key === 'Escape') closeMenu(); });
  }
  const search = document.getElementById('admLiveSearch');
  if (search) {
    search.addEventListener('input', () => {
      const query = search.value.trim().toLocaleLowerCase();
      document.querySelectorAll('.a-table tbody tr, .a-list-item, .a-entity-card').forEach(row => {
        row.hidden = !!query && !row.textContent.toLocaleLowerCase().includes(query);
      });
    });
  }
});
</script>
</body>
</html>
