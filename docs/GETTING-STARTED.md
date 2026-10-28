# Getting started: no coding required

This guide creates a new portfolio site. If replacing an existing theme, first back up the **database and uploads as well as theme files**. A theme ZIP does not contain your writing or settings. Prefer staging before changing a live site.

## 1. Install the theme and editing plugin

Download `john-platt-theme.zip` from the repository's Releases page. Do not unpack it before WordPress upload. Choose **Appearance → Themes → Add New → Upload Theme**, select the ZIP, install and activate **John Platt**. If updating, WordPress may ask to replace the installed version.

Under **Plugins → Add New**, search for **Advanced Custom Fields**, install and activate the free plugin. Do not buy ACF Pro for this theme. The field definitions are included in PHP and appear automatically: no ACF import, API key or separate field-group setup is needed.

If Themes/Plugins upload is missing, check that you are an administrator and your hosting plan permits custom themes/plugins.

## 2. Set your identity

Open **Settings → General**. Set your **Site Title** to your name/studio and add a tagline. The header and copyright use the Site Title. Save.

## 3. Create and assign the homepage

Create a normal Page called **Home** and publish it. Create a second Page called **Writing**, with slug `writing`, and publish it.

Open **Settings → Reading**:

- Select **A static page**.
- Homepage: **Home**.
- Posts page: **Writing**.
- Choose how many posts should appear per archive page, then save.

Home uses the theme's homepage automatically. Writing must be assigned as the **Posts page** to get the article archive. Its normal editor content supplies the large introductory paragraph, not a manually built article list.

Edit Home and find **Homepage Content** beneath the editor. In **Intro**, set your Label, Headline, Body, Supporting introduction copy and Opening portrait/image. Use intentional new lines in the Headline. Complete **Work**, **Writing** and **Experiments** tabs. In the **Contact** tab, enter your own email; it supplies the public footer and fallback Contact CTA. These Contact tab fields are settings, not an extra homepage contact section.

If custom fields are not visible, confirm ACF is active, Home is assigned under Reading, and the editor's Preferences/Panels have not hidden custom fields/meta boxes. Look beneath the main editor, not just the right sidebar. Blank text fields can show fallback copy; enter your replacement copy rather than expecting every blank field to remove a section.

## 4. Set up Projects

The `/projects/` address is the automatic **Projects archive**. Do **not** create a normal Page with slug `projects`: it can compete with that archive.

To add the introduction, create a normal Page called **Projects introduction** with slug **`projects-info`**. Write the introductory text in its editor and publish it. Keep this helper page out of navigation. Do not put confidential content there: it is a published Page, not a private settings record.

Under **Projects → Add New**, add your first Project. Publish it with Presentation Mode **Simple** to start. Add a featured image and an archive narrative/excerpt. Read [Project modes and video](FEATURES.md#projects) before choosing Immersive.

If `/projects/` is a 404, visit **Settings → Permalinks → Save Changes** once. If you previously created a competing Projects Page, rename its slug or move only that unwanted page to Trash; do not delete your Project entries.

## 5. Create Contact and Index

Create a Page **Contact**, slug `contact`. Choose the **Contact** template if it is not selected automatically. Add your heading/paragraphs to the editor. In **Contact CTA**, set **Get in Touch destination**, e.g. `mailto:you@example.com` or an HTTPS contact link. An optional CTA hand image overrides the homepage symbol. Without a destination, the homepage Contact email supplies the fallback; without either, no dead CTA is shown.

Create a Page **Index**, slug `index`, using the **Index** template. Its editor content is the introduction; published Writing and Projects are listed automatically. Portfolios are excluded. An Index link appears in the footer when that published page exists.

The legacy About template is supplied, but when Contact exists the old `/about/` address redirects to Contact. This distribution is designed around one Contact destination, not two independent About/Contact sections.

## 6. Build the menu

Open **Appearance → Menus**. Create a menu, add Home, Writing and Contact, then add a **Custom Link** for the Projects archive (your domain followed by `/projects/`). Set Home's navigation label to **Home**, even if the page/site title is your name. Order the links as desired and assign the menu to **Primary navigation**.

The theme does not guess or create a menu. An unassigned menu can leave the navigation empty. If you cannot see the menu screen, try the classic WordPress dashboard; this is a classic theme, not a Navigation-block-only theme.

## 7. Publish content and filters

- Add Writing under **Posts → Add New**. Set a featured image and optional excerpt.
- Create terms under **Posts → Writing Topics**. Assign one or more to each article.
- Create separate terms under **Projects → Project Categories**. Select them on your Projects and any articles included in Projects.
- Set **Display Order** on terms: smaller numbers appear first; ties sort by name. Terms without eligible published content are hidden from the frontend filters.
- If using Experiments, create a Writing Topic named **AI** (slug `ai`) and assign relevant posts. The homepage's default Experiments quick link uses it.

No terms or demonstration articles are created permanently by the theme.

## 8. Before sharing your site

Check logged out, on your phone and on a desktop: menu, footer email, Writing filters, Project links, Contact CTA and images. Verify you have replaced fallback text and added useful alt text in the Media Library. Test reduced motion and keyboard navigation. Confirm unlisted portfolio links are not in menus.

Check that image/video URLs are public and not addresses from a local computer. If moving a whole existing site, migrate its database and uploads or re-enter content; uploading the theme alone is not a site migration.

Once an update is installed, clear your host's relevant page/edge cache once if old markup persists. Frequent cache purges slow down visitors while the cache rebuilds. Keep backups and the previous theme ZIP available for rollback.
