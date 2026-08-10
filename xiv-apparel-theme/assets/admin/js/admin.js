/**
 * XIV Admin - media uploader, upload foto (dengan validasi), form helpers.
 */
(function ($) {
  'use strict';

  var UPLOAD_MAX = 5 * 1024 * 1024; // 5MB
  var UPLOAD_EXT = /\.(jpe?g|png|webp)$/i;

  $(function () {
    initTypePanels();
    initMediaUploader();
    initGallery();
    initDeleteConfirm();
    initFileUploads();
  });

  function initTypePanels() {
    var radios = $('.xiv-type-radio');
    var panels = $('.xiv-type-panel');

    function apply() {
      var target = radios.filter(':checked').data('target');
      panels.addClass('xiv-admin-hidden');
      $('.xiv-type-panel[data-panel="' + target + '"]').removeClass('xiv-admin-hidden');
    }

    radios.on('change', apply);
    apply();
  }

  function initMediaUploader() {
    var input = $('#xiv-image-id');
    var preview = $('#xiv-image-preview');
    var removeBtn = $('#xiv-remove-image');

    $('#xiv-upload-image').on('click', function (e) {
      e.preventDefault();
      var frame = wp.media({
        title: 'Pilih Gambar Produk',
        library: { type: 'image' },
        multiple: false
      });

      frame.on('select', function () {
        var attachment = frame.state().get('selection').first().toJSON();
        input.val(attachment.id);
        preview.attr('src', attachment.sizes.thumbnail.url).show();
        removeBtn.removeClass('xiv-admin-hidden');
      });

      frame.open();
    });

    removeBtn.on('click', function () {
      input.val('');
      preview.attr('src', '').hide();
      removeBtn.addClass('xiv-admin-hidden');
    });

    if (input.val()) {
      removeBtn.removeClass('xiv-admin-hidden');
    }
  }

  function initGallery() {
    var hidden = $('#xiv-gallery-ids');
    var previewWrap = $('#xiv-gallery-preview');

    function render() {
      var ids = hidden.val().split(',').filter(Boolean);
      previewWrap.empty();
      ids.forEach(function (id) {
        previewWrap.append(
          '<img src="' + window.wp.media.attachment(id).attributes.url + '" data-id="' + id + '" alt="" />'
        );
      });
    }

    $('#xiv-upload-gallery').on('click', function (e) {
      e.preventDefault();
      var frame = wp.media({
        title: 'Pilih Gambar Galeri',
        library: { type: 'image' },
        multiple: true
      });

      frame.on('select', function () {
        var ids = frame.state().get('selection').map(function (a) { return a.id; });
        hidden.val(ids.join(','));
        render();
      });

      frame.open();
    });

    previewWrap.on('click', 'img', function () {
      var id = $(this).data('id');
      var ids = hidden.val().split(',').filter(function (i) { return i !== String(id); });
      hidden.val(ids.join(','));
      render();
    });
  }

  function initDeleteConfirm() {
    $('.xiv-admin-delete').on('click', function (e) {
      if (!window.confirm($(this).data('confirm'))) {
        e.preventDefault();
      }
    });
  }

  function initFileUploads() {
    var form = document.getElementById('xiv-product-form');
    if (!form) return;

    var uploadUrl = form.getAttribute('data-upload-url');
    var nonce = form.querySelector('input[name="xiv_product_nonce"]').value;

    var featuredInput = document.getElementById('xiv-upload-file');
    var filesInput = document.getElementById('xiv-upload-files');
    var featuredBtn = document.getElementById('xiv-upload-file-btn');
    var filesBtn = document.getElementById('xiv-upload-files-btn');

    if (featuredBtn && featuredInput) {
      featuredBtn.addEventListener('click', function () { featuredInput.click(); });
      featuredInput.addEventListener('change', function () {
        if (featuredInput.files.length) uploadFiles(form, uploadUrl, nonce, [featuredInput.files[0]], 'featured');
        featuredInput.value = '';
      });
    }

    if (filesBtn && filesInput) {
      filesBtn.addEventListener('click', function () { filesInput.click(); });
      filesInput.addEventListener('change', function () {
        if (filesInput.files.length) uploadFiles(form, uploadUrl, nonce, Array.prototype.slice.call(filesInput.files), 'gallery');
        filesInput.value = '';
      });
    }
  }

  function uploadFiles(form, uploadUrl, nonce, files, mode) {
    if (!files.length) return;

    for (var i = 0; i < files.length; i++) {
      var file = files[i];
      if (!UPLOAD_EXT.test(file.name)) {
        window.alert('Format tidak diizinkan: JPG, PNG, atau WebP. ("' + file.name + '")');
        return;
      }
      if (file.size > UPLOAD_MAX) {
        window.alert('Ukuran melebihi 5MB. ("' + file.name + '")');
        return;
      }
    }

    var fd = new FormData();
    fd.append('_wpnonce', nonce);
    fd.append('action', 'xiv_upload_product_image');
    files.forEach(function (file) { fd.append('files[]', file); });

    var status = mode === 'featured'
      ? document.querySelector('#xiv-upload-file-btn')
      : document.querySelector('#xiv-upload-files-btn');
    var original = status ? status.textContent : '';
    if (status) status.textContent = 'Uploading…';

    fetch(uploadUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: fd
    })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (status) status.textContent = original;
        if (!res || !res.success || !res.data) {
          window.alert((res && res.data && res.data.message) || 'Upload gagal.');
          return;
        }

        var ok = res.data.files.filter(function (f) { return !f.error; });
        var errors = res.data.files.filter(function (f) { return f.error; });

        if (mode === 'featured') {
          if (ok.length) setFeatured(ok[0]);
        } else {
          ok.forEach(function (f) { addToGallery(f); });
        }

        if (errors.length) {
          window.alert('Beberapa file ditolak:\n' + errors.map(function (e) { return '• ' + e.error; }).join('\n'));
        }
      })
      .catch(function () {
        if (status) status.textContent = original;
        window.alert('Terjadi kesalahan saat upload.');
      });
  }

  function setFeatured(file) {
    var input = $('#xiv-image-id');
    var preview = $('#xiv-image-preview');
    input.val(file.attachment_id);
    preview.attr('src', file.url).show();
    $('#xiv-remove-image').removeClass('xiv-admin-hidden');
  }

  function addToGallery(file) {
    var hidden = $('#xiv-gallery-ids');
    var ids = hidden.val().split(',').filter(Boolean);
    ids.push(String(file.attachment_id));
    hidden.val(ids.join(','));
    $('#xiv-gallery-preview').append('<img src="' + file.url + '" data-id="' + file.attachment_id + '" alt="" />');
  }
})(jQuery);
