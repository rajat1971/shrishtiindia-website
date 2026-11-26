=== Shrishti Smart Cards ===
Requires at least: 6.5
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPL-2.0-or-later

Personal online visiting cards for the Shrishti team.

== Addresses ==
* Card:          https://shrishtiindia.com/<code>      (e.g. /gg007; /GG007 redirects to /gg007)
* Save contact:  https://shrishtiindia.com/<code>.vcf  (vCard 3.0 with photo)
* QR image:      https://shrishtiindia.com/<code>.png
Code = two letters + three digits. Unknown code: 404. Inactive card: 410 with company details only.
Cards are unlisted: X-Robots-Tag and meta robots noindex, nofollow, noarchive; not in sitemaps; not linked; not in robots.txt (on purpose).

== Admin ==
* Smart Cards (left menu): one entry per person. Untick "Active" when someone leaves.
* "Card links & QR" box: view/preview, vCard, QR download (PNG 1024 px, SVG).
* "Email signature" box: copy and paste into Outlook.
* Settings > Smart Cards: company details shared by all cards (phones, address, hours, group links, credentials, logo).

== Say hello ==
Optional per card. A visitor fills in first/last name, mobile (required), company and email (optional). WhatsApp opens on the visitor's own phone with the message addressed to the card holder's WhatsApp; the visitor presses Send. Nothing is sent to or stored on the website.

== Privacy ==
No cookies, no visitor data stored. The only counter is "Saved to contacts" (a number, no IPs).

== Third-party code ==
vendor/chillerlan/php-qrcode 5.0.5 (MIT / Apache-2.0) and vendor/chillerlan/php-settings-container 3.3.0 (MIT), loaded by the plugin's own autoloader. Fonts come from the shrishti-heritage theme (self-hosted); system fonts are used if that theme is missing.

== Spec ==
docs/visiting-cards/BUILD_SPEC.md in the shrishtiindia-website repository.
