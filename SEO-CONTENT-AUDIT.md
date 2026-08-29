# A+ Esthetic — SEO & Content Parity Audit

Audit scope: `https://a-esthetic.de/` vs `https://newesthetic.pages.dev/`

## Final migration rule

The target is a completely new visual design with the current production content preserved sentence-for-sentence.

For every page preserve exactly:
- production permalink / slug
- visible production copy
- H1/H2/H3 text and hierarchy
- FAQ questions and answers
- prices and medical/legal wording
- relevant internal-link destinations
- SEO-plugin title/meta data at production cutover
- structured data / FAQ schema where applicable

The staging design may change markup, layout, wrappers, components, CSS and imagery, but it must not paraphrase, shorten or expand production copy.

Staging remains `noindex,nofollow` with canonical pointing to the corresponding production URL.

## Status legend

- PASS — production copy has been matched at high confidence
- VERIFY — staging exists; exact sentence-level parity still needs verification
- MISSING — real production page exists but no staging child page exists
- REDIRECT — legacy URL must be preserved with 301

## Main pages

| Production path | Staging | Status | Notes |
|---|---|---|---|
| `/` | yes | PASS / LINK CLEANUP | Main visible copy substantially matches production; some staging internal links still point to absolute production URLs. |
| `/behandlungen/` | yes | PASS | Parent content audited. |
| `/botox-behandlungen/` | yes | PASS | Parent content audited. |
| `/hyaluronsaure-behandlungen/` | yes | PASS | Parent content audited. |
| `/infusionstherapien/` | yes | PASS | Parent content audited. |
| `/injektions-lipolyse/` | yes | PASS | Parent content audited. |
| `/laser-behandlungen/` | yes | PASS | Parent content audited. |
| `/prp-behandlung/` | yes | PASS | Parent content audited. |
| `/rf-microneedling/` | yes | PASS | Parent content audited. |
| `/skinbooster/` | yes | PASS | Parent content audited. |
| `/kontakt/` | yes | PASS | Production contact data used. |
| `/impressum/` | yes | PASS | Current legal data used. |
| `/datenschutzerklaerung/` | yes | PASS | Current privacy content used. |

## Botox child pages

| Path | Status | Notes |
|---|---|---|
| `/botox-behandlungen/botox-stirnfalten-zornesfalte-frankfurt/` | VERIFY | Built before strict exact-copy rule. |
| `/botox-behandlungen/botox-lachfalten-kraehenfuesse-frankfurt/` | VERIFY | Built before strict exact-copy rule. |
| `/botox-behandlungen/browlift-botox-frankfurt/` | VERIFY | Built before strict exact-copy rule. |
| `/botox-behandlungen/lip-flip-frankfurt/` | PASS | Rebuilt against current production copy. |
| `/botox-behandlungen/gummy-smile-botox-frankfurt/` | VERIFY | Built before strict exact-copy rule. |
| `/botox-behandlungen/masseter-botox-frankfurt/` | PASS | Rebuilt against current production copy. |
| `/botox-behandlungen/nefertiti-lift-botox-frankfurt/` | VERIFY | Combined Platysma / Nefertiti landing page. |
| `/botox-behandlungen/traptox-barbie-botox-frankfurt/` | VERIFY | Needs sentence-level verification. |
| `/botox-behandlungen/hyperhidrose-botox-frankfurt/` | VERIFY | Needs sentence-level verification. |

## Hyaluronsäure child pages

| Path | Status | Notes |
|---|---|---|
| `/hyaluronsaure-behandlungen/lippenunterspritzung-frankfurt/` | PASS | Rebuilt against current production copy; internal Datenschutz link issue removed in rebuilt footer. |
| `/hyaluronsaure-behandlungen/wangenaufbau-frankfurt/` | VERIFY | Built before strict exact-copy rule. |
| `/hyaluronsaure-behandlungen/jawline-kinnaufbau-frankfurt/` | VERIFY | Built before strict exact-copy rule. |
| `/hyaluronsaure-behandlungen/nasolabialfalte-hyaluron-frankfurt/` | VERIFY | Built before strict exact-copy rule. |

