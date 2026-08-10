/**
 * XIV Apparel - AJAX add-to-cart + cart drawer updates.
 */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    xivInitAddToCart(document);
    xivInitCartControls();
  });

  function xivInitAddToCart(scope) {
    var buttons = scope.querySelectorAll('a.add_to_cart_button, .ajax_add_to_cart');
    buttons.forEach(function (btn) {
      if (btn.dataset.xivBound) return;
      btn.dataset.xivBound = '1';

      btn.addEventListener('click', function (e) {
        e.preventDefault();
        var productId = btn.getAttribute('data-product_id');
        if (!productId) return;

        xivAddToCart(parseInt(productId, 10), 1, btn);
      });
    });
  }

  function xivAddToCart(productId, quantity, button) {
    if (!window.XIV) return;

    var body = new URLSearchParams({
      action: 'xiv_add_to_cart',
      security: XIV.cartNonce,
      product_id: String(productId),
      quantity: String(quantity || 1)
    });

    if (button) {
      var label = button.textContent.trim();
      button.setAttribute('data-xiv-label', label);
      button.textContent = XIV.i18n.loading;
    }

    fetch(XIV.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: body.toString()
    })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (button) {
          button.textContent = res && res.success ? XIV.i18n.added : (XIV.i18n.error || '');
        }
        if (res && res.success) {
          xivUpdateDrawer(res.data);
        }
        setTimeout(function () {
          if (button && button.getAttribute('data-xiv-label')) {
            button.textContent = button.getAttribute('data-xiv-label');
          }
        }, 2000);
      })
      .catch(function () {
        if (button) button.textContent = XIV.i18n.error;
      });
  }

  function xivUpdateDrawer(data) {
    if (!data || !data.fragments) return;

    var drawer = document.getElementById('xiv-cart-drawer');
    var itemsWrap = document.getElementById('xiv-cart-items');
    var footerWrap = document.getElementById('xiv-cart-footer');
    var badges = document.querySelectorAll('.xiv-cart-count');

    if (itemsWrap && data.fragments.items) itemsWrap.innerHTML = data.fragments.items;
    if (footerWrap && data.fragments.footer) footerWrap.innerHTML = data.fragments.footer;

    badges.forEach(function (badge) {
      badge.textContent = data.count;
      badge.classList.toggle('xiv-hidden', data.count === 0);
    });

    if (drawer) {
      drawer.classList.add('xiv-open');
      drawer.setAttribute('aria-hidden', 'false');
      document.body.classList.add('xiv-overflow-hidden');
    }

    xivInitCartControls();
  }

  function xivInitCartControls() {
    var items = document.querySelectorAll('.xiv-cart-item');
    items.forEach(function (item) {
      var key = item.getAttribute('data-cart-item-key');
      var qty = item.querySelector('.xiv-cart-qty');
      var remove = item.querySelector('.xiv-cart-remove');

      if (qty && !qty.dataset.xivBound) {
        qty.dataset.xivBound = '1';
        qty.addEventListener('change', function () {
          xivUpdateCart(key, parseInt(qty.value, 10) || 1, item);
        });
      }

      if (remove && !remove.dataset.xivBound) {
        remove.dataset.xivBound = '1';
        remove.addEventListener('click', function () {
          xivUpdateCart(key, 0, item);
        });
      }
    });
  }

  function xivUpdateCart(key, quantity, item) {
    if (!window.XIV) return;

    var body = new URLSearchParams({
      action: 'xiv_update_cart',
      security: XIV.cartNonce,
      cart_item_key: key,
      quantity: String(quantity)
    });

    fetch(XIV.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: body.toString()
    })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (!res || !res.success) return;
        if (quantity === 0 && item) item.remove();
        xivUpdateDrawer(res.data);
      })
      .catch(function () {});
  }
})();
