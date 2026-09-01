<?php
if (!defined('ABSPATH')) { exit; }

function aesthetic_is_snapshot_page() {
    return is_singular('page') && (bool) get_post_meta(get_queried_object_id(), '_aesthetic_snapshot_html', true);
}


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

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('aesthetic-child', get_stylesheet_uri(), array(), '1.0.13');
}, 100);

/**
 * Snapshot pages already ship their approved layout CSS inside the imported
 * snapshot. WoodMart/WPBakery/WooCommerce/slider front-end bundles are not
 * used there and add substantial render-blocking CSS/JS. Remove only those
 * known presentation bundles; consent, chat, analytics, SEO, WP Rocket,
 * jQuery and WordPress/admin-bar assets remain untouched.
 */
add_action('wp_enqueue_scripts', function () {
    if (!aesthetic_is_snapshot_page()) { return; }

    $patterns = array(
        '/themes/woodmart/',
        '/plugins/woodmart-core/',
        '/plugins/js_composer/',
        '/plugins/woocommerce/',
        '/plugins/revslider/',
        '/plugins/slider-revolution/'
    );

    global $wp_styles, $wp_scripts;

    if ($wp_styles instanceof WP_Styles) {
        foreach ((array) $wp_styles->queue as $handle) {
            if (empty($wp_styles->registered[$handle])) { continue; }
            $src = (string) $wp_styles->registered[$handle]->src;
            foreach ($patterns as $pattern) {
                if (stripos($src, $pattern) !== false) {
                    wp_dequeue_style($handle);
                    break;
                }
            }
        }
    }

    if ($wp_scripts instanceof WP_Scripts) {
        foreach ((array) $wp_scripts->queue as $handle) {
            if (empty($wp_scripts->registered[$handle])) { continue; }
            $src = (string) $wp_scripts->registered[$handle]->src;
            foreach ($patterns as $pattern) {
                if (stripos($src, $pattern) !== false) {
                    wp_dequeue_script($handle);
                    break;
                }
            }
        }
    }
}, 999);

add_filter('body_class', function ($classes) {
    if (aesthetic_is_snapshot_page()) {
        $classes[] = 'aesthetic-snapshot-active';

        $permalink = get_permalink(get_queried_object_id());
        $path = trim((string) wp_parse_url($permalink, PHP_URL_PATH), '/');
        $route = $path === '' ? 'home' : str_replace('/', '-', $path);
        $classes[] = 'aesthetic-route-' . sanitize_html_class($route);
    }
    return $classes;
});

function aesthetic_snapshot_asset_url($path) {
    $path = ltrim((string) $path, '/');
    return trailingslashit(get_stylesheet_directory_uri()) . 'assets/staging/' . preg_replace('#^assets/#', '', $path);
}

function aesthetic_snapshot_rewrite_assets($html) {
    $base = trailingslashit(get_stylesheet_directory_uri()) . 'assets/staging/';
    $html = preg_replace('#(["\'])/assets/#', '$1' . $base, $html);
    return $html;
}

/**
 * Keep the first (hero) image eager/high-priority and make lower-page imagery
 * lazy/async. The tag processor preserves trusted snapshot markup instead of
 * rebuilding the document.
 */
function aesthetic_snapshot_optimize_images($html) {
    if (!$html || !class_exists('WP_HTML_Tag_Processor')) { return $html; }

    $processor = new WP_HTML_Tag_Processor($html);
    $first_image = true;

    while ($processor->next_tag('IMG')) {
        $processor->set_attribute('decoding', 'async');

        if ($first_image) {
            $processor->set_attribute('loading', 'eager');
            $processor->set_attribute('fetchpriority', 'high');
            $first_image = false;
        } else {
            if (!$processor->get_attribute('loading')) {
                $processor->set_attribute('loading', 'lazy');
            }
        }
    }

    return $processor->get_updated_html();
}

