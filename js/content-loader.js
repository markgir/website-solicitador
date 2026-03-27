/* ============================================
   Solicitador Website - Content Loader
   Loads dynamic content from JSON data files
   managed via the backoffice admin panel.
   ============================================ */

(function () {
  'use strict';

  // Determine language from <html lang=""> attribute
  var lang = document.documentElement.lang || 'pt';

  // Determine base path to reach the data/ directory from current page
  var basePath = '';
  var path = window.location.pathname;
  if (path.indexOf('/fr/services/') !== -1) {
    basePath = '../../';
  } else if (path.indexOf('/fr/') !== -1 || path.indexOf('/services/') !== -1) {
    basePath = '../';
  }

  var jsonUrl = basePath + 'data/content-' + lang + '.json';

  fetch(jsonUrl)
    .then(function (response) {
      if (!response.ok) return null;
      return response.json();
    })
    .then(function (data) {
      if (!data) return;
      applyContent(data);
    })
    .catch(function () {
      // Silently fail — static HTML content serves as fallback
    });

  function applyContent(data) {
    // Hero section
    if (data.hero) {
      updateText('.hero-content h1', data.hero.title);
      updateText('.hero-content > p', data.hero.subtitle);
      updateText('.hero-buttons .btn-primary', data.hero.cta_primary);
      updateText('.hero-buttons .btn-outline', data.hero.cta_secondary);
    }

    // Services section
    if (data.services) {
      updateText('.services-section .section-title', data.services.title);
      updateText('.services-section .section-subtitle', data.services.subtitle);
    }

    // About section
    if (data.about) {
      updateText('.about-section .section-title', data.about.title);
      updateText('.about-section .btn-primary', data.about.cta);

      if (data.about.text) {
        var aboutContainer = document.querySelector('.about-section .container');
        if (aboutContainer) {
          var paragraphs = data.about.text.split('\n\n');
          var pElements = aboutContainer.querySelectorAll('p');
          for (var i = 0; i < paragraphs.length && i < pElements.length; i++) {
            pElements[i].innerHTML = paragraphs[i].replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
          }
        }
      }
    }

    // Contact info in footer
    if (data.contact) {
      if (data.contact.email) {
        var emailLinks = document.querySelectorAll('a[href^="mailto:"]');
        emailLinks.forEach(function (el) {
          el.href = 'mailto:' + data.contact.email;
          el.textContent = data.contact.email;
        });
      }
      if (data.contact.phone) {
        var phoneLinks = document.querySelectorAll('a[href^="tel:"]');
        phoneLinks.forEach(function (el) {
          el.href = 'tel:' + data.contact.phone.replace(/\s/g, '');
          el.textContent = data.contact.phone;
        });
      }
    }

    // Footer description
    if (data.footer && data.footer.description) {
      var footerCols = document.querySelectorAll('.footer-col');
      if (footerCols.length > 0) {
        var firstP = footerCols[0].querySelector('p');
        if (firstP) firstP.textContent = data.footer.description;
      }
    }
  }

  function updateText(selector, text) {
    if (!text) return;
    var el = document.querySelector(selector);
    if (el) el.textContent = text;
  }

})();
