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
    xivInitNewsletterPopup();
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

    var input = document.getElementById('xiv-search-input');
    var resultsBox = document.getElementById('xiv-live-search-results');
    if (!input || !resultsBox || !window.XIV) return;

    var debounceTimer = null;

    function escHtml(str) {
      return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    function render(data) {
      if (!data || !data.results || !data.results.length) {
        resultsBox.innerHTML = '<p class="xiv-live-search-empty xiv-text-xs xiv-uppercase xiv-tracking-widest xiv-text-xiv-gray-text xiv-py-4">' + escHtml(XIV.i18n.noResults) + '</p>';
        return;
      }
      var searchUrl = input.form.action + '?s=' + encodeURIComponent(input.value) + '&post_type=product';
      var html = '<ul class="xiv-live-search-list xiv-border xiv-border-xiv-gray-light xiv-bg-xiv-bg xiv-divide-y xiv-divide-xiv-gray-light">';
      data.results.forEach(function (item) {
        html += '<li><a href="' + escHtml(item.url) + '" class="xiv-flex xiv-items-center xiv-gap-4 xiv-py-3 xiv-px-4 hover:xiv-bg-white xiv-transition">';
        html += '<img src="' + escHtml(item.image) + '" alt="" class="xiv-w-10 xiv-h-14 xiv-object-cover xiv-bg-stone-200 xiv-shrink-0" loading="lazy" />';
        html += '<span class="xiv-flex-1 xiv-min-w-0">';
        html += '<span class="xiv-block xiv-text-xs xiv-font-bold xiv-uppercase xiv-truncate">' + escHtml(item.title) + '</span>';
        html += '<span class="xiv-block xiv-text-xs xiv-font-mono xiv-text-xiv-gray-text xiv-mt-0.5">' + escHtml(item.price) + '</span>';
        html += '</span>';
        if (item.stock === 'out') {
          html += '<span class="xiv-text-[10px] xiv-font-mono xiv-uppercase xiv-text-xiv-gray-text">' + escHtml(XIV.i18n.soldOut) + '</span>';
        }
        html += '</a></li>';
      });
      html += '</ul>';
      html += '<a href="' + escHtml(searchUrl) + '" class="xiv-block xiv-text-center xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-py-3 xiv-bg-xiv-black xiv-text-white hover:xiv-bg-xiv-gray-text xiv-transition">' + escHtml(XIV.i18n.viewAll) + '</a>';
      resultsBox.innerHTML = html;
    }

    function run() {
      var term = input.value.trim();
      if (term.length < 2) {
        resultsBox.innerHTML = '';
        return;
      }
      fetch(window.XIV.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: 'action=xiv_live_search&term=' + encodeURIComponent(term) +
          '&security=' + encodeURIComponent(XIV.nonce)
      })
        .then(function (r) { return r.json(); })
        .then(function (res) { render(res.data); })
        .catch(function () { resultsBox.innerHTML = ''; });
    }

    input.addEventListener('input', function () {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(run, 300);
    });

    document.addEventListener('click', function (e) {
      if (!overlay.contains(e.target)) resultsBox.innerHTML = '';
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

  function xivInitNewsletterPopup() {
    var popup = document.getElementById('xiv-newsletter-popup');
    if (!popup || !window.XIV) return;

    var shown = false;

    function show() {
      if (shown) return;
      shown = true;
      popup.classList.remove('xiv-hidden');
      var email = document.getElementById('xiv-popup-newsletter-email');
      if (email) email.focus();
    }

    function setCookie(name, value, days) {
      var date = new Date();
      date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
      document.cookie = name + '=' + encodeURIComponent(value) + '; expires=' + date.toUTCString() + '; path=/';
    }

    function close() {
      popup.classList.add('xiv-hidden');
    }

    setTimeout(show, 4000);

    popup.querySelectorAll('[data-xiv-popup-close]').forEach(function (el) {
      el.addEventListener('click', close);
    });

    var dismiss = popup.querySelector('[data-xiv-popup-dismiss]');
    if (dismiss) {
      dismiss.addEventListener('click', function () {
        setCookie('xiv_popup_seen', '1', 7);
        close();
      });
    }

    var form = document.getElementById('xiv-popup-newsletter-form');
    if (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var email = document.getElementById('xiv-popup-newsletter-email');
        var msg = form.querySelector('.xiv-popup-newsletter-msg');
        if (!email || !email.value || !msg) return;

        var button = form.querySelector('button[type="submit"]');
        var original = button.textContent;
        button.textContent = '...';

        fetch(window.XIV.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
          body: 'action=xiv_newsletter&email=' + encodeURIComponent(email.value) +
            '&security=' + encodeURIComponent(XIV.nonce)
        })
          .then(function (r) { return r.json(); })
          .then(function (res) {
            msg.classList.remove('xiv-hidden');
            msg.textContent = res && res.data && res.data.message ? res.data.message : original;
            button.textContent = original;
            if (res && res.success) {
              setCookie('xiv_popup_seen', '1', 7);
              email.value = '';
              setTimeout(close, 2500);
            }
          })
          .catch(function () {
            button.textContent = original;
          });
      });
    }
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
