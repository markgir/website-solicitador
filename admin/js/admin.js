/* ============================================
   Solicitador Backoffice - Admin JavaScript
   ============================================ */

document.addEventListener('DOMContentLoaded', function () {

  /* --- Sidebar toggle for mobile --- */
  var sidebarToggle = document.getElementById('sidebar-toggle');
  var sidebar = document.getElementById('admin-sidebar');

  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', function () {
      sidebar.classList.toggle('open');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function (e) {
      if (window.innerWidth <= 768 &&
          sidebar.classList.contains('open') &&
          !sidebar.contains(e.target) &&
          e.target !== sidebarToggle) {
        sidebar.classList.remove('open');
      }
    });
  }

  /* --- Tabs --- */
  var tabs = document.querySelectorAll('.admin-tab');
  tabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      var targetId = this.getAttribute('data-tab');

      // Deactivate all tabs and content
      document.querySelectorAll('.admin-tab').forEach(function (t) {
        t.classList.remove('active');
      });
      document.querySelectorAll('.tab-content').forEach(function (c) {
        c.classList.remove('active');
      });

      // Activate clicked tab and its content
      this.classList.add('active');
      var target = document.getElementById(targetId);
      if (target) target.classList.add('active');
    });
  });

  /* --- Auto-dismiss alerts after 5 seconds --- */
  var alerts = document.querySelectorAll('.alert');
  alerts.forEach(function (alert) {
    setTimeout(function () {
      alert.style.transition = 'opacity 0.3s ease';
      alert.style.opacity = '0';
      setTimeout(function () {
        alert.remove();
      }, 300);
    }, 5000);
  });

});
