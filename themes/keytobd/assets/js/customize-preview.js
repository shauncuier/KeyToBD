/* Live Customizer preview — colors update instantly. 3s-Soft */
(function () {
	'use strict';
	if (typeof wp === 'undefined' || !wp.customize) { return; }

	var vars = {
		kt_color_navy: '--navy',
		kt_color_blue: '--blue',
		kt_color_sky: '--sky',
		kt_color_accent: '--accent',
		kt_color_accent_dark: '--accent-dark',
		kt_color_teal: '--teal',
		kt_color_ink: '--ink'
	};

	Object.keys(vars).forEach(function (setting) {
		wp.customize(setting, function (value) {
			value.bind(function (to) {
				if (to) { document.documentElement.style.setProperty(vars[setting], to); }
			});
		});
	});

	wp.customize('blogname', function (value) {
		value.bind(function (to) {
			var el = document.querySelector('.site-logo, .site-title');
			if (el && !document.querySelector('.custom-logo')) { el.textContent = to; }
		});
	});
})();
