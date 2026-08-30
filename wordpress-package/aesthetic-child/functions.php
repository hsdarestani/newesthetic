<?php
if (!defined('ABSPATH')) { exit; }

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('aesthetic-child', get_stylesheet_uri(), array(), '1.0.1');
}, 100);

add_filter('body_class', function ($classes) {
    if (is_singular('page') && get_post_meta(get_queried_object_id(), '_aesthetic_snapshot_html', true)) {
        $classes[] = 'aesthetic-snapshot-active';
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
    return $out;
}

add_action('template_redirect', function () {
    if (!is_singular('page')) { return; }
    $post_id = get_queried_object_id();
    $snapshot = get_post_meta($post_id, '_aesthetic_snapshot_html', true);
    if (!$snapshot) { return; }

    // Prevent the static staging robots/canonical tags from becoming authoritative on WordPress.
    // WordPress + the existing SEO plugin remain authoritative for production SEO metadata.
    add_filter('wp_robots', function ($robots) {
        unset($robots['noindex']);
        unset($robots['nofollow']);
        return $robots;
    }, 999);
});

/**
 * The old WordPress installation adds separate fixed booking/contact rails.
 * The approved snapshot already contains its own booking CTAs, so hide only
 * legacy floating Doctolib / WhatsApp / SimplyBook controls outside the
 * snapshot root. Normal in-content/header/footer links stay untouched.
 */
add_action('wp_footer', function () {
    if (!is_singular('page')) { return; }
    $post_id = get_queried_object_id();
    if (!get_post_meta($post_id, '_aesthetic_snapshot_html', true)) { return; }
    ?>
    <script id="aesthetic-legacy-floating-cleanup">
    (function () {
        function cleanLegacyFloatingWidgets() {
            var root = document.querySelector('.aesthetic-snapshot-root');
            if (!root) return;

            var selector = [
                'a[href*="doctolib."]',
                'a[href*="wa.me"]',
                'a[href*="api.whatsapp.com"]',
                'a[href*="whatsapp.com/send"]',
                'a[href*="simplybook"]'
            ].join(',');

            document.querySelectorAll(selector).forEach(function (link) {
                if (root.contains(link)) return;

                var node = link;
                var floating = null;
                for (var depth = 0; node && node !== document.body && depth < 8; depth++, node = node.parentElement) {
                    var style = window.getComputedStyle(node);
                    if (style.position === 'fixed' || style.position === 'sticky') {
                        floating = node;
                        break;
                    }
                }

                if (floating) {
                    floating.classList.add('aesthetic-hide-legacy-floating');
                    floating.setAttribute('aria-hidden', 'true');
                }
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', cleanLegacyFloatingWidgets);
        } else {
            cleanLegacyFloatingWidgets();
        }

        // Some booking/WhatsApp plugins inject their buttons after load.
        var observer = new MutationObserver(function () {
            window.requestAnimationFrame(cleanLegacyFloatingWidgets);
        });
        observer.observe(document.documentElement, { childList: true, subtree: true });
        window.setTimeout(function () { observer.disconnect(); }, 12000);
    }());
    </script>
    <?php
}, 999);
