/**
 * XIV Apparel - product compare (cookie list).
 */
(function () {
  'use strict';

  var MAX = 4;
  var COOKIE = 'xiv_compare';

  function read() {
    return document.cookie.split('; ').reduce(function (acc, part) {
      var kv = part.split('=');
      if (kv[0] === COOKIE) {
        acc = kv[1].split(',').map(Number).filter(Boolean);
      }
      return acc;
    }, []);
  }

  function write(list) {
    var days = 365;
    var exp = new Date(Date.now() + days * 864e5).toUTCString();
    document.cookie = COOKIE + '=' + list.join(',') + '; expires=' + exp + '; path=/';
  }

  function updateBadges(list) {
    var badge = document.querySelector('.xiv-compare-count');
    if (badge) {
      badge.textContent = String(list.length);
      badge.classList.toggle('xiv-hidden', list.length === 0);
    }
    document.querySelectorAll('.xiv-compare-toggle').forEach(function (btn) {
      var active = list.indexOf(Number(btn.getAttribute('data-product-id'))) !== -1;
      btn.classList.toggle('xiv-is-active', active);
      btn.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
  }

  function removeRow(id) {
    var row = document.querySelector('.xiv-compare-remove[data-product-id="' + id + '"]');
    if (row) {
      var th = row.closest('th');
      if (th) th.remove();
      document.querySelectorAll('tr').forEach(function (tr) {
        var cell = tr.querySelector('td[data-pid="' + id + '"]');
        if (cell) cell.remove();
      });
    }
  }  document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener('click', function (e) {
      var toggle = e.target.closest ? e.target.closest('.xiv-compare-toggle') : null;
      if (toggle) {
        e.preventDefault();
        var list = read();
        var id = Number(toggle.getAttribute('data-product-id'));
        var i = list.indexOf(id);
        if (i !== -1) {
          list.splice(i, 1);
        } else {
          if (list.length >= MAX) list.shift();
          list.push(id);
        }
        write(list);
        updateBadges(list);
        return;
      }

      var remove = e.target.closest ? e.target.closest('.xiv-compare-remove') : null;
      if (remove) {
        e.preventDefault();
        var rid = Number(remove.getAttribute('data-product-id'));
        var rl = read().filter(function (x) { return x !== rid; });
        write(rl);
        updateBadges(rl);
        removeRow(rid);
        if (rl.length === 0) window.location.reload();
      }
    });

    updateBadges(read());
  });
})();
