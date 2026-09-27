</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const fSidebar = document.getElementById('fSidebar');
const fToggle = document.getElementById('fToggle');
const fOverlay = document.getElementById('fOverlay');
const closeFarmerMenu = () => {
  if (!fSidebar || !fToggle || !fOverlay) return;
  fSidebar.classList.remove('open');
  fOverlay.classList.remove('show');
  fOverlay.style.opacity = '0';
  fOverlay.style.visibility = 'hidden';
  fToggle.setAttribute('aria-expanded', 'false');
};
if (fSidebar && fToggle && fOverlay) {
  fToggle.setAttribute('aria-expanded', 'false');
  fToggle.addEventListener('click', () => {
    const isOpen = fSidebar.classList.toggle('open');
    fOverlay.style.opacity = isOpen ? '1' : '0';
    fOverlay.style.visibility = isOpen ? 'visible' : 'hidden';
    fToggle.setAttribute('aria-expanded', String(isOpen));
  });
  fOverlay.addEventListener('click', closeFarmerMenu);
  fSidebar.querySelectorAll('a').forEach(link => link.addEventListener('click', closeFarmerMenu));
  document.addEventListener('keydown', event => { if (event.key === 'Escape') closeFarmerMenu(); });
}
</script>
</body>
</html>
