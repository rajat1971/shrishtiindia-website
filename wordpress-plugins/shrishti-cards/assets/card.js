/* Shrishti Smart Card: copy, share, and the optional "say hello" WhatsApp message. Nothing is sent to this website. */
(function () {
  var toast = document.getElementById('toast'), tt;
  function say(msg) {
    if (!toast) return;
    toast.textContent = msg; toast.classList.add('on');
    clearTimeout(tt); tt = setTimeout(function () { toast.classList.remove('on'); }, 1800);
  }
  function copy(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(text).then(function () { say('Copied'); }, function () { say('Copy blocked here. Select the text instead.'); });
    } else { say('Copy blocked here. Select the text instead.'); }
  }
  Array.prototype.forEach.call(document.querySelectorAll('button.copy[data-copy]'), function (b) {
    b.addEventListener('click', function () { copy(b.getAttribute('data-copy')); });
  });

  var share = document.getElementById('share');
  if (share && navigator.share) {
    share.hidden = false;
    share.addEventListener('click', function () {
      navigator.share({ title: share.getAttribute('data-title'), url: share.getAttribute('data-url') }).catch(function () {});
    });
  }

  var form = document.getElementById('hello');
  if (!form) return;
  var err = document.getElementById('hello-err');
  function val(name) { return (form.elements[name].value || '').trim().replace(/\s+/g, ' '); }
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    err.hidden = true;
    var mobile = form.elements.mobile;
    var digits = val('mobile').replace(/\D/g, '');
    mobile.setCustomValidity(digits.length >= 8 && digits.length <= 15 ? '' : 'Please enter a valid mobile number.');
    if (!form.checkValidity()) { form.reportValidity(); return; }

    var lines = [
      (form.getAttribute('data-greeting') || 'Hello,'),
      '',
      'I would like to stay in touch. My details:',
      'Name: ' + val('first') + ' ' + val('last')
    ];
    if (val('company')) lines.push('Company: ' + val('company'));
    lines.push('Mobile: ' + val('mobile'));
    if (val('email')) lines.push('Email: ' + val('email'));
    lines.push('', '(Sent from your Shrishti visiting card)');

    var a = document.createElement('a');
    a.href = 'https://wa.me/' + form.getAttribute('data-wa') + '?text=' + encodeURIComponent(lines.join('\n'));
    a.target = '_blank'; a.rel = 'noopener';
    document.body.appendChild(a); a.click(); a.remove();
    say('Opening WhatsApp…');
  });
})();
