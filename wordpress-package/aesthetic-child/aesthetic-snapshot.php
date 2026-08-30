<?php
/*
Template Name: A+ Esthetic Approved Snapshot
Template Post Type: page
*/
if (!defined('ABSPATH')) { exit; }

$post_id = get_queried_object_id();
$snapshot_html = get_post_meta($post_id, '_aesthetic_snapshot_html', true);

if (!$snapshot_html) {
    get_header();
    while (have_posts()) { the_post(); the_content(); }
    get_footer();
    return;
}

$parts = aesthetic_snapshot_extract($snapshot_html);
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<?php wp_head(); ?>
<?php echo $parts['head']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted migration snapshot, admin-only import. ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="aesthetic-snapshot-root">
<?php echo $parts['body']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted migration snapshot, admin-only import. ?>
</div>
<div class="aesthetic-snapshot-warning">A+ migration snapshot</div>
<?php wp_footer(); ?>
</body>
</html>
