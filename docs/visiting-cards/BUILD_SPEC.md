# Shrishti Smart Visiting Cards — build spec

Brief for Claude Code. Approved sample format: `sample-card.html` in this folder (open with the two images beside it). Read CLAUDE.md first; the Emerald Heritage design rules apply.

## 1. What it is
A personal, phone-first online visiting card for each Shrishti team member, opened by QR code, by a link in an email signature, or by typing a short address. Visitors can call, WhatsApp, email, open the websites and **save the person straight into their phone contacts**.

## 2. Addresses
- Card: `https://shrishtiindia.com/<code>`, e.g. `/rb009` (Rajat Bagaria), `/gg007`.
- Contact file: `https://shrishtiindia.com/<code>.vcf`
- QR image: `https://shrishtiindia.com/<code>.png` (or generate in admin, see §7)
- Code format: 2 lowercase letters + 3 digits (`^[a-z]{2}[0-9]{3}$`). Lowercase only; redirect uppercase to lowercase.
- A code must never clash with a WordPress page slug. Reserve the pattern.
- Unknown or deactivated code: show a polite "This card is no longer active" page with the company card details (HTTP 410 for deactivated, 404 for unknown). Never reveal other codes.

## 3. Not searchable, not indexed
"Unlisted", not secret (anyone with the link can open it):
- `X-Robots-Tag: noindex, nofollow, noarchive` HTTP header **and** `<meta name="robots" content="noindex, nofollow, noarchive">` on cards, `.vcf` and `.png`.
- Not in any sitemap, not linked from the website, no listing page.
- Do **not** add the codes to robots.txt: a Disallow would publish the list and stop crawlers seeing the noindex.
- Open Graph tags (title, role, photo) so WhatsApp/LinkedIn link previews look good; these do not cause indexing.

## 4. Card content (top to bottom, as in the sample)
1. Emerald header: Shrishti script logo, "Tours & Travels · Est. 1997".
2. Round photo (brass ring). Staff photo or sketch; fallback: initials monogram.
3. Name (Bodoni Moda), designation, "Shrishti Tours & Travels".
4. **Save to contacts** (primary button) → `/<code>.vcf`.
5. Quick actions: Call · WhatsApp · Email · Website.
6. Details list with Copy buttons: mobile/WhatsApp, office numbers, email, office address (+ Google Maps link), office hours.
7. The Shrishti group: Shrishti Tours & Travels (shrishtiindia.com), Aura Holiday (aura.holiday), Curated Affaires by Shrishti (shrishtiindia.com/#curated-affairs).
8. Share block: QR code of the card URL, the short address, Copy link, and a native Share button where `navigator.share` exists.
9. Credentials line: IATA accredited since 2006 · Member TAFI · TAAI · IATO.
10. Footer: company name, card code.
Optional per person: LinkedIn, second WhatsApp text, languages spoken, department (e.g. Corporate desk, Visas).

## 5. Fields
| Per person (admin edits) | Company (one settings screen, applies to all cards) |
|---|---|
| code, active yes/no, first name, last name, designation, department | company name, logo, office phones, address, map link, hours |
| photo (square, min 400×400), mobile, WhatsApp number (if different), email | website links + labels for the group list |
| optional: LinkedIn URL, WhatsApp greeting text, note | credentials line, vCard NOTE text, default photo/monogram |

## 6. vCard (`/<code>.vcf`)
- vCard **3.0**, UTF-8, CRLF line endings, lines folded at 75 octets; escape `\ , ;` in values.
- Headers: `Content-Type: text/vcard; charset=utf-8`, `Content-Disposition: attachment; filename="<firstname>-<lastname>-shrishti.vcf"`, plus the noindex header.
- Fields: `N`, `FN`, `ORG`, `TITLE`, `TEL;TYPE=CELL` (mobile), `TEL;TYPE=WORK` ×2 (office), `EMAIL;TYPE=WORK`, `ADR;TYPE=WORK`, `URL;TYPE=WORK` (shrishtiindia.com), `URL` (card link), `NOTE`, `PHOTO;ENCODING=b;TYPE=JPEG` (base64, resized to 240×240, under ~25 KB), `REV`.
- Expected behaviour: iPhone Safari → "Create New Contact" sheet; Android Chrome → downloads, opens in Contacts; desktop → downloads the file.
- The sample's vCard panel shows the exact layout.

## 7. QR codes
- Encodes `https://shrishtiindia.com/<code>` (short URL = simpler, more scannable code). Error correction M, 2-module quiet zone, ink `#0A2A24` on `#FAF6EE` or white.
- Admin: "Download QR" as PNG (1024px, for print) and SVG; generated server-side with a local library (e.g. `chillerlan/php-qrcode` bundled in the plugin). No external QR services.
- Use on printed cards, email signature, office desk stand, event badges.

## 8. Implementation in WordPress (recommended)
- A small **plugin** `shrishti-cards` (not inside the theme, so cards survive theme changes), stored in this repo under `wordpress-plugins/shrishti-cards/`.
- Custom post type `shrishti_card`: `public => false`, `show_ui => true`, no archive, no REST exposure of mobile numbers to anonymous users.
- Rewrite rules: `^([a-z]{2}[0-9]{3})/?$`, `^([a-z]{2}[0-9]{3})\.vcf$`, `^([a-z]{2}[0-9]{3})\.png$` → plugin handlers. Flush rewrite rules on activation.
- Card page renders as a **standalone minimal document** (own `<head>`, the theme's self-hosted fonts and a small card CSS), not the full site header and footer. Target under 100 KB plus photo; loads fast on 4G.
- Admin list columns: code, name, designation, active, opens (optional), links to View / vCard / QR.
- Company settings page under Settings → Smart Cards.
- Optional: a privacy-friendly open counter and Save counter per card (no cookies, no IP storage).
- Cache: LiteSpeed may cache card pages; purge a card on save. Never cache `.vcf` responses with stale data (`Cache-Control: no-cache` or purge on save).

## 9. Email signature snippet
Generated per card in admin, ready to paste into Outlook:
name · designation · "Shrishti Tours & Travels" · office phone · `shrishtiindia.com/rb009` (link) · small QR (optional).

## 10. Privacy and staff lifecycle
- Staff consent before publishing mobile numbers and photos. A personal home address never goes on the card.
- When someone leaves: set inactive. The address then shows the company card (no personal details), and the vCard stops working.
- Never store or log visitors' personal data.

## 11. Acceptance tests
- iPhone (Safari, iOS 17+) and Android (Chrome): card opens, Save adds the contact with photo, Call/WhatsApp/Email work, Copy works, QR scans from a printed card.
- Desktop Chrome/Edge: card centred, vCard downloads.
- Response headers include `X-Robots-Tag: noindex…`; Google search for the name + "shrishtiindia.com/rb009" finds nothing after launch; card URLs are absent from the sitemap.
- Unknown code → 404 page; inactive code → 410 company card.
- Lighthouse mobile performance ≥ 90; no layout shift; accessible labels on icon buttons; contrast AA.
- Deploy only after Rajat approves (CLAUDE.md §8). Add to CHANGELOG.

## 12. Inputs still needed from Rajat
- Team list: code, name, designation, mobile/WhatsApp, email, photo (or sketch), for each person.
- Whether staff photos are real photos or pencil sketches (the sample uses Rajat's sketch).
- Rajat's own mobile/WhatsApp number for rb009 (placeholder in the sample).
