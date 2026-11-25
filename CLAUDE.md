# CLAUDE.md — shrishtiindia.com

Permanent context for Claude Code. Read this before every session. Facts marked *(unconfirmed)* still need checking.

## 1. Business background
- **Shrishti Tours & Travels Pvt Ltd**, Mumbai. Founded 1996–97, IATA accredited 2006. Team of about 20.
- **Founder & CEO:** Rajat Bagaria (non-developer, decides everything about this site). TAFI office bearer 2007–2021.
- **Memberships:** IATA, TAFI, TAAI, IATO; affiliated with Incredible India (Ministry of Tourism).
- **Office:** 315 Lotus House, New Marine Lines, Mumbai 400020 · +91-22-22053535 / 22053636 · info@shrishtiindia.com
- **Group brands:**
  - **Shrishti Tours & Travels** (this site): corporate travel, ticketing, visas, hotels.
  - **Curated Affaires by Shrishti** (French spelling, deliberate): luxury destination weddings and events. Never write "Curated Affairs".
  - **Aura Holiday** (tagline "Curated Journeys"; "by Shrishti" as a sub-line): the holidays division, future site at aura.holiday. The site still says "Shrishti Holidays" in a few places until Aura is live (see §9).
  - **MTF Events** is NOT part of Shrishti. It is an independent company co-founded by three agency owners (Rajat one of them) that runs Melange. Always describe it that way.
- Booking portal: **book.shrishti.net** (separate system, untouched by this project).

## 2. Purpose and audience
A brochure-style "visiting card" used in Shrishti's advertising. Audiences: corporate travel buyers, HNI and family holiday clients, wedding/event clients, the travel trade. Goal: credibility, then an enquiry, call or email.

## 3. Technology and architecture
- WordPress 7.1.5, PHP 8.3, MariaDB 11.8, LiteSpeed web server + LiteSpeed Cache, on **Hostinger**.
- **Custom block theme `shrishti-heritage`** (`wordpress-theme/shrishti-heritage/`). No page builder. No build step.
  - `theme.json`: colour palette, fonts, layout widths (what the block editor sees)
  - `style.css`: all visual styling. Plain CSS. Later rules override earlier ones, so append fixes at the end in a labelled block.
  - `functions.php`: enqueues, enquiry form (`[shrishti_enquiry]` shortcode), Enquiries admin page, Google structured data, mobile Call/WhatsApp bar, hero globe scripts
  - `templates/`, `parts/header.html`, `parts/footer.html`: block templates
  - `assets/fonts/`: self-hosted Bodoni Moda + Figtree (no Google Fonts calls)
  - `assets/js/globe.js` + `d3-geo` / `d3-array` + `assets/data/land-110m.json`: rotating dotted globe in the hero (desktop only)
- **Content lives in the WordPress database**, not in theme files. The home page (ID 6) is one page of native Gutenberg blocks. `content/home.html` is a copy of it, and `content/home-blocks.json` is the block structure Claude used to generate it.
- Active plugins: LiteSpeed Cache, Hostinger Tools / Easy Onboarding / Reach, **Novamira** (lets Claude edit the live site). Inactive, unused: Elementor, Gutenverse ×3, Starter Templates, WordPress Importer, License For Envato.
- Hostinger Reach injects `https://cdn-reach.hostinger.com/js/embed.js` on the front end.

## 4. Page structure (home page, top to bottom)
Top bar → sticky header (Shrishti script logo, menu, "Plan with us") → hero (headline, globe, stats 1997 / 2006 / 50–1,250 / 2010) → Curated Holidays → Corporate Travel (incl. corporate self-booking tool) → Hotels & Villas → **Curated Affaires by Shrishti** band → Corporate Events → Weddings & Social Events → Cruises/Air Charter/Sports & Event Tickets (photos) → Visas/Worldwide Railways/Passport & Forex (photos) → Our Story timeline → The Shrishti group → Accredited and affiliated (5 logos in one row) → Founder & CEO (bio + TAFI and convention timelines) → MTF Events (independent venture) → Specialist programmes (10 logos only) → Preferred partners (20 hotel logos, 11 airline logos) → Contact + enquiry form → footer.
Other pages: `/privacy-policy/`, `/cancellation-policy/`, `/terms-of-use/`.

## 5. Approved design rules (do not change without Rajat's approval)
- Palette: emerald `#0F3B33`, deep emerald `#0A2A24`, cream `#F4EFE4`, paper `#FAF6EE`, sand `#EAE2D2`, ink `#1B2320`, muted `#4E5A55`, brass `#7A5C24`, light brass `#C7A263`, on-dark text `#CFD9D3`.
- Type: Bodoni Moda (headings, numbers), Figtree (body). Self-hosted only.
- Layout: brochure feel, generous spacing, hairline rules, square corners. Content max width 1248px, side padding `clamp(20px, 6.5vw, 96px)`.
- Logo grids: logos only (no captions), white tiles, 6 per row for specialists/airlines, 5 per row for hotels and accreditations, last row centred. Partner logos in original colours with `mix-blend-mode: multiply` so white file backgrounds disappear. Do not grey-scale them (Rajat rejected that look).
- Header logo 44px tall. Photos in service cards same size; Travel essentials photos are small 170px strips.
- Responsive: ≤1100px tablet rules, ≤900px single column + mobile Call/Email bar, ≤560px phone tweaks. Globe hidden on mobile. Respect `prefers-reduced-motion`.
- No stock photos of suppliers (e.g. Ottila); use Rajat's own or licensed Unsplash/Envato images. Avoid club/league logos and famous players.
- Copy: third person, plain and confident, no invented figures. Founder bio wording was approved by Rajat; keep it.

