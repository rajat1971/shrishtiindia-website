/* Shrishti Smart Cards admin: media pickers and the email signature copy button. */
jQuery(function ($) {
  $(document).on('click', '.sc-image-pick', function (e) {
    e.preventDefault();
    var box = $(this).closest('.sc-image');
    var frame = wp.media({ title: 'Choose image', library: { type: 'image' }, multiple: false });
    frame.on('select', function () {
      var a = frame.state().get('selection').first().toJSON();
      var src = (a.sizes && a.sizes.thumbnail) ? a.sizes.thumbnail.url : a.url;
      box.find('input[type=hidden]').val(a.id);
      box.find('.sc-image-preview').attr('src', src).prop('hidden', false);
      box.find('.sc-image-clear').prop('hidden', false);
    });
    frame.open();
  });
  $(document).on('click', '.sc-image-clear', function (e) {
    e.preventDefault();
    var box = $(this).closest('.sc-image');
    box.find('input[type=hidden]').val('0');
    box.find('.sc-image-preview').attr('src', '').prop('hidden', true);
    $(this).prop('hidden', true);
  });

  $('.sc-sig-qr').on('change', function () {
    var qr = this.checked;
    $('.sc-sig').each(function () { this.hidden = ($(this).data('variant') === 'qr') !== qr; });
  });
  $('.sc-sig-copy').on('click', function () {
    var sig = $('.sc-sig').filter(function () { return !this.hidden; });
    var html = sig.find('.sc-sig-html').val();
    var text = sig.find('.sc-sig-preview')[0].innerText;
    var done = function () { $('.sc-sig-done').prop('hidden', false); };
    if (window.ClipboardItem && navigator.clipboard && navigator.clipboard.write) {
      navigator.clipboard.write([new ClipboardItem({
        'text/html': new Blob([html], { type: 'text/html' }),
        'text/plain': new Blob([text], { type: 'text/plain' })
      })]).then(done, fallback);
    } else { fallback(); }
    function fallback() {
      var range = document.createRange();
      range.selectNodeContents(sig.find('.sc-sig-preview')[0]);
      var s = window.getSelection(); s.removeAllRanges(); s.addRange(range);
      try { document.execCommand('copy'); done(); } catch (err) { /* user can press Ctrl+C on the selection */ }
    }
  });
});
