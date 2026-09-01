# Search Console SEO improvements — 2026-09-01

This pass is based on the 2026-09-01 Google Search Console export for a-esthetic.de.
The visual design and core medical/pricing content remain intact. The changes are deliberately limited to search-intent signals and consistency.

## Implemented

- Homepage: added a Frankfurt-focused semantic H1 while preserving the existing visual hero statement; fixed visible German typos and formal-address consistency.
- Botox: strengthened `Botox Frankfurt` / price intent in H1 and description; aligned visible FAQ wording to formal `Sie/Ihre`.
- Masseter: kept the exact-match H1; added a missing meta description and a more informative SERP title with price orientation.
- PRP, Hyaluron, Infusionen, Skinbooster and Injektionslipolyse: made parent-page H1s explicitly local to Frankfurt where Search Console showed opportunity.
- Hyaluron: replaced the most visible informal `du/dein` copy with formal address.
- Legacy cleanup: `/impressum-2/` → `/impressum/`; empty `/category/unkategorisiert/` → `/behandlungen/`; retained the laser legacy redirect.
- WordPress bridge: route-level SEO title/description filters were added because the snapshot renderer intentionally imports styles/body but not `<title>` or meta description. Core title handling, Yoast and Rank Math filters are covered without changing stored SEO-plugin metadata.

## Guardrails

- Staging keeps `noindex,nofollow` and production canonicals.
- No new treatment claims or guarantees were introduced.
- Existing child landing pages and their canonical routes are preserved; no new overlapping infusion pages were created.
- Existing WordPress page content, post IDs, slugs and SEO-plugin database fields remain untouched by the migrator.
