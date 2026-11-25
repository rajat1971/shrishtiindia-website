# shrishtiindia.com — company website

The corporate one-page website of **Shrishti Tours & Travels Pvt Ltd** (Mumbai, since 1997, IATA accredited since 2006).
Live at **https://shrishtiindia.com**.

| | |
|---|---|
| Version | v1.0.0 (approved live site, handover snapshot of 25 Nov 2026) |
| Platform | WordPress 7.1.5 on Hostinger (PHP 8.3, LiteSpeed) |
| Code | Custom block theme `shrishti-heritage` (in `wordpress-theme/`) |
| Content | WordPress pages built from native Gutenberg blocks (copy in `content/`) |
| Design | "Emerald Heritage": emerald, cream and brass; Bodoni Moda + Figtree |

## What is in this folder

```
shrishtiindia-website/
├── wordpress-theme/shrishti-heritage/   The theme: all code, styles, fonts, scripts (deployable)
├── content/                             Page content, WordPress export (WXR), site settings
├── media/                               Every image in the WordPress media library (originals)
├── static-snapshot/                     Offline copy of the live home page (open in a browser)
├── docs/MAINTENANCE_GUIDE.md            Which file or screen to change for each kind of edit
├── docs/screenshots/                    Approved desktop / tablet / mobile reference screenshots
├── tools/                               Package the theme, preview the snapshot
├── archive/                             Rollback copies and earlier prototypes (not live)
├── CLAUDE.md / AGENTS.md                Permanent instructions for Claude Code and other AI assistants
├── HANDOVER.md                          Technical handover, hosting, deployment, restore, rollback
└── CHANGELOG.md                         History of changes
```

## Software needed

| Purpose | Software |
|---|---|
| Edit and version the code | [VS Code](https://code.visualstudio.com/), [Git for Windows](https://git-scm.com/download/win) |
| AI-assisted development | Claude Code (VS Code extension or `claude` in the terminal) |
| Preview the offline snapshot | Python 3 **or** Node.js (either one) |
| Run a full local WordPress copy (optional) | [Local](https://localwp.com/) (already installed on this PC: `C:\Users\Rajat\Local Sites`) |

There is no build step. The theme is plain PHP, HTML, CSS and JavaScript.

## Common commands (Windows PowerShell, run in this folder)

```powershell
# View the offline copy of the home page at http://localhost:8080
.\tools\preview-snapshot.ps1

# Make an installable theme ZIP in dist\shrishti-heritage.zip
.\tools\package-theme.ps1

# Save your work to GitHub
git add -A
git commit -m "Describe the change"
git push
```

## Deploying changes

Deploying means changing the **live** website. Never deploy without explicit approval from Rajat.
See **HANDOVER.md → Deployment** for the three supported routes:
- WordPress editor, for text, photos and logos
- Claude with the Novamira connector
- Theme ZIP upload, for code changes

See **HANDOVER.md → Rollback** to undo a change.

## Restore from scratch

See **HANDOVER.md → Full restore**. In short: fresh WordPress → install the theme ZIP → import `content/wordpress-export.xml` → copy `media/` into `wp-content/uploads/` → apply `content/site-settings.json`.
