# Make it your own

## Everyday changes — no code

| Change | Where |
| --- | --- |
| Header name and copyright | Settings → General → Site Title |
| Large opening label and headline | Home → Homepage Content → Intro |
| Portrait and browser-tab icon | Home → Intro → Opening portrait/image; no portrait uses WordPress's ordinary site icon |
| Navigation text/order | Appearance → Menus → your Primary menu; rename its Home navigation label independently of the page title |
| Quick-link words and symbols | Home → each section → Opening grid statement / Opening grid symbol image |
| Section headings, descriptions, illustrations and CTAs | Home → Work/Writing/Experiments tabs |
| Footer email | Home → Contact tab → Email |
| Contact text | Contact Page → ordinary editor |
| Contact CTA URL and hand image | Contact Page → Contact CTA |
| Writing introduction | The Page assigned as Posts page → ordinary editor |
| Projects introduction | Published Page with slug projects-info → ordinary editor |
| Topic/category labels and ordering | Posts → Writing Topics; Projects → Project Categories |
| Article/Project content and images | Its normal editor, featured image and relevant fields |
| Body paragraph family | Paragraph block → Typography → Font family |

The public edition's empty Contact destination falls back to your configured email; it never sends inquiries to John's email. If you change a previously populated site, replace its already-saved personal values too: new defaults do not overwrite existing WordPress data.

The normal homepage editor body is not a free-form homepage builder; use Homepage Content for that grid. Its three top quick-link destinations are Projects, Writing and AI-filtered Writing. Section CTA URLs can be overridden independently in this public edition. Some older/legacy Contact/homepage fields remain for compatibility and do not create another homepage section.

## Typography and images

The display stack is **Impact, Haettenschweiler, Arial Narrow, sans-serif**. Body text uses **Arial, Helvetica Neue, Helvetica, sans-serif**. Editorial italic introductions use **Georgia, Times New Roman, serif**. These are local system fonts: the theme does not include or download commercial font files. A device without Impact will use a fallback, so letter shapes and line wrapping can differ.

Use real H2/H3 structure rather than choosing headings just to enlarge text. Paragraphs may use Normal or Impact independently. Short labels work best in the condensed grid; preview edits at narrow widths.

Use your own licensed images. Upload through the Media Library and add alt text for meaningful images. Symbol images beside labels are decorative and intentionally have empty alt text. Featured images/posters are separate from video attachments. A theme ZIP does not transport uploads between sites.

## Colours and larger design changes

For small visual tweaks, use **Appearance → Customize → Additional CSS** if your host provides it. Example:

```css
:root {
  --color-background: #f5f1e8;
  --color-text: #191919;
  --color-border: #191919;
}
```

Preview contrast and keyboard focus before publishing. Additional CSS is saved in your site's database, not baked into the downloadable theme.

The overall grid, animation timeline, breakpoints and template structure are code-level choices. If you want those changed, use a child theme or ask a developer. Do not edit the installed parent files and expect changes to survive updates. Keep a copy of your modifications and follow the GPL when redistributing an adapted theme.

## Analytics, plugins and privacy

Install/configure analytics only if you want it; this theme has no preconfigured tracking account. The delayed GA4 integration applies to the recognised Jetpack snippet and does not create a property, provide a consent banner or replace a privacy policy. Verify any consent/plugin combination on your site.

Jetpack/VideoPress, SEO plugins, backups and caching are separate services. Most theme functionality works without them; ACF Free supplies editing fields. Do not add several overlapping cache/optimisation plugins at once. A host-provided cache may already do the job.
