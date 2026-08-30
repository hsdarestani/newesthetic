<?php
/**
 * Plugin Name: A+ Esthetic Migrator
 * Description: Dry-run, selectively apply and rollback approved newesthetic staging snapshots without replacing WordPress page content or SEO metadata.
 * Version: 1.2.0
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
            'user-agent' => 'Aesthetic-WP-Migrator/1.2.0; ' . home_url('/'),
        ));
        if (is_wp_error($response)) {
            return $response;
        }
        $code = (int) wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        if ($code !== 200 || !$body) {
            return new WP_Error('bad_snapshot', sprintf('Staging returned HTTP %d for %s (source %s)', $code, $route, $source_route));
        }
        if (stripos($body, 'noindex,nofollow') === false) {
            return new WP_Error('guard_missing', 'Staging SEO guard not detected for ' . $source_route);
        }
        return $body;
    }

    private static function allowed_routes() {
        $manifest = self::manifest();
        return isset($manifest['routes']) && is_array($manifest['routes']) ? array_values($manifest['routes']) : array();
    }

    private static function selected_routes() {
        $allowed = self::allowed_routes();
        $raw = isset($_POST['routes']) && is_array($_POST['routes']) ? wp_unslash($_POST['routes']) : array();
        $selected = array();
        foreach ($raw as $route) {
            $route = (string) $route;
            if (in_array($route, $allowed, true)) {
                $selected[] = $route;
            }
        }
        return array_values(array_unique($selected));
    }

    private static function apply_route($route, &$messages) {
        $page = self::page_for_route($route);
        if (!$page) {
            $messages[] = 'SKIP missing WordPress page: ' . $route;
            return;
        }

        $snapshot = self::fetch_snapshot($route);
        if (is_wp_error($snapshot)) {
            $messages[] = 'ERROR ' . $route . ': ' . $snapshot->get_error_message();
            return;
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

    private static function rollback_route($route, &$messages) {
        $page = self::page_for_route($route);
        if (!$page) { return; }
        if (!metadata_exists('post', $page->ID, self::META_SNAPSHOT)) {
            $messages[] = 'SKIP no active snapshot: ' . $route;
            return;
        }

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

    public static function admin_menu() {
        add_management_page('A+ Esthetic Migration', 'A+ Esthetic Migration', self::CAP, 'aesthetic-migration', array(__CLASS__, 'render_admin'));
    }

    private static function status_rows() {
        $rows = array();
        foreach (self::allowed_routes() as $route) {
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

        if ($action === 'apply_selected') {
            $routes = self::selected_routes();
            if (!$routes) {
                $messages[] = 'NO ACTION: select at least one route.';
            } else {
                foreach ($routes as $route) {
                    self::apply_route($route, $messages);
                }
            }
        } elseif ($action === 'rollback_selected') {
            $routes = self::selected_routes();
            if (!$routes) {
                $messages[] = 'NO ACTION: select at least one route.';
            } else {
                foreach ($routes as $route) {
                    self::rollback_route($route, $messages);
                }
            }
        } elseif ($action === 'rollback_all') {
            foreach (self::allowed_routes() as $route) {
                $page = self::page_for_route($route);
                if ($page && metadata_exists('post', $page->ID, self::META_SNAPSHOT)) {
                    self::rollback_route($route, $messages);
                }
            }
            if (!$messages) {
                $messages[] = 'NO ACTION: no active snapshots found.';
            }
        } else {
            $messages[] = 'NO ACTION: unsupported operation.';
        }

        set_transient('aesthetic_migration_messages', $messages, 180);
        wp_safe_redirect(admin_url('tools.php?page=aesthetic-migration'));
        exit;
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
        $is_live = (bool) preg_match('#(^|\.)a-esthetic\.de$#i', (string) wp_parse_url(home_url('/'), PHP_URL_HOST));
        ?>
        <div class="wrap">
            <h1>A+ Esthetic Migration</h1>
            <p><strong>Safe mode:</strong> this tool does not replace page content, slugs, post IDs or SEO-plugin metadata.</p>
            <p>Site: <code><?php echo esc_html(home_url('/')); ?></code> <?php if ($is_live): ?><strong style="color:#b32d2e">LIVE PRODUCTION</strong><?php endif; ?></p>
            <p>Approved routes found in WordPress: <strong><?php echo esc_html($found); ?>/<?php echo esc_html(count($rows)); ?></strong>. Snapshot template active: <strong><?php echo esc_html($active); ?></strong>.</p>

            <?php if (is_array($messages) && $messages): ?>
                <div class="notice notice-info"><p><strong>Last operation</strong></p><pre style="max-height:280px;overflow:auto;white-space:pre-wrap"><?php echo esc_html(implode("\n", $messages)); ?></pre></div>
            <?php endif; ?>

            <?php if ($is_live): ?>
                <div class="notice notice-warning"><p><strong>Production mode:</strong> continue in small batches and verify one representative public page after each batch. Emergency rollback remains available below.</p></div>
            <?php endif; ?>

            <h2>Dry run / selective rollout</h2>
            <form method="post" id="aesthetic-migration-form">
                <?php wp_nonce_field('aesthetic_migration_action'); ?>

                <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;margin:0 0 12px">
                    <strong style="margin-right:4px">Quick select:</strong>
                    <button type="button" class="button aesthetic-batch" data-batch="top">Top-level</button>
                    <button type="button" class="button aesthetic-batch" data-prefix="/botox-behandlungen/">Botox</button>
                    <button type="button" class="button aesthetic-batch" data-prefix="/hyaluronsaure-behandlungen/">Hyaluron</button>
                    <button type="button" class="button aesthetic-batch" data-prefix="/infusionstherapien/">Infusion</button>
                    <button type="button" class="button aesthetic-batch" data-prefix="/injektions-lipolyse/">Lipolyse</button>
                    <button type="button" class="button aesthetic-batch" data-prefix="/laser-behandlungen/">Laser</button>
                    <button type="button" class="button aesthetic-batch" data-prefix="/prp-behandlung/">PRP</button>
                    <button type="button" class="button aesthetic-batch" data-prefix="/rf-microneedling/">RF</button>
                    <button type="button" class="button aesthetic-batch" data-prefix="/skinbooster/">Skinbooster</button>
                    <button type="button" class="button" id="aesthetic-select-inactive">Inactive only</button>
                    <button type="button" class="button" id="aesthetic-clear-selection">Clear</button>
                </div>

                <table class="widefat striped">
                    <thead><tr><th style="width:40px"><input type="checkbox" id="aesthetic-select-all" aria-label="Select all"></th><th>Route</th><th>Snapshot source</th><th>WordPress page</th><th>ID</th><th>Current template</th><th>Snapshot</th></tr></thead>
                    <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><input class="aesthetic-route-check" type="checkbox" name="routes[]" value="<?php echo esc_attr($row['route']); ?>" data-active="<?php echo $row['snapshot'] ? '1' : '0'; ?>" <?php disabled(!$row['page']); ?>></td>
                            <td><code><?php echo esc_html($row['route']); ?></code></td>
                            <td><code><?php echo esc_html($row['source']); ?></code><?php echo $row['source'] !== $row['route'] ? ' <small>(alias)</small>' : ''; ?></td>
                            <td><?php echo $row['page'] ? esc_html($row['page']->post_title) : '<strong style="color:#b32d2e">MISSING</strong>'; ?></td>
                            <td><?php echo $row['page'] ? esc_html($row['page']->ID) : '—'; ?></td>
                            <td><code><?php echo esc_html($row['template'] ?: 'default'); ?></code></td>
                            <td><strong><?php echo $row['snapshot'] ? 'YES' : 'NO'; ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <p style="margin-top:18px">Batch buttons only select rows; nothing changes until you press <strong>Apply selected</strong>. Existing WordPress content remains untouched.</p>
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
                    <button type="submit" class="button button-primary" name="aesthetic_migration_action" value="apply_selected" onclick="return confirm('Apply snapshots ONLY to the selected routes? Original post_content stays untouched.');">Apply selected</button>
                    <button type="submit" class="button" name="aesthetic_migration_action" value="rollback_selected" onclick="return confirm('Rollback ONLY the selected routes to their previous templates?');">Rollback selected</button>
                    <button type="submit" class="button" style="color:#b32d2e;border-color:#b32d2e" name="aesthetic_migration_action" value="rollback_all" onclick="return confirm('EMERGENCY ROLLBACK: restore previous templates for ALL active snapshots?');">Emergency rollback ALL</button>
                </div>
            </form>

            <script>
            (function(){
                var checks = function(){ return Array.prototype.slice.call(document.querySelectorAll('.aesthetic-route-check:not(:disabled)')); };
                var clear = function(){ checks().forEach(function(cb){ cb.checked = false; }); var a=document.getElementById('aesthetic-select-all'); if(a){a.checked=false;} };
                var all = document.getElementById('aesthetic-select-all');
                if (all) {
                    all.addEventListener('change', function(){ checks().forEach(function(cb){ cb.checked = all.checked; }); });
                }
                document.querySelectorAll('.aesthetic-batch').forEach(function(btn){
                    btn.addEventListener('click', function(){
                        clear();
                        var prefix = btn.getAttribute('data-prefix');
                        var batch = btn.getAttribute('data-batch');
                        checks().forEach(function(cb){
                            var route = cb.value || '';
                            if (batch === 'top') {
                                var trimmed = route.replace(/^\/+|\/+$/g, '');
                                cb.checked = route !== '/' && trimmed.indexOf('/') === -1;
                            } else if (prefix) {
                                cb.checked = route.indexOf(prefix) === 0;
                            }
                        });
                    });
                });
                var inactive = document.getElementById('aesthetic-select-inactive');
                if (inactive) inactive.addEventListener('click', function(){ clear(); checks().forEach(function(cb){ cb.checked = cb.getAttribute('data-active') !== '1'; }); });
                var clearBtn = document.getElementById('aesthetic-clear-selection');
                if (clearBtn) clearBtn.addEventListener('click', clear);
            })();
            </script>
        </div>
        <?php
    }
}

add_action('admin_menu', array('Aesthetic_Migrator', 'admin_menu'));
add_action('admin_init', array('Aesthetic_Migrator', 'handle_action'));
add_action('template_redirect', array('Aesthetic_Migrator', 'legacy_redirect'), 1);
