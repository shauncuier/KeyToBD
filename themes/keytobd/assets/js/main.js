/* KeyToBD theme scripts — 3s-Soft */
(function () {
	'use strict';

	var doc = document;

	function ready(fn) {
		if (doc.readyState !== 'loading') { fn(); }
		else { doc.addEventListener('DOMContentLoaded', fn); }
	}

	ready(function () {
		stickyHeader();
		mobileNav();
		searchTabs();
		faqAccordion();
		scrollReveal();
		wishlist();
	});

	/* Wishlist hearts — persisted in localStorage, event-delegated so AJAX-loaded cards work */
	function wishlist() {
		var KEY = 'ktb_wishlist';
		var saved;
		try { saved = JSON.parse(localStorage.getItem(KEY) || '[]'); } catch (e) { saved = []; }

		function mark() {
			doc.querySelectorAll('.tour-card').forEach(function (card) {
				var link = card.querySelector('h3 a');
				var fav = card.querySelector('.tour-card__fav');
				if (link && fav && saved.indexOf(link.getAttribute('href')) > -1) {
					fav.classList.add('is-saved');
				}
			});
		}
		mark();

		doc.addEventListener('click', function (e) {
			var fav = e.target.closest ? e.target.closest('.tour-card__fav') : null;
			if (!fav) { return; }
			e.preventDefault();
			var card = fav.closest('.tour-card');
			var link = card && card.querySelector('h3 a');
			if (!link) { return; }
			var href = link.getAttribute('href');
			var i = saved.indexOf(href);
			if (i > -1) { saved.splice(i, 1); fav.classList.remove('is-saved'); }
			else { saved.push(href); fav.classList.add('is-saved'); }
			try { localStorage.setItem(KEY, JSON.stringify(saved)); } catch (e2) {}
		});

		// Re-mark after AJAX archive updates.
		var results = doc.querySelector('[data-ktb-results]');
		if (results && 'MutationObserver' in window) {
			new MutationObserver(mark).observe(results, { childList: true });
		}
	}

	/* Add shadow to header on scroll */
	function stickyHeader() {
		var header = doc.querySelector('.site-header[data-sticky]');
		if (!header) { return; }
		var onScroll = function () {
			header.classList.toggle('is-scrolled', window.scrollY > 8);
		};
		window.addEventListener('scroll', onScroll, { passive: true });
		onScroll();
	}

	/* Mobile drawer open/close */
	function mobileNav() {
		var toggle = doc.querySelector('.nav-toggle');
		var drawer = doc.getElementById('mobile-nav');
		var overlay = doc.querySelector('.nav-overlay');
		var closeBtn = doc.querySelector('.nav-close');
		if (!toggle || !drawer || !overlay) { return; }

		function open() {
			drawer.classList.add('is-open');
			overlay.classList.add('is-open');
			toggle.setAttribute('aria-expanded', 'true');
			doc.body.style.overflow = 'hidden';
		}
		function close() {
			drawer.classList.remove('is-open');
			overlay.classList.remove('is-open');
			toggle.setAttribute('aria-expanded', 'false');
			doc.body.style.overflow = '';
		}
		toggle.addEventListener('click', open);
		overlay.addEventListener('click', close);
		if (closeBtn) { closeBtn.addEventListener('click', close); }
		doc.addEventListener('keydown', function (e) { if (e.key === 'Escape') { close(); } });
	}

	/* Hero search widget tab switching */
	function searchTabs() {
		var widget = doc.querySelector('.search-widget');
		if (!widget) { return; }
		var tabs = widget.querySelectorAll('.search-tab');
		var panels = widget.querySelectorAll('.search-panel');

		tabs.forEach(function (tab) {
			tab.addEventListener('click', function () {
				var target = tab.getAttribute('data-target');
				tabs.forEach(function (t) {
					var active = t === tab;
					t.classList.toggle('is-active', active);
					t.setAttribute('aria-selected', active ? 'true' : 'false');
				});
				panels.forEach(function (p) {
					p.classList.toggle('is-active', p.getAttribute('data-panel') === target);
				});
			});
		});
	}

	/* FAQ accordion */
	function faqAccordion() {
		var items = doc.querySelectorAll('.faq-item');
		items.forEach(function (item) {
			var q = item.querySelector('.faq-q');
			if (!q) { return; }
			q.setAttribute('aria-expanded', 'false');
			q.addEventListener('click', function () {
				var open = item.classList.toggle('is-open');
				q.setAttribute('aria-expanded', open ? 'true' : 'false');
			});
		});
	}

	/* Reveal-on-scroll */
	function scrollReveal() {
		var els = doc.querySelectorAll('.reveal');
		if (!els.length || !('IntersectionObserver' in window)) {
			els.forEach(function (el) { el.classList.add('is-visible'); });
			return;
		}
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (entry.isIntersecting) {
					entry.target.classList.add('is-visible');
					io.unobserve(entry.target);
				}
			});
		}, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
		els.forEach(function (el) { io.observe(el); });
	}
})();
