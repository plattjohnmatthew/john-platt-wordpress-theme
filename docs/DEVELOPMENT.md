# Developer and maintenance notes

## Public edition

Version 1.0.0 is a clean public snapshot of the John Platt theme, including the immersive video, speaker control and performance work. The original site's database, uploads, local paths, private QA notes and development history are not included. This repository is separate from that site's development checkout.

Public-edition adaptations: Lorem Ipsum prose and site-title-based identity defaults; editable shared footer email; Contact CTA with no personal hard-coded destination; section CTA URLs honour their existing editable fields. These changes do not modify the originating live site. The placeholder screenshot was omitted; no personal/client image assets or font binaries are bundled. Theme name/author credit and compatibility identifiers retain John Platt by the author's choice.

## Layout

- `john-platt/`: installable theme source (classic PHP templates, `inc/`, `template-parts/`, CSS/JS, `theme.json`).
- `docs/`: owner documentation, also copied into the release ZIP.
- `tests/`: portable Node VM fixtures for the analytics scheduler and Project audio playback.
- `tools/build_release.py`: standard-library Python release builder, exporting committed Git bytes only.

No frontend framework, npm install or build step is needed to run the theme. PHP supplies markup and the main archive queries remain WordPress-owned. Dedicated taxonomies are `writing_topic` and `project_category`; Projects use `project`, curated application portfolios use `jp_portfolio`. ACF field names keep the existing `project_`/homepage prefixes for compatibility.

Custom post types/taxonomies currently live in the theme. Deactivating it leaves records in the database but their editing UI/routes may disappear until the theme is reactivated. Moving content ownership into a plugin is a future architectural change, not part of this release. Do not assume a theme switch migrates these records to another system.

## Validation

With Node installed, from the repository root:

```sh
node tests/analytics-delay.cjs
node tests/project-sound.cjs
python tools/check_public.py
```

PHP syntax can be checked with your installed PHP executable. Test on a separate WordPress site with ACF active; no WordPress/database fixtures are bundled. The existing features were exercised on WordPress 7.1/PHP 8.2, but clean-install, host, media, browser and plugin integration require target-site checks. The minimum version declaration is not a guarantee for every historical version/plugin combination.

Manual regression matrix: Home, Writing, filtered/paginated Writing, article (two H2s/menu on/off), Projects (all modes, image/native MP4/public VideoPress), single Project, Contact, Index, portfolio and 404. Check 320/375/430/768/1280/1440/1920 CSS pixels, keyboard/focus, reduced motion, no JS, browser history, autoplay rejection, hidden-tab pausing and missing/long content. Public VideoPress previews are archive/curated-only to preserve existing case-study embeds.

## Release build

Commit intended source changes, then run:

```sh
python tools/build_release.py
```

Python 3.9+ and Git are build tools only, not WordPress runtime dependencies. The builder refuses a dirty Git tree, archives HEAD, packages only the theme plus owner docs/licence, checks entry names/bytes and emits `dist/john-platt-theme.zip` plus `dist/SHA256SUMS.txt`. Tests, build tools, Git metadata, databases and uploads are excluded. Publish those two files as release assets under the matching version tag. GitHub's automatic source archive includes the repository layout and is not the WordPress installation ZIP.

## Integrations and performance

Immersive motion uses IntersectionObserver, cached geometry, passive scroll input and finite requestAnimationFrame updates; video retains one node and user pause/sound state. Filters use server-rendered HTML, abort stale requests, keep bounded expiring in-memory caches, preserve history and fall back to navigation. No cache/security plugin is installed by the theme.

The optional asset rules are conservative, exact-path and dependency-aware. Integrations may retain all assets with `add_filter( 'john_platt_trim_surface_assets', '__return_false' );`. The recognised Jetpack GA4 delay may be disabled with `add_filter( 'john_platt_delay_google_analytics', '__return_false' );`. Apply through a child theme/site plugin; do not strip arbitrary host/consent scripts. Changing plugin versions can change hooks/paths; verify fallbacks.

Do not add private portfolios to cache-warming lists, public examples or documentation. Do not ship a site's analytics ID, email defaults, uploads, tokens or database. Keep server-side queries scoped; preserve main queries, routes, field names, static fallbacks and accessibility controls.
