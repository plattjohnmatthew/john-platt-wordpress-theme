# Using the theme's features

## Homepage

Edit the Page assigned as Home under **Settings → Reading**, then open **Homepage Content**.

| Tab/field | Controls |
| --- | --- |
| Intro → Label | Large name/label in the desktop opening grid |
| Intro → Headline | Main opening H1; new lines create deliberate line breaks |
| Intro → Body | Short italic desktop introduction; deliberately hidden on mobile |
| Intro → Supporting introduction copy | Copy beside the main opening statement |
| Intro → Opening portrait/image | Homepage portrait, also used as the browser tab icon when available |
| Work/Writing/Experiments → Opening grid statement | The short quick-link text, initially Lorem Ipsum placeholders |
| Opening grid symbol image | Small image beside that quick link; empty uses × |
| Label | Numbered section banner, e.g. 01 / WORK |
| Headline / Body | Main section heading and description |
| Image | Larger illustration in that section; independent of quick-link symbol/portrait |
| CTA label / CTA URL | Section call-to-action text/destination; blank URL uses the built-in archive destination |
| Contact → Email / LinkedIn URL | Shared contact information; email also supplies the footer |

The three top quick links go to Projects, Writing and AI-filtered Writing. Their text/images can change, but their routing is intentional. The public edition honours the separate section CTA URL override; you can send a section CTA somewhere else without coding.

Desktop uses the name/header transition and editorial grids. Mobile has one name, a hamburger menu and section illustrations beside headings; the header is not forced into the desktop morph. The portrait is not a mobile header icon. Headline lengths still matter: preview unusually long labels on a narrow phone.

## Writing and topics

Writing uses ordinary **Posts**, not a second article system. Publish in Posts; set the article title, body, featured image, excerpt and Writing Topics. The featured image appears in the archive and beside the article heading, with its date below. No image means the article remains readable without an empty image placeholder.

Manage filter terms under **Posts → Writing Topics**, not core Categories/Tags. Add, rename or delete terms there. Edit **Display Order** to order filters: 10, 20, 30 is easy to maintain. Topics are non-hierarchical and multiple assignments are supported.

On the frontend, **ALL** clears filters. Clicking another topic adds/removes it. Multiple selections use **OR**: AI + B2B means articles in either topic. Active terms have square brackets. URLs such as `/writing/?topics=ai,b2b` can be copied/shared; values sort deterministically. Pagination preserves the selection. With JavaScript, results update in place and browser Back/Forward works; without it, links navigate to the server-rendered result. Empty results include **SHOW ALL**.

Core Categories/Tags can still be used for other WordPress purposes; they do not populate this topic filter.

## Article formatting

Use native editor blocks:

- **Headings:** choose H2 for sections and H3 for subsections; the article title is the page's main H1. Body headings use the display stack.
- **Contents menu:** in the article's **Article Settings**, use **Enable article contents**. When enabled, the menu appears after introductory content, before the first H2. It requires **at least two real H2s**; H3-only content and Related widgets do not create it. H3s nest under their H2. Heading links are generated automatically.
- **Paragraph fonts:** select the Paragraph block, then the typography/font-family control: **Normal (Arial)** or **Impact (display)**. Depending on WordPress, open the Typography options menu to expose Font family. Use Normal for long text. If copied text carries inline styling, paste as plain text or clear its formatting; imported HTML can override theme defaults.
- **Separator:** the native Separator block becomes a full-screen-width rule with generous spacing in article/Project body content.
- **Quote:** use the native Quote block for a frame with full-width rules and body-sized text; a Pullquote or custom HTML block is a different element.
- **Images:** insert from the Media Library to get responsive variants, dimensions and alt text. Avoid copying image URLs from your local computer.
- **Embeds:** paste supported embed URLs or use Embed/Custom HTML blocks. Eligible visible HTTP(S) iframes get native lazy loading when no explicit loading instruction exists. Existing sizes, permissions and resize scripts are preserved; interactive charts stay interactive. Explicit eager/hidden communication frames are left alone.

The theme suppresses automatic Jetpack Share, Like and Related additions on reading layouts, including explicit corresponding Jetpack blocks. Its native previous/next article links are separate and remain available.

## Projects

Under **Projects → Add New**, enter title, case-study content, featured image and **Project Details**. The content editor is the full case study; **Archive narrative** is the copy shown on the Projects archive. An excerpt supplies a fallback. Client/company, Year, Role and External link label/URL are separate structured details.

### Presentation modes

| Mode | Archive behaviour |
| --- | --- |
| Immersive media | On eligible desktop, opening media dominates, then collapses toward the right as copy appears; final split gives time to read |
| Editorial / text-first | Static text-led presentation rather than a forced full-media opening |
| Simple | Direct static text-left/media-right composition; no artificial multi-viewport chapter |