function aesthetic_snapshot_first_image_src($html) {
    $html = (string) $html;

    // Prefer the actual <picture><source> selected for a hero image. The old
    // implementation preloaded the fallback <img src>, which could trigger a
    // second unnecessary request on pages such as Laser.
    if (preg_match("#<picture\\b[^>]*>.*?<source\\b[^>]*\\bsrcset=[\"']([^\"']+)[\"'][^>]*>.*?<img\\b[^>]*class=[\"'][^\"']*hero-media[^\"']*[\"']#is", $html, $match)) {
        $srcset = trim($match[1]);
        $first = preg_split('/\\s*,\\s*/', $srcset)[0] ?? '';
        $url = preg_split('/\\s+/', trim($first))[0] ?? '';
        if ($url !== '') { return $url; }
    }

    if (preg_match("#<img\\b[^>]*class=[\"'][^\"']*hero-media[^\"']*[\"'][^>]*\\bsrc=[\"']([^\"']+)[\"']#i", $html, $match) ||
        preg_match("#<img\\b[^>]*\\bsrc=[\"']([^\"']+)[\"'][^>]*class=[\"'][^\"']*hero-media[^\"']*[\"']#i", $html, $match)) {
        return $match[1];
    }

    if (preg_match("#<img\\b(?![^>]*aesthetic-brand-logo)[^>]*\\bsrc=[\"']([^\"']+)[\"']#i", $html, $match)) {
        return $match[1];
    }
    return '';
}

function aesthetic_snapshot_brand_logo_markup() {
    $url = 'https://a-esthetic.de/wp-content/uploads/6.png';
    return '<img class="aesthetic-brand-logo" src="' . esc_url($url) . '" alt="A+ Esthetic" decoding="async">';
}

/**
 * Use the official production logo in every snapshot header. Source pages use
 * either `.brand` or `.site-brand`; replace only brand anchors inside <header>
 * so editorial/footer copy remains untouched.
 */
function aesthetic_snapshot_apply_brand_logo($html) {
    $logo = aesthetic_snapshot_brand_logo_markup();
    return preg_replace_callback('#<header\\b[^>]*>.*?</header>#is', function ($match) use ($logo) {
        return preg_replace(
            "#(<a\\b[^>]*class=[\"'][^\"']*(?:site-brand|brand)[^\"']*[\"'][^>]*>).*?(</a>)#is",
            '$1' . $logo . '$2',
            $match[0],
            1
        );
    }, (string) $html);
}

/**
 * Production-only interaction/data bridge.
 * The approved Laser landing snapshot intentionally shipped a visual price
 * placeholder with a note that WordPress would reconnect the production price
 * component. Render the migrated price data server-side so the page remains
 * useful even before JavaScript runs; the selector below updates it in-place.
 */
