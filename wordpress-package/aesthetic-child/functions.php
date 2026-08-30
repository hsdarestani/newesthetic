<?php
if (!defined('ABSPATH')) { exit; }

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('aesthetic-child', get_stylesheet_uri(), array(), '1.0.3');
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

    add_filter('wp_robots', function ($robots) {
        unset($robots['noindex']);
        unset($robots['nofollow']);
        return $robots;
    }, 999);
});

/**
 * Hide only legacy booking/contact rails injected by the old WordPress setup.
 * Approved snapshot CTAs remain untouched because anything inside
 * .aesthetic-snapshot-root is explicitly ignored.
 */
add_action('wp_footer', function () {
    if (!is_singular('page')) { return; }
    $post_id = get_queried_object_id();
    if (!get_post_meta($post_id, '_aesthetic_snapshot_html', true)) { return; }
    ?>
    <script id="aesthetic-legacy-floating-cleanup">
    (function () {
        function isLegacyBookingTarget(el) {
            if (!el || el.nodeType !== 1) return false;
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
            if (!node || node === document.body || node === document.documentElement) return;
            node.classList.add('aesthetic-hide-legacy-floating');
            node.setAttribute('aria-hidden', 'true');
        }

        function findRailAncestor(start) {
            var best = null;
            var node = start;
            for (var depth = 0; node && node !== document.body && depth < 18; depth++, node = node.parentElement) {
                var style = window.getComputedStyle(node);
                var rect = node.getBoundingClientRect();
                var fixed = style.position === 'fixed' || style.position === 'sticky';
                var rightRail = rect.width >= 60 && rect.width <= 460 && rect.height >= 70 &&
                    rect.right >= window.innerWidth - 70 && rect.left >= window.innerWidth * 0.60;

                if (fixed) {
                    return node;
                }
                if (rightRail) {
                    best = node;
                }
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

        function runCleanup() {
            window.requestAnimationFrame(cleanLegacyFloatingWidgets);
            window.setTimeout(cleanLegacyFloatingWidgets, 150);
            window.setTimeout(cleanLegacyFloatingWidgets, 700);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', runCleanup);
        } else {
            runCleanup();
        }

        var observer = new MutationObserver(runCleanup);
        observer.observe(document.documentElement, { childList: true, subtree: true, attributes: true, attributeFilter: ['style', 'class', 'src', 'href'] });
        window.setTimeout(function () { observer.disconnect(); }, 20000);
    }());
    </script>
    <?php
}, 999);
