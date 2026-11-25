# HANDOVER — shrishtiindia.com v1.0.0

Handover date: 25 November 2026. Prepared by Claude from the live site, the build conversation and direct server checks.
**Status legend:** ✅ verified on 25 Nov 2026 · ⚠️ unconfirmed / needs checking.

## 1. What was handed over
The approved live website, exactly as it runs on shrishtiindia.com, with nothing redesigned or changed during the handover.

| Item | Location in this project | Source |
|---|---|---|
| Theme code, styles, fonts, scripts, data | `wordpress-theme/shrishti-heritage/` | Copied from the live server ✅ |
| Home page block content | `content/home.html` (+ `home-blocks.json` generator spec) | Live database ✅ |
| Policy pages | `content/privacy-policy.html`, `cancellation-policy.html`, `terms-of-use.html` | Live database ✅ |
| WordPress export (pages + 62 media items) | `content/wordpress-export.xml` | WordPress exporter, filtered to pages and attachments ✅ |
| Global styles (theme customisations stored in DB) | `content/global-styles.json` | Live database ✅ |
| Site settings (non-secret) | `content/site-settings.json` | Live database ✅ |
| All media files (originals) | `media/2026/09`, `media/2026/10` + `media/media-index.json` | `wp-content/uploads` ✅ |
| Offline view of the home page | `static-snapshot/index.html` | Rendered live page with all local assets ✅ |
| Reference screenshots | `docs/screenshots/` (desktop 1440, tablet 900, mobile 390) | Rendered from the snapshot ✅ |
| Rollback copy of the theme before the globe | `archive/theme-before-globe/` | Live server ✅ |
| Earlier static HTML prototype (never live) | `archive/standalone-html-prototype/index.html` | Build conversation |
| Pending asset: founder sketch portrait | `archive/pending-assets/rajat-bagaria-sketch.jpg` | Build conversation |

**Deliberately excluded:** WordPress core and plugins (reinstall from wordpress.org / Hostinger), `wp-config.php`, user accounts and passwords, the Novamira plugin and its tokens, the 6 stored website enquiries (customer personal data), database dumps. The WXR export contains the admin author name and email, which WordPress needs for import. It is kept only because the repository is private.

## 2. Technology stack
WordPress 7.1.5 ✅ · PHP 8.3.37 ✅ · MariaDB 11.8 ✅ · LiteSpeed + LiteSpeed Cache 7.9 ✅ · custom block theme `shrishti-heritage` 1.0.0 ✅ · no page builder · no build step · no npm dependencies.
Front-end libraries bundled in the theme: d3-geo 3, d3-array 3 (globe), Natural Earth 110m land data. Fonts: Bodoni Moda, Figtree (self-hosted woff2).

## 3. Hosting, domain and services

| Item | Detail | Status |
|---|---|---|
| Hosting provider | Hostinger (shared/business plan, user `u216457838`) | ✅ |
| Production path | `/home/u216457838/domains/shrishtiindia.com/public_html/` | ✅ |
| Theme path | `public_html/wp-content/themes/shrishti-heritage/` | ✅ |
| Server IP | 145.79.213.126 | ✅ |
| DNS | Hostinger DNS (nameservers `ns1/ns2.dns-parking.com`) | ✅ |
| Domain registrar | Hostinger | ⚠️ likely, confirm in hPanel → Domains |
| SSL | Let's Encrypt, auto-renewed by Hostinger, current cert valid to 1 Feb 2027 | ✅ issuer/date · ⚠️ auto-renew assumed |
| www | `www.shrishtiindia.com` → 301 → `https://shrishtiindia.com/` | ✅ |
| Redirects | `shrishti.net` and `shrishti-holidays.com` → 301 → shrishtiindia.com | ✅ |
| Email for the domain | Microsoft 365 (MX `shrishtiindia-com.mail.protection.outlook.com`) | ✅ |
| Enquiry email delivery | PHP `wp_mail` from the server, no SMTP plugin | ✅ setup · ⚠️ inbox delivery test pending |
| Booking portal | book.shrishti.net (separate system, not part of this project) | ✅ |
| Claude access to the live site | Novamira plugin (MCP), connected to Claude as "Novamira Shrishti", acts as WordPress user ID 1 | ✅ |
| Hostinger Reach | Plugin active; injects `cdn-reach.hostinger.com/js/embed.js` | ✅ |
| Analytics, Search Console, Business Profile | Not set up | ✅ (absent) |

