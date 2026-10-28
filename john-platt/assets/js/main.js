(() => {
	'use strict';

	const page = document.querySelector('.home-page, .writing-page');
	if (!page || !('IntersectionObserver' in window)) return;

	const isWriting = page.classList.contains('writing-page');
	const focalClass = isWriting ? 'has-focal-row' : 'has-focal-chapter';
	let chapters = Array.from(page.querySelectorAll(isWriting ? '.writing-row' : '.chapter'));
	if (!chapters.length && !isWriting) return;

	const desktop = window.matchMedia('(min-width: 64rem)');
	const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
	const active = new Set();
	let observer = null;
	let resizeTimer = null;

	const reset = () => {
		if (observer) observer.disconnect();
		observer = null;
		active.clear();
		page.classList.remove(focalClass, 'scroll-enhanced');
		chapters.forEach(chapter => chapter.classList.remove('is-active', 'is-revealed'));
	};

	const configure = () => {
		if (observer) observer.disconnect();
		active.clear();
		page.classList.remove(focalClass);
		chapters.forEach(chapter => chapter.classList.remove('is-active'));

		if (!desktop.matches || reducedMotion.matches) {
			reset();
			return;
		}

		// Pixel margins keep the focal band at 60% of viewport height.
		// IntersectionObserver percentage margins are based on root width.
		const inset = Math.round(window.innerHeight * 0.2);
		try {
			observer = new IntersectionObserver(entries => {
				entries.forEach(entry => {
					const chapter = entry.target;
					if (entry.isIntersecting) {
						active.add(chapter);
						chapter.classList.add('is-revealed');
					} else {
						active.delete(chapter);
					}
					chapter.classList.toggle('is-active', entry.isIntersecting);
				});
				page.classList.toggle(focalClass, active.size > 0);
			}, { rootMargin: '-' + inset + 'px 0px', threshold: 0 });

			chapters.forEach(chapter => observer.observe(chapter));
			page.classList.add('scroll-enhanced');
		} catch {
			// Failed enhancement must leave every element fully readable.
			reset();
		}
	};

	const scheduleResize = () => {
		window.clearTimeout(resizeTimer);
		if (!desktop.matches || reducedMotion.matches) return;
		resizeTimer = window.setTimeout(configure, 150);
	};

	// Older engines keep the static page instead of partial enhancement.
	if (!desktop.addEventListener || !reducedMotion.addEventListener) return;

	desktop.addEventListener('change', configure);
	reducedMotion.addEventListener('change', configure);
	window.addEventListener('resize', scheduleResize, { passive: true });
	if (isWriting) page.addEventListener('john-platt:writing-updated', () => {
		reset();
		chapters = Array.from(page.querySelectorAll('.writing-row'));
		configure();
	});
	configure();
})();
