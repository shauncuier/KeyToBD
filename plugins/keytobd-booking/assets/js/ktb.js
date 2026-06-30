/* KeyToBD Booking — front-end. 3s-Soft */
(function () {
	'use strict';

	var QTY_LABELS = {
		tour: 'Travellers', hotel: 'Rooms', car: 'Vehicles', ship: 'Seats', houseboat: 'Guests'
	};

	function ready(fn) {
		if (document.readyState !== 'loading') { fn(); }
		else { document.addEventListener('DOMContentLoaded', fn); }
	}

	function post(action, data) {
		var body = new URLSearchParams();
		body.append('action', action);
		body.append('nonce', KTB.nonce);
		Object.keys(data).forEach(function (k) { body.append(k, data[k]); });
		return fetch(KTB.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString()
		}).then(function (r) { return r.json(); });
	}

	function selectedOption(form) {
		var sel = form.querySelector('[name="service_id"]');
		if (!sel) { return null; }
		if (sel.tagName === 'SELECT') { return sel.options[sel.selectedIndex] || null; }
		return sel; // hidden input on locked forms
	}

	function meta(form) {
		var opt = selectedOption(form);
		if (!opt) { return { id: 0, type: 'tour', range: false }; }
		return {
			id: parseInt(opt.value || opt.getAttribute('value'), 10) || 0,
			type: opt.getAttribute('data-type') || 'tour',
			range: opt.getAttribute('data-range') === '1'
		};
	}

	function initForm(form) {
		var msg = form.querySelector('[data-ktb-msg]');
		var summary = form.querySelector('[data-ktb-summary]');
		var totalEl = form.querySelector('[data-ktb-total]');
		var availEl = form.querySelector('[data-ktb-avail]');
		var rangeField = form.querySelector('[data-ktb-range]');
		var qtyLabel = form.querySelector('[data-ktb-qty-label]');
		var endInput = form.querySelector('[name="date_end"]');

		function syncType() {
			var m = meta(form);
			if (rangeField) {
				rangeField.hidden = !m.range;
				if (endInput) { endInput.disabled = !m.range; }
			}
			if (qtyLabel) {
				qtyLabel.firstChild.textContent = (QTY_LABELS[m.type] || 'Quantity') + ' ';
			}
			refresh();
		}

		function values() {
			var coupon = form.querySelector('[name="coupon"]');
			return {
				service_id: meta(form).id,
				date: (form.querySelector('[name="date"]') || {}).value || '',
				date_end: (endInput && !endInput.disabled ? endInput.value : '') || '',
				qty: (form.querySelector('[name="qty"]') || {}).value || 1,
				coupon: coupon ? coupon.value : ''
			};
		}

		var t;
		function refresh() {
			var v = values();
			if (!v.service_id || !v.date) { if (summary) { summary.hidden = true; } return; }
			clearTimeout(t);
			t = setTimeout(function () {
				post('ktb_availability', v).then(function (res) {
					if (!summary) { return; }
					if (res && res.success) {
						summary.hidden = false;
							var d = res.data;
							totalEl.textContent = d.total_fmt;
							var note = "";
							if (d.notice) { note = d.notice; availEl.className = "ktb-summary__avail bad"; }
							else if (d.coupon_valid === false) { note = "Invalid coupon"; availEl.className = "ktb-summary__avail bad"; }
							else if (!d.available) { note = KTB.i18n.unavailable; availEl.className = "ktb-summary__avail bad"; }
							else {
								var bits = [];
								if (d.coupon_valid === true && d.coupon_label) { bits.push(d.coupon_label); }
								if (d.deposit_fmt) { bits.push("Deposit " + d.deposit_fmt); }
								if (d.remaining !== null) { bits.push(d.remaining + " left"); }
								note = bits.join(" · ");
								availEl.className = "ktb-summary__avail ok";
							}
							availEl.textContent = note;
					}
				}).catch(function () {});
			}, 250);
		}

		form.addEventListener('change', function (e) {
			if (e.target.name === 'service_id') { syncType(); }
			else if (['date', 'date_end', 'qty', 'coupon'].indexOf(e.target.name) > -1) { refresh(); }
		});

		// Keep end-date min in sync with start.
		var startInput = form.querySelector('[name="date"]');
		if (startInput && endInput) {
			startInput.addEventListener('change', function () { endInput.min = startInput.value; });
		}

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var data = {};
			['service_id', 'name', 'phone', 'email', 'date', 'date_end', 'qty', 'notes', 'coupon', 'ktb_website', 'ktb_t', 'cf-turnstile-response'].forEach(function (k) {
				var el = form.querySelector('[name="' + k + '"]');
				data[k] = el ? el.value : '';
			});
				var agreeEl = form.querySelector('[name="agree"]');
				data.agree = agreeEl ? (agreeEl.checked ? '1' : '') : '1';
			data.service_id = meta(form).id;
			if (endInput && endInput.disabled) { data.date_end = ''; }

			if (!data.service_id || !data.name || !data.phone || !data.date) {
				show(msg, KTB.i18n.required, 'bad');
				return;
			}

			var btn = form.querySelector('.ktb-submit');
			btn.disabled = true;
			var label = btn.textContent;
			btn.textContent = KTB.i18n.submitting;

			post('ktb_create_booking', data).then(function (res) {
				btn.disabled = false;
				btn.textContent = label;
				if (res && res.success) {
					if (res.data.pay_url) {
						window.location.href = res.data.pay_url;
						return;
					}
					form.reset();
					if (summary) { summary.hidden = true; }
						var okMsg = res.data.message + ' Ref: ' + res.data.ref;
						if (res.data.deposit_fmt) { okMsg += ' - Deposit due: ' + res.data.deposit_fmt; }
						show(msg, okMsg, 'ok');
				} else {
					show(msg, (res && res.data && res.data.message) || KTB.i18n.error, 'bad');
				}
			}).catch(function () {
				btn.disabled = false;
				btn.textContent = label;
				show(msg, KTB.i18n.error, 'bad');
			});
		});

		syncType();
	}

	function show(el, text, cls) {
		if (!el) { return; }
		el.hidden = false;
		el.textContent = text;
		el.className = 'ktb-msg ' + (cls || '');
	}

	function initLookup(form) {
		var msg = form.querySelector('[data-ktb-msg]');
		var out = form.querySelector('[data-ktb-lookup-result]');
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var ref = (form.querySelector('[name="ref"]') || {}).value || '';
			var phone = (form.querySelector('[name="phone"]') || {}).value || '';
			if (!ref || !phone) { show(msg, KTB.i18n.required, 'bad'); return; }
			var btn = form.querySelector('.ktb-submit');
			btn.disabled = true;
			post('ktb_lookup', { ref: ref, phone: phone }).then(function (res) {
				btn.disabled = false;
				if (res && res.success) {
					if (msg) { msg.hidden = true; }
					var d = res.data;
					out.hidden = false;
					out.innerHTML = '<div class="ktb-lookup__card"><strong>' + d.ref + '</strong> ' +
						'<span class="ktb-lk-status">' + d.status + '</span>' +
						'<ul><li>' + d.service + '</li>' +
						'<li>' + d.date + ' &middot; ' + d.qty + ' pax</li>' +
						'<li>' + d.total + ' &middot; ' + d.paid + '</li></ul></div>';
				} else {
					out.hidden = true;
					show(msg, (res && res.data && res.data.message) || KTB.i18n.error, 'bad');
				}
			}).catch(function () { btn.disabled = false; show(msg, KTB.i18n.error, 'bad'); });
		});
	}

	ready(function () {
		document.querySelectorAll('[data-ktb-form]').forEach(initForm);
		document.querySelectorAll('[data-ktb-lookup]').forEach(initLookup);
	});
})();
