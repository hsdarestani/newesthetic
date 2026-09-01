from pathlib import Path
import json

ROOT = Path(__file__).resolve().parents[1]
changed = []


def read(path):
    return (ROOT / path).read_text(encoding="utf-8")


def write(path, text):
    p = ROOT / path
    p.write_text(text, encoding="utf-8")
    changed.append(path)


def replace_once(path, old, new):
    text = read(path)
    count = text.count(old)
    if count != 1:
        raise RuntimeError(f"{path}: expected exactly 1 occurrence, found {count}: {old[:90]!r}")
    write(path, text.replace(old, new, 1))


def replace_all(path, old, new, minimum=1):
    text = read(path)
    count = text.count(old)
    if count < minimum:
        raise RuntimeError(f"{path}: expected at least {minimum} occurrences, found {count}: {old[:90]!r}")
    write(path, text.replace(old, new))


def update_json_redirects(path, additions):
    data = json.loads(read(path))
    redirects = data.setdefault("redirects", {})
    redirects.update(additions)
    write(path, json.dumps(data, ensure_ascii=False, indent=2) + "\n")


# ---------------------------------------------------------------------------
# Homepage: preserve the visual statement while adding a query-relevant H1.
# ---------------------------------------------------------------------------
replace_once(
    "index.html",
    '<meta name="description" content="A+ Esthetic Frankfurt – moderne ästhetische Medizin, Hautbildverbesserung, Vitalitätsbehandlungen, Botox, Hyaluron, PRP, Skinbooster, Laser und mehr.">',
    '<meta name="description" content="Ästhetische Medizin in Frankfurt: Botox, Hyaluron, PRP, Skinbooster, Infusionen, Laser und mehr. Ärztliche Beratung bei A+ Esthetic.">',
)
replace_once(
    "index.html",
    '<h1 class="hero-title">Frische &amp; Vitalität<br>für Ihre natürliche<br><span>Ausstrahlung</span></h1>',
    '<h1 class="seo-h1">Ästhetische Medizin in Frankfurt – natürlich &amp; individuell</h1><div class="hero-title">Frische &amp; Vitalität<br>für Ihre natürliche<br><span>Ausstrahlung</span></div>',
)
replace_all("index.html", "Ästehtische Behandlungen auf höhstem Niveau", "Ästhetische Behandlungen auf höchstem Niveau", minimum=2)
replace_all("index.html", "Deine Experten für Ästhetische Medizin", "Ihre Experten für ästhetische Medizin", minimum=1)
if "Geben Deine Telefonnummer ein" in read("index.html"):
    replace_all("index.html", "Geben Deine Telefonnummer ein", "Telefonnummer eingeben")


# ---------------------------------------------------------------------------
# Botox: strongest generic opportunity in GSC; make local intent explicit.
# ---------------------------------------------------------------------------
replace_once(
    "botox-behandlungen/index.html",
    '<meta name="description" content="Ob Stirnfalten, Augenpartie, Zornesfalte oder Kieferbereich. Botox-Behandlungen können mimisch bedingte Falten gezielt reduzieren und ein frisches, entspanntes Erscheinungsbild unterstützen.">',
    '<meta name="description" content="Botox in Frankfurt ab 119 €: Stirnfalten, Zornesfalte, Krähenfüße, Masseter und weitere Bereiche. Ärztliche Beratung bei A+ Esthetic.">',
)
replace_once(
    "botox-behandlungen/index.html",
    '<h1>Faltenbehandlung:<br><span>Einfach, innovativ</span><br>und effektiv.</h1>',
    '<h1>Botox in Frankfurt –<br><span>natürliche Faltenbehandlung</span><br>ab 119 €</h1>',
)
for old, new in [
    ("Welches Botox verwendet ihr?", "Welches Botox wird verwendet?"),
    ("So läuft deine Behandlung ab:", "So läuft Ihre Behandlung ab:"),
    ("deine Haut", "Ihre Haut"),
    ("deine Mimik", "Ihre Mimik"),
    ("du wirkst", "Sie wirken"),
    ("Du bist", "Sie sind"),
    ("deinem Stoffwechsel", "Ihrem Stoffwechsel"),
]:
    if old in read("botox-behandlungen/index.html"):
        replace_all("botox-behandlungen/index.html", old, new)


# Masseter: already has a good exact-match H1; improve SERP information only.
masseter = "botox-behandlungen/masseter-botox-frankfurt/index.html"
replace_once(
    masseter,
    '<title>Masseter Botox Frankfurt | Kieferkontur & Kosten</title>',
    '<title>Masseter Botox Frankfurt | Kieferkontur ab 299 € | A+ Esthetic</title><meta name="description" content="Masseter Botox in Frankfurt ab 299 €: ärztliche Beurteilung des Kaumuskels, Kieferkontur, Ablauf, Risiken und Kosten bei A+ Esthetic.">',
)


# ---------------------------------------------------------------------------
# Parent treatment pages that sit just outside / around page one.
# ---------------------------------------------------------------------------
replace_once(
    "prp-behandlung/index.html",
    '<h1>PRP-Behandlung –<br><span>natürliche Hautregeneration</span><br>mit Eigenblut</h1>',
    '<h1>PRP in Frankfurt –<br><span>Eigenbluttherapie</span><br>für Haut &amp; Haare</h1>',
)

