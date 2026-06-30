/* KeyToBD branded auth — tabs, show/hide password, strength meter, Google. 3s-Soft */
(function () {
	'use strict';
	var doc = document;

	function ready(fn) {
		if (doc.readyState !== 'loading') { fn(); } else { doc.addEventListener('DOMContentLoaded', fn); }
	}

	ready(function () {
		var root = doc.querySelector('[data-ktb-auth]');
		if (root) {
			tabs(root);
			passwordToggles(root);
			strengthMeter(root);
		}
		google(); // works on the auth card even within dashboards
	});

	/* Tab switching (login / register / forgot) */
	function tabs(root) {
		var btns = root.querySelectorAll('[data-auth-tab]');
		var panels = root.querySelectorAll('[data-auth-panel]');
		var tabbtns = root.querySelectorAll('.ktb-auth__tab');

		function show(name) {
			panels.forEach(function (p) { p.classList.toggle('is-active', p.getAttribute('data-auth-panel') === name); });
			tabbtns.forEach(function (t) { t.classList.toggle('is-active', t.getAttribute('data-auth-tab') === name); });
		}
		btns.forEach(function (b) {
			b.addEventListener('click', function () { show(b.getAttribute('data-auth-tab')); });
		});
	}

	/* Show / hide password */
	function passwordToggles(root) {
		root.querySelectorAll('[data-pw-toggle]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var input = btn.parentNode.querySelector('input');
				if (!input) { return; }
				var show = input.type === 'password';
				input.type = show ? 'text' : 'password';
				btn.classList.toggle('is-on', show);
				btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
			});
		});
	}

	/* Password strength meter */
	function strengthMeter(root) {
		var input = root.querySelector('[data-pw-meter]');
		var bar = root.querySelector('[data-pw-bar]');
		if (!input || !bar) { return; }
		var fill = bar.querySelector('span');
		input.addEventListener('input', function () {
			var v = input.value, score = 0;
			if (v.length >= 8) { score++; }
			if (/[A-Z]/.test(v) && /[a-z]/.test(v)) { score++; }
			if (/\d/.test(v)) { score++; }
			if (/[^A-Za-z0-9]/.test(v)) { score++; }
			var pct = (score / 4) * 100;
			fill.style.width = pct + '%';
			bar.className = 'ktb-pw__bar s' + score;
		});
	}

	/* Google Identity Services */
	function google() {
		var holder = doc.querySelector('[data-ktb-google]');
		if (!holder || typeof KeyToBDAuth === 'undefined' || !KeyToBDAuth.clientId) { return; }
		var tries = 0;
		(function wait() {
			if (window.google && google.accounts && google.accounts.id) {
				google.accounts.id.initialize({
					client_id: KeyToBDAuth.clientId,
					callback: function (resp) { sendCredential(resp.credential, holder.getAttribute('data-redirect') || ''); }
				});
				google.accounts.id.renderButton(holder, { theme: 'outline', size: 'large', width: 320, text: 'continue_with' });
			} else if (tries++ < 40) {
				setTimeout(wait, 100);
			}
		})();
	}

	function sendCredential(credential, redirect) {
		var body = new URLSearchParams();
		body.append('action', 'ktb_google_login');
		body.append('nonce', KeyToBDAuth.nonce);
		body.append('credential', credential);
		body.append('redirect_to', redirect);
		fetch(KeyToBDAuth.ajaxUrl, {
			method: 'POST', credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString()
		}).then(function (r) { return r.json(); }).then(function (res) {
			if (res && res.success && res.data && res.data.redirect) {
				window.location.href = res.data.redirect;
			} else {
				alert((res && res.data && res.data.message) || 'Google sign-in failed.');
			}
		}).catch(function () { alert('Google sign-in failed.'); });
	}
})();
