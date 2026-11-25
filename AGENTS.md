# AGENTS.md — instructions for AI coding assistants

This project's full, authoritative context is in **CLAUDE.md**. Read it first; this file does not repeat it. If the two ever disagree, CLAUDE.md wins and this file must be corrected.

## Ground rules (all assistants)
1. GitHub repo `shrishtiindia-website` (private) is the master copy. `git pull` before you start.
2. This is a WordPress block theme plus content exports, not a Node app. There is no build step and no `npm install`.
3. Code lives in `wordpress-theme/shrishti-heritage/`. Page content lives in the WordPress database. `content/` holds the latest exported copy.
4. Preserve the approved Emerald Heritage design, brand names ("Curated Affaires by Shrishti", "Aura Holiday", MTF Events as an independent company) and approved wording.
5. Never change the live site, hosting, DNS or email settings without explicit approval from Rajat Bagaria.
6. Never commit secrets, user accounts, database dumps or enquiry data.
7. Work on a branch for non-trivial changes. Update CHANGELOG.md. Report what changed, what was tested, and the GitHub sync status at the end of each session.

## Where to look
| Need | File |
|---|---|
| What to edit for a given change | `docs/MAINTENANCE_GUIDE.md` |
| Hosting, deploy, rollback, restore | `HANDOVER.md` |
| Visual reference | `docs/screenshots/` and `static-snapshot/index.html` |