## 6. Coding standards
- WordPress-native: blocks in content, CSS in `style.css`, PHP hooks in `functions.php`. No page builders, no inline `<script>` in content, no raw HTML blocks unless unavoidable.
- Escape all output (`esc_html`, `esc_url`, `esc_attr`); sanitise all input; nonce-check forms.
- CSS: use the custom properties in `:root`; append changes in a labelled block; keep `!important` to overrides of WordPress core styles.
- Keep fonts and scripts local. Never add Google Fonts, CDNs or tracking without approval.
- Windows paths: forward slashes in docs/scripts where possible; files are UTF-8, LF line endings (see `.gitattributes`).

## 7. Integrations
- **Enquiry form** posts to `admin-post.php` → emails `info@shrishtiindia.com` via `wp_mail` (PHP mail, no SMTP plugin) and stores a copy in the `shrishti_enquiries` option, visible at wp-admin → **Enquiries**. Enquiry data is customer personal data: never export it into this repo.
- **Mobile bar:** Call `+912222053535`; WhatsApp if option `shrishti_whatsapp` is set (digits with country code), otherwise "Email us".
- **Email links** on service cards: `mailto:info@shrishtiindia.com?subject=…` in the page content.
- **Structured data:** TravelAgency JSON-LD in `functions.php`.
- Email for shrishtiindia.com runs on Microsoft 365 (MX `shrishtiindia-com.mail.protection.outlook.com`). Do not touch DNS MX records.

## 8. GitHub and workflow (mandatory)
- GitHub repo **`shrishtiindia-website`** (private) is the master copy of the code. See HANDOVER.md for its URL.
- Before starting: `git pull`, read CLAUDE.md, HANDOVER.md and docs/MAINTENANCE_GUIDE.md.
- Preserve approved design, wording and functionality unless Rajat explicitly asks for a change. No rewrites or tech changes without approval.
- Use a branch for anything bigger than a small fix (`git switch -c feature/<name>`), merge to `main` when approved.
- Test locally (static snapshot, or Local WP copy) before deploying. Check desktop 1440px, tablet 900px, mobile 390px.
- Update CHANGELOG.md and any affected docs. Commit with clear messages. Push to GitHub.
- **Never deploy to the live site without Rajat's explicit "yes".** Keep a rollback path (git tag before deploy, theme ZIP of the previous version).
- Never commit passwords, application passwords, API keys, `.env`, database dumps with users, or enquiry data.
- **If content is edited in WordPress** (text, images), re-export it into `content/` (see HANDOVER.md → Keeping the repo in sync), so GitHub does not fall behind the live site.
- At the end of every session, report: changes made, tests done, GitHub sync status (commit id, pushed or not), pending items.

## 9. Known limitations and pending work
- WhatsApp number not set; the mobile bar shows "Email us".
- Founder portrait: placeholder "RB" monogram. A pencil-sketch portrait is ready in `archive/pending-assets/rajat-bagaria-sketch.jpg` (Rajat deferred it).
- IATA logo file is low resolution; the official one from the IATA Customer Portal should replace it.
- Hotel and airline logos: several source files are small (under 200px) and may look soft. Lufthansa and one hotel logo failed to upload; Rajat said to ignore them.
- "Shrishti Holidays" still appears in the footer, group card and holiday links. Change to **Aura Holiday** and link to aura.holiday when that site goes live.
- shrishti.net and shrishti-holidays.com currently redirect (301) to shrishtiindia.com.
- Policy pages (Privacy, Cancellation, Terms) are standard drafts. Rajat's CA/lawyer should review them before ad campaigns.
- Email deliverability of the enquiry form uses PHP mail; a test is pending on Rajat's side. If mails go to spam, add SMTP through the Microsoft 365 mailbox.
- MTF stats strip still shows "100,000 sq ft" and "4,000+": confirm with Rajat whether to keep them.
- No Google Search Console / Business Profile / analytics set up yet.
- Static snapshot is a view-only copy; the enquiry form and dynamic WordPress features do not work in it.

## 10. Key decisions log (from the build conversation, Sep–Nov 2026)
- WordPress with a custom block theme chosen over Elementor or the Matour theme: native editing for Rajat, fast, on-brand.
- Emerald Heritage palette on the Option A brochure layout; Shrishti content first, founder and MTF after.
- Events brand renamed to "Curated Affaires by Shrishti" (numerology + luxury feel).
- Holidays brand: "Aura Holiday", tagline "Curated Journeys", domain aura.holiday.
- Logo sections are logos only; names without logos were removed.
- MICE group size shown as 50–1,250 (Macau convention had 1,200 delegates).
- After-hours line: "Beyond office hours: urgent travel matters only."