replace_once(
    "hyaluronsaure-behandlungen/index.html",
    '<meta name="description" content="Gönn deiner Haut intensive Feuchtigkeit und Volumen mit Hyaluronsäure-Behandlungen. Ob feine Linien, Falten oder Volumenverlust – Hyaluronsäure bringt deine natürliche Frische zurück und sorgt für glatte, strahlende Haut.">',
    '<meta name="description" content="Hyaluron in Frankfurt für Lippen, Konturen und Faltenbehandlung. Individuelle ärztliche Beratung und transparente Behandlungsplanung bei A+ Esthetic.">',
)
replace_once(
    "hyaluronsaure-behandlungen/index.html",
    '<h1>Deine natürliche Fülle<br><span>wiederentdecken</span></h1>',
    '<h1>Hyaluron in Frankfurt –<br><span>Lippen, Konturen &amp; Falten</span></h1>',
)
for old, new in [
    ("Gönn deiner Haut", "Gönnen Sie Ihrer Haut"),
    ("deine natürliche Frische", "Ihre natürliche Frische"),
    ("schenkt deinem Gesicht sofort neue Frische und Spannkraft", "kann Ihrem Gesicht neue Frische und harmonische Konturen verleihen"),
]:
    if old in read("hyaluronsaure-behandlungen/index.html"):
        replace_all("hyaluronsaure-behandlungen/index.html", old, new)

replace_once(
    "infusionstherapien/index.html",
    '<meta name="description" content="Revitalisieren Sie Ihren Körper mit individuell abgestimmten Infusionstherapien in Frankfurt. Je nach Bedarf können ausgewählte Vitamine, Mikronährstoffe und Wirkstoffkombinationen zur Unterstützung von Energie, Regeneration, Immunsystem und allgemeinem Wohlbefinden eingesetzt werden.">',
    '<meta name="description" content="Vitamininfusionen und Infusionstherapie in Frankfurt: Vitamin C, B-Komplex, Glutathion, NAD+ und weitere Optionen nach ärztlicher Beratung bei A+ Esthetic.">',
)
replace_once(
    "infusionstherapien/index.html",
    '<h1>Infusionstherapie –<br><span>Energie, Gesundheit &amp; Beauty</span><br>von innen heraus</h1>',
    '<h1>Infusionstherapie in Frankfurt –<br><span>Vitamine &amp; Mikronährstoffe</span><br>individuell abgestimmt</h1>',
)
if "unterstützt dein Immunsystem, schützt deine Zellen" in read("infusionstherapien/index.html"):
    replace_all(
        "infusionstherapien/index.html",
        "unterstützt dein Immunsystem, schützt deine Zellen",
        "kann das Immunsystem unterstützen, zum Zellschutz beitragen",
    )

replace_once(
    "skinbooster/index.html",
    '<h1>SkinBooster<br><span>by Neauvia</span></h1>',
    '<h1>Skinbooster in Frankfurt<br><span>by Neauvia</span></h1>',
)

replace_once(
    "injektions-lipolyse/index.html",
    '<h1>Gezielte Behandlung<br><span>kleiner Fettdepots</span></h1>',
    '<h1>Injektionslipolyse in Frankfurt<br><span>für kleine Fettdepots</span></h1>',
)


# ---------------------------------------------------------------------------
# Legacy / empty URLs: consolidate signals instead of leaving crawlable noise.
# ---------------------------------------------------------------------------
redirect_lines = [
    "/laserbehandlungen/ /laser-behandlungen/ 301",
    "/impressum-2/ /impressum/ 301",
    "/category/unkategorisiert/ /behandlungen/ 301",
]
write("_redirects", "\n".join(redirect_lines) + "\n")
redirect_map = {
    "/impressum-2/": "/impressum/",
    "/category/unkategorisiert/": "/behandlungen/",
}
update_json_redirects("wordpress-package/aesthetic-migrator/route-manifest.json", redirect_map)
update_json_redirects("wordpress-package/route-manifest.json", redirect_map)


