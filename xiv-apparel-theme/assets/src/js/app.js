/**
 * XIV Apparel - global UI behaviour.
 * Mobile nav, search overlay, cart drawer, thumbnail swap, newsletter.
 */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    xivInitMobileNav();
    xivInitSearchOverlay();
    xivInitGallery();
    xivInitNewsletter();
    xivInitDrawer();
  });

  function xivInitMobileNav() {
    var toggle = document.querySelector('.xiv-mobile-nav-toggle');
    var menu = document.getElementById('xiv-mobile-menu');
    if (!toggle || !menu) return;

    toggle.addEventListener('click', function () {
      var open = menu.classList.toggle('xiv-hidden');
      toggle.setAttribute('aria-expanded', String(!open));
    });
  }

  function xivInitSearchOverlay() {
    var toggle = document.querySelector('.xiv-search-toggle');
    var overlay = document.getElementById('xiv-search-overlay');
    if (!toggle || !overlay) return;

    toggle.addEventListener('click', function () {
      var hidden = overlay.classList.toggle('xiv-hidden');
      toggle.setAttribute('aria-expanded', String(!hidden));
      if (!hidden) {
        var input = document.getElementById('xiv-search-input');
        if (input) input.focus();
      }
    });
  }

  function xivInitGallery() {
    var mainImage = document.querySelector('.xiv-main-image img');
    var thumbs = document.querySelectorAll('.xiv-thumb-item');
    if (!mainImage || !thumbs.length) return;

    thumbs.forEach(function (thumb) {
      thumb.addEventListener('click', function () {
        var full = thumb.getAttribute('data-full');
        if (!full) return;
        mainImage.src = full;
        thumbs.forEach(function (t) {
          t.classList.remove('xiv-ring-2', 'xiv-ring-xiv-black');
        });
        thumb.classList.add('xiv-ring-2', 'xiv-ring-xiv-black');
      });
    });
  }

  function xivInitNewsletter() {
    var form = document.getElementById('xiv-newsletter-form');
    if (!form) return;

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var email = document.getElementById('xiv-newsletter-email');
      if (!email || !email.value) return;

      var button = form.querySelector('button');
      var original = button.textContent;
      button.textContent = '...';

      fetch(window.XIV ? XIV.ajaxUrl : '/wp-admin/admin-ajax.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: 'action=xiv_newsletter&email=' + encodeURIComponent(email.value) +
          '&security=' + (window.XIV ? XIV.nonce : '')
      })
        .then(function (r) { return r.json(); })
        .then(function () {
          button.textContent = 'DONE';
          email.value = '';
          setTimeout(function () { button.textContent = original; }, 3000);
        })
        .catch(function () {
          button.textContent = original;
        });
    });
  }

  function xivInitDrawer() {
    var drawer = document.getElementById('xiv-cart-drawer');
    var toggle = document.querySelector('.xiv-cart-toggle');
    if (!drawer) return;

    var overlay = drawer.querySelector('.xiv-cart-overlay');
    var close = drawer.querySelector('.xiv-cart-close');
    var panel = drawer.querySelector('.xiv-cart-panel');

    function open() {
      drawer.classList.add('xiv-open');
      drawer.setAttribute('aria-hidden', 'false');
      document.body.classList.add('xiv-overflow-hidden');
      panel.focus && panel.focus();
    }

    function closeDrawer() {
      drawer.classList.remove('xiv-open');
      drawer.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('xiv-overflow-hidden');
    }

    if (toggle) toggle.addEventListener('click', open);
    if (overlay) overlay.addEventListener('click', closeDrawer);
    if (close) close.addEventListener('click', closeDrawer);

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && drawer.classList.contains('xiv-open')) {
        closeDrawer();
      }
    });
  }
})();
