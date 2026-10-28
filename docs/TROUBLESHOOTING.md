# Troubleshooting and safe updates

## The installed site does not look like the demo

The release contains the theme, not a database export. It does not include John's photos, menus, articles, Projects, ACF values or uploads. Follow Getting Started: set the homepage/Posts page, install ACF, create the Primary menu and enter your images/content.

## No navigation / my name appears twice

Assign a menu to **Primary navigation** in Appearance → Menus. Set the homepage menu item's **Navigation Label** to Home: it otherwise may inherit a page title such as your name. The left-hand header identity is a separate Site Title link.

## Writing looks like a blank normal Page

Assign Writing as **Posts page** under Settings → Reading. The page's editor controls the introduction; actual articles must be published under Posts. Do not insert a manual Query Loop to recreate the archive.

## Projects is a normal Page or a 404

Projects is a custom post-type archive, not a normal Page. Remove/rename a competing `projects` Page only if it is the unwanted duplicate. Keep your Project records. Save Settings → Permalinks once to refresh routes. Ensure the theme is active. The introductory helper Page must use `projects-info`, not `projects`.

If the plain `?post_type=project` address works but `/projects/` does not, that suggests a rewrite/permalink/server rule issue; ask your host if resaving permalinks does not resolve it.

## Fields are missing

Activate ACF Free. Confirm you are editing the assigned Home Page or a Project, not a different Page. Check the editor's Preferences/Panels and scroll below the content to the meta boxes. Field groups register from the theme automatically; do not make duplicate groups with the same field names.

## An immersive Project does not animate

Check Presentation Mode=Immersive and that its **Optional MP4** or featured image is valid. A body embed alone is not archive media. Use a desktop viewport at least 1280 CSS pixels wide, JavaScript enabled and reduced motion disabled. Browser zoom can reduce the CSS viewport width. Mobile/static/no-JS/reduced-motion layouts intentionally do not animate. The intro appears before the first Project.

## Video missing, black, blocked or silent

- Select the actual attachment in Optional MP4; a YouTube/VideoPress iframe URL is not an MP4 attachment.
- WordPress.com can label the attachment `video/videopress`; public MP4-path attachments are supported in archive previews. Private VideoPress media needs its authenticated player and is not exposed as a public preview.
- Open its file URL in a logged-out window. 403/404 means permissions, migration, expiry or missing media, not a scroll setting. Do not bypass private access controls.
- Add a featured image/poster so a still can appear before playback or when playback fails.
- Autoplay is muted and browser-dependent. Use native controls if autoplay is rejected. The immersive speaker icon toggles sound; the pause control is separate.
- Some browsers reject resuming an unmuted video without another gesture; native controls remain the fallback.
- The original public MP4 can be large; prepare a suitable H.264/AAC encode and test delivery/byte-range support. The native preview does not implement VideoPress adaptive streaming or automatic subtitle importing.

## Filters are missing, wrong or slow

Use **Writing Topics** for Writing and **Project Categories** for Projects. Publish and assign content; empty terms are hidden. For an article in Projects, tick Include this article in the Projects archive. Display Order controls filter order; multiple terms use OR. Clear ALL to reset.

JavaScript fetches server-rendered URLs, so an uncached host response can still take time. Cache/prefetch improves repeat requests but cannot make the server instantaneous. Normal links work when JavaScript/fetch fails. Check that a security/cache plugin does not remove the `topics`/`categories` parameters or return the wrong cached selection.

## No article contents menu

Enable Article contents in its settings, then use at least **two H2 blocks** in the actual article body. An H3 or plugin Related heading does not count as another real H2. The menu sits before the first H2, after introductory text. Imported headings must be real HTML headings, not bold paragraphs.

## Share / Like / Related widgets appear

The theme suppresses common Jetpack additions on reading layouts. Other plugins, host injection or content copied with those widgets may need their own settings changed. Disable the relevant plugin option rather than deleting your authored article content blindly. Test with the actual live plugin stack.

## Broken imported images / awkward movement

Look for old-domain or local-computer URLs. Upload missing files and replace their Image blocks through the Media Library; changing a URL cannot recreate a missing file. Native attachment images get responsive variants and dimensions. External images may lack dimensions and need manual cleanup. Do not bulk-replace URLs without a backup.

## Portfolios and search engines

Portfolios are unlisted/noindex, not confidential. Anyone with a link can open them. A theme/plugin change, cache or custom sitemap may alter signals; verify response headers, robots metadata and menus on the deployed site. Do not use these links to store sensitive application material requiring authentication.

## Update / rollback

1. Back up the database, uploads and files; save your previous working ZIP.
   GitHub updates are manual ZIP installs; setting a GitHub Update URI does not install an automatic updater. Watch Releases on GitHub if you want release notifications.
2. Test the new ZIP on staging with the same plugins, content and PHP version.
3. Upload the new release through Appearance → Themes. Replace the parent theme when prompted.
4. Clear the relevant host/page/CDN cache once if necessary, then check logged out on phone and desktop.
5. Verify menus, footer email, filters/back/forward, articles, videos, Contact CTA and unlisted portfolios. Verify analytics separately if configured.
6. If a regression appears, reinstall the previous ZIP and clear the relevant cache. A theme rollback does not automatically undo content/database changes.

Do not sync an old staging database over a newer live database just to update this theme. Do not overwrite an active development checkout containing `.git` through WordPress's replace-theme flow.

For a bug report, include theme/WordPress/PHP versions, browser/viewport, relevant plugins, steps, expected/actual behaviour and a public example if appropriate. Do not post credentials, tokens, database exports or confidential portfolio links. Community issue reports are welcome; guaranteed support is not included.