function aesthetic_laser_price_catalog() {
    return array(
        'damen' => array(
            array('group' => 'Gesicht', 'name' => 'Augenbrauenkontur / Stirn', 'price' => 59),
            array('group' => 'Gesicht', 'name' => 'Oberlippe', 'price' => 49),
            array('group' => 'Gesicht', 'name' => 'Oberlippe, Kinn', 'price' => 69),
            array('group' => 'Gesicht', 'name' => 'Kinn', 'price' => 49),
            array('group' => 'Gesicht', 'name' => 'Koteletten', 'price' => 59),
            array('group' => 'Gesicht', 'name' => 'Gesicht teilweise', 'price' => 99),
            array('group' => 'Gesicht', 'name' => 'Hals', 'price' => 59),
            array('group' => 'Gesicht', 'name' => 'Gesicht, Hals', 'price' => 109),
            array('group' => 'Körper', 'name' => 'Achseln', 'price' => 59),
            array('group' => 'Körper', 'name' => 'Brustwarzen', 'price' => 49),
            array('group' => 'Körper', 'name' => 'Dekolleté', 'price' => 109),
            array('group' => 'Körper', 'name' => 'Bauch komplett', 'price' => 129),
            array('group' => 'Körper', 'name' => 'Medianlinie', 'price' => 59),
            array('group' => 'Körper', 'name' => 'Oberarme', 'price' => 119),
            array('group' => 'Körper', 'name' => 'Unterarme', 'price' => 119),
            array('group' => 'Körper', 'name' => 'Hände', 'price' => 59),
            array('group' => 'Körper', 'name' => 'Rücken komplett', 'price' => 179),
            array('group' => 'Körper', 'name' => 'Unterer Rücken / Steißbein', 'price' => 89),
            array('group' => 'Intim & Beine', 'name' => 'Bikinizone', 'price' => 69),
            array('group' => 'Intim & Beine', 'name' => 'Bikini-Intimzone', 'price' => 119),
            array('group' => 'Intim & Beine', 'name' => 'Gesäß inkl. Pofalte', 'price' => 149),
            array('group' => 'Intim & Beine', 'name' => 'Po', 'price' => 119),
            array('group' => 'Intim & Beine', 'name' => 'Pofalte', 'price' => 79),
            array('group' => 'Intim & Beine', 'name' => 'Oberschenkel', 'price' => 159),
            array('group' => 'Intim & Beine', 'name' => 'Unterschenkel', 'price' => 149),
            array('group' => 'Intim & Beine', 'name' => 'Füße', 'price' => 59),
            array('group' => 'Pakete', 'name' => 'Damenpaket 1 · Achseln + Bikinizone', 'price' => 109),
            array('group' => 'Pakete', 'name' => 'Damenpaket 2 · Achseln + Bikini-Intimzone', 'price' => 149),
            array('group' => 'Pakete', 'name' => 'Damenpaket 3 · Bikini-Intimzone + Pofalte', 'price' => 159),
            array('group' => 'Pakete', 'name' => 'Damenpaket 4 · Arme komplett + Hände', 'price' => 149),
            array('group' => 'Pakete', 'name' => 'Damenpaket 5 · Beine komplett + Füße', 'price' => 229),
            array('group' => 'Pakete', 'name' => 'Damenpaket 6 · Unterschenkel + Achseln oder Bikinizone', 'price' => 169),
            array('group' => 'Pakete', 'name' => 'Damenpaket 7 · Unterschenkel + Achseln + Bikinizone', 'price' => 189),
            array('group' => 'Pakete', 'name' => 'Damenpaket 8 · Beine komplett + Bikinizone', 'price' => 269),
            array('group' => 'Pakete', 'name' => 'Damenpaket 9 · Beine komplett + Achseln + Bikinizone', 'price' => 289),
            array('group' => 'Pakete', 'name' => 'Damenpaket 10 · Beine komplett + Achseln + Bikini-Intimzone', 'price' => 389),
            array('group' => 'Pakete', 'name' => 'Damenpaket 11 · Ganzkörper ohne Gesicht / Intimzone', 'price' => 599),
            array('group' => 'Pakete', 'name' => 'Damenpaket 12 · Oberkörper ohne Gesicht', 'price' => 399),
            array('group' => 'Pakete', 'name' => 'Damenpaket 13 · Unterkörper ohne Intimzone', 'price' => 399),
        ),
        'herren' => array(
            array('group' => 'Gesicht', 'name' => 'Augenbrauen / Stirn', 'price' => 59),
            array('group' => 'Gesicht', 'name' => 'Koteletten', 'price' => 59),
            array('group' => 'Gesicht', 'name' => 'Wangenkontur', 'price' => 59),
            array('group' => 'Gesicht', 'name' => 'Hals', 'price' => 59),
            array('group' => 'Gesicht', 'name' => 'Bart, Hals', 'price' => 109),
            array('group' => 'Körper', 'name' => 'Nacken', 'price' => 59),
            array('group' => 'Körper', 'name' => 'Schultern', 'price' => 109),
            array('group' => 'Körper', 'name' => 'Achseln', 'price' => 79),
            array('group' => 'Körper', 'name' => 'Oberarme', 'price' => 119),
            array('group' => 'Körper', 'name' => 'Unterarme', 'price' => 119),
            array('group' => 'Körper', 'name' => 'Hände', 'price' => 59),
            array('group' => 'Körper', 'name' => 'Brust', 'price' => 69),
            array('group' => 'Körper', 'name' => 'Bauch komplett', 'price' => 129),
            array('group' => 'Körper', 'name' => 'Rücken komplett', 'price' => 179),
            array('group' => 'Körper', 'name' => 'Oberer Rücken', 'price' => 159),
            array('group' => 'Intim & Beine', 'name' => 'Gesäß inkl. Pofalte', 'price' => 169),
            array('group' => 'Intim & Beine', 'name' => 'Po', 'price' => 129),
            array('group' => 'Intim & Beine', 'name' => 'Pofalte', 'price' => 79),
            array('group' => 'Intim & Beine', 'name' => 'Intimbereich', 'price' => 199),
            array('group' => 'Intim & Beine', 'name' => 'Oberschenkel', 'price' => 169),
            array('group' => 'Intim & Beine', 'name' => 'Unterschenkel', 'price' => 159),
            array('group' => 'Intim & Beine', 'name' => 'Füße', 'price' => 59),
            array('group' => 'Pakete', 'name' => 'Herrenpaket 1 · Brust + Bauch', 'price' => 179),
            array('group' => 'Pakete', 'name' => 'Herrenpaket 2 · Rücken + Schultern + Nacken', 'price' => 199),
            array('group' => 'Pakete', 'name' => 'Herrenpaket 3 · Rücken + Schultern + Nacken + Oberarme', 'price' => 259),
            array('group' => 'Pakete', 'name' => 'Herrenpaket 4 · Rücken + Schultern + Nacken + Brust + Bauch', 'price' => 289),
            array('group' => 'Pakete', 'name' => 'Herrenpaket 5 · Rücken + Schultern + Nacken + Brust + Bauch + Oberarme', 'price' => 339),
            array('group' => 'Pakete', 'name' => 'Herrenpaket 6 · Arme komplett + Hände', 'price' => 169),
            array('group' => 'Pakete', 'name' => 'Herrenpaket 7 · Beine komplett + Füße', 'price' => 249),
            array('group' => 'Pakete', 'name' => 'Herrenpaket 8 · Ganzkörper ohne Gesicht / Intimzone', 'price' => 599),
            array('group' => 'Pakete', 'name' => 'Herrenpaket 9 · Oberkörper ohne Gesicht', 'price' => 399),
            array('group' => 'Pakete', 'name' => 'Herrenpaket 10 · Unterkörper ohne Intimzone', 'price' => 399),
        ),
    );
}

