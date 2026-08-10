/**
 * XIV Apparel - catalog filters (AJAX, optimistic updates).
 * Requires XIV global (localized in inc/enqueue.php).
 */
(function () {
  'use strict';

  var form = document.getElementById('xiv-filter-form');
  var grid = document.getElementById('xiv-product-grid');
  var status = document.querySelector('.xiv-filter-status');
  var orderby = document.querySelector('.xiv-catalog-orderby');

  if (!form || !grid || !window.XIV) return;

  var timer = null;
  var currentPage = 1;

  initFilterDrawer();
  bindEvents();
  updateStatus();

  function bindEvents() {
    form.addEventListener('change', onFilterChange);
    form.addEventListener('submit', function (e) { e.preventDefault(); onFilterChange(); });

    if (orderby) {
      orderby.addEventListener('change', function () {
        currentPage = 1;
        fetchProducts();
      });
    }

    document.addEventListener('click', function (e) {
      var link = e.target.closest('.xiv-pagination a');
      if (!link) return;
      e.preventDefault();
      var m = link.href.match(/paged=(\d+)/) || link.href.match(/page\/(\d+)/);
      currentPage = m ? parseInt(m[1], 10) : 1;
      fetchProducts();
    });
  }

  function initFilterDrawer() {
    var openBtn = document.querySelector('.xiv-filter-open');
    var closeBtn = document.querySelector('.xiv-filter-close');
    var body = document.querySelector('.xiv-filter-body');
    if (!body) return;

    if (openBtn) {
      openBtn.addEventListener('click', function () {
        body.classList.remove('xiv-hidden');
        document.body.classList.add('xiv-overflow-hidden');
      });
    }
    if (closeBtn) {
      closeBtn.addEventListener('click', function () {
        body.classList.add('xiv-hidden');
        document.body.classList.remove('xiv-overflow-hidden');
      });
    }
  }

  function onFilterChange() {
    clearTimeout(timer);
    currentPage = 1;
    timer = setTimeout(fetchProducts, 300);
  }

  function collectData() {
    var data = new FormData();
    data.append('action', 'xiv_filter_products');
    data.append('nonce', XIV.nonce);
    data.append('paged', String(currentPage));

    var sizes = form.querySelectorAll('input[name="sizes[]"]:checked');
    sizes.forEach(function (el) { data.append('sizes[]', el.value); });

    var cats = form.querySelectorAll('input[name="categories[]"]:checked');
    cats.forEach(function (el) { data.append('categories[]', el.value); });

    var availability = form.querySelector('input[name="availability"]:checked');
    if (availability && availability.value) data.append('availability', availability.value);

    var minPrice = form.querySelector('input[name="min_price"]');
    var maxPrice = form.querySelector('input[name="max_price"]');
    if (minPrice && minPrice.value) data.append('min_price', minPrice.value);
    if (maxPrice && maxPrice.value) data.append('max_price', maxPrice.value);

    if (orderby && orderby.value) data.append('orderby', orderby.value);

    return data;
  }

  function fetchProducts() {
    if (!grid || !status) return;
    grid.classList.add('xiv-opacity-40');
    status.textContent = XIV.i18n ? XIV.i18n.loading : 'LOADING';

    fetch(XIV.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: collectData()
    })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        grid.classList.remove('xiv-opacity-40');
        if (!res || !res.success || !res.data) return;

        grid.innerHTML = res.data.grid;
        var pagination = document.querySelector('.xiv-pagination');
        if (pagination) {
          pagination.innerHTML = res.data.pagination || '';
        }

        if (status) {
          var pages = Math.ceil(res.data.found / res.data.perPage);
          status.textContent = res.data.found + ' PRODUCTS' + (pages > 1 ? ' / PAGE ' + currentPage + ' OF ' + pages : '');
        }

        xivReinitAddToCart(grid);
        window.history.replaceState({}, '', XIV.shopUrl || window.location.href);
      })
      .catch(function () {
        grid.classList.remove('xiv-opacity-40');
        if (status) status.textContent = XIV.i18n ? XIV.i18n.error : 'ERROR';
      });
  }

  function updateStatus() {
    var countEl = document.querySelector('.xiv-result-count');
    if (status && countEl) status.textContent = countEl.textContent;
  }

  function xivReinitAddToCart(scope) {
    var links = scope.querySelectorAll('.add_to_cart_button');
    links.forEach(function (link) {
      link.classList.remove('added');
    });
  }
})();
