(() => {
	'use strict';
	const header = document.querySelector('.site-header');
	if (!header) return;
	const root = document.documentElement;
	const toggle = header.querySelector('.site-header__toggle');
	const navigation = document.querySelector('#primary-navigation');
	const mobile = matchMedia('(max-width: 63.999rem)');
	const setOpen = open => {
		if (!toggle || !navigation) return;
		toggle.setAttribute('aria-expanded', String(open));
		toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
		navigation.hidden = mobile.matches && !open;
	};
	if (toggle && navigation) {
		toggle.hidden = false;
		const configureMenu = () => {
			if (mobile.matches && header.classList.contains('site-header--homepage')) {
				const target = header.querySelector('[data-header-navigation-target]');
				if (target && !header.contains(navigation)) {
					const placeholder = document.createElement('div');
					placeholder.className = 'editorial-grid__cell editorial-grid__navigation';
					placeholder.setAttribute('data-home-nav-origin', '');
					placeholder.setAttribute('aria-hidden', 'true');
					navigation.replaceWith(placeholder);
					target.append(navigation);
					navigation.className = 'site-header__nav';
					navigation.querySelector('ul').className = 'site-header__menu';
				}
			}
			setOpen(false);
		};
		toggle.addEventListener('click', () => setOpen(toggle.getAttribute('aria-expanded') !== 'true'));
		header.addEventListener('keydown', event => {
			if (event.key === 'Escape' && mobile.matches && toggle.getAttribute('aria-expanded') === 'true') {
				setOpen(false);
				toggle.focus();
			}
		});
		navigation.addEventListener('click', event => { if (event.target.closest('a') && mobile.matches) setOpen(false); });
		if (mobile.addEventListener) mobile.addEventListener('change', configureMenu);
		configureMenu();
	}
	let frame = 0;
	let last = '';
	const measure = () => {
		frame = 0;
		const height = mobile.matches ? 0 : header.getBoundingClientRect().height;
		const top = mobile.matches ? 0 : parseFloat(getComputedStyle(header).top) || 0;
		const value = `${height}:${top}`;
		if (value === last) return;
		last = value;
		root.style.setProperty('--site-header-height', `${height}px`);
		root.style.setProperty('--site-header-top', `${top}px`);
		window.dispatchEvent(new Event('john-platt:header-resize'));
	};
	const schedule = () => { if (!frame) frame = requestAnimationFrame(measure); };
	if ('ResizeObserver' in window) new ResizeObserver(schedule).observe(header);
	window.addEventListener('resize', schedule, { passive: true });
	window.addEventListener('pageshow', schedule);
	measure();
})();