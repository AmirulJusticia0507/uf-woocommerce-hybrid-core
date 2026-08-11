/**
 * XIV Apparel - AJAX add-to-cart + cart drawer updates.
 */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    xivInitAddToCart(document);
    xivInitCartControls();
    xivInitVariations();
  });

  document.addEventListener('xiv:content-updated', function () {
    xivInitAddToCart(document);
    xivInitCartControls();
    xivInitVariations();
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

  function xivAddToCart(productId, quantity, button, variationId, variationAttributes) {
    if (!window.XIV) return;

    var body = new URLSearchParams({
      action: 'xiv_add_to_cart',
      security: XIV.cartNonce,
      product_id: String(productId),
      quantity: String(quantity || 1)
    });

    if (variationId) body.append('variation_id', String(variationId));
    if (variationAttributes) {
      Object.keys(variationAttributes).forEach(function (key) {
        body.append('variation[' + key + ']', variationAttributes[key]);
      });
    }

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
          var msg = res && res.data && res.data.message;
          button.textContent = res && res.success ? XIV.i18n.added : (msg || XIV.i18n.error);
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

  function xivInitVariations() {
    var variations = document.querySelectorAll('.xiv-variations');
    variations.forEach(function (wrap) {
      var productId = wrap.getAttribute('data-product-id');
      var options = Array.prototype.slice.call(wrap.querySelectorAll('.xiv-size-option'));
      var priceEl = wrap.querySelector('.xiv-selected-price');
      var addBtn = wrap.querySelector('.xiv-add-bag');
      var jsonEl = wrap.querySelector('.xiv-variations-json');

      if (!jsonEl || !addBtn) return;

      var list;
      try { list = JSON.parse(jsonEl.textContent); } catch (e) { return; }

      var bySize = {};
      list.forEach(function (v) {
        var size = v.attributes.attribute_pa_size;
        if (size) bySize[size] = v;
      });

      var selected = null;

      function render() {
        options.forEach(function (opt) {
          var isActive = selected && opt.getAttribute('data-size') === selected.attributes.attribute_pa_size;
          opt.classList.toggle('xiv-border-xiv-black', !!isActive);
          opt.classList.toggle('xiv-bg-xiv-black', !!isActive);
          opt.classList.toggle('xiv-text-white', !!isActive);
        });

        if (selected) {
          if (XIV.currency) {
            priceEl.textContent = XIV.currency + Number(selected.display_price || selected.display_regular_price || 0).toFixed(2);
          }
          addBtn.disabled = false;
        } else {
          priceEl.textContent = '';
          addBtn.disabled = true;
        }
      }

      options.forEach(function (opt) {
        opt.addEventListener('click', function () {
          var size = opt.getAttribute('data-size');
          selected = bySize[size] || null;
          render();
        });
      });

      addBtn.addEventListener('click', function () {
        if (!selected) return;
        xivAddToCart(
          parseInt(productId, 10),
          1,
          addBtn,
          selected.variation_id,
          { attribute_pa_size: selected.attributes.attribute_pa_size }
        );
      });
    });
  }
})();