## Infusion child pages

- `/infusionstherapien/vitamin-c-infusion-frankfurt/` — PASS
- `/infusionstherapien/nad-infusion-frankfurt/` — PASS
- `/infusionstherapien/vagusvit-n-forte-infusion-frankfurt/` — PASS
- `/infusionstherapien/mitoenergy-infusion-frankfurt/` — PASS
- `/infusionstherapien/curcumin-infusion-frankfurt-ablauf-kosten/` — PASS
- `/infusionstherapien/vitamin-b-komplex-infusion-frankfurt/` — PASS
- `/infusionstherapien/glutathion-infusion-frankfurt/` — PASS

## Injektionslipolyse child pages

- `/injektions-lipolyse/fett-weg-spritze-bauch-frankfurt/` — PASS
- `/injektions-lipolyse/fett-weg-spritze-huefte-frankfurt/` — PASS
- `/injektions-lipolyse/fett-weg-spritze-doppelkinn-frankfurt/` — MISSING
- `/injektions-lipolyse/fett-weg-spritze-oberarme-frankfurt/` — MISSING
- `/injektions-lipolyse/fett-weg-spritze-oberschenkel-frankfurt/` — MISSING

## RF-Microneedling child pages

- `/rf-microneedling/aknenarben-frankfurt/` — VERIFY
- `/rf-microneedling/poren-hautstruktur-frankfurt/` — VERIFY
- `/rf-microneedling/feine-linien-falten-frankfurt/` — VERIFY
- `/rf-microneedling/hautstraffung-frankfurt/` — MISSING
- `/rf-microneedling/op-narben-frankfurt/` — MISSING
- `/rf-microneedling/pigmentflecken-hautton-frankfurt/` — MISSING

## PRP child pages

- `/prp-behandlung/haarausfall-frankfurt/` — PASS
- `/prp-behandlung/gesicht-frankfurt/` — MISSING

## Skinbooster child pages

- `/skinbooster/gesicht-frankfurt/` — MISSING

## Laser child pages

- `/laser-behandlungen/laser-haarentfernung-achseln-frankfurt/` — VERIFY
- `/laser-behandlungen/laser-haarentfernung-ruecken-frankfurt/` — VERIFY
- `/laser-behandlungen/laser-haarentfernung-gesicht-frankfurt/` — VERIFY
- `/laser-behandlungen/laser-haarentfernung-intimbereich-frankfurt/` — VERIFY
- `/laser-behandlungen/laser-haarentfernung-beine-frankfurt/` — VERIFY

## Redirects / legacy URLs

- `/laserbehandlungen/` → `/laser-behandlungen/` — REDIRECT REQUIRED (301)

Before launch, export existing WordPress redirects / legacy indexed URLs and preserve every valid redirect target.

## Safe migration sequence

1. Finish all new-design staging pages with exact production copy.
2. Verify every current production URL has the same visible copy and heading content in staging.
3. Preserve production SEO-plugin title/meta values at cutover.
4. Keep every current permalink exactly the same.
5. Preserve or recreate schema from the exact same FAQ/content.
6. Preserve all required legacy 301 redirects.
7. Run a pre-launch crawl for 200/301/404, canonical, robots, title, meta, H1 and internal links.
8. Deploy the new design to WordPress without changing the approved text corpus.
9. Keep production `index,follow`; staging remains `noindex,nofollow`.
10. Compare production crawl before/after launch and fix any regression immediately.

## Current priority

1. Verify and fix every remaining `VERIFY` child page against live production text.
2. Build the 8 `MISSING` child pages only from exact production source copy.
3. Clean staging internal links without changing their semantic destinations.
4. Only after 100% parity, package the new design for WordPress deployment.