## 4. Deployment
The live site is WordPress, so code and content deploy differently. **Get Rajat's explicit approval before any live change.**

**A. Content (text, photos, logos):** edit in wp-admin → Pages → Home, then Update. Or ask Claude with the Novamira connector. Then purge LiteSpeed Cache and export the changes back into `content/` (§6).

**B. Theme code (CSS, PHP, templates, scripts):**
1. Make the change on a branch, test (§7), merge to `main`, `git tag vX.Y.Z`, push.
2. Run `.\tools\package-theme.ps1` to create `dist\shrishti-heritage.zip`.
3. Upload the ZIP: wp-admin → Appearance → Themes → Add New → Upload Theme, then choose **Replace current with uploaded**. Alternatives: Hostinger hPanel → File Manager into the theme path above, or ask Claude to write the files through Novamira.
4. Purge LiteSpeed Cache and check the live site on desktop and mobile.

**C. Via Claude (Novamira):** Claude can write theme files and queue block-content changes. Block-content changes need the wp-admin page *Novamira → Block Editor Queue* (`/wp-admin/admin.php?page=novamira-gutenberg-finalize`) open in a logged-in browser tab while they save. A Claude Code session can reach the same plugin if the Novamira MCP server is added to Claude Code (endpoint `/wp-json/mcp/novamira` with a WordPress Application Password) ⚠️ not yet configured.

## 5. Rollback
- **Theme code:** check out the previous tag (`git checkout v1.0.0 -- wordpress-theme`), package and upload it as in §4B. The pre-globe theme is also in `archive/theme-before-globe/`. To remove only the globe, delete the "Hero globe" block at the end of `functions.php` (the CSS falls back to `assets/globe.svg`).
- **Page content:** wp-admin → Pages → Home → Revisions (WordPress keeps every saved version), or paste `content/home.html` into the Code editor view of the page.
- **Whole site:** Hostinger hPanel → Websites → Backups (daily/weekly backups, depending on plan ⚠️ check retention), or the full restore below.

## 6. Keeping the repo in sync with the live site
GitHub is the master for **code**. The database is the master for **content** until it is exported. After any content edit:
- Ask Claude (Novamira connector): *"Export the live shrishtiindia.com home page, policy pages, settings and any new media into the repo's content/ and media/ folders, then commit."*
- Or by hand: wp-admin → Tools → Export → All content saves `content/wordpress-export.xml`. For `content/home.html`, open Pages → Home, choose Code editor and copy everything. New images: download them from Media into `media/YYYY/MM/`.
Commit with a message such as `content: update hotel logos`.

## 7. Testing before any deploy
1. `.\tools\preview-snapshot.ps1` shows the approved look offline, for comparison.
2. For real testing, use a local WordPress copy in **Local** (§8), install the changed theme, and import the content.
3. Check at 1440px, 900px and 390px widths. Check that all logos and photos load, the menu anchors scroll correctly, the mobile menu and Call bar work, and there are no console errors other than the blocked Hostinger Reach script.
4. Compare against `docs/screenshots/`.

## 8. Full restore (fresh WordPress, e.g. in Local or a new Hostinger site)
1. Install WordPress 6.5+ (PHP 8.1+). Settings → Permalinks → Post name.
2. Package and upload the theme (§4B), then activate **Shrishti Heritage**.
3. Plugins → Add New → install and activate **WordPress Importer**. Then Tools → Import → WordPress → upload `content/wordpress-export.xml`, assign content to your admin user, and leave **Download and import file attachments** unticked.
4. Copy the contents of `media/` into `wp-content/uploads/` (keep the `2026/09`, `2026/10` folders). Image URLs then resolve. If the site URL differs from shrishtiindia.com, run a search-replace (e.g. the Better Search Replace plugin) from `https://shrishtiindia.com` to the new URL.
5. Settings → Reading → "A static page", front page **Home**. Settings → General: title "Shrishti Tours & Travels", tagline from `content/site-settings.json`. Settings → Privacy: Privacy Policy page.
6. Optional: set the WhatsApp number option `shrishti_whatsapp`. Install LiteSpeed Cache on LiteSpeed hosting.
7. Compare with `docs/screenshots/`.

