# A+ Esthetic — SEO & Content Parity Audit

Audit scope: `https://a-esthetic.de/` vs `https://newesthetic.pages.dev/`

## Rule for final WordPress migration

The new design must be implemented as WordPress templates/components around the EXISTING production content. Do not replace existing WordPress page content with rewritten staging copy.

Preserve:
- existing production URL slugs
- existing WordPress page/post content
- existing SEO-plugin title/meta data
- H1/H2/H3 hierarchy and FAQ content
- current prices and medical/legal wording
- internal-link destinations
- existing schema where applicable
- production index/follow state

Staging remains `noindex,nofollow` with canonical pointing to the corresponding production URL.

## Status legend

- PASS — high-confidence production-content parity for the current staging purpose
- PARITY FIX — staging exists but visible copy is not sentence-for-sentence production copy
- VERIFY — staging exists; exact sentence-level parity still needs verification before migration
- MISSING — real production page exists but no staging child page exists
- REDIRECT — legacy URL must be preserved with 301

## Main pages

| Production path | Staging | Status | Notes |
|---|---|---|---|
| `/` | yes | PASS / LINK CLEANUP | Main visible copy substantially matches production. Staging still contains several absolute `a-esthetic.de` internal links. |
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
| `/datenschutzerklaerung/` | yes | PASS | Current production privacy content used. |

## Botox child pages

| Path | Status | Notes |
|---|---|---|
| `/botox-behandlungen/botox-stirnfalten-zornesfalte-frankfurt/` | VERIFY | Built before strict exact-copy rule. |
| `/botox-behandlungen/botox-lachfalten-kraehenfuesse-frankfurt/` | VERIFY | Built before strict exact-copy rule. |
| `/botox-behandlungen/browlift-botox-frankfurt/` | VERIFY | Built before strict exact-copy rule. |
| `/botox-behandlungen/lip-flip-frankfurt/` | PARITY FIX | Confirmed: staging hero/body wording differs from production. |
| `/botox-behandlungen/gummy-smile-botox-frankfurt/` | VERIFY | Built before strict exact-copy rule. |
| `/botox-behandlungen/masseter-botox-frankfurt/` | PARITY FIX | Confirmed: staging wording differs from production. |
| `/botox-behandlungen/nefertiti-lift-botox-frankfurt/` | VERIFY | Combined Platysma / Nefertiti landing page. |
| `/botox-behandlungen/traptox-barbie-botox-frankfurt/` | VERIFY | Built before strict exact-copy rule. |
| `/botox-behandlungen/hyperhidrose-botox-frankfurt/` | VERIFY | Built before strict exact-copy rule. |

## Hyaluronsäure child pages

| Path | Status | Notes |
|---|---|---|
| `/hyaluronsaure-behandlungen/lippenunterspritzung-frankfurt/` | PARITY FIX | Confirmed: production paragraph wording is longer/different; staging is condensed. Also staging footer currently points Datenschutz to `/datenschutz/` instead of `/datenschutzerklaerung/`. |
| `/hyaluronsaure-behandlungen/wangenaufbau-frankfurt/` | VERIFY | Built before strict exact-copy rule. |
| `/hyaluronsaure-behandlungen/jawline-kinnaufbau-frankfurt/` | VERIFY | Built before strict exact-copy rule. |
| `/hyaluronsaure-behandlungen/nasolabialfalte-hyaluron-frankfurt/` | VERIFY | Built before strict exact-copy rule. |

## Infusion child pages

These were built after the strict production-copy rule and are higher-confidence parity pages.

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

Existing staging pages:
- `/laser-behandlungen/laser-haarentfernung-achseln-frankfurt/` — VERIFY
- `/laser-behandlungen/laser-haarentfernung-ruecken-frankfurt/` — VERIFY
- `/laser-behandlungen/laser-haarentfernung-gesicht-frankfurt/` — VERIFY
- `/laser-behandlungen/laser-haarentfernung-intimbereich-frankfurt/` — VERIFY
- `/laser-behandlungen/laser-haarentfernung-beine-frankfurt/` — VERIFY

## Redirects / legacy URLs

- `/laserbehandlungen/` → `/laser-behandlungen/` — REDIRECT REQUIRED (301)

Before launch, export existing WordPress redirects / legacy indexed URLs and preserve every valid redirect target.

## Safe migration sequence

1. Keep current WordPress database and page content untouched.
2. Build the new visual layer as a custom theme / templates.
3. Render existing page content into the new templates rather than importing staging copy.
4. Preserve each current permalink exactly.
5. Preserve SEO plugin metadata and canonical behavior.
6. Preserve existing structured data / FAQ data or regenerate it from the same page content.
7. Add required legacy 301 redirects.
8. Run pre-launch crawl for 200/301/404, canonical, robots, title, meta, H1 and internal links.
9. Remove staging `noindex` only on production launch, not before.
10. Compare a production crawl before/after launch and fix any URL/content regression immediately.

## Current priority

1. Do NOT manually rewrite the confirmed PARITY FIX pages for final production.
2. Use the existing WordPress content as source of truth when implementing templates.
3. Complete the 8 MISSING real production child routes in the design system when their source content is available, or let the final WordPress template render their existing production content directly.
4. Verify all remaining `VERIFY` child pages before replacing any live template.