# ---------------------------------------------------------------------------
# Production WordPress SEO bridge.
# Snapshot import deliberately strips title/meta; these route filters ensure
# GSC-driven SERP metadata reaches production while leaving page/post content,
# IDs and SEO-plugin storage untouched. Supports core + Yoast + Rank Math.
# ---------------------------------------------------------------------------
functions_path = "wordpress-package/aesthetic-child/functions.php"
functions = read(functions_path)
marker = "/* GSC SEO route targets — 2026-09-01 */"
if marker not in functions:
    anchor = "function aesthetic_is_snapshot_page() {\n    return is_singular('page') && (bool) get_post_meta(get_queried_object_id(), '_aesthetic_snapshot_html', true);\n}\n"
    if anchor not in functions:
        raise RuntimeError("functions.php: SEO insertion anchor not found")
    block = r'''

/* GSC SEO route targets — 2026-09-01 */
function aesthetic_gsc_seo_target() {
    if (!is_singular('page')) { return null; }

    $post_id = get_queried_object_id();
    $permalink = get_permalink($post_id);
    $path = trim((string) wp_parse_url($permalink, PHP_URL_PATH), '/');
    $route = $path === '' ? '/' : '/' . $path . '/';

    $targets = array(
        '/' => array(
            'title' => 'A+ Esthetic Frankfurt | Ästhetische Medizin, Botox & Laser',
            'description' => 'Ästhetische Medizin in Frankfurt: Botox, Hyaluron, PRP, Skinbooster, Infusionen, Laser und mehr. Ärztliche Beratung bei A+ Esthetic.',
        ),
        '/botox-behandlungen/' => array(
            'title' => 'Botox Frankfurt | Faltenbehandlung ab 119 € | A+ Esthetic',
            'description' => 'Botox in Frankfurt ab 119 €: Stirnfalten, Zornesfalte, Krähenfüße, Masseter und weitere Bereiche. Ärztliche Beratung bei A+ Esthetic.',
        ),
        '/botox-behandlungen/masseter-botox-frankfurt/' => array(
            'title' => 'Masseter Botox Frankfurt | Kieferkontur ab 299 € | A+ Esthetic',
            'description' => 'Masseter Botox in Frankfurt ab 299 €: ärztliche Beurteilung des Kaumuskels, Kieferkontur, Ablauf, Risiken und Kosten bei A+ Esthetic.',
        ),
        '/prp-behandlung/' => array(
            'title' => 'PRP Frankfurt | Eigenbluttherapie für Haut & Haare | A+ Esthetic',
            'description' => 'PRP-Behandlung in Frankfurt für Haut und Haare. Eigenbluttherapie mit plättchenreichem Plasma, individuell ärztlich geplant bei A+ Esthetic.',
        ),
        '/hyaluronsaure-behandlungen/' => array(
            'title' => 'Hyaluron Frankfurt | Lippen, Filler & Faltenbehandlung | A+ Esthetic',
            'description' => 'Hyaluron in Frankfurt für Lippen, Konturen und Faltenbehandlung. Individuelle ärztliche Beratung und transparente Behandlungsplanung bei A+ Esthetic.',
        ),
        '/infusionstherapien/' => array(
            'title' => 'Vitamininfusion Frankfurt | Infusionstherapie | A+ Esthetic',
            'description' => 'Vitamininfusionen und Infusionstherapie in Frankfurt: Vitamin C, B-Komplex, Glutathion, NAD+ und weitere Optionen nach ärztlicher Beratung bei A+ Esthetic.',
        ),
        '/skinbooster/' => array(
            'title' => 'Skinbooster Frankfurt | Feuchtigkeit & Glow | A+ Esthetic',
            'description' => 'Neauvia Skinbooster in Frankfurt für intensive Hydration, verbesserte Hautqualität und einen frischen natürlichen Glow. Preisorientierung ab 250 €.',
        ),
        '/injektions-lipolyse/' => array(
            'title' => 'Fett-weg-Spritze Frankfurt | Injektionslipolyse | A+ Esthetic',
            'description' => 'Injektionslipolyse in Frankfurt zur gezielten Behandlung kleiner Fettdepots, zum Beispiel am Doppelkinn, Bauch, Hüfte, Oberarmen oder Oberschenkeln.',
        ),
    );

    return isset($targets[$route]) ? $targets[$route] : null;
}

function aesthetic_gsc_seo_title($title) {
    $target = aesthetic_gsc_seo_target();
    return $target && !empty($target['title']) ? $target['title'] : $title;
}
add_filter('pre_get_document_title', 'aesthetic_gsc_seo_title', 999);
add_filter('wpseo_title', 'aesthetic_gsc_seo_title', 999);
add_filter('rank_math/frontend/title', 'aesthetic_gsc_seo_title', 999);

function aesthetic_gsc_seo_description($description) {
    $target = aesthetic_gsc_seo_target();
    return $target && !empty($target['description']) ? $target['description'] : $description;
}
add_filter('wpseo_metadesc', 'aesthetic_gsc_seo_description', 999);
add_filter('rank_math/frontend/description', 'aesthetic_gsc_seo_description', 999);

add_action('wp_head', function () {
    $target = aesthetic_gsc_seo_target();
    if (!$target || empty($target['description'])) { return; }

    // Yoast and Rank Math receive the description through their native filters
    // above. Only provide a fallback when neither plugin is active.
    if (defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION')) { return; }
    echo "\n<meta name=\"description\" content=\"" . esc_attr($target['description']) . "\">\n";
}, 2);
'''
    functions = functions.replace(anchor, anchor + block, 1)
    write(functions_path, functions)


# ---------------------------------------------------------------------------
# Audit note so future migration work does not accidentally undo this pass.
# ---------------------------------------------------------------------------
audit = """# Search Console SEO improvements — 2026-09-01

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
"""
write("GSC-SEO-IMPROVEMENTS-2026-09-01.md", audit)

print("Updated files:")
for path in sorted(set(changed)):
    print(" -", path)
