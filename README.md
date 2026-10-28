# John Platt editorial WordPress theme

A free, open-source portfolio theme by John Platt: oversized condensed headings, warm paper colours, strong grid rules, native Writing posts and immersive Project previews.

**[Download the installable theme ZIP](https://github.com/plattjohnmatthew/john-platt-wordpress-theme/releases/latest/download/john-platt-theme.zip)** · **[Start here](docs/GETTING-STARTED.md)** · **[Feature guide](docs/FEATURES.md)** · **[Troubleshooting](docs/TROUBLESHOOTING.md)**

You do not need to write code to set up the pages, change the text/images, publish articles and Projects, manage filters, or curate application portfolios. Install the free **Advanced Custom Fields (ACF)** plugin for the editing fields. ACF Pro and page builders are not required.

Default prose is **Lorem Ipsum**, not a pre-written personal biography or brand statement. Only the theme name/author credit retains John Platt; the site's header identity and contact information come from the new owner's settings. See [public-distribution defaults](docs/PUBLIC-DEFAULTS.md).

## What you get

| Area | Features |
| --- | --- |
| Homepage | Editable editorial grid, portrait, three quick links and Work/Writing/Experiments sections; desktop name/header morph |
| Navigation | Desktop sticky header; mobile name and accessible hamburger menu |
| Writing | Native WordPress Posts, featured images, editable introduction, independently managed Topics and multi-select filters |
| Articles | Condensed headings, optional automatic contents menu, full-width separators, framed quotes, paragraph font choices |
| Projects | Native custom post type; Immersive, Editorial and Simple modes; independent category filters |
| Video | Uploaded MP4/public VideoPress archive previews, muted autoplay where eligible, pause and speaker controls |
| Cross-listing | Show an article in Projects while linking to the original article; no duplicate case-study page |
| Portfolios | Separate unlisted application links, editable introductions and manually ordered selections |
| Other pages | Contact, legacy About, chronological Index and 404 |
| Accessibility | Keyboard focus, skip link, reduced-motion/static fallbacks, native media controls and no-JavaScript links |
| Performance | No framework/build dependency or external font download; responsive images, lazy embeds and guarded optional assets |

## Requirements

- WordPress **6.6+**; use a current supported version. The originating site was tested on WordPress 7.1 with PHP 8.2; every host/plugin combination is not guaranteed.
- PHP **7.4+** as declared by the theme; use a currently supported PHP release recommended by your host.
- Free [Advanced Custom Fields](https://wordpress.org/plugins/advanced-custom-fields/) for the complete editing experience. It is installed separately, not bundled.
- Permission to install custom themes/plugins on your host. WordPress.com requires an eligible plan.
- A modern browser. Desktop immersive animation starts at **1280 CSS pixels**, with reduced motion off. Smaller screens use static layouts.

## Install in five steps

1. Back up your site, or start with a fresh staging site.
2. Download **john-platt-theme.zip** from [Releases](https://github.com/plattjohnmatthew/john-platt-wordpress-theme/releases/latest). GitHub's automatic **Source code** ZIP is a developer download, not the installable theme.
3. In WordPress: **Appearance → Themes → Add New → Upload Theme**. Upload the ZIP and activate it.
4. Install and activate **Advanced Custom Fields** under **Plugins → Add New**.
5. Follow the [page, navigation and content setup](docs/GETTING-STARTED.md). Your own content and images are needed: this ZIP is a theme, not a copy of John's website/database.

## Documentation

- [Getting started](docs/GETTING-STARTED.md): installation, Reading settings, routes, menus, first content and launch checklist.
- [Using every feature](docs/FEATURES.md): homepage fields, filters, video, article formatting, Contact and portfolios.
- [Make it your own](docs/CUSTOMIZATION.md): editable identity/contact details, fonts, images and optional code customisation.
- [Troubleshooting](docs/TROUBLESHOOTING.md): 404s, missing media/menus, plugin conflicts and updates.
- [Developer and maintenance notes](docs/DEVELOPMENT.md): architecture, build/test instructions, licensing and scope.

The guides are also included inside the release ZIP at `john-platt/docs/`.

## What is deliberately not included

No articles, Projects, menus, database, uploads, personal portraits, client media, fonts, analytics ID, credentials or private development history. Jetpack, VideoPress and SEO plugins are optional integrations, not requirements. The theme does not install analytics or require a tracking account.

This is a **classic theme**, not a full-site-editing block theme. Content is editable through WordPress and ACF; the entire grid is not a drag-and-drop page builder. “Unlisted/noindex” portfolios are **not private**: anyone with the link can view them.

## Reuse and support

Theme code and bundled documentation are licensed **GPL-2.0-or-later**. You may use, study, adapt and redistribute them under that licence, including for commercial sites. Retain the applicable notices/licence when redistributing. Fonts referenced by name are not distributed; use your own properly licensed content and media. John Platt's identity and clients' material are not supplied as reusable assets.

See [LICENSE](LICENSE), [NOTICE](NOTICE.md) and [CONTRIBUTING](CONTRIBUTING.md). Report reproducible problems through [GitHub Issues](https://github.com/plattjohnmatthew/john-platt-wordpress-theme/issues). This is provided without warranty or a guaranteed support response time.
