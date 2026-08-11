/**
 * XIV Apparel - back-in-stock notifications (PDP).
 */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('.xiv-bis-form');
    if (!form || !window.XIV) return;

    form.addEventListener('submit', function (e) {
      e.preventDefault();

      var email = form.querySelector('input[type="email"]');
      var msg = form.querySelector('.xiv-bis-msg');
      if (!email || !email.value || !msg) return;

      var button = form.querySelector('button[type="submit"]');
      var original = button.textContent;
      button.textContent = '...';

      fetch(window.XIV.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: 'action=xiv_back_in_stock&product_id=' + encodeURIComponent(form.dataset.productId || '') +
          '&email=' + encodeURIComponent(email.value) +
          '&security=' + encodeURIComponent(XIV.nonce)
      })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          msg.classList.remove('xiv-hidden');
          if (res && res.success) {
            msg.textContent = res.data.message;
            email.value = '';
            button.style.display = 'none';
          } else {
            msg.textContent = (res && res.data && res.data.message) ? res.data.message : XIV.i18n.error;
            button.textContent = original;
          }
        })
        .catch(function () {
          msg.classList.remove('xiv-hidden');
          msg.textContent = XIV.i18n.error;
          button.textContent = original;
        });
    });
  });
})();