function aesthetic_laser_price_markup($gender = 'damen') {
    $catalog = aesthetic_laser_price_catalog();
    $items = isset($catalog[$gender]) ? $catalog[$gender] : $catalog['damen'];
    $groups = array();
    foreach ($items as $item) {
        $groups[$item['group']][] = $item;
    }

    $html = '<div class="aesthetic-laser-prices" data-gender="' . esc_attr($gender) . '" data-discount="0">';
    $first = true;
    foreach ($groups as $group => $group_items) {
        $html .= '<details class="aesthetic-price-group"' . ($first ? ' open' : '') . '>';
        $html .= '<summary>' . esc_html($group) . '<span>' . count($group_items) . ' Positionen</span></summary>';
        $html .= '<div class="aesthetic-price-rows">';
        foreach ($group_items as $item) {
            $html .= '<div class="aesthetic-price-row" data-base="' . esc_attr((string) $item['price']) . '">';
            $html .= '<span>' . esc_html($item['name']) . '</span>';
            $html .= '<strong>' . esc_html(number_format_i18n($item['price'], 2)) . ' €</strong>';
            $html .= '</div>';
        }
        $html .= '</div></details>';
        $first = false;
    }
    $html .= '<div class="aesthetic-consult-fee"><span>Beratungsgebühr</span><strong>30,00 €</strong></div>';
    $html .= '</div>';
    return $html;
}

function aesthetic_snapshot_production_enhancements($html, $post_id) {
    $permalink = get_permalink($post_id);
    $path = trim((string) wp_parse_url($permalink, PHP_URL_PATH), '/');

    if ($path === 'laser-behandlungen') {
        $replacement = aesthetic_laser_price_markup('damen');
        $html = preg_replace('#<div class="price-placeholder">.*?<p class="price-note">.*?</p>#is',
            $replacement . '<p class="price-note">Der angezeigte Betrag gilt jeweils pro Behandlung. Mehrfachpreise sind Preise pro Sitzung. Die Beratungsgebühr beträgt 30,00 €. Alle Preise dienen der Orientierung und können abhängig von Befund, Aufwand und Behandlungsumfang variieren.</p>',
            $html,
            1
        );
    }

    if ($path === '') {
        $action = esc_url(admin_url('admin-post.php'));
        $nonce = wp_nonce_field('aesthetic_contact_submit', 'aesthetic_nonce', true, false);
        $hidden = '<input type="hidden" name="action" value="aesthetic_contact_submit">' . $nonce . '<input class="aesthetic-hp" type="text" name="website" value="" tabindex="-1" autocomplete="off" aria-hidden="true">';
        $html = preg_replace('#<form class="form"\s+onsubmit="event\.preventDefault\(\)"\s+aria-label="Kontaktformular">#i', '<form class="form aesthetic-contact-form" action="' . $action . '" method="post" aria-label="Kontaktformular">' . $hidden, $html, 1);
        $html = preg_replace('#<label class="consent"><input type="checkbox">#i', '<label class="consent"><input type="checkbox" name="consent" value="1" required>', $html, 1);
    }

    return $html;
}

