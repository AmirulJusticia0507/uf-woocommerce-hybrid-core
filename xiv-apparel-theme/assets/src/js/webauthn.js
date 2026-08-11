/* XIV Apparel - WebAuthn / Passkey (fingerprint / Face ID). */
(function () {
	'use strict';

	if (!window.XIV_WKN) return;

	var cfg = window.XIV_WKN;

	function b64uEncode(buf) {
		var bytes = new Uint8Array(buf);
		var s = '';
		for (var i = 0; i < bytes.length; i += 0x8000) {
			s += String.fromCharCode.apply(null, bytes.subarray(i, i + 0x8000));
		}
		return btoa(s).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
	}

	function b64uDecode(str) {
		str = str.replace(/-/g, '+').replace(/_/g, '/');
		while (str.length % 4) str += '=';
		var bin = atob(str);
		var bytes = new Uint8Array(bin.length);
		for (var i = 0; i < bin.length; i++) bytes[i] = bin.charCodeAt(i);
		return bytes;
	}

	function post(action, body) {
		var payload = Object.assign({ action: action, nonce: cfg.nonce }, body || {});
		return fetch(cfg.ajaxUrl + '?action=' + encodeURIComponent(action), {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify(payload),
		})
			.then(function (res) { return res.json(); })
			.then(function (data) {
				if (!data || typeof data.success === 'undefined') throw new Error('Invalid response');
				return data;
			});
	}

	function credToPlain(cred) {
		return {
			id: cred.id,
			rawId: b64uEncode(cred.rawId),
			type: cred.type,
			response: {
				clientDataJSON: b64uEncode(cred.response.clientDataJSON),
			},
		};
	}

	function deviceName() {
		var ua = navigator.userAgent;
		if (/Android/i.test(ua)) return 'Android';
		if (/iPhone|iPad/i.test(ua)) return 'iPhone / iPad';
		if (/Mac/i.test(ua)) return 'Mac';
		if (/Windows/i.test(ua)) return 'Windows';
		if (/Linux/i.test(ua)) return 'Linux';
		return 'Perangkat biometrik';
	}

	function setStatus(el, msg) {
		if (el) el.textContent = msg;
	}

	function renderDevices(devices) {
		var list = document.querySelector('.xiv-wkn-section .xiv-wkn-devices');
		if (!list) return;
		list.innerHTML = '';

		if (!devices.length) {
			var empty = document.createElement('li');
			empty.className = 'xiv-wkn-devices-empty xiv-text-xiv-gray-text';
			empty.textContent = 'Belum ada perangkat terdaftar.';
			list.appendChild(empty);
			return;
		}

		devices.forEach(function (d) {
			var li = document.createElement('li');
			li.className = 'xiv-wkn-device xiv-flex xiv-items-center xiv-justify-between xiv-gap-3 xiv-border xiv-border-xiv-gray-light xiv-px-3 xiv-py-2';
			li.dataset.id = d.id;

			var label = document.createElement('span');
			var name = document.createElement('span');
			name.className = 'xiv-font-bold';
			name.textContent = d.name;
			var meta = document.createElement('span');
			meta.className = 'xiv-text-xs xiv-text-xiv-gray-text xiv-block';
			meta.textContent = d.created;
			label.appendChild(name);
			label.appendChild(meta);

			var btn = document.createElement('button');
			btn.type = 'button';
			btn.className = 'xiv-wkn-delete xiv-text-xs xiv-font-bold xiv-uppercase xiv-tracking-widest xiv-text-xiv-gray-text hover:xiv-text-xiv-black';
			btn.textContent = cfg.i18n.delete;

			li.appendChild(label);
			li.appendChild(btn);
			list.appendChild(li);
		});
	}

	function registerDevice() {
		var scope = document.querySelector('.xiv-wkn-section');
		var status = scope ? scope.querySelector('.xiv-wkn-status') : null;
		var btn = scope ? scope.querySelector('.xiv-wkn-register') : null;
		if (!scope || !btn) return;

		btn.disabled = true;
		setStatus(status, cfg.i18n.registering);

		post('xiv_wkn_register_options', {})
			.then(function (r) {
				if (!r.success) throw new Error(r.data.message);
				return navigator.credentials.create(r.data.options).then(function (cred) {
					var plain = credToPlain(cred);
					plain.name = deviceName();
					plain.response.attestationObject = b64uEncode(cred.response.attestationObject);
					return post('xiv_wkn_register', Object.assign({ session: r.data.session }, plain));
				});
			})
			.then(function (r) {
				if (!r.success) throw new Error(r.data.message);
				renderDevices(r.data.devices);
				setStatus(status, cfg.i18n.registered);
			})
			.catch(function (e) {
				if (e.name === 'NotAllowedError' || e.name === 'AbortError') {
					setStatus(status, cfg.i18n.cancelled);
				} else {
					setStatus(status, (e.message || 'GAGAL').toUpperCase());
				}
			})
			.then(function () { btn.disabled = false; });
	}

	function loginWithBiometric() {
		var box = document.querySelector('.xiv-wkn-login');
		var status = box ? box.querySelector('.xiv-wkn-status') : null;
		var btn = box ? box.querySelector('.xiv-wkn-login-btn') : null;
		if (!box || !btn) return;

		btn.disabled = true;
		setStatus(status, cfg.i18n.waiting);

		post('xiv_wkn_login_options', {})
			.then(function (r) {
				if (!r.success) throw new Error(r.data.message);
				return navigator.credentials.get({ publicKey: r.data.options.publicKey }).then(function (cred) {
					var plain = credToPlain(cred);
					plain.response.authenticatorData = b64uEncode(cred.response.authenticatorData);
					plain.response.signature = b64uEncode(cred.response.signature);
					plain.session = r.data.session;
					return post('xiv_wkn_login_verify', plain);
				});
			})
			.then(function (r) {
				if (!r.success) throw new Error(r.data.message);
				window.location.href = r.data.redirect;
			})
			.catch(function (e) {
				if (e.name === 'NotAllowedError' || e.name === 'AbortError') {
					setStatus(status, cfg.i18n.cancelled);
				} else {
					setStatus(status, (e.message || 'LOGIN GAGAL').toUpperCase());
				}
			})
			.then(function () { if (btn) btn.disabled = false; });
	}

	function deleteDevice(id) {
		var scope = document.querySelector('.xiv-wkn-section');
		var status = scope ? scope.querySelector('.xiv-wkn-status') : null;
		if (!window.confirm(cfg.i18n.confirm)) return;
		post('xiv_wkn_delete', { id: id })
			.then(function (r) {
				if (!r.success) throw new Error(r.data.message);
				renderDevices(r.data.devices);
				setStatus(status, '');
			})
			.catch(function (e) { setStatus(status, (e.message || 'GAGAL').toUpperCase()); });
	}

	function bind() {
		var section = document.querySelector('.xiv-wkn-section');
		if (section) {
			var regBtn = section.querySelector('.xiv-wkn-register');
			if (regBtn) regBtn.addEventListener('click', registerDevice);
			section.addEventListener('click', function (e) {
				var del = e.target.closest('.xiv-wkn-delete');
				if (del) deleteDevice(del.closest('.xiv-wkn-device').dataset.id);
			});
		}
		var loginBtn = document.querySelector('.xiv-wkn-login-btn');
		if (loginBtn) loginBtn.addEventListener('click', loginWithBiometric);
	}

	function init() {
		var supported = window.PublicKeyCredential &&
			typeof window.PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable === 'function';
		if (!supported) return;

		window.PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable()
			.then(function (ok) {
				if (!ok) return;
				bind();
				var loginBox = document.querySelector('.xiv-wkn-login');
				if (loginBox) loginBox.classList.remove('xiv-hidden');
			})
			.catch(function () {});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
