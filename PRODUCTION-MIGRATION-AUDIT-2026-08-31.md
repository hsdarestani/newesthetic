# A+ Esthetic — Production Migration Audit

Date: 2026-08-31

## Migration state

- Approved route manifest: 52 production routes.
- Snapshot rollout: all route families applied through Skinbooster.
- Migration remains non-destructive: original `post_content`, Page IDs, slugs and SEO-plugin metadata are preserved.
- Child theme source of truth is synced in GitHub at version 1.0.13.
- Latest package build completed successfully.

## v1.0.13 production layer

- Official A+ Esthetic logo uses `https://a-esthetic.de/wp-content/uploads/6.png` in snapshot headers and the shared mobile drawer.
- Mobile hamburger is shared across all snapshot pages.
- Mobile hero quality rules apply across migrated pages.
- Global mobile overflow / typography guard remains active.
- First-paint `.reveal` animations are disabled on production so semantic copy never waits for scroll.
- Mobile density rules remove desktop fixed-height empty space after grids collapse.
- WoodMart, WoodMart Core, WPBakery, WooCommerce and Revolution Slider presentation assets are dequeued only on snapshot pages.
- Hero preload now prefers the actual `<picture><source>` asset rather than preloading a fallback `<img>` unnecessarily.
- First image is eager/high-priority; lower page images are lazy/async.
- Seers cookie consent remains excluded from legacy floating-widget cleanup and forced above the snapshot layer.
- Laser landing page has a real Damen/Herren + Einzel/4x/6x/8x production price selector.
- Home contact form posts through WordPress `admin-post.php`, validates nonce/consent, includes a honeypot, and sends through `wp_mail()` to `Info@a-esthetic.de`.

## SEO / URL integrity

The snapshot template imports presentation CSS/body content only. Static staging `noindex,nofollow`, static canonical tags and staging JSON-LD are not imported into WordPress. WordPress/Yoast remains authoritative for production head metadata.

The active snapshot template removes accidental WordPress `noindex`/`nofollow` flags on migrated production pages.

The migration manifest preserves the intended URL set and these aliases:

- `/laserbehandlungen/` -> `/laser-behandlungen/`
- Curcumin staging source alias maps to the existing production Curcumin route.

Public search/crawl checks on 2026-08-31 returned current discoverable results for the homepage, `/behandlungen/`, all eight main treatment families, Kontakt/Impressum, and multiple treatment child pages. Some direct fetches returned crawler cache misses; those are recorded as unverified rather than treated as page failures.

## Post-install runtime smoke test required

These checks can only be considered final after v1.0.13 is installed on production and caches are purged:

1. Header logo renders on desktop and mobile.
2. Mobile hamburger opens/closes and all menu links work.
3. Seers consent accepts/rejects without hanging.
4. Laser Damen/Herren and package discounts update the displayed price rows.
5. Submit one real homepage contact form and confirm receipt in `Info@a-esthetic.de` (SMTP/deliverability is hosting-dependent).
6. Representative desktop/mobile visual smoke test: Home, Behandlungen, Botox, Hyaluron, Infusion, Lipolyse, Laser, PRP, RF, Skinbooster, Kontakt, legal pages.
7. Confirm the two legacy aliases return the intended production destination with HTTP 301 using a browser/network or server-level HTTP check.
8. Purge WP Rocket and Cloudflare after deployment.

## Remaining architecture note

The live design is intentionally stored in `_aesthetic_snapshot_html`; the old WordPress editor `post_content` remains untouched for rollback safety. This is SEO-safe because the snapshot is server-rendered into the production HTML response, but it is not yet a fully native WordPress editing experience. Converting the new design into editor-managed blocks/templates would be a separate Phase 2, not required for production launch.
