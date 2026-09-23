<?php
/** Explicit migration: wp eval-file .../products.php languages|content. */
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$product_mode = $args[0] ?? 'content';
if ('languages' === $product_mode) {
    if (function_exists('PLL')) {
        $options = get_option('polylang');
        $options['post_types'] = array_values(array_unique(array_merge($options['post_types'] ?? array(), array('sv_product'))));
        $options['taxonomies'] = array_values(array_unique(array_merge($options['taxonomies'] ?? array(), array('sv_product_category'))));
        update_option('polylang', $options);
    }
    WP_CLI::success('Product languages enabled. Run content in a fresh WordPress request.');
    return;
}
if ('content' !== $product_mode) { WP_CLI::error('Use languages or content.'); }
$args = array('schema');
require __DIR__ . '/import.php';
$f = 'project_theme_constructor_field';
$m = 'project_theme_constructor_message';
if (!acf_get_field_group('group_project_theme_product')) {
    acf_import_field_group(array(
        'key' => 'group_project_theme_product', 'title' => 'Дані товару',
        'location' => array(array(array('param' => 'post_type', 'operator' => '==', 'value' => 'sv_product'))),
        'fields' => project_theme_constructor_key_fields(array_merge(array(
            $m('content_message', 'Картка товару'),
            $f('product_label', 'Короткий підпис', 'text', 50),
            $f('product_button_text', 'Текст кнопки', 'text', 50, array('default_value' => 'Додати')),
            $m('price_message', 'Вартість'),
            $f('product_price', 'Ціна', 'number', 50, array('required' => 1, 'step' => '0.01')),
            $f('product_currency', 'Валюта', 'text', 50, array('default_value' => '₴', 'required' => 1)),
            $m('image_message', 'Зображення товару'),
        ), project_theme_constructor_image_fields('product_image', 'Зображення')), 'product'),
        'active' => true, 'style' => 'default',
    ));
}
$languages = function_exists('pll_languages_list') ? pll_languages_list() : array('uk');
if (function_exists('pll_is_translated_post_type') && (!pll_is_translated_post_type('sv_product') || !pll_is_translated_taxonomy('sv_product_category'))) {
    WP_CLI::error('Run the languages migration before importing products.');
}
foreach ($languages as $language) {
    if (!in_array($language, array('uk', 'ru'), true)) { WP_CLI::error('Translation required for ' . $language); }
}
$seeds = json_decode(file_get_contents(__DIR__ . '/content-uk.json'), true, 512, JSON_THROW_ON_ERROR)['products'];
$translations = json_decode(file_get_contents(__DIR__ . '/translations-ru.json'), true, 512, JSON_THROW_ON_ERROR);
$categories = array(
    'bottle-deposit' => array('slug' => 'accessories', 'uk' => 'Аксесуари', 'ru' => 'Аксессуары'),
    'pump' => array('slug' => 'accessories', 'uk' => 'Аксесуари', 'ru' => 'Аксессуары'),
);
foreach ($seeds as $position => $seed) {
    $posts = array();
    $terms = array();
    foreach ($languages as $language) {
        $category = $categories[$seed['slug']];
        $term_slug = $category['slug'] . '-' . $language;
        $term = get_term_by('slug', $term_slug, 'sv_product_category');
        if (!$term) {
            $created = wp_insert_term($category[$language], 'sv_product_category', array('slug' => $term_slug));
            if (is_wp_error($created)) { WP_CLI::error($created->get_error_message()); }
            $term = get_term($created['term_id'], 'sv_product_category');
        }
        $terms[$language] = (int) $term->term_id;
        if (function_exists('pll_set_term_language')) { pll_set_term_language($term->term_id, $language); }
        $values = project_theme_constructor_resolve_content($seed, $language, $translations);
        $existing = get_posts(array('post_type' => 'sv_product', 'post_status' => 'any', 'numberposts' => 1, 'lang' => $language,
            'meta_key' => '_constructor_product_key', 'meta_value' => $seed['slug'], 'suppress_filters' => false));
        if (!$existing) {
            $existing = get_posts(array('post_type' => 'sv_product', 'post_status' => 'any', 'numberposts' => 1, 'lang' => $language,
                'title' => $values['title'], 'suppress_filters' => false));
        }
        if ($existing) {
            $id = $existing[0]->ID;
            WP_CLI::log('Preserved existing product ' . $id);
        } else {
            $id = wp_insert_post(array('post_type' => 'sv_product', 'post_status' => 'publish', 'post_title' => $values['title'],
                'post_name' => $seed['slug'] . '-' . $language, 'menu_order' => $position,
                'meta_input' => array('_constructor_product_key' => $seed['slug'])), true);
            if (is_wp_error($id)) { WP_CLI::error($id->get_error_message()); }
            if (function_exists('pll_set_post_language')) { pll_set_post_language($id, $language); }
            foreach (array('label', 'button_text', 'price', 'currency', 'image', 'image_alt') as $name) {
                update_field('field_ct_product_product_' . $name, $values[$name], $id);
            }
            WP_CLI::log('Imported product ' . $id . ' ' . $language . ': ' . $values['title']);
        }
        update_field('field_ct_product_product_kind', 'extra', $id);
        wp_set_object_terms($id, array($term->term_id), 'sv_product_category');
        $posts[$language] = $id;
    }
    if (function_exists('pll_save_post_translations')) {
        pll_save_post_translations($posts);
        pll_save_term_translations($terms);
    }
}
WP_CLI::success('Shared products imported.');
