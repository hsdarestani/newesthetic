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

- PASS — production copy has been matched at high confidence from the retrievable live source
- PASS* — production copy has been matched from the retrievable live source, but one source fragment was not exposed by the crawler and was intentionally not invented
- MISSING SOURCE — real production page/route exists, but its exact full child-page copy is currently not retrievable; no placeholder copy has been invented
- REDIRECT — legacy URL must be preserved with 301

## Main pages

| Production path | Staging | Status | Notes |
|---|---|---|---|
| `/` | yes | PASS / LINK CLEANUP | Main visible copy substantially matches production; some staging internal links still point to absolute production URLs and should be made local before final staging sign-off. |
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

All currently implemented production Botox child routes have been checked/rebuilt against retrievable live production copy.

| Path | Status |
|---|---|
| `/botox-behandlungen/botox-stirnfalten-zornesfalte-frankfurt/` | PASS |
| `/botox-behandlungen/botox-lachfalten-kraehenfuesse-frankfurt/` | PASS |
| `/botox-behandlungen/browlift-botox-frankfurt/` | PASS |
| `/botox-behandlungen/lip-flip-frankfurt/` | PASS |
| `/botox-behandlungen/gummy-smile-botox-frankfurt/` | PASS |
| `/botox-behandlungen/masseter-botox-frankfurt/` | PASS |
| `/botox-behandlungen/nefertiti-lift-botox-frankfurt/` | PASS |
| `/botox-behandlungen/traptox-barbie-botox-frankfurt/` | PASS |
| `/botox-behandlungen/hyperhidrose-botox-frankfurt/` | PASS |

## Hyaluronsäure child pages

| Path | Status |
|---|---|
| `/hyaluronsaure-behandlungen/lippenunterspritzung-frankfurt/` | PASS |
| `/hyaluronsaure-behandlungen/wangenaufbau-frankfurt/` | PASS |
| `/hyaluronsaure-behandlungen/jawline-kinnaufbau-frankfurt/` | PASS |
| `/hyaluronsaure-behandlungen/nasolabialfalte-hyaluron-frankfurt/` | PASS |

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
- `/injektions-lipolyse/fett-weg-spritze-doppelkinn-frankfurt/` — MISSING SOURCE
- `/injektions-lipolyse/fett-weg-spritze-oberarme-frankfurt/` — MISSING SOURCE
- `/injektions-lipolyse/fett-weg-spritze-oberschenkel-frankfurt/` — MISSING SOURCE

The parent production page confirms these child routes, but direct retrieval of the full child-page body currently fails. Do not infer their copy from the parent summaries.

## RF-Microneedling child pages

- `/rf-microneedling/aknenarben-frankfurt/` — PASS* — all retrievable production sections/FAQ were restored; the live crawler exposed the heading `Warnzeichen sofort medizinisch abklären` without its following paragraph, so no text was invented beneath it.
- `/rf-microneedling/poren-hautstruktur-frankfurt/` — PASS
- `/rf-microneedling/feine-linien-falten-frankfurt/` — PASS
- `/rf-microneedling/hautstraffung-frankfurt/` — MISSING SOURCE
- `/rf-microneedling/op-narben-frankfurt/` — MISSING SOURCE
- `/rf-microneedling/pigmentflecken-hautton-frankfurt/` — MISSING SOURCE

The parent production page confirms the three missing RF routes, but the full child-page copy currently returns a source/cache miss. No placeholder copy has been created.

## PRP child pages

- `/prp-behandlung/haarausfall-frankfurt/` — PASS
- `/prp-behandlung/gesicht-frankfurt/` — MISSING SOURCE

## Skinbooster child pages

- `/skinbooster/gesicht-frankfurt/` — MISSING SOURCE

## Laser child pages

All five implemented Laser child routes were rebuilt from the retrievable live production copy rather than the earlier condensed staging versions.

- `/laser-behandlungen/laser-haarentfernung-achseln-frankfurt/` — PASS
- `/laser-behandlungen/laser-haarentfernung-ruecken-frankfurt/` — PASS
- `/laser-behandlungen/laser-haarentfernung-gesicht-frankfurt/` — PASS
- `/laser-behandlungen/laser-haarentfernung-intimbereich-frankfurt/` — PASS
- `/laser-behandlungen/laser-haarentfernung-beine-frankfurt/` — PASS

Where production does not expose a confirmed numeric zone price (for example Rücken or Beine), staging intentionally preserves the production wording instead of inventing a price.

## Exact-source blockers remaining: 8

1. `/injektions-lipolyse/fett-weg-spritze-doppelkinn-frankfurt/`
2. `/injektions-lipolyse/fett-weg-spritze-oberarme-frankfurt/`
3. `/injektions-lipolyse/fett-weg-spritze-oberschenkel-frankfurt/`
4. `/rf-microneedling/hautstraffung-frankfurt/`
5. `/rf-microneedling/op-narben-frankfurt/`
6. `/rf-microneedling/pigmentflecken-hautton-frankfurt/`
7. `/prp-behandlung/gesicht-frankfurt/`
8. `/skinbooster/gesicht-frankfurt/`

These routes are real production routes, but the exact full child-page source is currently not retrievable through the available live-page fetch/search path. They must remain unbuilt until exact source copy is available. This is deliberate to protect content parity and SEO.

## Redirects / legacy URLs

- `/laserbehandlungen/` → `/laser-behandlungen/` — REDIRECT REQUIRED (301)

Before launch, export existing WordPress redirects / legacy indexed URLs and preserve every valid redirect target.

## Remaining staging work before WordPress cutover

1. Obtain exact production source for the 8 `MISSING SOURCE` routes and build them in the same new design without paraphrasing.
2. Clean Homepage staging-only absolute internal links so they route through staging while preserving the same semantic destinations.
3. Re-check title, description, canonical, robots, heading hierarchy, FAQ/schema and prices for every route.
4. Run a complete internal-link and HTTP-status crawl on staging.
5. Preserve required 301 redirects, including `/laserbehandlungen/` → `/laser-behandlungen/`.
6. Only after 100% parity, transfer the approved new design and exact text corpus to WordPress.

## Launch rule

Do not replace production with a page that contains inferred, shortened or AI-rewritten copy. The approved staging page must contain the same production text corpus; only visual structure, markup, styling, imagery and responsive behavior may change.