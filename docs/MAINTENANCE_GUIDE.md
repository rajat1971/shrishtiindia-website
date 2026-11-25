# Maintenance guide: what to change, where

There are two kinds of change:

- **Content** (words, photos, logos, links) is stored in the WordPress database. Edit it in **wp-admin → Pages → Home** (block editor), or ask Claude (Novamira connector). Afterwards, re-export it into `content/` (HANDOVER.md → Keeping the repo in sync).
- **Code and design** (colours, fonts, layout, header/footer structure, form, scripts) is stored in the theme `wordpress-theme/shrishti-heritage/`. Edit it in VS Code, commit, then deploy (HANDOVER.md → Deployment).

Theme path below = `wordpress-theme/shrishti-heritage/`. Home page = WordPress page ID 6 ("Home"); its saved copy is `content/home.html`.

| Change | Where | Notes |
|---|---|---|
| Any text on the home page | Pages → Home in wp-admin | Click the text and type. Each section is a Group block with a CSS class (below). |
| Company logo (header/footer) | `parts/header.html`, `parts/footer.html` (image ID 270, `Shrishti-W3.png`) | Or Appearance → Editor → Patterns → Header/Footer. Size: `.brand-logo img` / `.foot-logo img` in `style.css` |
| Photos in service cards | Home page blocks in `#more` (Cruises/Charter/Tickets) and `#essentials` (Visas/Rail/Forex) | Click the image → Replace. Size rules: `.cards3 .pic img`, `#essentials .pic img` |
| Accreditation logos (IATA, TAFI, TAAI, IATO, Incredible India) | Home page, section `#credentials`, group `.logos4.logos5` | Tile size `.logo-tile` |
| Specialist programme logos | Home page, section `#specialists`, group `.badges`, tiles `.b-tile` | Logos only, 6 per row, last row centred (`style.css` "v1.2 logo rows") |
| Hotel partner logos | Home page, section `#partners`, group `.wordmarks.hotels`, tiles `.w-tile` | 5 per row ("v1.3"); colours/size "v1.4 partner logos" |
| Airline partner logos | Same section, group `.wordmarks.airlines` | 6 per row |
| Business divisions and links | Home page sections `#holidays`, `#corporate`, `#hotels`, `#curated-affairs`, `#corporate-events`, `#weddings`; group card in `.family` | When Aura Holiday launches: change "Shrishti Holidays" text and point holiday links to https://aura.holiday |
| Founder bio and timelines | Home page, section `#founder` | Portrait box: `.portrait` (currently "RB" placeholder) |
| MTF Events section | Home page, section `#mtf` | Keep it labelled as an independent venture |
| Contact details (address, phones, email, hours) | Home page `#contact` **and** `parts/header.html` (top bar) **and** `parts/footer.html` **and** `functions.php` (JSON-LD `telephone`, `address`, `openingHours`; mobile bar `tel:` link) | Change all four places together |
| Enquiry form recipient | `functions.php` → `SHRISHTI_ENQUIRY_TO` | Form fields/labels: `shrishti_enquiry` shortcode in `functions.php`; styling `.sh-form` |
| Stored enquiries | wp-admin → Enquiries | Data in option `shrishti_enquiries` (last 500). Never put this in Git. |
| WhatsApp button (mobile) | WordPress option `shrishti_whatsapp` (digits incl. 91) | Rendered by `functions.php` `wp_footer` hook; without it the bar shows "Email us" |
| Department email links on cards | Home page content: `mailto:info@shrishtiindia.com?subject=…` links | Find-and-replace the address per card |
| Social media links | Not on the site yet | Add to `parts/footer.html` (e.g. a Social Icons block) |
| Navigation menu | `parts/header.html` (`wp:navigation-link` items pointing to `/#section-id`) | Mobile menu overlay colours set there and in `.sh-menu` CSS |
| Add / remove / reorder sections | Home page in the block editor (drag Group blocks), or `content/home-blocks.json` → re-generate via Claude | Keep the `anchor` ids the menu uses |
| Colours | `style.css` `:root` variables **and** `theme.json` `settings.color.palette` | Keep both in step |
| Fonts | `assets/fonts/` + `assets/fonts/fonts.css`; families in `:root` (`--serif`, `--sans`) and `theme.json` | Self-hosted only |
| Hero globe animation | `assets/js/globe.js` (colours `C`, speed `0.006`, Mumbai marker), data `assets/data/land-110m.json`, enqueued at the end of `functions.php` | Hidden ≤900px. Roll back: remove the "Hero globe" block in `functions.php`; `assets/globe.svg` is the static fallback |
| Animations / transitions | `style.css` (hover zoom on photos, logo hover), `globe.js` | Respect `prefers-reduced-motion` |
| Desktop / tablet / mobile layout | `style.css` media queries at 1100px, 900px, 560px (search "@media") | Several later "v1.x" blocks override earlier ones; edit the latest block |
| SEO title / description | Settings → General (site title, tagline). Per-page: page title | No SEO plugin installed. Structured data in `functions.php` |
| Favicon / site icon | Appearance → Editor → Site Icon (or Settings → General) | Not set yet |
| Social sharing preview image | Not set | Needs an SEO plugin or an `og:image` tag added in `functions.php` |
| Footer text and copyright | `parts/footer.html` | Year is fixed text "© 2026" |
| Policy pages | Pages → Privacy Policy / Cancellation Policy / Terms of Use; copies in `content/*.html` | Footer links in `parts/footer.html` |
| Header top bar text | `parts/header.html` (`.topbar`) | |

After any theme edit, the stylesheet version updates automatically (`filemtime` in `functions.php`), so browsers fetch the new CSS. LiteSpeed Cache may still serve old HTML: purge it from the WordPress admin bar (LiteSpeed Cache → Purge All).
