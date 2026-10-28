(() => {
	'use strict';
	const eligibility = matchMedia('(min-width: 80rem)');
	const motionPreference = matchMedia('(prefers-reduced-motion: reduce)');
	let started = false;
	const start = () => {
		if (started || !eligibility.matches || motionPreference.matches) return;
		started = true;
	let chapters = Array.from(document.querySelectorAll('.project-chapter--immersive'));
	if (!('IntersectionObserver' in window)) return;
	const desktop = matchMedia('(min-width: 80rem)');
	const reduced = matchMedia('(prefers-reduced-motion: reduce)');
	if (!desktop.addEventListener || !reduced.addEventListener) return;
	const active = new Set();
	const failedMedia = new Set();
	const boundChapters = new WeakSet();
	let layoutObserver = null;
	let measurements = new Map();
	let observer = null;
	let frame = 0;
	let resizeTimer = 0;
	let stickyTop = 16;

	const update = () => {
		frame = 0;
		const scroll = window.scrollY;
		active.forEach(chapter => {
			const metric = measurements.get(chapter);
			if (!metric) return;
			const progress = Math.max(0, Math.min(1, (scroll - metric.top + stickyTop) / metric.travel));
			const value = progress.toFixed(4);
			if (metric.lastProgress === value) return;
			metric.lastProgress = value;
			chapter.style.setProperty('--project-progress', value);
			if (metric.immersive) {
				const collapse = Math.max(0, Math.min(1, (progress - 0.25) / 0.4)).toFixed(4);
				if (metric.lastCollapse !== collapse) {
					chapter.style.setProperty('--project-collapse', collapse);
					metric.lastCollapse = collapse;
				}
				const state = progress <= 0.25 ? 'opening' : progress < 0.65 ? 'transition' : 'detail';
				if (chapter.dataset.projectState !== state) chapter.dataset.projectState = state;
			} else {
				chapter.style.setProperty('--project-text', Math.max(0, Math.min(1, (progress - 0.35) / 0.5)).toFixed(4));
			}
		});
	};
	const schedule = () => {
		if (!frame && active.size) frame = requestAnimationFrame(update);
	};
	const reset = () => {
		if (observer) observer.disconnect();
		observer = null;
		cancelAnimationFrame(frame);
		frame = 0;
		active.clear();
		measurements.clear();
		window.removeEventListener('scroll', schedule);
		chapters.forEach(chapter => {
			chapter.classList.remove('project-motion', 'project-immersive-motion');
			['--project-progress', '--project-text', '--project-collapse', '--project-frame-height', '--project-chapter-height', '--project-stage-height', '--project-media-open-height', '--project-media-detail-height', '--project-opening-scale'].forEach(name => chapter.style.removeProperty(name));
			if (chapter.classList.contains('project-chapter--immersive')) chapter.dataset.projectState = 'detail';
		});
	};
	const configure = () => {
		reset();
		if (!desktop.matches || reduced.matches) return;
		try {
			// Measure only on setup/resizing; scrolling uses cached geometry.
			stickyTop = parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--site-header-height')) || 0;
			stickyTop += (parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--site-header-top')) || 0) + 16;
			const frameHeight = Math.max(1, innerHeight - stickyTop - 16);
			const eligible = chapters.map(chapter => {
				const content = chapter.querySelector('.project-chapter__content');
				const immersive = chapter.classList.contains('project-chapter--immersive');
				const contentHeight = content.getBoundingClientRect().height;
				if (failedMedia.has(chapter) || chapter.querySelector('details[open]') || (!immersive && contentHeight > frameHeight)) return null;
				const media = chapter.querySelector('.project-chapter__media');
				const mediaStyle = getComputedStyle(media);
				const padding = parseFloat(mediaStyle.paddingTop) + parseFloat(mediaStyle.paddingBottom);
				const horizontalPadding = parseFloat(mediaStyle.paddingLeft) + parseFloat(mediaStyle.paddingRight);
				const chapterStyle = getComputedStyle(chapter);
				const share = parseFloat(chapterStyle.getPropertyValue('--project-media-share')) || 34;
				const ratio = parseFloat(chapterStyle.getPropertyValue('--project-media-ratio')) || 1;
				const detailWidth = Math.max(1, chapter.getBoundingClientRect().width * share / 100 - horizontalPadding);
				const fallback = media.querySelector('details');
				const reserve = fallback ? fallback.getBoundingClientRect().height + 16 : 0;
				const openWidth = Math.max(1, chapter.getBoundingClientRect().width - horizontalPadding);
				const openHeight = Math.max(1, frameHeight - padding - reserve);
				const openingAspect = openWidth / openHeight;
				const openingScale = Math.max(ratio / openingAspect, openingAspect / ratio);
				const overflow = Math.max(0, contentHeight - frameHeight);
				const height = Math.max(innerHeight * 2.8, frameHeight + (overflow + innerHeight * 0.35) / 0.35);
				const travel = Math.max(1, height - frameHeight);
				return { chapter, immersive, height, travel, openingScale, stageHeight: overflow ? frameHeight + travel * 0.65 : height, openHeight: Math.max(1, frameHeight - padding - reserve), detailHeight: Math.max(1, Math.min(detailWidth / ratio, frameHeight - padding - reserve)) };
			}).filter(Boolean);
			// Batch setup writes before measuring chapter positions, including preceding chapters.
			eligible.forEach(item => {
				const chapter = item.chapter;
				if (item.immersive) {
					chapter.classList.add('project-immersive-motion');
					chapter.style.setProperty('--project-opening-scale', item.openingScale.toFixed(6));
					chapter.style.setProperty('--project-frame-height', `${frameHeight}px`);
					chapter.style.setProperty('--project-chapter-height', `${item.height}px`);
					chapter.style.setProperty('--project-stage-height', `${item.stageHeight}px`);
					chapter.style.setProperty('--project-media-open-height', `${item.openHeight}px`);
					chapter.style.setProperty('--project-media-detail-height', `${item.detailHeight}px`);
				} else chapter.classList.add('project-motion');
			});
			eligible.forEach(item => {
				const rect = item.chapter.getBoundingClientRect();
				measurements.set(item.chapter, { top: rect.top + scrollY, travel: item.immersive ? item.travel : Math.max(1, (rect.height - innerHeight) * 0.45), immersive: item.immersive });
			});
			if (!measurements.size) return;
			observer = new IntersectionObserver(entries => {
				entries.forEach(entry => {
					if (entry.isIntersecting) active.add(entry.target);
					else active.delete(entry.target);
				});
				schedule();
			});
			measurements.forEach((value, chapter) => observer.observe(chapter));
			window.addEventListener('scroll', schedule, { passive: true });
		} catch {
			reset();
		}
	};
	window.addEventListener('john-platt:header-resize', () => resize());
	const resize = () => {
		clearTimeout(resizeTimer);
		resizeTimer = setTimeout(configure, 150);
	};
	const bindChapters = () => chapters.forEach(chapter => {
		if (boundChapters.has(chapter)) return;
		boundChapters.add(chapter);
		const fallback = chapter.querySelector('details');
		if (fallback) fallback.addEventListener('toggle', configure);
		const video = chapter.querySelector('video');
		if (video) {
			const failed = () => {
				failedMedia.add(chapter);
				configure();
			};
			video.addEventListener('error', failed);
			const source = video.querySelector('source');
			if (source) source.addEventListener('error', failed);
		}
	});

	// Content/image height changes can shift every later chapter's cached top.
	if ('ResizeObserver' in window) {
		const heights = new WeakMap();
		layoutObserver = new ResizeObserver(entries => {
			let changed = false;
			entries.forEach(entry => {
				const height = entry.contentRect.height;
				const previous = heights.get(entry.target);
				if (previous !== undefined && Math.abs(previous - height) > 1) changed = true;
				heights.set(entry.target, height);
			});
			if (changed) resize();
		});
		document.querySelectorAll('.project-chapter, .project-chapter__content').forEach(element => layoutObserver.observe(element));
	}
	bindChapters();
	window.addEventListener('john-platt:projects-updated', () => {
		clearTimeout(resizeTimer);
		reset();
		failedMedia.clear();
		if (layoutObserver) layoutObserver.disconnect();
		chapters = Array.from(document.querySelectorAll('.project-chapter--immersive'));
		bindChapters();
		if (layoutObserver) document.querySelectorAll('.project-chapter, .project-chapter__content').forEach(element => layoutObserver.observe(element));
		configure();
	});
	desktop.addEventListener('change', configure);
	reduced.addEventListener('change', configure);
	window.addEventListener('resize', resize, { passive: true });
	window.addEventListener('load', configure, { once: true });
	window.addEventListener('pageshow', configure);
	configure();
	};
	if (eligibility.addEventListener && motionPreference.addEventListener) {
		eligibility.addEventListener('change', start);
		motionPreference.addEventListener('change', start);
	}
	start();
})();

/* Project video playback is independent of the chapter collapse enhancement. */
(() => {
	'use strict';
	const root = document.querySelector('.projects-page, .project-page');
	if (!root || !('IntersectionObserver' in window)) return;
	let videos = Array.from(root.querySelectorAll('.project-media__video'));
	const boundVideos = new WeakSet();
	const prepared = new WeakSet();
	const prepare = video => {
		if (prepared.has(video)) return;
		prepared.add(video);
		video.preload = 'metadata';
		if (video.paused) video.load();
	};
	const nearObserver = new IntersectionObserver(entries => {
		entries.forEach(entry => {
			const video = entry.target;
			if (!entry.isIntersecting || prepared.has(video)) return;
			if (!matchMedia('(min-width: 80rem)').matches) return;
			prepare(video);
			nearObserver.unobserve(video);
		});
	}, { rootMargin: '200px 0px', threshold: 0 });
	const desktop = matchMedia('(min-width: 80rem)');
	const reduced = matchMedia('(prefers-reduced-motion: reduce)');
	const visible = new Map();
	const blocked = new Set();
	const pausedByUser = new Set();
	let selected = null;
	let attempt = 0;
	const buttonFor = video => video.parentElement.querySelector('.project-media__toggle');
	const soundFor = video => video.parentElement.querySelector('.project-media__sound');
	const syncSound = video => {
		const sound = soundFor(video);
		if (!sound) return;
		const muted = video.muted || video.volume === 0;
		const label = muted ? sound.dataset.unmuteLabel : sound.dataset.muteLabel;
		sound.dataset.muted = String(muted);
		sound.setAttribute('aria-label', label);
		sound.title = label;
	};
	const staticControls = video => {
		video.controls = true;
		const button = buttonFor(video);
		if (button) button.hidden = true;
		const sound = soundFor(video);
		if (sound) sound.hidden = true;
	};
	const pauseOthers = current => root.querySelectorAll('video').forEach(video => {
		if (video !== current) video.pause();
	});
	const choose = (preferred = null) => {
		const token = ++attempt;
		let candidate = null;
		let best = 0;
		if (desktop.matches && !reduced.matches && !document.hidden) {
			visible.forEach((ratio, video) => {
				if (!blocked.has(video) && !pausedByUser.has(video) && ratio > best) {
					candidate = video;
					best = ratio;
				}
			});
		}
		if (desktop.matches && !reduced.matches && !document.hidden && visible.has(preferred) && !blocked.has(preferred)) candidate = preferred;
		selected = candidate;
		if (!desktop.matches || reduced.matches) {
			videos.forEach(video => { if (document.hidden || !visible.has(video)) video.pause(); });
			return;
		}
		pauseOthers(candidate);
		if (!candidate) return;
		if (!candidate.paused && !candidate.controls) return;
		prepare(candidate);
		// The HTML starts muted. Preserve the user's sound choice on this node.
		// Catch browser autoplay rejection; native controls remain the fallback.
		Promise.resolve().then(() => candidate.play()).then(() => {
			if (token !== attempt || selected !== candidate || document.hidden || pausedByUser.has(candidate)) {
				if (selected !== candidate || document.hidden || pausedByUser.has(candidate)) candidate.pause();
				return;
			}
			candidate.controls = false;
			const sound = soundFor(candidate);
			if (sound && candidate.closest('.project-chapter--immersive')) {
				syncSound(candidate);
				sound.hidden = false;
			}
			const button = buttonFor(candidate);
			if (button) {
				button.hidden = false;
				button.textContent = button.dataset.pauseLabel;
			}
		}).catch(() => {
			if (token !== attempt) return;
			blocked.add(candidate);
			candidate.pause();
			staticControls(candidate);
		});
	};
	const observer = new IntersectionObserver(entries => {
		entries.forEach(entry => {
			if (entry.isIntersecting && entry.intersectionRatio >= 0.15) {
				visible.set(entry.target, entry.intersectionRatio);
				if (!desktop.matches) prepare(entry.target);
			}
			else {
				visible.delete(entry.target);
				entry.target.pause();
			}
		});
		choose();
	}, { threshold: [0, 0.15, 0.5, 0.8, 1] });
	const bindVideos = () => videos.forEach(video => {
		if (boundVideos.has(video)) return;
		boundVideos.add(video);
		const button = buttonFor(video);
		const sound = soundFor(video);
		if (sound) {
			syncSound(video);
			sound.addEventListener('click', () => {
				const makeAudible = video.muted || video.volume === 0;
				video.muted = !makeAudible;
				if (makeAudible && video.volume === 0) video.volume = 1;
				syncSound(video);
			});
			video.addEventListener('volumechange', () => syncSound(video));
		}
		if (button) button.addEventListener('click', () => {
			if (!video.paused) {
				pausedByUser.add(video);
				++attempt;
				video.pause();
				button.textContent = button.dataset.playLabel;
			} else {
				pausedByUser.delete(video);
				choose(video);
			}
		});
		video.addEventListener('play', () => {
			if (document.hidden || !visible.has(video)) {
				video.pause();
				return;
			}
			pauseOthers(video);
		});
		const fail = () => {
			blocked.add(video);
			visible.delete(video);
			video.pause();
			video.hidden = true;
			if (button) button.hidden = true;
			if (sound) sound.hidden = true;
			const fallback = video.parentElement.querySelector('.project-media__failure');
			if (fallback) fallback.hidden = false;
			choose();
		};
		video.addEventListener('error', fail);
		const source = video.querySelector('source');
		if (source) source.addEventListener('error', fail);
		observer.observe(video);
		nearObserver.observe(video);
	});
	const preferenceChanged = () => {
		videos.forEach(video => { video.pause(); staticControls(video); });
		choose();
	};
	if (desktop.addEventListener && reduced.addEventListener) {
		desktop.addEventListener('change', preferenceChanged);
		reduced.addEventListener('change', preferenceChanged);
	}
	bindVideos();
	window.addEventListener('john-platt:projects-updated', () => {
		++attempt;
		videos.forEach(video => video.pause());
		observer.disconnect();
		nearObserver.disconnect();
		visible.clear();
		blocked.clear();
		pausedByUser.clear();
		selected = null;
		videos = Array.from(root.querySelectorAll('.project-media__video'));
		bindVideos();
	});
	// Authored case-study videos keep native controls and share the one-player limit.
	root.addEventListener('play', event => {
		const current = event.target;
		if (current?.tagName !== 'VIDEO') return;
		if (!videos.includes(current)) {
			++attempt;
			selected = null;
			videos.forEach(video => pausedByUser.add(video));
		}
		pauseOthers(current);
	}, true);
	document.addEventListener('visibilitychange', choose);
})();