The archive introduction appears first. Immersive enhancement requires valid media, desktop width of at least **1280 CSS pixels**, JavaScript, and reduced motion disabled. Mobile/tablet and reduced-motion/no-JS layouts are static and readable. Native vertical scrolling remains in control.

Use the Project's native **Order**/page-attributes order field for manual archive ordering; lower values come first, with date/ID fallbacks. Categories filter that archive rather than defining its order.

### Video and media

Select a Media Library attachment in **Optional MP4**. Uploaded `video/mp4` and public `video/videopress` attachments with a real MP4 URL are supported for archive/curated previews. A VideoPress block in the body alone does not populate the archive field. Do not paste a YouTube/Vimeo player URL into the attachment field.

Public VideoPress preview support uses the attachment's MP4, not the full adaptive VideoPress iframe player. Known-private VideoPress attachments and non-MP4 player URLs are excluded. Keep the VideoPress block in the case study for its existing player; the preview fix does not duplicate it there.

Add **Optional video poster** or a featured image for a still fallback. Supply an equivalent transcript/captions when speech matters. The archive does not automatically import VideoPress subtitle tracks into its native preview.

**Fill video opening frame** defaults on: the immersive opening crops/zooms to fill, then the final right column shows the complete video. Media proportions are derived from attachment metadata, with landscape media receiving a wider final column (up to about 40%). **Media fit** affects the static media presentation.

Eligible videos start muted, loop and use the same video node through the transition. **Pause/Play** respects a deliberate pause. The speaker icon on immersive Projects toggles mute without restarting; crossed-out means muted. Each video keeps its own sound choice while its node remains on the page. Off-screen/background videos pause, and only one is selected for playback. Browsers may reject autoplay; native controls are the fallback. Mobile/static layouts use native controls rather than requiring autoplay.

### Categories and article cross-listing

Create terms under **Projects → Project Categories**. Change **Display Order** there. Select existing categories from the dropdowns on a Project; **Add another category** allows multiple selections. Without JS, empty selector rows remain available. New categories are created on the category-management screen, not as free text while assigning them.

In a Post's **Show in Projects** box, tick **Include this article in the Projects archive**, choose Project categories and update the article. It then appears in the Projects backend/archive. Its CTA is **Read article** and opens the original Writing URL. It is not a duplicated Project post or a second SEO page; editing it still edits the original Post. Unchecking removes that cross-listing. Writing Topics and Project Categories are independent.

Project filters sit below PROJECTS and are visually larger than Writing filters. They use the same multi-select OR behaviour, shareable URLs such as `/projects/?categories=brand,ai`, pagination and no-JS fallback. Empty terms with no eligible published entries do not appear.

## Application portfolios

1. Open **Portfolios → Add New**.
2. Enter the application-specific title and introduction.
3. In **Selected Writing and Projects**, tick existing items and enter order numbers; lower numbers appear first.
4. Publish and copy the link, usually `/portfolio/your-slug/`.

You can also use the **Application Portfolios** box while editing a Post/Project to add/remove membership. Only published, non-password-protected selections are shown to visitors. Items open their original URLs. Each application has its own selection/order and no public portfolio archive is created.

These links are **unlisted, not secret or access-controlled**. The theme sends noindex and removes portfolios from common sitemap providers/search/menu pickers, but anyone with the link can read them and indexing instructions are not a security boundary. Do not put confidential information there. Changing themes/plugins or custom sitemaps may change discoverability, so verify your actual site. Removing a public menu link does not make a URL private.

## Contact, About, Index and footer

Contact body text is edited in its normal Page editor. **Contact CTA** chooses the Get in Touch destination and hand image. The homepage Contact email/LinkedIn settings supply shared details; Contact CTA destination overrides the email fallback. The footer's centred inquiries email uses that same editable email. Copyright uses your Site Title.

Index lists published Posts and Projects chronologically with year headings and pagination. Legacy About is supplied for compatibility; creating Contact makes `/about/` redirect there. The 404 template offers routes back into the site; no special setup is needed.

## Performance, analytics and integrations

The theme does **not** add Google Analytics on a fresh site. If your host/Jetpack already adds the recognised GA4 snippet, the theme delays its library until roughly 1–2 seconds after the initial layout has an opportunity to paint, preserving its existing command queue. No property ID is bundled. Very short visits/automatic interactions before startup can be missed. Existing consent/exclusion behaviour and any custom consent manager still need site-specific verification. Other analytics providers are not universally delayed.

Known optional plugin assets are omitted only on strictly checked plain custom pages; articles, plugin/gallery content, logged-in/admin/preview requests and declared dependencies retain them. The theme does not replace your hosting cache, install a cache plugin or promise a particular performance score. Use appropriately sized/compressed media and your host's existing caching.
