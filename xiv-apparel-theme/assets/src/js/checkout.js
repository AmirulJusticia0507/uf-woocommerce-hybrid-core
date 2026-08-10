/**
 * XIV Apparel - checkout step flow + "Find Your Size" modal.
 */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    xivInitCheckoutSteps();
    xivInitSizeGuide();
  });

  function xivInitCheckoutSteps() {
    var checkout = document.querySelector('.woocommerce-checkout, form.checkout');
    if (!checkout) return;

    // Mark WooCommerce default sections with step badges.
    var sections = checkout.querySelectorAll('.woocommerce-billing-fields, .woocommerce-shipping-fields, #payment');
    var labels = ['INFORMATION', 'SHIPPING', 'PAYMENT'];
    sections.forEach(function (section, index) {
      if (!labels[index]) return;
      var heading = section.querySelector('h3');
      if (!heading) return;
      heading.classList.add('xiv-text-xs', 'xiv-font-black', 'xiv-uppercase', 'xiv-tracking-widest');
      heading.textContent = labels[index] + ' — ' + heading.textContent.replace(/^\s*[IVX]+\s*[-–]?\s*/, '');
    });
  }

  function xivInitSizeGuide() {
    var trigger = document.querySelector('.xiv-size-guide-trigger');
    var modal = document.getElementById('xiv-size-guide-modal');
    if (!trigger || !modal) return;

    var close = modal.querySelector('.xiv-size-guide-close');
    var overlay = modal.querySelector('.xiv-size-guide-overlay');
    var tbody = modal.querySelector('tbody');
    var category = trigger.getAttribute('data-category') || 'T-Shirts';

    function open() {
      modal.classList.remove('xiv-hidden');
      document.body.classList.add('xiv-overflow-hidden');
      if (window.XIV) {
        fetch(XIV.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
          body: 'action=xiv_size_guide&nonce=' + XIV.nonce + '&category=' + encodeURIComponent(category)
        })
          .then(function (r) { return r.json(); })
          .then(function (res) {
            if (tbody && res && res.success && res.data.guides.length) {
              tbody.innerHTML = res.data.guides.map(function (row) {
                return '<tr>' +
                  '<td>' + row.size_label + '</td>' +
                  '<td>' + (row.chest_cm || '—') + '</td>' +
                  '<td>' + (row.shoulder_cm || '—') + '</td>' +
                  '<td>' + (row.waist_cm || '—') + '</td>' +
                  '<td>' + (row.length_cm || '—') + '</td>' +
                  '</tr>';
              }).join('');
            }
          })
          .catch(function () {});
      }
    }

    function closeModal() {
      modal.classList.add('xiv-hidden');
      document.body.classList.remove('xiv-overflow-hidden');
    }

    trigger.addEventListener('click', open);
    if (close) close.addEventListener('click', closeModal);
    if (overlay) overlay.addEventListener('click', closeModal);
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !modal.classList.contains('xiv-hidden')) closeModal();
    });
  }
})();
