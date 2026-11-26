# Changelog

All notable changes to shrishtiindia.com. Newest first. Format: date, version, what changed, who approved.

## Content – 2026-11-26 (approved by Rajat Bagaria)
- Card gg007 (Geeta Goyal): backdrops added, shrishtiindia.com on the left and aura.holiday on the right (Media Library: "Card backdrop: …"). Settings only, no code change.

## [1.2.0] – 2026-11-26 — Card extras and rb009 (deployed, approved by Rajat Bagaria)
- shrishti-cards plugin 1.1.0: per-card "Other roles" (role, organisation, link, email; also in the vCard) and optional faded left/right backdrops on wide screens.
- Card rb009 for Rajat Bagaria (Managing Director; Co-Founder, MTF Events) with backdrops of shrishtiindia.com (left) and mtf.events (right).
- Tested locally and on the live site. Rollback: tag `v1.1.0` (plugin 1.0.0); a copy of plugin 1.0.0 is also on the server in `wp-content/upgrade/shrishti-cards-1.0.0-backup`.

## [1.1.0] – 2026-11-26 — Smart visiting cards (deployed, approved by Rajat Bagaria)
- New plugin `wordpress-plugins/shrishti-cards/` (per docs/visiting-cards/BUILD_SPEC.md): cards at `/<code>`, vCard at `/<code>.vcf`, QR at `/<code>.png`; noindex; 404/410 company card; admin editor, list columns, QR PNG/SVG downloads, email signature; Settings → Smart Cards.
- Optional "Say hello" panel (requested by Rajat): the visitor's details open as a ready WhatsApp message to the card holder; nothing is stored on the site.
- First card live: gg007, Geeta Goyal, Director (photo and details with her consent).
- Tested locally in WordPress Playground (PHP 8.3) and on the live site. Rollback point: tag `pre-smart-cards` (deactivate the plugin to roll back; no theme or page changes).

## [1.0.0] – 2026-11-25 — Handover baseline
Approved live site captured into this repository; no change to the live website.
- Repository created with theme, content exports, media, static snapshot, reference screenshots and documentation (README, CLAUDE, AGENTS, HANDOVER, maintenance guide).

## Build history before version control (Sep–Nov 2026, approved by Rajat Bagaria)
Exact dates for individual edits were not recorded. The site went live on WordPress at the end of September 2026.
- Replaced the Matour demo theme with the custom block theme **Shrishti Heritage** (Emerald Heritage design). Removed demo pages and media and switched off unused plugins.
- Built the one-page home in native blocks: hero, five signature services, Curated Affaires band, grouped services with photos, story, group, accreditations, founder, MTF Events, specialist programmes, preferred partners, contact.
- Added the enquiry form with email to info@shrishtiindia.com, an Enquiries admin page, JSON-LD, and a mobile Call bar. Added Privacy, Cancellation and Terms pages.
- Added the Shrishti script logo, section photos, accreditation logos (incl. Incredible India), specialist programme logos (10), hotel partner logos (20) and airline logos (11). Logo grids are logos only, in brand colours.
- Replaced the line-drawn globe with a live dotted globe (d3, self-hosted). The previous theme is kept in `archive/theme-before-globe/`.
- Content corrections: group size 50–1,250; corporate self-booking tool; TAFI roles 2007–2021 and convention years; founder bio rewritten; MTF timeline without size figures; after-hours line.
- Renamed the events brand to **Curated Affaires by Shrishti**.
