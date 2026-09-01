# A+ Esthetic WordPress migration package

This package is intentionally non-destructive.

## Goal
Move the approved `newesthetic.pages.dev` presentation into the existing WordPress installation while preserving the current WordPress pages, post IDs, slugs, SEO-plugin metadata, plugins and database content.

## Safety model
- The migrator never replaces `post_content`.
- It never changes page slugs or post IDs.
- It does not delete Yoast/RankMath metadata or plugin data.
- It stores the approved staging HTML in page meta (`_aesthetic_snapshot_html`).
- It stores the previous page template before activation.
- Rollback removes the snapshot and restores the previous page template.
- Default mode is DRY RUN.
- GSC-driven title/meta improvements are applied at render time on selected routes; stored SEO-plugin fields remain untouched.

## Components
- `aesthetic-child/` — WoodMart child theme containing the snapshot page template and the production SEO bridge for selected GSC target routes.
- `aesthetic-migrator/` — admin-only migration plugin for dry-run/apply/rollback and legacy redirects.
- `route-manifest.json` — approved route list.
- GitHub Actions workflow `build-wordpress-package.yml` — builds installable ZIP files and bundles the current staging assets into the child theme.

## Recommended deployment sequence
1. Make a full backup of WordPress files and database.
2. Clone production to a WordPress staging/subdomain. Do not test first on production.
3. Download the generated `aesthetic-child.zip` and `aesthetic-migrator.zip` artifacts from GitHub Actions.
4. Install the child theme, but do not activate it yet.
5. Install and activate `A+ Esthetic Migrator`.
6. In WP Admin → Tools → A+ Esthetic Migration, run **Dry Run**.
7. Verify that all expected existing WordPress pages are found by path.
8. Activate the child theme on the WordPress staging site.
9. Run **Apply snapshots** in the migrator.
10. Crawl the WordPress staging site and visually check desktop/mobile, forms, booking buttons, WooCommerce/plugin pages and legal pages.
11. Only after approval repeat the same package installation on production during a controlled cutover.

## Important production-cutover checks
- Production must be `index,follow` (the static staging is intentionally `noindex,nofollow`).
- On the selected GSC target routes, the child theme supplies the reviewed title/meta description through WordPress core plus Yoast/RankMath-compatible filters; stored SEO-plugin metadata is not deleted or rewritten.
- Export and preserve all unrelated existing WordPress redirects before cutover.
- Preserve `/laserbehandlungen/` → `/laser-behandlungen/` as 301.
- Preserve `/impressum-2/` → `/impressum/` as 301.
- Redirect the empty `/category/unkategorisiert/` archive to `/behandlungen/`.
- Test contact forms and any plugin-generated forms separately because the visual snapshot layer does not alter their underlying plugin configuration.

## Rollback
WP Admin → Tools → A+ Esthetic Migration → **Rollback snapshots**.

Rollback restores each page's previous template and removes only the migration snapshot metadata. Original WordPress content remains untouched throughout.