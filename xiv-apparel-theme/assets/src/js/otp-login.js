(function () {
  const panel = document.getElementById('xiv-otp-panel');
  if (!panel || typeof XIV_OTP === 'undefined') return;

  const tabs = document.querySelectorAll('.xiv-otp-tab');
  const passwordForm = document.querySelector('.woocommerce-form-login');
  const phoneInput = document.getElementById('xiv_otp_phone');
  const codeRow = panel.querySelector('.xiv-otp-code-row');
  const sendBtn = panel.querySelector('.xiv-otp-send');
  const verifyBtn = panel.querySelector('button[name="verify_otp"]');
  const statusEl = panel.querySelector('.xiv-otp-status');
  const codeInput = document.getElementById('xiv_otp_code');

  let cooldownUntil = 0;

  function setStatus(msg, isError) {
    statusEl.textContent = msg || '';
    statusEl.classList.toggle('xiv-text-red-600', !!isError);
    statusEl.classList.toggle('xiv-text-xiv-gray-text', !isError);
  }

  function setSendText(text) {
    sendBtn.innerHTML = text;
  }

  function resetTimer() {
    const tick = () => {
      const left = cooldownUntil - Date.now();
      if (left <= 0) {
        setSendText(XIV_OTP.i18n.sent);
        sendBtn.disabled = false;
        return;
      }
      setSendText(XIV_OTP.i18n.sent + ' (' + Math.ceil(left / 1000) + 's)');
      setTimeout(tick, 1000);
    };
    tick();
  }

  tabs.forEach((tab) => {
    tab.addEventListener('click', () => {
      const isOtp = tab.dataset.panel === 'otp';
      tabs.forEach((t) => {
        const active = t === tab;
        t.classList.toggle('xiv-otp-tab--active', active);
        t.classList.toggle('xiv-bg-xiv-black', active);
        t.classList.toggle('xiv-text-white', active);
        t.classList.toggle('xiv-text-xiv-gray-text', !active);
        t.setAttribute('aria-selected', active ? 'true' : 'false');
      });
      panel.classList.toggle('xiv-hidden', !isOtp);
      if (passwordForm) passwordForm.classList.toggle('xiv-hidden', isOtp);
    });
  });

  sendBtn.addEventListener('click', () => {
    const phone = phoneInput.value.trim();
    if (!phone) {
      setStatus('Nomor HP wajib diisi.', true);
      return;
    }
    if (cooldownUntil > Date.now()) return;

    setSendText(XIV_OTP.i18n.sending);
    sendBtn.disabled = true;
    setStatus('');

    const body = new FormData();
    body.append('action', 'xiv_otp_send');
    body.append('nonce', XIV_OTP.nonce);
    body.append('phone', phone);

    fetch(XIV_OTP.ajaxUrl, { method: 'POST', credentials: 'same-origin', body })
      .then((r) => r.json())
      .then((res) => {
        if (!res.success) {
          setStatus(res.data.message, true);
          setSendText(XIV_OTP.i18n.sent);
          sendBtn.disabled = false;
          return;
        }
        codeRow.classList.remove('xiv-hidden');
        codeInput.focus();
        setStatus(res.data.message);
        cooldownUntil = Date.now() + 60000;
        resetTimer();
        if (res.data.dev_code) {
          codeInput.value = res.data.dev_code;
          setStatus('DEV: ' + res.data.message + ' (kode: ' + res.data.dev_code + ')');
        }
      })
      .catch(() => {
        setStatus('Gagal mengirim. Coba lagi.', true);
        setSendText(XIV_OTP.i18n.sent);
        sendBtn.disabled = false;
      });
  });

  panel.querySelector('.xiv-otp-form').addEventListener('submit', (e) => {
    e.preventDefault();
    if (!codeInput.value.trim()) {
      setStatus('Masukkan kode OTP.', true);
      return;
    }

    verifyBtn.disabled = true;
    verifyBtn.textContent = XIV_OTP.i18n.verifying;
    setStatus('');

    const body = new FormData();
    body.append('action', 'xiv_otp_verify');
    body.append('nonce', XIV_OTP.nonce);
    body.append('phone', phoneInput.value.trim());
    body.append('code', codeInput.value.trim());

    fetch(XIV_OTP.ajaxUrl, { method: 'POST', credentials: 'same-origin', body })
      .then((r) => r.json())
      .then((res) => {
        if (!res.success) {
          setStatus(res.data.message, true);
          verifyBtn.disabled = false;
          verifyBtn.textContent = 'Login dengan OTP';
          return;
        }
        window.location.href = res.data.redirect || '/my-account/';
      })
      .catch(() => {
        setStatus('Gagal verifikasi. Coba lagi.', true);
        verifyBtn.disabled = false;
        verifyBtn.textContent = 'Login dengan OTP';
      });
  });
})();