function aesthetic_contact_submit_handler() {
    if (!isset($_POST['aesthetic_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['aesthetic_nonce'])), 'aesthetic_contact_submit')) {
        wp_die('Ungültige Anfrage.', 'A+ Esthetic', array('response' => 403));
    }

    if (!empty($_POST['website'])) {
        wp_safe_redirect(home_url('/?contact=sent#kontakt'));
        exit;
    }

    $first = isset($_POST['vorname']) ? sanitize_text_field(wp_unslash($_POST['vorname'])) : '';
    $last = isset($_POST['nachname']) ? sanitize_text_field(wp_unslash($_POST['nachname'])) : '';
    $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $phone = isset($_POST['telefon']) ? sanitize_text_field(wp_unslash($_POST['telefon'])) : '';
    $consent = !empty($_POST['consent']);

    if (!$email || !is_email($email) || !$consent) {
        wp_safe_redirect(home_url('/?contact=invalid#kontakt'));
        exit;
    }

    $subject = 'Kontaktanfrage über a-esthetic.de';
    $message = "Neue Kontaktanfrage über a-esthetic.de\n\n";
    $message .= "Vorname: {$first}\nNachname: {$last}\nE-Mail: {$email}\nTelefon: {$phone}\n";
    $message .= "Rückruf/Kontaktaufnahme: zugestimmt\n";
    $headers = array('Reply-To: ' . trim($first . ' ' . $last) . ' <' . $email . '>');

    $sent = wp_mail('Info@a-esthetic.de', $subject, $message, $headers);
    wp_safe_redirect(home_url('/?contact=' . ($sent ? 'sent' : 'error') . '#kontakt'));
    exit;
}
add_action('admin_post_nopriv_aesthetic_contact_submit', 'aesthetic_contact_submit_handler');
add_action('admin_post_aesthetic_contact_submit', 'aesthetic_contact_submit_handler');

function aesthetic_snapshot_extract($html) {
    $out = array('head' => '', 'body' => '');
    if (!$html) { return $out; }

    if (preg_match('#<head[^>]*>(.*?)</head>#is', $html, $m)) {
        $head = $m[1];
        preg_match_all('#<style\b[^>]*>.*?</style>#is', $head, $styles);
        preg_match_all('#<link\b[^>]*rel=["\'][^"\']*stylesheet[^"\']*["\'][^>]*>#is', $head, $links);
        $out['head'] = implode("\n", array_merge($styles[0] ?? array(), $links[0] ?? array()));
    }

    if (preg_match('#<body[^>]*>(.*?)</body>#is', $html, $m)) {
        $out['body'] = $m[1];
    } else {
        $out['body'] = $html;
    }

    $out['head'] = aesthetic_snapshot_rewrite_assets($out['head']);
    $out['body'] = aesthetic_snapshot_rewrite_assets($out['body']);
    $out['body'] = aesthetic_snapshot_apply_brand_logo($out['body']);
    $out['body'] = aesthetic_snapshot_optimize_images($out['body']);
    $out['body'] = aesthetic_snapshot_production_enhancements($out['body'], get_queried_object_id());
    return $out;
}

add_action('template_redirect', function () {
    if (!aesthetic_is_snapshot_page()) { return; }

    add_filter('wp_robots', function ($robots) {
        unset($robots['noindex']);
        unset($robots['nofollow']);
        return $robots;
    }, 999);
});

/**
 * Keep legacy booking rails out of the new design without touching consent UI.
 * The observer intentionally watches only inserted/removed nodes (not every
 * style/class mutation), which avoids main-thread churn while cookie CMPs are
 * updating their own state after a tap/click.
 */
add_action('wp_footer', function () {
    if (!aesthetic_is_snapshot_page()) { return; }
    ?>
    <script id="aesthetic-legacy-floating-cleanup">
    (function () {
        function isCookieConsentNode(el) {
            if (!el || el.nodeType !== 1) return false;
            var id = (el.id || '').toLowerCase();
            var cls = (typeof el.className === 'string' ? el.className : '').toLowerCase();
            var src = (el.getAttribute && el.getAttribute('src') || '').toLowerCase();
            var alt = (el.getAttribute && el.getAttribute('alt') || '').toLowerCase();
            var dataName = (el.getAttribute && el.getAttribute('data-name') || '').toLowerCase();
            var haystack = id + ' ' + cls + ' ' + src + ' ' + alt + ' ' + dataName;
            return haystack.indexOf('seers') !== -1 ||
                haystack.indexOf('cookiexray') !== -1 ||
                haystack.indexOf('cookie-consent') !== -1 ||
                haystack.indexOf('cookie_consent') !== -1;
        }

        function insideCookieConsent(el) {
            var node = el;
            for (var depth = 0; node && node !== document.body && depth < 12; depth++, node = node.parentElement) {
                if (isCookieConsentNode(node)) return true;
            }
            return false;
        }

        function promoteCookieConsent() {
            var selector = [
                '[id*="seers" i]',
                '[class*="seers" i]',
                'iframe[src*="seers" i]',
                'iframe[src*="seersco" i]',
                'img[alt*="seers cmp badge" i]',
                '[data-name="CookieXray"]'
            ].join(',');

            document.querySelectorAll(selector).forEach(function (el) {
                var node = el;
                var best = el;
                for (var depth = 0; node && node !== document.body && depth < 8; depth++, node = node.parentElement) {
                    var style = window.getComputedStyle(node);
                    if (style.position === 'fixed' || style.position === 'sticky' || style.position === 'absolute') {
                        best = node;
                    }
                }
                best.classList.add('aesthetic-cookie-layer');
            });
        }

        function isLegacyBookingTarget(el) {
            if (!el || el.nodeType !== 1 || insideCookieConsent(el)) return false;
            var href = (el.getAttribute && el.getAttribute('href') || '').toLowerCase();
            var alt = (el.getAttribute && el.getAttribute('alt') || '').toLowerCase();
            var title = (el.getAttribute && el.getAttribute('title') || '').toLowerCase();
            var src = (el.getAttribute && el.getAttribute('src') || '').toLowerCase();
            var haystack = href + ' ' + alt + ' ' + title + ' ' + src;
            return haystack.indexOf('doctolib') !== -1 ||
                haystack.indexOf('wa.me') !== -1 ||
                haystack.indexOf('whatsapp') !== -1 ||
                haystack.indexOf('simplybook') !== -1 ||
                haystack.indexOf('termin buchen') !== -1 ||
                haystack.indexOf('termin-buchen') !== -1;
        }

        function markHidden(node) {
            if (!node || node === document.body || node === document.documentElement || insideCookieConsent(node)) return;
            node.classList.add('aesthetic-hide-legacy-floating');
            node.setAttribute('aria-hidden', 'true');
        }

        function findRailAncestor(start) {
            var best = null;
            var node = start;
            for (var depth = 0; node && node !== document.body && depth < 18; depth++, node = node.parentElement) {
                if (insideCookieConsent(node)) return null;
                var style = window.getComputedStyle(node);
                var rect = node.getBoundingClientRect();
                var fixed = style.position === 'fixed' || style.position === 'sticky';
                var rightRail = rect.width >= 60 && rect.width <= 460 && rect.height >= 70 &&
                    rect.right >= window.innerWidth - 70 && rect.left >= window.innerWidth * 0.60;

                if (fixed) return node;
                if (rightRail) best = node;
            }
            return best;
        }

        function cleanLegacyFloatingWidgets() {
            var root = document.querySelector('.aesthetic-snapshot-root');
            if (!root) return;

            var candidates = document.querySelectorAll('a, img, button, [role="button"]');
            Array.prototype.forEach.call(candidates, function (el) {
                if (root.contains(el) || !isLegacyBookingTarget(el)) return;
                var rail = findRailAncestor(el);
                if (rail) {
                    markHidden(rail);
                } else {
                    markHidden(el.closest('a,button,div,section,aside') || el);
                }
            });
        }

        var scheduled = false;
        function runCleanup() {
            if (scheduled) return;
            scheduled = true;
            window.requestAnimationFrame(function () {
                scheduled = false;
                promoteCookieConsent();
                cleanLegacyFloatingWidgets();
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', runCleanup, { once: true });
        } else {
            runCleanup();
        }
        window.setTimeout(runCleanup, 250);
        window.setTimeout(runCleanup, 1200);

        var observer = new MutationObserver(runCleanup);
        observer.observe(document.documentElement, { childList: true, subtree: true });
        window.setTimeout(function () { observer.disconnect(); }, 8000);
    }());
    </script>
    <?php
}, 999);

/** Production interaction layer for migrated snapshots. */
add_action('wp_footer', function () {
    if (!aesthetic_is_snapshot_page()) { return; }
    $path = trim((string) wp_parse_url(get_permalink(get_queried_object_id()), PHP_URL_PATH), '/');

    if ($path === 'laser-behandlungen') {
        $catalog = aesthetic_laser_price_catalog();
        ?>
        <script id="aesthetic-laser-price-js">
        (function(){
          var shell=document.querySelector('.aesthetic-laser-prices');
          if(!shell)return;
          var groups=document.querySelectorAll('#preise .seg');
          if(groups.length<2)return;
          var catalog=<?php echo wp_json_encode($catalog); ?>;
          var gender='damen', discount=0;
          function money(v){return new Intl.NumberFormat('de-DE',{minimumFractionDigits:2,maximumFractionDigits:2}).format(v)+' €'}
          function render(){
            var items=catalog[gender]||catalog.damen, grouped={};
            items.forEach(function(item){(grouped[item.group]||(grouped[item.group]=[])).push(item)});
            var html='', first=true;
            Object.keys(grouped).forEach(function(name){
              html+='<details class="aesthetic-price-group"'+(first?' open':'')+'><summary>'+name+'<span>'+grouped[name].length+' Positionen</span></summary><div class="aesthetic-price-rows">';
              grouped[name].forEach(function(item){var value=item.price*(1-discount/100);html+='<div class="aesthetic-price-row"><span>'+item.name+'</span><strong>'+money(value)+'</strong></div>'});
              html+='</div></details>';first=false;
            });
            html+='<div class="aesthetic-consult-fee"><span>Beratungsgebühr</span><strong>30,00 €</strong></div>';
            shell.innerHTML=html;shell.dataset.gender=gender;shell.dataset.discount=String(discount);
          }
          groups[0].addEventListener('click',function(e){if(e.target.tagName!=='BUTTON')return;gender=e.target.textContent.trim().toLowerCase().indexOf('herr')===0?'herren':'damen';render()});
          groups[1].addEventListener('click',function(e){if(e.target.tagName!=='BUTTON')return;var t=e.target.textContent;discount=t.indexOf('4×')!==-1?4:t.indexOf('6×')!==-1?8:t.indexOf('8×')!==-1?10:0;render()});
        }());
        </script>
        <?php
    }

    if ($path === '') {
        $status = isset($_GET['contact']) ? sanitize_key(wp_unslash($_GET['contact'])) : '';
        if ($status) {
            $message = $status === 'sent' ? 'Vielen Dank. Ihre Anfrage wurde gesendet.' : ($status === 'invalid' ? 'Bitte geben Sie eine gültige E-Mail-Adresse ein und bestätigen Sie die Kontaktaufnahme.' : 'Die Nachricht konnte nicht gesendet werden. Bitte kontaktieren Sie uns telefonisch oder per E-Mail.');
            ?>
            <script id="aesthetic-contact-status-js">
            (function(){var form=document.querySelector('.aesthetic-contact-form');if(!form)return;var n=document.createElement('div');n.className='aesthetic-form-status';n.textContent=<?php echo wp_json_encode($message); ?>;form.prepend(n)}());
            </script>
            <?php
        }
    }
}, 1001);
