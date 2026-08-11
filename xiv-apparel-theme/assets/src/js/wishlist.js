/**
 * XIV Apparel - Wishlist.
 * Toggle heart on product cards, remove items on the wishlist page,
 * keep the header count in sync. Requires XIV global.
 */
(function () {
  'use strict';

  var COOKIE = 'xiv_wishlist';

  document.addEventListener('DOMContentLoaded', function () {
    xivWishlistSync();

    document.addEventListener('click', function (e) {
      var toggle = e.target.closest('.xiv-wishlist-toggle');
      if (toggle) {
        e.preventDefault();
        xivWishlistToggle(toggle);
        return;
      }

      var remove = e.target.closest('.xiv-wishlist-remove');
      if (remove) {
        e.preventDefault();
        xivWishlistRemove(remove);
      }
    });
  });

  function xivWishlistSync() {
    if (!window.XIV || !XIV.ajaxUrl) return;
    var body = 'action=xiv_wishlist_get&nonce=' + encodeURIComponent(XIV.wishlistNonce || '');
    xivWishlistFetch(body, function (res) {
      if (res && res.success && res.data) {
        xivWishlistCount(res.data.count);
        if (!isUserLoggedIn()) {
          setCookie(COOKIE, (res.data.ids || []).join(','));
        }
      }
    });
  }

  function xivWishlistToggle(btn) {
    var id = btn.getAttribute('data-product-id');
    if (!id || !window.XIV) return;

    var body = 'action=xiv_wishlist_toggle&product_id=' + encodeURIComponent(id) +
      '&nonce=' + encodeURIComponent(XIV.wishlistNonce || '');

    xivWishlistFetch(body, function (res) {
      if (!res || !res.success || !res.data) return;

      var active = !!res.data.added;
      btn.classList.toggle('xiv-is-active', active);
      btn.setAttribute('aria-pressed', active ? 'true' : 'false');
      xivWishlistCount(res.data.count);

      var label = btn.querySelector('.xiv-wishlist-btn-label');
      if (label) {
        var on = btn.getAttribute('data-label-on');
        var off = btn.getAttribute('data-label-off');
        if (on && off) label.textContent = active ? on : off;
      }

      if (!isUserLoggedIn()) {
        setCookie(COOKIE, res.data.ids.join(','));
      }
    });
  }

  function xivWishlistRemove(btn) {
    var id = btn.getAttribute('data-product-id');
    if (!id || !window.XIV) return;

    var body = 'action=xiv_wishlist_remove&product_id=' + encodeURIComponent(id) +
      '&nonce=' + encodeURIComponent(XIV.wishlistNonce || '');

    xivWishlistFetch(body, function (res) {
      if (!res || !res.success || !res.data) return;

      var card = btn.closest('.xiv-wishlist [data-wishlist-item], .xiv-group');
      if (card && card.parentNode) card.parentNode.removeChild(card);

      xivWishlistCount(res.data.count);

      if (!isUserLoggedIn()) {
        setCookie(COOKIE, res.data.ids.join(','));
      }

      var empty = document.querySelector('.xiv-wishlist .xiv-wishlist-empty-placeholder');
      if (!res.data.count && empty) empty.classList.remove('xiv-hidden');
    });
  }

  function xivWishlistFetch(body, done) {
    fetch(XIV.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: body
    })
      .then(function (r) { return r.json(); })
      .then(done)
      .catch(function () {});
  }

  function xivWishlistCount(count) {
    document.querySelectorAll('.xiv-wishlist-count').forEach(function (el) {
      el.textContent = String(count);
      el.classList.toggle('xiv-hidden', !count);
    });
  }

  function isUserLoggedIn() {
    var body = document.body;
    return !!(body && body.classList && body.classList.contains('logged-in'));
  }

  function setCookie(name, value) {
    var days = 365;
    var d = new Date();
    d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000);
    document.cookie = name + '=' + encodeURIComponent(value) + '; expires=' + d.toUTCString() + '; path=/; SameSite=Lax';
  }
})();
