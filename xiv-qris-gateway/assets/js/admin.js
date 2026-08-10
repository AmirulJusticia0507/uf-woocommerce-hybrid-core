/**
 * XIV QRIS Gateway - admin (media library picker + preview).
 */
(function ($) {
  'use strict';

  function renderPreview($input, value) {
    var $field = $input.closest('td.forminp-image');
    if (!value) {
      $field.find('.xiv-qris-preview').remove();
      return;
    }
    if (!$field.find('.xiv-qris-preview').length) {
      $field.append(
        '<div class="xiv-qris-preview" style="margin-top:8px;max-width:220px;">' +
        '<img style="width:100%;border:1px solid #dcdcde;border-radius:4px;" />' +
        '</div>'
      );
    }
    $field.find('.xiv-qris-preview img').attr('src', value);
  }

  $(document).on('click', '.xiv-qris-image-upload', function (e) {
    e.preventDefault();

    var $target = $('#' + $(this).data('target'));
    var frame = wp.media({
      title: 'Pilih QR Code',
      multiple: false,
      library: { type: 'image' }
    });

    frame.on('select', function () {
      var attachment = frame.state().get('selection').first().toJSON();
      $target.val(attachment.url).trigger('change');
    });

    frame.open();
  });

  $(document).on('change', '.xiv-qris-image-input', function () {
    renderPreview($(this), $(this).val());
  });
})(jQuery);
