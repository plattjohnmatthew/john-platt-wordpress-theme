(() => {
	'use strict';

	const isProjects = !!document.querySelector('.projects-page');
	const pageSelector = isProjects ? '.projects-page' : '.writing-page';
	const listSelector = isProjects ? '.project-list' : '.writing-list';
	const topicSelector = isProjects ? '.project-topics' : '.writing-topics';
	const page = document.querySelector(pageSelector);
	if (!page || !window.fetch || !window.AbortController || !window.DOMParser || !window.history.pushState || !window.matchMedia) return;
	const list = page.querySelector(listSelector);
	const topics = page.querySelector(topicSelector);
	const status = page.querySelector('.writing-filter-status');
	if (!list || !topics || !status) return;
	const archive = new URL(topics.querySelector('a').href);
	const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
	let controller = null;
	let animation = null;
	let revision = 0;
	let displayed = window.location.href;

	const transition = entering => {
		if (animation) animation.cancel();
		animation = null;
		if (reduced.matches || !list.animate) return Promise.resolve();
		const distance = window.matchMedia('(max-width: 47.999rem)').matches ? 8 : 12;
		animation = list.animate(
			entering
				? [{ opacity: 0, transform: 'translateY(' + distance + 'px)' }, { opacity: 1, transform: 'translateY(0)' }]
				: [{ opacity: 1, transform: 'translateY(0)' }, { opacity: 0, transform: 'translateY(' + distance + 'px)' }],
			{ duration: entering ? 150 : 90, easing: 'ease', fill: 'forwards' }
		);
		return animation.finished.catch(() => {});
	};
	const writingURL = url => url.origin === archive.origin && url.pathname === archive.pathname;
	// Page-memory only: no persistent storage and no sharing across visitors.
	const cache = new Map();
	const remember = (key, value) => {
		cache.delete(key);
		cache.set(key, { ...value, expires: Date.now() + 90000 });
		while (cache.size > 12) cache.delete(cache.keys().next().value);
	};
	const cached = key => {
		const value = cache.get(key);
		if (value && value.expires > Date.now()) return value;
		cache.delete(key);
		return null;
	};
	const parse = html => {
		const documentNext = new DOMParser().parseFromString(html, 'text/html');
		const pageNext = documentNext.querySelector(pageSelector);
		const listNext = pageNext?.querySelector(listSelector);
		const topicsNext = pageNext?.querySelector(topicSelector);
		if (!listNext || !topicsNext?.querySelector('a[data-writing-topic=""]') || !listNext.dataset.resultsMessage) throw new Error('Incomplete archive response');
		return { documentNext, listNext, topicsNext };
	};
	const fetchMarkup = async (url, signal) => {
		const response = await fetch(url.href, { signal, credentials: 'same-origin', headers: { Accept: 'text/html' } });
		if (!response.ok || !response.headers.get('content-type')?.includes('text/html')) throw new Error('Invalid archive response');
		const resolved = new URL(response.url || url.href);
		if (!writingURL(resolved)) throw new Error('Archive redirected');
		const html = await response.text();
		parse(html); // Cache only complete server-rendered archive states.
		const value = { html, resolved: resolved.href };
		remember(url.href, value);
		return value;
	};
	if (writingURL(new URL(displayed))) remember(displayed, { html: document.documentElement.outerHTML, resolved: displayed });
	let prefetch = null;
	const warm = event => {
		if (!(event.target instanceof Element) || event.pointerType === 'touch') return;
		const connection = navigator.connection;
		if (connection?.saveData || ['slow-2g', '2g'].includes(connection?.effectiveType)) return;
		const link = event.target.closest('a[data-writing-topic]');
		if (!link || !topics.contains(link)) return;
		const url = new URL(link.href);
		if (!writingURL(url) || cached(url.href) || controller || prefetch?.url === url.href) return;
		if (prefetch) prefetch.controller.abort();
		const entry = { url: url.href, controller: new AbortController(), promise: null };
		prefetch = entry;
		const timeout = window.setTimeout(() => entry.controller.abort(), 8000);
		entry.promise = fetchMarkup(url, entry.controller.signal).catch(() => null).finally(() => {
			window.clearTimeout(timeout);
			if (prefetch === entry) prefetch = null;
		});
	};
	topics.addEventListener('pointerover', warm);
	topics.addEventListener('focusin', warm);
	const position = () => ({ x: window.scrollX, y: window.scrollY });
	const restore = point => window.scrollTo(point.x, point.y);

	const load = async (url, push, savedPosition = null) => {
		const current = ++revision;
		if (animation) animation.cancel();
		animation = null;
		if (controller) controller.abort();
		controller = new AbortController();
		const request = controller;
		const timeout = window.setTimeout(() => request.abort(), 12000);
		list.setAttribute('aria-busy', 'true');
		if (status.textContent !== status.dataset.loadingMessage) status.textContent = status.dataset.loadingMessage;
		// Keep the current list readable during network latency; animate only when ready.
		try {
			let value = cached(url.href);
			if (!value && prefetch?.url === url.href) value = await prefetch.promise;
			if (current !== revision) return;
			if (request.signal.aborted) throw new Error('Archive request timed out');
			if (!value) value = await fetchMarkup(url, request.signal);
			if (current !== revision) return;
			const resolved = new URL(value.resolved);
			const { documentNext, listNext, topicsNext } = parse(value.html);
			await transition(false);
			if (current !== revision) return;

			const point = savedPosition || position();
			const focused = topics.contains(document.activeElement) ? document.activeElement.closest('a[data-writing-topic]') : null;
			const focusedTopic = focused?.dataset.writingTopic;
			if (push) {
				// Store position per history entry without changing native scroll restoration.
				history.replaceState(Object.assign({}, history.state, { johnPlattWriting: position() }), '', window.location.href);
				history.pushState({ johnPlattWriting: point }, '', resolved.href);
			}
			list.style.minHeight = `${list.getBoundingClientRect().height}px`;
			list.replaceChildren(...listNext.childNodes);
			list.dataset.resultsMessage = listNext.dataset.resultsMessage;
			topics.replaceChildren(...topicsNext.childNodes);
			document.title = documentNext.title;
			displayed = resolved.href;
			if (isProjects) window.dispatchEvent(new Event('john-platt:projects-updated'));
			else page.dispatchEvent(new Event('john-platt:writing-updated'));
			if (focusedTopic !== undefined) {
				const replacement = Array.from(topics.querySelectorAll('a[data-writing-topic]')).find(link => link.dataset.writingTopic === focusedTopic);
				(replacement || topics.querySelector('a')).focus({ preventScroll: true });
			}
			restore(point);
			await transition(true);
			if (current !== revision) return;
			status.textContent = list.dataset.resultsMessage;
		} catch {
			// A superseded request is ignored; current failures use the original real link.
			if (current === revision) window.location.assign(url.href);
		} finally {
			window.clearTimeout(timeout);
			if (current === revision) {
				controller = null;
				if (animation) animation.cancel();
				animation = null;
				list.style.minHeight = '';
				list.removeAttribute('aria-busy');
			}
		}
	};

	topics.addEventListener('click', event => {
		if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || !(event.target instanceof Element)) return;
		const link = event.target.closest('a');
		if (!link || !topics.contains(link) || link.hasAttribute('download') || (link.target && link.target !== '_self')) return;
		const url = new URL(link.href);
		if (!writingURL(url)) return;
		event.preventDefault();
		if (url.href !== displayed || controller) load(url, true);
	});
	window.addEventListener('popstate', event => {
		const url = new URL(window.location.href);
		if (!writingURL(url)) {
			window.location.assign(url.href);
			return;
		}
		const saved = event.state?.johnPlattWriting;
		load(url, false, saved && Number.isFinite(saved.x) && Number.isFinite(saved.y) ? saved : null);
	});
	window.addEventListener('pagehide', () => {
		if (prefetch) prefetch.controller.abort();
		prefetch = null;
		cache.clear();
		++revision;
		if (controller) controller.abort();
		controller = null;
		if (animation) animation.cancel();
		animation = null;
		list.style.minHeight = '';
		list.removeAttribute('aria-busy');
		status.textContent = '';
	});
	if (reduced.addEventListener) reduced.addEventListener('change', () => {
		if (reduced.matches && animation) animation.cancel();
	});
})();
