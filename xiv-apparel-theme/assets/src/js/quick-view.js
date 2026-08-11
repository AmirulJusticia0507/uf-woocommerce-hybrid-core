/**
 * XIV Apparel - quick view modal.
 */
(function () {
  'use strict';

  var modal, content, panel;

  function open() {
    if (!modal) return;
    modal.classList.remove('xiv-hidden');
    document.body.classList.add('xiv-overflow-hidden');
    panel.scrollTop = 0;
  }

  function close() {
    if (!modal) return;
    modal.classList.add('xiv-hidden');
    document.body.classList.remove('xiv-overflow-hidden');
  }

  function initThumbs() {
    var main = content.querySelector('.xiv-qv-main');
    var thumbs = content.querySelectorAll('.xiv-qv-thumb');
    if (!main) return;
    thumbs.forEach(function (t) {
      t.addEventListener('click', function () {
        var full = t.getAttribute('data-full');
        if (!full) return;
        main.src = full;
        thumbs.forEach(function (x) {
          x.classList.remove('xiv-border-xiv-black');
          x.classList.add('xiv-border-xiv-gray-light');
        });
        t.classList.add('xiv-border-xiv-black');
        t.classList.remove('xiv-border-xiv-gray-light');
      });
    });
  }

  function load(productId) {
    if (!content || !window.XIV) return;

    content.innerHTML = '<p class="xiv-text-xs xiv-uppercase xiv-tracking-widest xiv-text-xiv-gray-text">' + (XIV.i18n.loading || '...') + '</p>';
    open();

    fetch(XIV.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: 'action=xiv_quick_view&product_id=' + encodeURIComponent(productId) +
        '&security=' + encodeURIComponent(XIV.nonce)
    })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res && res.success) {
          content.innerHTML = res.data.html;
          initThumbs();
          document.dispatchEvent(new CustomEvent('xiv:content-updated'));
        } else {
          content.innerHTML = '<p class="xiv-text-sm xiv-text-xiv-gray-text">' + ((res && res.data && res.data.message) || XIV.i18n.error) + '</p>';
        }
      })
      .catch(function () {
        content.innerHTML = '<p class="xiv-text-sm xiv-text-xiv-gray-text">' + XIV.i18n.error + '</p>';
      });
  }

  document.addEventListener('DOMContentLoaded', function () {
    modal = document.getElementById('xiv-quick-view-modal');
    if (!modal) return;
    content = modal.querySelector('.xiv-qv-content');
    panel = modal.querySelector('.xiv-qv-panel');

    modal.querySelectorAll('[data-xiv-qv-close]').forEach(function (el) {
      el.addEventListener('click', close);
    });

    document.addEventListener('click', function (e) {
      var btn = e.target.closest ? e.target.closest('.xiv-quick-view') : null;
      if (btn) {
        e.preventDefault();
        load(btn.getAttribute('data-product-id'));
      }
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && modal && !modal.classList.contains('xiv-hidden')) {
        close();
      }
    });
  });
})();
