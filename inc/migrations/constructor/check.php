<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$mode = $args[0] ?? 'create';
if ('create' === $mode) {
    $existing = get_posts(array('post_type' => 'page', 'post_status' => 'draft', 'meta_key' => '_constructor_audit', 'meta_value' => 1, 'fields' => 'ids'));
    if ($existing) { WP_CLI::error('An audit page already exists: ' . $existing[0]); }
    $rows = get_field('constructor', 7, false);
    $field = acf_get_field('field_project_theme_constructor');
    $promo = null;
    $home = null;
    foreach ($rows as $row) {
        if ('template-promotions' === $row['acf_fc_layout']) { $promo = $row; }
        if ('template-banner-main' === $row['acf_fc_layout']) { $home = $row; }
    }
    $home['field_ct_banner_main_disable_block'] = 1;
    $copy = $promo;
    $copy['field_ct_promotions_title'] = 'Перевірка повторної секції';
    $id = wp_insert_post(array('post_type' => 'page', 'post_status' => 'draft', 'post_title' => 'Constructor audit', 'meta_input' => array('_constructor_audit' => 1, '_wp_page_template' => 'page-constructor.php')), true);
    if (is_wp_error($id)) { WP_CLI::error($id->get_error_message()); }
    update_field('field_project_theme_constructor', array($copy, $home, $promo), $id);
    if (function_exists('pll_set_post_language')) { pll_set_post_language($id, 'uk'); }
    WP_CLI::success('Audit page ' . $id);
} elseif ('verify' === $mode) {
    require_once __DIR__ . '/schema.php';
    $keys = array();
    $walk = function ($value) use (&$walk, &$keys): void {
        if (!is_array($value)) { return; }
        if (isset($value['key']) && str_starts_with($value['key'], 'field_')) {
            if (isset($keys[$value['key']])) { WP_CLI::error('Duplicate field key: ' . $value['key']); }
            $keys[$value['key']] = true;
        }
        foreach ($value as $child) { if (is_array($child)) { $walk($child); } }
    };
    $walk(project_theme_constructor_group());
    $seeds = json_decode(file_get_contents(__DIR__ . '/content-uk.json'), true);
    $by_layout = array();
    foreach ($seeds as $seed) { if (isset($seed['acf_fc_layout'])) { $by_layout[$seed['acf_fc_layout']] = $seed; } }
    $validate = function (array $seed, array $actual, string $path) use (&$validate): void {
        foreach ($seed as $key => $expected) {
            if (!array_key_exists($key, $actual)) { WP_CLI::error('Missing field ' . $path . '/' . $key); }
            if (is_array($expected) && isset($expected['file'])) {
                if (!$actual[$key]) { WP_CLI::error('Missing image ' . $path . '/' . $key); }
            } elseif (is_array($expected) && isset($expected['page_slug'])) {
                if (!$actual[$key]) { WP_CLI::error('Missing page link ' . $path . '/' . $key); }
            } elseif (is_array($expected)) {
                if (!$expected) { continue; }
                if (!is_array($actual[$key]) || count($expected) !== count($actual[$key])) { WP_CLI::error('Repeater size ' . $path . '/' . $key); }
                foreach ($expected as $index => $row) { $validate($row, $actual[$key][$index], $path . '/' . $key . '/' . $index); }
            } elseif (is_string($expected) && '' !== $expected && '' === trim((string) $actual[$key])) {
                WP_CLI::error('Empty value ' . $path . '/' . $key);
            }
        }
    };
    foreach (array(7,41,8,43,9,45,10,47) as $id) {
        foreach (get_field('constructor', $id) as $row) {
            $seed = $by_layout[$row['acf_fc_layout']];
            if ('template-banner-inner' === $row['acf_fc_layout']) {
                $base = pll_get_post($id, 'uk');
                $seed = $seeds[get_post_field('post_name', $base)];
            }
            $validate($seed, $row, 'page-' . $id . '/' . $row['acf_fc_layout']);
        }
    }
    WP_CLI::success(count($keys) . ' unique ACF field keys; content and images verified on 8 pages.');
} elseif ('cleanup' === $mode) {
    foreach (get_posts(array('post_type' => 'page', 'post_status' => 'draft', 'meta_key' => '_constructor_audit', 'meta_value' => 1)) as $post) {
        wp_delete_post($post->ID, true);
        WP_CLI::log('Removed temporary audit page ' . $post->ID);
    }
}
