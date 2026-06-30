/* KeyToBD Booking — advanced dynamic services archive. 3s-Soft */
(function () {
	'use strict';

	var root = document.querySelector('[data-ktb-archive]');
	if (!root || typeof KTB === 'undefined') { return; }

	var resultsEl = root.querySelector('[data-ktb-results]');
	var countEl   = root.querySelector('[data-ktb-count]');
	var pillsEl   = root.querySelector('[data-ktb-pills]');
	var moreBtn   = root.querySelector('[data-ktb-more]');
	var typeInput = root.querySelector('[data-ktb-filter="type"]');
	var chips     = Array.prototype.slice.call(root.querySelectorAll('.ktb-chip'));
	var bounds    = { min: parseInt(root.dataset.priceMin, 10) || 0, max: parseInt(root.dataset.priceMax, 10) || 0 };
	var action    = root.dataset.action || 'ktb_filter';

	var LABELS = {
		type: '', destination: 'Destination', q: 'Keyword', date: 'Date',
		sort: '', min: 'Min', max: 'Max'
	};

	function money(n) {
		return (KTB.currency || '') + Number(n).toLocaleString();
	}

	function controls() {
		return Array.prototype.slice.call(root.querySelectorAll('[data-ktb-filter]'));
	}

	function readParams() {
		var p = {};
		controls().forEach(function (el) {
			p[el.getAttribute('data-ktb-filter')] = el.value;
		});
		return p;
	}

	function isDefault(key, val) {
		if (!val) { return true; }
		if (key === 'sort' && val === 'newest') { return true; }
		if (key === 'min' && parseInt(val, 10) <= bounds.min) { return true; }
		if (key === 'max' && parseInt(val, 10) >= bounds.max) { return true; }
		return false;
	}

	/* ---------- Dual range slider ---------- */
	function initRange() {
		var wrap = root.querySelector('[data-ktb-range]');
		if (!wrap) { return null; }
		var lo = wrap.querySelector('.ktb-range__min');
		var hi = wrap.querySelector('.ktb-range__max');
		var fill = wrap.querySelector('[data-range-fill]');
		var loOut = root.querySelector('[data-range-lo]');
		var hiOut = root.querySelector('[data-range-hi]');
		var span = bounds.max - bounds.min || 1;

		function paint() {
			var a = Math.min(+lo.value, +hi.value);
			var b = Math.max(+lo.value, +hi.value);
			if (+lo.value > +hi.value) { lo.value = a; hi.value = b; }
			fill.style.left = ((a - bounds.min) / span * 100) + '%';
			fill.style.right = (100 - (b - bounds.min) / span * 100) + '%';
			loOut.textContent = money(a);
			hiOut.textContent = money(b);
		}
		lo.addEventListener('input', paint);
		hi.addEventListener('input', paint);
		paint();
		return paint;
	}
	var paintRange = initRange();

	/* ---------- Active filter pills ---------- */
	function renderPills(p) {
		if (!pillsEl) { return; }
		pillsEl.innerHTML = '';
		Object.keys(p).forEach(function (key) {
			if (isDefault(key, p[key]) || key === 'sort') { return; }
			var text;
			if (key === 'type') {
				var chip = chips.filter(function (c) { return c.dataset.type === p.type; })[0];
				text = chip ? chip.textContent.trim() : p.type;
			} else if (key === 'destination') {
				var opt = root.querySelector('[data-ktb-filter="destination"] option[value="' + p.destination + '"]');
				text = opt ? opt.textContent : p.destination;
			} else if (key === 'min') { text = 'Min ' + money(p.min); }
			else if (key === 'max') { text = 'Max ' + money(p.max); }
			else if (key === 'date') { text = 'On ' + p.date; }
			else if (key === 'q') { text = '“' + p.q + '”'; }
			else { text = p[key]; }

			var pill = document.createElement('button');
			pill.type = 'button';
			pill.className = 'ktb-pill';
			pill.innerHTML = '<span>' + text + '</span><i aria-hidden="true">×</i>';
			pill.setAttribute('aria-label', 'Remove filter');
			pill.addEventListener('click', function () { clearKey(key); });
			pillsEl.appendChild(pill);
		});
	}

	function clearKey(key) {
		if (key === 'type') { setType(''); }
		else if (key === 'min') { setRange(bounds.min, null); }
		else if (key === 'max') { setRange(null, bounds.max); }
		else {
			var el = root.querySelector('[data-ktb-filter="' + key + '"]');
			if (el) { el.value = ''; }
		}
		apply();
	}

	function setType(val) {
		if (typeInput) { typeInput.value = val; }
		chips.forEach(function (c) { c.classList.toggle('is-active', c.dataset.type === val); });
	}

	function setRange(lo, hi) {
		var loEl = root.querySelector('.ktb-range__min');
		var hiEl = root.querySelector('.ktb-range__max');
		if (loEl && lo !== null) { loEl.value = lo; }
		if (hiEl && hi !== null) { hiEl.value = hi; }
		if (paintRange) { paintRange(); }
	}

	/* ---------- URL sync ---------- */
	function pushUrl(p) {
		var qs = new URLSearchParams();
		Object.keys(p).forEach(function (key) {
			if (!isDefault(key, p[key])) {
				qs.set(key === 'type' ? 'ktb_type' : (key === 'q' ? 'ktb_q' : key), p[key]);
			}
		});
		var url = location.pathname + (qs.toString() ? '?' + qs.toString() : '');
		history.replaceState(p, '', url);
	}

	/* ---------- AJAX ---------- */
	var timer;
	function debounce(fn, ms) {
		clearTimeout(timer);
		timer = setTimeout(fn, ms);
	}

	function request(p, paged, append) {
		var body = new URLSearchParams();
		body.append('action', action);
		body.append('nonce', KTB.nonce);
		Object.keys(p).forEach(function (k) {
			body.append(k === 'type' ? 'ktb_type' : (k === 'q' ? 'ktb_q' : k), p[k]);
		});
		body.append('paged', paged || 1);

		root.classList.add('is-loading');
		resultsEl.setAttribute('aria-busy', 'true');
		if (!append) { showSkeleton(); }

		return fetch(KTB.ajaxUrl, {
			method: 'POST', credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString()
		}).then(function (r) { return r.json(); }).then(function (res) {
			root.classList.remove('is-loading');
			resultsEl.setAttribute('aria-busy', 'false');
			if (!res || !res.success) { return; }
			var d = res.data;
			if (append) {
				resultsEl.insertAdjacentHTML('beforeend', d.html);
			} else {
				resultsEl.innerHTML = d.html || '<p class="ktb-empty">' + (KTB.i18n ? '' : '') + 'No services match your filters.</p>';
			}
			if (countEl) { countEl.textContent = d.count_fmt; }
			if (moreBtn) {
				moreBtn.dataset.paged = d.paged;
				moreBtn.dataset.max = d.max_pages;
				moreBtn.hidden = (d.paged >= d.max_pages);
			}
		}).catch(function () {
			root.classList.remove('is-loading');
			resultsEl.setAttribute('aria-busy', 'false');
		});
	}

	function apply() {
		var p = readParams();
		renderPills(p);
		pushUrl(p);
		if (moreBtn) { moreBtn.dataset.paged = 1; }
		request(p, 1, false);
	}

	/* ---------- Events ---------- */
	chips.forEach(function (chip) {
		chip.addEventListener('click', function () { setType(chip.dataset.type); apply(); });
	});

	controls().forEach(function (el) {
		var key = el.getAttribute('data-ktb-filter');
		if (key === 'type') { return; }
		var evt = (el.type === 'search' || el.type === 'text') ? 'input' : 'change';
		el.addEventListener(evt, function () {
			if (evt === 'input') { debounce(apply, 350); } else { apply(); }
		});
	});

	var resetBtn = root.querySelector('[data-ktb-reset]');
	if (resetBtn) {
		resetBtn.addEventListener('click', function () {
			setType('');
			setRange(bounds.min, bounds.max);
			controls().forEach(function (el) {
				var key = el.getAttribute('data-ktb-filter');
				if (key === 'sort') { el.value = 'newest'; }
				else if (key === 'destination' || key === 'q' || key === 'date') { el.value = ''; }
			});
			apply();
		});
	}

	if (moreBtn) {
		moreBtn.addEventListener('click', function () {
			var next = (parseInt(moreBtn.dataset.paged, 10) || 1) + 1;
			request(readParams(), next, true);
		});
	}

	/* Skeleton placeholders while loading */
	function showSkeleton() {
		var n = parseInt(root.dataset.perPage, 10) || 6;
		var card = '<div class="tour-card ktb-skeleton"><div class="ktb-skeleton__img"></div><div class="ktb-skeleton__line"></div><div class="ktb-skeleton__line sm"></div></div>';
		var html = '';
		for (var i = 0; i < Math.min(n, 6); i++) { html += card; }
		resultsEl.innerHTML = html;
	}

	/* Mobile filter drawer */
	(function drawer() {
		var bar = root.querySelector('[data-ktb-filterbar]');
		var openB = root.querySelector('[data-ktb-filter-open]');
		var closeB = root.querySelector('[data-ktb-filter-close]');
		if (!bar || !openB) { return; }
		var back = document.createElement('div');
		back.className = 'ktb-filter-backdrop';
		root.appendChild(back);
		function open() { bar.classList.add('is-open'); back.classList.add('is-open'); document.body.style.overflow = 'hidden'; }
		function close() { bar.classList.remove('is-open'); back.classList.remove('is-open'); document.body.style.overflow = ''; }
		openB.addEventListener('click', open);
		if (closeB) { closeB.addEventListener('click', close); }
		back.addEventListener('click', close);
		// Close drawer after a filter is applied on mobile.
		root.addEventListener('click', function (e) {
			if (e.target.closest && e.target.closest('.ktb-chip') && window.matchMedia('(max-width:900px)').matches) {
				setTimeout(close, 150);
			}
		});
	})();

	// Keep pills in sync on first load.
	renderPills(readParams());
})();
