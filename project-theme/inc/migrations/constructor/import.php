<?php
/** Run explicitly with wp eval-file. Never runs on frontend requests. */
if (!defined('WP_CLI') || !WP_CLI) { exit; }
require_once __DIR__ . '/schema.php';

function project_theme_constructor_import_media(string $file): int {
    static $cache = array();
    if (isset($cache[$file])) { return $cache[$file]; }
    $source = realpath(get_template_directory() . '/assets/' . $file);
    $root = realpath(get_template_directory() . '/assets');
    if (!$source || !str_starts_with($source, $root . DIRECTORY_SEPARATOR)) { WP_CLI::error('Missing asset: ' . $file); }
    $matches = get_posts(array('post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => 1, 'meta_key' => '_constructor_source', 'meta_value' => $file, 'fields' => 'ids', 'lang' => ''));
    if (!$matches) {
        // Reuse earlier imports that predate the source marker.
        $candidates = get_posts(array('post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => -1, 'fields' => 'ids', 'lang' => ''));
        foreach ($candidates as $candidate) {
            $attached = get_attached_file($candidate);
            if ($attached && is_file($attached) && hash_file('sha256', $attached) === hash_file('sha256', $source)) { $matches = array($candidate); break; }
        }
    }
    if ($matches) { return $cache[$file] = (int) $matches[0]; }
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $temporary = wp_tempnam(basename($source));
    if (!copy($source, $temporary)) { WP_CLI::error('Cannot copy ' . $file); }
    $id = media_handle_sideload(array('name' => basename($source), 'tmp_name' => $temporary), 0);
    if (is_wp_error($id)) { WP_CLI::error($id->get_error_message()); }
    update_post_meta($id, '_constructor_source', $file);
    return $cache[$file] = (int) $id;
}

function project_theme_constructor_resolve_content($value, string $language, array $translations) {
    static $captions = null;
    if (null === $captions) { $captions = json_decode(file_get_contents(__DIR__ . '/media-captions.json'), true, 512, JSON_THROW_ON_ERROR); }
    if (is_string($value)) {
        if (isset($captions[$value][$language])) { return $captions[$value][$language]; }
        if ('ru' === $language && isset($translations[$value])) { return $translations[$value]; }
        return $value;
    }
    if (!is_array($value)) { return $value; }
    if (isset($value['page_slug'])) {
        $page = get_page_by_path($value['page_slug'], OBJECT, 'page');
        if (!$page) { WP_CLI::error('Missing existing page: ' . $value['page_slug']); }
        $id = function_exists('pll_get_post') ? pll_get_post($page->ID, $language) : $page->ID;
        if (!$id) { WP_CLI::error('Missing translation: ' . $value['page_slug'] . ' ' . $language); }
        return (int) $id;
    }
    $result = array();
    foreach ($value as $key => $item) {
        if (is_array($item) && isset($item['file'])) {
            $result[$key] = project_theme_constructor_import_media($item['file']);
            $result[$key . '_alt'] = project_theme_constructor_resolve_content($item['alt'] ?? '', $language, $translations);
        } else {
            $result[$key] = project_theme_constructor_resolve_content($item, $language, $translations);
        }
    }
    return $result;
}

$mode = $args[0] ?? 'schema';
if ('schema' === $mode) {
    if (!acf_get_field_group('group_project_theme_constructor')) {
        acf_import_field_group(project_theme_constructor_group());
        WP_CLI::log('Imported editable constructor field group.');
    } else { WP_CLI::log('Existing constructor definition preserved.'); }
    if (!acf_get_post_type('post_type_svitvody_product')) {
        if (post_type_exists('sv_product')) { WP_CLI::error('sv_product already belongs to another registration.'); }
        acf_import_post_type(array(
            'key' => 'post_type_svitvody_product', 'title' => 'Товари', 'post_type' => 'sv_product', 'active' => true, 'advanced_configuration' => true,
            'labels' => array('name' => 'Товари', 'singular_name' => 'Товар', 'menu_name' => 'Товари', 'add_new_item' => 'Додати товар', 'edit_item' => 'Редагувати товар'),
            'public' => false, 'publicly_queryable' => false, 'show_ui' => true, 'show_in_menu' => true, 'show_in_nav_menus' => false,
            'show_in_rest' => false, 'exclude_from_search' => true, 'has_archive' => false,
            'rewrite' => array('permalink_rewrite' => 'no_permalink'), 'query_var' => 'none',
            'supports' => array('title'), 'taxonomies' => array('sv_product_category'), 'menu_icon' => 'dashicons-products',
        ));
        WP_CLI::log('Imported editable ACF product type.');
    }
    if (!acf_get_taxonomy('taxonomy_svitvody_product_category')) {
        if (taxonomy_exists('sv_product_category')) { WP_CLI::error('sv_product_category already belongs to another registration.'); }
        acf_import_taxonomy(array(
            'key' => 'taxonomy_svitvody_product_category', 'title' => 'Категорії товарів', 'taxonomy' => 'sv_product_category', 'active' => true, 'advanced_configuration' => true,
            'object_type' => array('sv_product'), 'labels' => array('name' => 'Категорії товарів', 'singular_name' => 'Категорія товарів'),
            'hierarchical' => true, 'public' => false, 'publicly_queryable' => false, 'show_ui' => true, 'show_in_menu' => true, 'show_in_nav_menus' => false, 'show_in_rest' => false,
            'rewrite' => array('permalink_rewrite' => 'no_permalink'), 'query_var' => 'none',
        ));
        WP_CLI::log('Imported editable ACF product taxonomy.');
    }
    WP_CLI::success('Schema ready.');
    return;
}
if ('content' !== $mode) { WP_CLI::error('Use schema or content.'); }
$seeds = json_decode(file_get_contents(__DIR__ . '/content-uk.json'), true, 512, JSON_THROW_ON_ERROR);
$translations = json_decode(file_get_contents(__DIR__ . '/translations-ru.json'), true, 512, JSON_THROW_ON_ERROR);
if (in_array('', $translations, true)) { WP_CLI::error('Unfinished translations.'); }
$page_sections = array(
    'home' => array('home', 'promo', 'pricing', 'work-steps'),
    'about-water' => array('about-water', 'water-quality', 'composition'),
    'water-for-home' => array('water-for-home', 'home-features', 'pricing'),
    'water-for-office' => array('water-for-office', 'office-usecases', 'pricing', 'work-steps'),
);
$languages = function_exists('pll_languages_list') ? pll_languages_list() : array('uk');
foreach ($languages as $language) {
    if (!in_array($language, array('uk', 'ru'), true)) { WP_CLI::error('Translation needed for ' . $language); }
}
foreach ($page_sections as $slug => $section_keys) {
    $page = get_page_by_path($slug, OBJECT, 'page');
    if (!$page) { WP_CLI::error('Missing page ' . $slug); }
    foreach ($languages as $language) {
        $id = function_exists('pll_get_post') ? pll_get_post($page->ID, $language) : $page->ID;
        if (!$id) { WP_CLI::error('Missing page translation ' . $slug . ' ' . $language); }
        if (metadata_exists('post', $id, 'constructor')) {
            WP_CLI::log('Preserved existing constructor for page ' . $id);
            continue;
        }
        $rows = array_map(function ($key) use ($seeds, $language, $translations) {
            return project_theme_constructor_resolve_content($seeds[$key], $language, $translations);
        }, $section_keys);
        update_field('field_project_theme_constructor', $rows, $id);
        update_post_meta($id, '_wp_page_template', 'page-constructor.php');
        update_post_meta($id, '_constructor_import_version', 1);
        WP_CLI::log($id . ' ' . $language . ': ' . count($rows) . ' sections');
    }
}
WP_CLI::success('Constructor content imported.');
