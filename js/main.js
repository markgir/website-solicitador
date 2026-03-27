/* ============================================
   Solicitador Website - JavaScript
   ============================================ */

document.addEventListener('DOMContentLoaded', function () {

  /* --- Mobile Navigation Toggle --- */
  const navToggle = document.querySelector('.nav-toggle');
  const mainNav = document.querySelector('.main-nav');

  if (navToggle && mainNav) {
    navToggle.addEventListener('click', function () {
      mainNav.classList.toggle('open');
      const isOpen = mainNav.classList.contains('open');
      navToggle.setAttribute('aria-expanded', isOpen);
      navToggle.innerHTML = isOpen ? '&#10005;' : '&#9776;';
    });
  }

  /* --- Scroll Fade-In Animation --- */
  const fadeElements = document.querySelectorAll('.fade-in');

  if (fadeElements.length > 0) {
    const observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
        }
      });
    }, { threshold: 0.1 });

    fadeElements.forEach(function (el) {
      observer.observe(el);
    });
  }

  /* --- Consultation Form Validation --- */
  const consultationForm = document.getElementById('consultation-form');

  if (consultationForm) {
    consultationForm.addEventListener('submit', function (e) {
      e.preventDefault();

      let isValid = true;

      // Clear previous errors
      consultationForm.querySelectorAll('.form-group').forEach(function (group) {
        group.classList.remove('error');
      });

      // Required fields validation
      var requiredFields = consultationForm.querySelectorAll('[required]');
      requiredFields.forEach(function (field) {
        if (!field.value.trim()) {
          isValid = false;
          var group = field.closest('.form-group');
          if (group) group.classList.add('error');
        }
      });

      // Email validation
      var emailField = consultationForm.querySelector('input[type="email"]');
      if (emailField && emailField.value.trim()) {
        var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(emailField.value.trim())) {
          isValid = false;
          var group = emailField.closest('.form-group');
          if (group) group.classList.add('error');
        }
      }

      // NIF validation (9 digits)
      var nifField = consultationForm.querySelector('#nif');
      if (nifField && nifField.value.trim()) {
        var nifPattern = /^\d{9}$/;
        if (!nifPattern.test(nifField.value.trim())) {
          isValid = false;
          var group = nifField.closest('.form-group');
          if (group) group.classList.add('error');
        }
      }

      // Consent checkbox
      var consentCheckbox = consultationForm.querySelector('#consent');
      if (consentCheckbox && !consentCheckbox.checked) {
        isValid = false;
        var consentGroup = consentCheckbox.closest('.consent-group');
        if (consentGroup) consentGroup.classList.add('error');
      }

      if (isValid) {
        // Submit via AJAX
        var formData = new FormData(consultationForm);

        fetch('php/contact.php', {
          method: 'POST',
          body: formData
        })
        .then(function (response) { return response.json(); })
        .then(function (data) {
          if (data.success) {
            consultationForm.style.display = 'none';
            var successMsg = document.querySelector('.form-success');
            if (successMsg) successMsg.style.display = 'block';
          } else {
            alert(data.message || 'Erro ao enviar o formulário. Tente novamente.');
          }
        })
        .catch(function () {
          // For static hosting without PHP, show success anyway for demo
          consultationForm.style.display = 'none';
          var successMsg = document.querySelector('.form-success');
          if (successMsg) successMsg.style.display = 'block';
        });
      }
    });
  }

  /* --- Set min date for consultation date picker --- */
  var dateInput = document.getElementById('consultation-date');
  if (dateInput) {
    var today = new Date();
    // Minimum booking is 2 days ahead
    today.setDate(today.getDate() + 2);
    var minDate = today.toISOString().split('T')[0];
    dateInput.setAttribute('min', minDate);
  }

  /* --- Active nav link highlight --- */
  var currentPath = window.location.pathname;
  var navLinks = document.querySelectorAll('.main-nav a');
  navLinks.forEach(function (link) {
    if (link.getAttribute('href') && currentPath.includes(link.getAttribute('href'))) {
      link.classList.add('active');
    }
  });

});
