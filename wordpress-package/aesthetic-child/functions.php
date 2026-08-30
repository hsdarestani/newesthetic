<?php
if (!defined('ABSPATH')) { exit; }

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('aesthetic-child', get_stylesheet_uri(), array(), '1.0.5');
}, 100);

add_filter('body_class', function ($classes) {
    if (is_singular('page') && get_post_meta(get_queried_object_id(), '_aesthetic_snapshot_html', true)) {
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
 * Keep legacy booking rails out of the new design without touching consent UI.
 * The observer intentionally watches only inserted/removed nodes (not every
 * style/class mutation), which avoids main-thread churn while cookie CMPs are
 * updating their own state after a tap/click.
 */
add_action('wp_footer', function () {
    if (!is_singular('page')) { return; }
    $post_id = get_queried_object_id();
    if (!get_post_meta($post_id, '_aesthetic_snapshot_html', true)) { return; }
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
