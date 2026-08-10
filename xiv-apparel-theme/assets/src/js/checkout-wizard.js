/**
 * XIV Apparel - 3-step checkout wizard (INFORMATION -> SHIPPING -> PAYMENT).
 *
 * Relayouts WooCommerce's native checkout form into visual steps while keeping
 * WooCommerce's engine (validation, gateways, order creation) untouched.
 *
 * WooCommerce replaces the whole form via AJAX on every `updated_checkout`,
 * so we re-build the wizard each time. On `checkout_error` we jump back to the
 * step containing the invalid fields.
 */
(function () {
  'use strict';

  var FORM_SELECTOR = 'form.checkout.woocommerce-checkout';

  var STEPS = [
    { id: 1, label: 'INFORMATION' },
    { id: 2, label: 'SHIPPING' },
    { id: 3, label: 'PAYMENT' }
  ];

  var currentStep = 1;

  document.addEventListener('DOMContentLoaded', function () {
    buildWizard();

    document.addEventListener('updated_checkout', function () {
      buildWizard();
    });

    document.addEventListener('checkout_error', function () {
      jumpToInvalidStep();
    });
  });

  function buildWizard() {
    var form = document.querySelector(FORM_SELECTOR);
    if (!form) return false;

    var customerDetails = form.querySelector('#customer_details');
    var orderReview = form.querySelector('#order_review');
    var payment = form.querySelector('#payment');
    if (!customerDetails || !orderReview || !payment) return false;

    var old = form.querySelector('.xiv-checkout-wizard');
    if (old) old.remove();

    var reviewTable = orderReview.querySelector('.woocommerce-checkout-review-order-table');

    var heading = form.querySelector('#order_review_heading');
    if (heading) heading.style.display = 'none';
    orderReview.style.display = 'none';

    var wizard = document.createElement('div');
    wizard.className = 'xiv-checkout-wizard xiv-max-w-3xl xiv-mx-auto';

    var nav = document.createElement('nav');
    nav.className = 'xiv-flex xiv-gap-8 xiv-text-xs xiv-font-black xiv-uppercase xiv-tracking-widest xiv-border-b xiv-border-xiv-gray-light xiv-mb-8';
    STEPS.forEach(function (step) {
      var tab = document.createElement('button');
      tab.type = 'button';
      tab.className = 'xiv-step-tab xiv-text-xiv-gray-text xiv-border-b-2 xiv-border-transparent xiv-pb-3 xiv-cursor-pointer xiv-bg-transparent';
      tab.setAttribute('data-step', String(step.id));
      tab.textContent = step.label;
      nav.appendChild(tab);
    });
    wizard.appendChild(nav);

    var body = document.createElement('div');
    body.className = 'xiv-step-body';

    var panel1 = createPanel(1);
    panel1.appendChild(customerDetails);
    appendActions(panel1, { next: 2, nextLabel: 'CONTINUE TO SHIPPING' });
    body.appendChild(panel1);

    var panel2 = createPanel(2);
    if (reviewTable) panel2.appendChild(reviewTable);
    appendActions(panel2, { prev: 1, next: 3, nextLabel: 'CONTINUE TO PAYMENT' });
    body.appendChild(panel2);

    var panel3 = createPanel(3);
    panel3.appendChild(payment);
    appendActions(panel3, { prev: 2 });
    body.appendChild(panel3);

    wizard.appendChild(body);
    form.insertBefore(wizard, form.firstChild);

    bindEvents(wizard);
    showStep(currentStep, false);
    return true;
  }

  function createPanel(stepId) {
    var panel = document.createElement('section');
    panel.className = 'xiv-step-panel xiv-hidden';
    panel.setAttribute('data-step', String(stepId));
    return panel;
  }

  function appendActions(panel, actions) {
    var wrap = document.createElement('div');
    wrap.className = 'xiv-step-actions xiv-flex xiv-items-center xiv-gap-4 xiv-mt-10 xiv-pt-6 xiv-border-t xiv-border-xiv-gray-light';

    if (actions.prev) {
      var back = document.createElement('button');
      back.type = 'button';
      back.className = 'xiv-step-prev xiv-text-xs xiv-font-black xiv-uppercase xiv-tracking-widest xiv-text-xiv-gray-text xiv-bg-transparent xiv-cursor-pointer hover:xiv-text-xiv-black';
      back.setAttribute('data-step', String(actions.prev));
      back.textContent = '\u2190 BACK';
      wrap.appendChild(back);
    }

    if (actions.next) {
      var next = document.createElement('button');
      next.type = 'button';
      next.className = 'xiv-step-next xiv-ml-auto xiv-bg-xiv-black xiv-text-white xiv-text-xs xiv-font-black xiv-uppercase xiv-tracking-widest xiv-py-3 xiv-px-6 xiv-cursor-pointer xiv-transition hover:xiv-bg-xiv-gray-text';
      next.setAttribute('data-step', String(actions.next));
      next.textContent = actions.nextLabel;
      wrap.appendChild(next);
    }

    panel.appendChild(wrap);
  }

  function bindEvents(wizard) {
    var tabs = wizard.querySelectorAll('.xiv-step-tab');
    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        showStep(parseInt(tab.getAttribute('data-step'), 10), true);
      });
    });

    var prevs = wizard.querySelectorAll('.xiv-step-prev');
    prevs.forEach(function (btn) {
      btn.addEventListener('click', function () {
        showStep(parseInt(btn.getAttribute('data-step'), 10), true);
      });
    });

    var nexts = wizard.querySelectorAll('.xiv-step-next');
    nexts.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var target = parseInt(btn.getAttribute('data-step'), 10);
        if (validateStep(currentStep)) {
          showStep(target, true);
        }
      });
    });
  }

  function showStep(stepId, scroll) {
    currentStep = stepId;

    var wizard = document.querySelector('.xiv-checkout-wizard');
    if (!wizard) return;

    wizard.querySelectorAll('.xiv-step-tab').forEach(function (tab) {
      var active = parseInt(tab.getAttribute('data-step'), 10) === stepId;
      tab.classList.toggle('xiv-text-xiv-black', active);
      tab.classList.toggle('xiv-border-xiv-black', active);
      tab.classList.toggle('xiv-text-xiv-gray-text', !active);
      tab.classList.toggle('xiv-border-transparent', !active);
    });

    wizard.querySelectorAll('.xiv-step-panel').forEach(function (panel) {
      var show = parseInt(panel.getAttribute('data-step'), 10) === stepId;
      panel.classList.toggle('xiv-hidden', !show);
    });

    if (scroll) {
      wizard.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }

  function validateStep(stepId) {
    var panel = getPanel(stepId);
    if (!panel) return true;

    var valid = true;

    panel.querySelectorAll('.validate-required').forEach(function (row) {
      var field = row.querySelector('input, select, textarea');
      if (!field) return;
      var fieldValid = isFieldValid(field);
      row.classList.toggle('woocommerce-invalid', !fieldValid);
      if (!fieldValid) valid = false;
    });

    if (stepId === 2) {
      var methods = panel.querySelectorAll('input[name^="shipping_method"]');
      var shipRow = panel.querySelector('tr.shipping');
      if (methods.length) {
        var checked = panel.querySelector('input[name^="shipping_method"]:checked');
        if (!checked) {
          valid = false;
          if (shipRow) shipRow.classList.add('woocommerce-invalid');
        } else if (shipRow) {
          shipRow.classList.remove('woocommerce-invalid');
        }
      }
    }

    if (!valid) {
      var firstInvalid = panel.querySelector('.woocommerce-invalid');
      if (firstInvalid) {
        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    }

    return valid;
  }

  function isFieldValid(field) {
    if (field.type === 'checkbox' || field.type === 'radio') return field.checked;
    return field.value.trim() !== '';
  }

  function jumpToInvalidStep() {
    var invalid = document.querySelector('.woocommerce-invalid');
    if (!invalid) return;
    var panel = invalid.closest('.xiv-step-panel');
    if (!panel) return;
    showStep(parseInt(panel.getAttribute('data-step'), 10), true);
  }

  function getPanel(stepId) {
    return document.querySelector('.xiv-step-panel[data-step="' + stepId + '"]');
  }
})();
