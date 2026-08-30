<?php
/**
 * Plugin Name: A+ Esthetic Migrator
 * Description: Dry-run, apply and rollback the approved newesthetic staging snapshots without replacing WordPress page content or SEO metadata.
 * Version: 1.0.1
 * Author: A+ Esthetic
 */

if (!defined('ABSPATH')) { exit; }

final class Aesthetic_Migrator {
    const CAP = 'manage_options';
    const SOURCE = 'https://newesthetic.pages.dev';
    const TEMPLATE = 'aesthetic-snapshot.php';
    const META_SNAPSHOT = '_aesthetic_snapshot_html';
    const META_PREV_TEMPLATE = '_aesthetic_previous_template';
    const META_SOURCE_HASH = '_aesthetic_snapshot_sha256';

    private static function manifest_path() {
        return plugin_dir_path(__FILE__) . 'route-manifest.json';
    }

    private static function manifest() {
        $raw = @file_get_contents(self::manifest_path());
        $data = $raw ? json_decode($raw, true) : null;
        return is_array($data) ? $data : array('routes' => array());
    }

    private static function source_route_for($route) {
        $manifest = self::manifest();
        $aliases = isset($manifest['source_routes']) && is_array($manifest['source_routes']) ? $manifest['source_routes'] : array();
        return isset($aliases[$route]) ? (string) $aliases[$route] : $route;
    }

    private static function page_for_route($route) {
        if ($route === '/') {
            $front = (int) get_option('page_on_front');
            return $front ? get_post($front) : null;
        }
        $path = trim($route, '/');
        return get_page_by_path($path, OBJECT, 'page');
    }

    private static function fetch_snapshot($route) {
        $source_route = self::source_route_for($route);
        $url = rtrim(self::SOURCE, '/') . $source_route;
        $response = wp_remote_get($url, array(
            'timeout' => 25,
            'redirection' => 3,
            'user-agent' => 'Aesthetic-WP-Migrator/1.0.1; ' . home_url('/'),
        ));
        if (is_wp_error($response)) {
            return $response;
        }
        $code = (int) wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        if ($code !== 200 || !$body) {
            return new WP_Error('bad_snapshot', sprintf('Staging returned HTTP %d for %s (source %s)', $code, $route, $source_route));
        }
        if (stripos($body, '<meta name="robots" content="noindex,nofollow">') === false && stripos($body, 'noindex,nofollow') === false) {
            return new WP_Error('guard_missing', 'Staging SEO guard not detected for ' . $source_route);
        }
        return $body;
    }

    public static function admin_menu() {
        add_management_page('A+ Esthetic Migration', 'A+ Esthetic Migration', self::CAP, 'aesthetic-migration', array(__CLASS__, 'render_admin'));
    }

    private static function status_rows() {
        $manifest = self::manifest();
        $rows = array();
        foreach (($manifest['routes'] ?? array()) as $route) {
            $page = self::page_for_route($route);
            $rows[] = array(
                'route' => $route,
                'source' => self::source_route_for($route),
                'page' => $page,
                'snapshot' => $page ? (bool) get_post_meta($page->ID, self::META_SNAPSHOT, true) : false,
                'template' => $page ? get_post_meta($page->ID, '_wp_page_template', true) : '',
            );
        }
        return $rows;
    }

    public static function handle_action() {
        if (!is_admin() || !current_user_can(self::CAP)) { return; }
        if (empty($_POST['aesthetic_migration_action'])) { return; }
        check_admin_referer('aesthetic_migration_action');

        $action = sanitize_key(wp_unslash($_POST['aesthetic_migration_action']));
        $messages = array();

        if ($action === 'apply') {
            $manifest = self::manifest();
            foreach (($manifest['routes'] ?? array()) as $route) {
                $page = self::page_for_route($route);
                if (!$page) {
                    $messages[] = 'SKIP missing WordPress page: ' . $route;
                    continue;
                }
                $snapshot = self::fetch_snapshot($route);
                if (is_wp_error($snapshot)) {
                    $messages[] = 'ERROR ' . $route . ': ' . $snapshot->get_error_message();
                    continue;
                }
                if (!metadata_exists('post', $page->ID, self::META_PREV_TEMPLATE)) {
                    update_post_meta($page->ID, self::META_PREV_TEMPLATE, (string) get_post_meta($page->ID, '_wp_page_template', true));
                }
                update_post_meta($page->ID, self::META_SNAPSHOT, $snapshot);
                update_post_meta($page->ID, self::META_SOURCE_HASH, hash('sha256', $snapshot));
                update_post_meta($page->ID, '_wp_page_template', self::TEMPLATE);
                $source_route = self::source_route_for($route);
                $suffix = $source_route !== $route ? ' (snapshot source ' . $source_route . ')' : '';
                $messages[] = 'APPLIED ' . $route . ' → page #' . $page->ID . $suffix;
            }
            set_transient('aesthetic_migration_messages', $messages, 120);
            wp_safe_redirect(admin_url('tools.php?page=aesthetic-migration'));
            exit;
        }

        if ($action === 'rollback') {
            $manifest = self::manifest();
            foreach (($manifest['routes'] ?? array()) as $route) {
                $page = self::page_for_route($route);
                if (!$page) { continue; }
                if (!metadata_exists('post', $page->ID, self::META_SNAPSHOT)) { continue; }
                $prev = (string) get_post_meta($page->ID, self::META_PREV_TEMPLATE, true);
                if ($prev !== '') {
                    update_post_meta($page->ID, '_wp_page_template', $prev);
                } else {
                    delete_post_meta($page->ID, '_wp_page_template');
                }
                delete_post_meta($page->ID, self::META_SNAPSHOT);
                delete_post_meta($page->ID, self::META_SOURCE_HASH);
                delete_post_meta($page->ID, self::META_PREV_TEMPLATE);
                $messages[] = 'ROLLED BACK ' . $route . ' → page #' . $page->ID;
            }
            set_transient('aesthetic_migration_messages', $messages, 120);
            wp_safe_redirect(admin_url('tools.php?page=aesthetic-migration'));
            exit;
        }
    }

