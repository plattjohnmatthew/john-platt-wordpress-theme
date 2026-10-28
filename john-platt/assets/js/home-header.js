(() => {
	'use strict';
	const breakpoint = matchMedia('(min-width: 64rem)');
	let dispose = null;
	const initialize = () => {
	const controller = new AbortController();
	const signal = controller.signal;
	const opening = document.querySelector('[data-header-expanded]');
	const header = document.querySelector('.site-header--homepage');
	const origin = opening ? opening.querySelector('.editorial-grid__identity') : null;
	if (!opening || !header || !origin) return;
	const sourceCell = opening.querySelector('[data-home-nav-origin]');
	const mobileNavigation = document.querySelector('[data-home-navigation]');
	if (!sourceCell || !mobileNavigation) return;
	if (sourceCell !== mobileNavigation) sourceCell.replaceWith(mobileNavigation);
	mobileNavigation.className = 'editorial-grid__cell editorial-grid__navigation';
	const nameLink = origin.querySelector('a');
	const identity = header.querySelector('.site-header__name');
	if (!nameLink || !identity) return;
	const reduced = matchMedia('(prefers-reduced-motion: reduce)');
	const desktop = matchMedia('(min-width: 64rem)');
	let nameGeometry = null;
	let slot = null;
	const measureIdentity = () => {
		const focused = document.activeElement === nameLink;
		origin.append(nameLink);
		nameLink.className = '';
		nameLink.style.fontSize = '';
		nameLink.style.lineHeight = '';
		nameLink.style.transform = '';
		origin.style.minHeight = '';
		const start = nameLink.getBoundingClientRect();
		const cell = origin.getBoundingClientRect();
		const startFont = parseFloat(getComputedStyle(nameLink).fontSize);
		origin.style.minHeight = `${cell.height}px`;
		if (!slot) {
			slot = document.createElement('span');
			slot.className = 'site-header__name-slot';
			identity.replaceWith(slot);
		}
		slot.style.width = '';
		nameLink.className = 'site-header__name';
		slot.append(nameLink);
		const end = nameLink.getBoundingClientRect();
		const endFont = parseFloat(getComputedStyle(nameLink).fontSize);
		slot.style.width = `${end.width}px`;
		nameGeometry = { x: start.left + scrollX, y: start.top + scrollY, endX: end.left, endY: end.top + Math.max(0, (end.height - endFont * .98) / 2), scale: endFont / startFont };
		nameLink.classList.add('home-name-link--moving');
		nameLink.style.fontSize = `${startFont}px`;
		nameLink.style.lineHeight = '.98';
		if (focused) nameLink.focus({ preventScroll: true });
	};
	const navigation = opening.querySelector('.editorial-grid__navigation');
	const navigationTarget = header.querySelector('[data-header-navigation-target]');
	const menu = navigation ? navigation.querySelector('ul') : null;
	let navigationOrigin = null;
	let navigationGeometry = null;
	const measureNavigation = () => {
		if (!navigation || !menu) return;
		const focused = navigation.contains(document.activeElement) ? document.activeElement : null;
		if (navigationOrigin) navigationOrigin.replaceWith(navigation);
		navigation.className = 'editorial-grid__cell editorial-grid__navigation';
		menu.className = 'editorial-grid__menu';
		navigation.style.transform = '';
		navigation.querySelectorAll('a').forEach(link => { link.style.transform = ''; });
		const start = navigation.getBoundingClientRect();
		const links = Array.from(navigation.querySelectorAll('a'));
		const starts = links.map(link => { const rect = link.getBoundingClientRect(); return { left: rect.left - start.left, top: rect.top - start.top, width: rect.width, height: rect.height }; });
		if (!navigationOrigin) navigationOrigin = document.createElement('div');
		navigationOrigin.className = 'editorial-grid__cell editorial-grid__navigation';
		navigationOrigin.setAttribute('aria-hidden', 'true');
		navigationOrigin.setAttribute('data-home-nav-origin', '');
		navigationOrigin.style.minHeight = `${start.height}px`;
		navigation.replaceWith(navigationOrigin);
		navigation.className = 'site-header__nav home-header-navigation';
		menu.className = 'site-header__menu';
		navigationTarget.append(navigation);
		const end = navigation.getBoundingClientRect();
		const ends = links.map(link => link.getBoundingClientRect());
		navigationGeometry = { x: start.left + scrollX, y: start.top + scrollY, end, links, starts, ends };
		if (focused) focused.focus({ preventScroll: true });
	};
	let geometry = null;
	let frame = 0;
	let lastProgress = null;
	let resizeFrame = 0;
	const renderers = [];
	const update = () => {
		frame = 0;
		if (!geometry) return;
		const progress = Math.max(0, Math.min(1, window.scrollY / geometry.end));
		if (progress === 1 && lastProgress === 1) return;
		lastProgress = progress;
		opening.style.setProperty('--header-progress', progress.toFixed(4));
		renderers.forEach(render => render(progress, geometry));
	};
	const schedule = () => { if (!frame) frame = requestAnimationFrame(update); };
	const measure = () => {
		resizeFrame = 0;
		lastProgress = null;
		measureNavigation();
		measureIdentity();
		const rect = origin.getBoundingClientRect();
		const headerRect = header.getBoundingClientRect();
		const openingRect = opening.getBoundingClientRect();
		const ruleStyle = getComputedStyle(origin);
		// Native borders may snap 1.5px to 1 CSS pixel; use those same painted widths.
		opening.style.setProperty('--header-grid-rule-width', ruleStyle.borderRightWidth || '1px');
		opening.style.setProperty('--header-grid-rule-height', ruleStyle.borderBottomWidth || '1px');
		header.style.setProperty('--header-surface-rule-width', getComputedStyle(header).borderBottomWidth || '1px');
		geometry = { end: Math.max(160, rect.top + window.scrollY + rect.height - headerRect.height), rowTop: rect.top + scrollY, rowHeight: rect.height, gridTop: openingRect.top + scrollY, headerBottom: headerRect.top + headerRect.height, dividers: [rect.left + rect.width, navigationGeometry ? navigationGeometry.x : 0] };
		// Apply geometry before a paint can expose the untransformed fixed elements.
		if (frame) cancelAnimationFrame(frame);
		update();
	};
	const resize = () => { if (!resizeFrame) resizeFrame = requestAnimationFrame(measure); };
	window.addEventListener('scroll', schedule, { passive: true, signal });
	window.addEventListener('resize', resize, { passive: true, signal });
	window.addEventListener('pageshow', resize, { signal });
	window.addEventListener('hashchange', schedule, { signal });
	window.addEventListener('john-platt:header-resize', resize, { signal });
	if (desktop.addEventListener) desktop.addEventListener('change', resize, { signal });
	if (reduced.addEventListener) reduced.addEventListener('change', resize, { signal });
	window.addEventListener('load', resize, { once: true, signal });
	const layoutObserver = 'ResizeObserver' in window ? new ResizeObserver(resize) : null;
	if (layoutObserver) layoutObserver.observe(opening);
	renderers.push(progress => {
		if (!nameGeometry) return;
		const g = nameGeometry;
		const value = desktop.matches && !reduced.matches ? progress : progress >= 1 ? 1 : 0;
		const x = g.x - scrollX;
		const y = g.y - scrollY;
		nameLink.style.transform = `translate(${x + (g.endX - x) * value}px, ${y + (g.endY - y) * value}px) scale(${1 + (g.scale - 1) * value})`;
	});
	renderers.push(progress => {
		if (!navigationGeometry) return;
		const g = navigationGeometry;
		const value = desktop.matches && !reduced.matches ? progress : progress >= 1 ? 1 : 0;
		const dx = (g.x - scrollX - g.end.left) * (1 - value);
		const dy = (g.y - scrollY - g.end.top) * (1 - value);
		navigation.style.transform = `translate(${dx}px, ${dy}px)`;
		g.links.forEach((link, index) => {
			const start = g.starts[index];
			const end = g.ends[index];
			const x = (start.left - (end.left - g.end.left)) * (1 - value);
			const y = (start.top - (end.top - g.end.top)) * (1 - value);
			const sx = 1 + (start.width / Math.max(1, end.width) - 1) * (1 - value);
			const sy = 1 + (start.height / Math.max(1, end.height) - 1) * (1 - value);
			link.style.transform = `translate(${x}px, ${y}px) scale(${sx}, ${sy})`;
		});
	});
	// Retire only the opening header row's rules; the rest of the grid remains intact.
	renderers.push((progress, geometry) => {
		const continuous = desktop.matches && !reduced.matches;
		const value = continuous ? progress : progress >= 1 ? 1 : 0;
		let inset = geometry.rowHeight * Math.min(1, value / .55);
		if (continuous && value > 0 && navigationGeometry) {
			const g = navigationGeometry;
			const dx = (g.x - scrollX - g.end.left) * (1 - value);
			const dy = (g.y - scrollY - g.end.top) * (1 - value);
			g.links.forEach((link, index) => {
				const start = g.starts[index];
				const end = g.ends[index];
				const left = end.left + dx + (start.left - (end.left - g.end.left)) * (1 - value);
				const top = end.top + dy + (start.top - (end.top - g.end.top)) * (1 - value);
				const width = end.width + (start.width - end.width) * (1 - value);
				const height = end.height + (start.height - end.height) * (1 - value);
				if (geometry.dividers.some(x => x >= left - 20 && x <= left + width + 20)) {
					inset = Math.max(inset, top + height + 20 - (geometry.rowTop - scrollY));
				}
			});
		}
		// Clip underlying grid dividers exactly at the header boundary during the handoff.
		const clipTop = value >= .8 ? Math.max(0, geometry.headerBottom - geometry.gridTop + scrollY) : 0;
		opening.style.setProperty('--header-grid-clip-top', clipTop.toFixed(2) + 'px');
		opening.style.setProperty('--header-rule-inset', Math.max(0, Math.min(geometry.rowHeight, inset)).toFixed(2) + 'px');
		opening.style.setProperty('--header-rule-opacity', (1 - Math.max(0, Math.min(1, (value - .25) / .3))).toFixed(4));
		opening.style.setProperty('--header-row-rule-opacity', (1 - Math.max(0, Math.min(1, (value - .6) / .2))).toFixed(4));
	});
	renderers.push(progress => {
		const surface = desktop.matches && !reduced.matches ? Math.max(0, Math.min(1, (progress - .8) / .2)) : progress >= 1 ? 1 : 0;
		header.style.setProperty('--header-surface', surface.toFixed(4));
		header.classList.toggle('is-compact', progress >= 1);
	});
	document.body.classList.add('has-home-header');
	header.classList.add('is-home-destination');
	document.documentElement.style.setProperty('--site-header-height', `${header.getBoundingClientRect().height}px`);
	measure();
	return () => {
		controller.abort();
		cancelAnimationFrame(frame);
		cancelAnimationFrame(resizeFrame);
		if (layoutObserver) layoutObserver.disconnect();
		origin.append(nameLink);
		nameLink.className = '';
		nameLink.style.fontSize = '';
		nameLink.style.lineHeight = '';
		nameLink.style.transform = '';
		origin.style.minHeight = '';
		if (slot) slot.replaceWith(identity);
		navigation.className = 'site-header__nav';
		navigation.style.transform = '';
		menu.className = 'site-header__menu';
		navigation.querySelectorAll('a').forEach(link => { link.style.transform = ''; });
		navigationTarget.append(navigation);
		document.body.classList.remove('has-home-header');
		header.classList.remove('is-home-destination', 'is-compact');
		header.style.removeProperty('--header-surface');
		opening.style.removeProperty('--header-grid-clip-top');
		window.dispatchEvent(new Event('john-platt:header-resize'));
	};
	};
	const configure = () => {
		if (breakpoint.matches && !dispose) dispose = initialize() || null;
		else if (!breakpoint.matches && dispose) { dispose(); dispose = null; }
	};
	if (!breakpoint.addEventListener || !window.AbortController) return;
	breakpoint.addEventListener('change', configure);
	configure();
})();