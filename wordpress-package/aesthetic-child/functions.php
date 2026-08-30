<?php
if (!defined('ABSPATH')) { exit; }

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('aesthetic-child', get_stylesheet_uri(), array(), '1.0.0');
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
