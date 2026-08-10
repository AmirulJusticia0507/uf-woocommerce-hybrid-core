/**
 * XIV Admin - media uploader + form helpers.
 */
(function ($) {
  'use strict';

  $(function () {
    initTypePanels();
    initMediaUploader();
    initGallery();
    initDeleteConfirm();
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
})(jQuery);