    public static function legacy_redirect() {
        $path = wp_parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $manifest = self::manifest();
        $redirects = isset($manifest['redirects']) && is_array($manifest['redirects']) ? $manifest['redirects'] : array();
        foreach ($redirects as $from => $to) {
            if (rtrim((string) $path, '/') === rtrim((string) $from, '/')) {
                wp_safe_redirect(home_url((string) $to), 301);
                exit;
            }
        }
    }

    public static function render_admin() {
        if (!current_user_can(self::CAP)) { return; }
        $rows = self::status_rows();
        $messages = get_transient('aesthetic_migration_messages');
        delete_transient('aesthetic_migration_messages');
        $found = count(array_filter($rows, function ($r) { return (bool) $r['page']; }));
        $active = count(array_filter($rows, function ($r) { return $r['snapshot']; }));
        ?>
        <div class="wrap">
            <h1>A+ Esthetic Migration</h1>
            <p><strong>Safe mode:</strong> this tool does not replace page content, slugs, post IDs or SEO-plugin metadata.</p>
            <p>Approved routes found in WordPress: <strong><?php echo esc_html($found); ?>/<?php echo esc_html(count($rows)); ?></strong>. Snapshot template active: <strong><?php echo esc_html($active); ?></strong>.</p>

            <?php if (is_array($messages) && $messages): ?>
                <div class="notice notice-info"><p><strong>Last operation</strong></p><pre style="max-height:280px;overflow:auto;white-space:pre-wrap"><?php echo esc_html(implode("\n", $messages)); ?></pre></div>
            <?php endif; ?>

            <h2>Dry run</h2>
            <table class="widefat striped">
                <thead><tr><th>Route</th><th>Snapshot source</th><th>WordPress page</th><th>ID</th><th>Current template</th><th>Snapshot</th></tr></thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><code><?php echo esc_html($row['route']); ?></code></td>
                        <td><code><?php echo esc_html($row['source']); ?></code><?php echo $row['source'] !== $row['route'] ? ' <small>(alias)</small>' : ''; ?></td>
                        <td><?php echo $row['page'] ? esc_html($row['page']->post_title) : '<strong style="color:#b32d2e">MISSING</strong>'; ?></td>
                        <td><?php echo $row['page'] ? esc_html($row['page']->ID) : '—'; ?></td>
                        <td><code><?php echo esc_html($row['template'] ?: 'default'); ?></code></td>
                        <td><?php echo $row['snapshot'] ? 'YES' : 'NO'; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <p style="margin-top:20px"><strong>Do not apply on production first.</strong> Apply only on a cloned WordPress staging installation after a full backup.</p>
            <div style="display:flex;gap:12px;align-items:center">
                <form method="post">
                    <?php wp_nonce_field('aesthetic_migration_action'); ?>
                    <input type="hidden" name="aesthetic_migration_action" value="apply">
                    <?php submit_button('Apply snapshots', 'primary', 'submit', false, array('onclick' => "return confirm('Apply approved staging snapshots to these existing pages? Original post_content will remain untouched.');")); ?>
                </form>
                <form method="post">
                    <?php wp_nonce_field('aesthetic_migration_action'); ?>
                    <input type="hidden" name="aesthetic_migration_action" value="rollback">
                    <?php submit_button('Rollback snapshots', 'secondary', 'submit', false, array('onclick' => "return confirm('Rollback snapshot templates and restore previous page templates?');")); ?>
                </form>
            </div>
        </div>
        <?php
    }
}

add_action('admin_menu', array('Aesthetic_Migrator', 'admin_menu'));
add_action('admin_init', array('Aesthetic_Migrator', 'handle_action'));
add_action('template_redirect', array('Aesthetic_Migrator', 'legacy_redirect'), 1);
