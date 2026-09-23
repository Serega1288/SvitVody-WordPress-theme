<?php
/** Normalize product categories to accessories and water for every Polylang language. */
if (!defined('WP_CLI') || !WP_CLI) { exit; }

$languages = function_exists('pll_languages_list') ? pll_languages_list() : array('uk');
$definitions = array(
    'accessories' => array('uk' => 'Аксесуари', 'ru' => 'Аксессуары'),
    'water' => array('uk' => 'Вода', 'ru' => 'Вода'),
);
$terms = array('accessories' => array(), 'water' => array());

foreach ($definitions as $category => $names) {
    foreach ($languages as $language) {
        if (!isset($names[$language])) { WP_CLI::error('Missing category translation for ' . $language); }
        $term = get_term_by('slug', $category . '-' . $language, 'sv_product_category');
        if (!$term) {
            $created = wp_insert_term($names[$language], 'sv_product_category', array('slug' => $category . '-' . $language));
            if (is_wp_error($created)) { WP_CLI::error($created->get_error_message()); }
            $term = get_term($created['term_id'], 'sv_product_category');
        } elseif ($term->name !== $names[$language]) {
            $updated = wp_update_term($term->term_id, 'sv_product_category', array('name' => $names[$language]));
            if (is_wp_error($updated)) { WP_CLI::error($updated->get_error_message()); }
        }
        $terms[$category][$language] = (int) $term->term_id;
        if (function_exists('pll_set_term_language')) { pll_set_term_language($term->term_id, $language); }
    }
    if (function_exists('pll_save_term_translations')) { pll_save_term_translations($terms[$category]); }
}

$products = get_posts(array('post_type' => 'sv_product', 'post_status' => 'any', 'numberposts' => -1, 'lang' => ''));
foreach ($products as $product) {
    $language = function_exists('pll_get_post_language') ? pll_get_post_language($product->ID) : 'uk';
    if (!isset($terms['accessories'][$language])) { WP_CLI::error('Unknown product language on #' . $product->ID); }
    $kind = get_field('product_kind', $product->ID) === 'water' ? 'water' : 'extra';
    if ('extra' === $kind && !get_field('product_kind', $product->ID)) {
        update_field('field_ct_product_product_kind', 'extra', $product->ID);
    }
    $category = 'water' === $kind ? 'water' : 'accessories';
    $result = wp_set_object_terms($product->ID, array($terms[$category][$language]), 'sv_product_category', false);
    if (is_wp_error($result)) { WP_CLI::error($result->get_error_message()); }
    WP_CLI::log(sprintf('Product #%d -> %s (%s)', $product->ID, $category, $language));
}

foreach (array('containers-uk', 'containers-ru') as $obsolete_slug) {
    $obsolete = get_term_by('slug', $obsolete_slug, 'sv_product_category');
    if (!$obsolete) { continue; }
    $deleted = wp_delete_term($obsolete->term_id, 'sv_product_category');
    if (is_wp_error($deleted)) { WP_CLI::error($deleted->get_error_message()); }
}

WP_CLI::success('Product categories normalized.');