## 9. Verification done for this handover (25 Nov 2026)

| Check | Result |
|---|---|
| Bundle integrity (server → PC) | ✅ SHA-256 identical (`e203fc38…8a4b`) |
| Snapshot opens and renders | ✅ desktop, tablet, mobile |
| Images | ✅ 54/54 loaded, 0 broken (after lazy images loaded) |
| Fonts | ✅ Bodoni Moda and Figtree loaded locally |
| Hero globe | ✅ renders on desktop · hidden on tablet/mobile by design |
| Console errors | ✅ none except the blocked external Hostinger Reach script (expected offline) |
| Theme package script | ✅ produces an installable ZIP (28 files) |
| Secrets scan of theme and content | ✅ none found |
| Live enquiry form | ⚠️ not submitted during handover (would email the office). Rajat to test. |
| Live links (tel, mailto, footer pages) | ✅ present and correct in content · ⚠️ not clicked end-to-end |
| Full restore into a fresh WordPress | ⚠️ not performed (no test WordPress available in this session); steps in §8 |
| Production website changed | ✅ No. Only a temporary download bundle was created in `wp-content/uploads` and removed straight after. |

## 10. Known issues and pending items
See CLAUDE.md §9. The most important: WhatsApp number, founder portrait, the official IATA logo, renaming "Shrishti Holidays" to Aura Holiday at launch, a legal review of the policy pages, an enquiry email test, and Search Console / Business Profile.

## 11. GitHub repository
| Item | Value |
|---|---|
| Repository | https://github.com/rajat1971/shrishtiindia-website |
| Visibility | Private |
| Primary branch | `main` |
| Baseline commit | `c884d68` (v1.0.0 content); this record added in the next commit |
| Release tag | `v1.0.0`. It exists in the local repository; the first push from Rajat's PC publishes it (`git push origin v1.0.0`), then create the GitHub release from it (HANDOVER §13). |
| Local working copy | `F:\GDrive\Claude Backups\shrishtiindia-website` (Google Drive synced folder, chosen by Rajat) |
| Offline backup | `F:\GDrive\Claude Backups\shrishtiindia-website-v1.0.0.zip` |

Note on Google Drive: the project folder, including `.git`, syncs to Google Drive. That is fine for a single user. Avoid editing the same repo on two PCs at once through Drive; use `git pull`/`git push` instead.

## 12. Opening in VS Code and starting Claude Code
1. Install Git for Windows and VS Code if not already installed. In VS Code, install the **Claude Code** extension (or use `claude` in a terminal).
2. VS Code → File → Open Folder → `F:\GDrive\Claude Backups\shrishtiindia-website`. Accept "Trust the authors".
3. Open the terminal (Ctrl+`) and run `git status` (should say "working tree clean") and `git pull`.
4. Start Claude Code in that folder. It reads `CLAUDE.md` automatically.
5. First time only: `git push origin v1.0.0`, then on github.com → Releases → Draft a new release → tag `v1.0.0` → title "v1.0.0 — Approved live site" → Publish.

## 13. Creating the GitHub release
The handover session could push the `main` branch but its GitHub proxy refused tag pushes and release creation. Two minutes on your PC:
```powershell
cd "F:\GDrive\Claude Backups\shrishtiindia-website"
git push origin v1.0.0
```
Then on GitHub: Releases → Draft a new release → choose `v1.0.0` → title *v1.0.0 — Approved live site (25 Nov 2026)* → notes: copy the 1.0.0 section of CHANGELOG.md → Publish release. Optionally attach `shrishtiindia-website-v1.0.0.zip`.
