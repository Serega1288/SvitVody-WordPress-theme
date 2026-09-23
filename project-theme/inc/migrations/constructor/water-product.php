<?php
/** Explicit migration of page price lists to a shared water product. */
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$pages = get_posts(array('post_type' => 'page', 'post_status' => 'any', 'numberposts' => -1, 'lang' => '', 'meta_key' => '_wp_page_template', 'meta_value' => 'page-constructor.php'));
$sources = array();
foreach ($pages as $page) {
    $language = pll_get_post_language($page->ID);
    foreach ((array) get_field('constructor', $page->ID) as $row) {
        if (($row['acf_fc_layout'] ?? '') !== 'template-water-pricing' || empty($row['prices'])) { continue; }
        if (isset($sources[$language]) && $sources[$language] !== $row['prices']) {
            WP_CLI::error('Different price lists require review: page ' . $page->ID);
        }
        $sources[$language] = $row['prices'];
    }
}
if (($args[0] ?? '') === 'inspect') { WP_CLI::log(wp_json_encode($sources, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)); return; }
require_once __DIR__ . '/schema.php';
$group = acf_get_field_group('group_project_theme_product');
if (!$group) { WP_CLI::error('Missing product group.'); }
function project_theme_import_water_field(array $field, int $parent): void {
    $children = $field['sub_fields'] ?? array();
    unset($field['sub_fields'], $field['_valid']);
    $saved = acf_get_field($field['key']);
    if (!$saved) { $saved = acf_update_field(acf_get_valid_field($field + array('ID' => 0, 'parent' => $parent))); }
    foreach ($children as $index => $child) {
        project_theme_import_water_field($child + array('menu_order' => $index), $saved['ID']);
    }
}
$kind_key = 'field_ct_product_product_kind';
project_theme_import_water_field(array('key' => $kind_key, 'name' => 'product_kind', 'label' => 'Тип товару', 'type' => 'select',
    'choices' => array('extra' => 'Додатковий товар або послуга', 'water' => 'Вода з ціною за кількістю'), 'default_value' => 'extra', 'required' => 1, 'menu_order' => -1, 'wrapper' => array('width' => '100')), $group['ID']);
$original = acf_get_field('field_ct_water_pricing_prices');
if (!acf_get_field('field_ct_product_water_tiers')) {
    if (!$original) { WP_CLI::error('Missing source tier definition.'); }
    $clean_fields = function (array $fields) use (&$clean_fields): array {
        foreach ($fields as &$field) {
            unset($field['ID'], $field['parent'], $field['parent_layout']);
            $field['key'] = 'field_ct_product_water_tier_' . $field['name'];
            if (isset($field['sub_fields'])) { $field['sub_fields'] = $clean_fields($field['sub_fields']); }
            if (in_array($field['name'], array('price', 'min_bottles', 'title', 'button_text'), true)) { $field['required'] = 1; }
            if ($field['name'] === 'price') { $field['step'] = '0.01'; }
            if ($field['name'] === 'min_bottles') { $field['max'] = 99; }
            // Currency belongs to the product, not individual quantity tiers.
        }
        return array_values(array_filter($fields, fn($field) => $field['name'] !== 'currency'));
    };
    project_theme_import_water_field(array('key' => 'field_ct_product_water_tiers_message', 'name' => '', 'label' => 'Вартість води за кількістю', 'type' => 'message',
        'message' => 'Ціна одного бутля діє від зазначеної кількості до наступного рівня. Рівні використовуються в блоці та кошику.', 'menu_order' => 30,
        'wrapper' => array('width' => '100'), 'conditional_logic' => array(array(array('field' => $kind_key, 'operator' => '==', 'value' => 'water')))), $group['ID']);
    project_theme_import_water_field(array('key' => 'field_ct_product_water_tiers', 'name' => 'water_tiers', 'label' => 'Цінові рівні', 'type' => 'repeater',
        'layout' => 'block', 'min' => 1, 'required' => 1, 'button_label' => 'Додати рівень', 'menu_order' => 31,
        'conditional_logic' => array(array(array('field' => $kind_key, 'operator' => '==', 'value' => 'water'))),
        'sub_fields' => $clean_fields($original['sub_fields'])), $group['ID']);
}
$price_field = acf_get_field('field_ct_product_product_price');
if ($price_field) {
    $price_field['conditional_logic'] = array(array(array('field' => $kind_key, 'operator' => '!=', 'value' => 'water')));
    acf_update_field($price_field);
}
$water_ids = array();
foreach (pll_languages_list() as $language) {
    $existing = get_posts(array('post_type' => 'sv_product', 'post_status' => 'any', 'numberposts' => 1, 'lang' => $language, 'suppress_filters' => false,
        'meta_key' => '_constructor_product_key', 'meta_value' => 'water'));
    if ($existing) { $water_ids[$language] = $existing[0]->ID; continue; }
    if (empty($sources[$language])) { WP_CLI::error('Missing water content for ' . $language); }
    $currencies = array_unique(array_column($sources[$language], 'currency'));
    if (count($currencies) !== 1) { WP_CLI::error('Water tiers must have one currency.'); }
    $id = wp_insert_post(array('post_type' => 'sv_product', 'post_status' => 'publish',
        'post_title' => $language === 'ru' ? 'Вода питьевая 18.9 л' : 'Вода питна 18.9 л',
        'meta_input' => array('_constructor_product_key' => 'water')), true);
    if (is_wp_error($id)) { WP_CLI::error($id->get_error_message()); }
    pll_set_post_language($id, $language);
    update_field($kind_key, 'water', $id);
    update_field('field_ct_product_water_tiers', $sources[$language], $id);
    update_field('field_ct_product_product_currency', reset($currencies), $id);
    update_field('field_ct_product_product_button_text', $language === 'ru' ? 'Заказать' : 'Замовити', $id);
    $bottle = get_posts(array('post_type' => 'sv_product', 'numberposts' => 1, 'lang' => $language, 'suppress_filters' => false, 'meta_key' => '_constructor_product_key', 'meta_value' => 'bottle-deposit'));
    if ($bottle) {
        update_field('field_ct_product_product_image', get_field('product_image', $bottle[0]->ID), $id);
        update_field('field_ct_product_product_image_alt', get_the_title($id), $id);
    }
    $water_ids[$language] = $id;
    WP_CLI::log('Created water product ' . $id . ' ' . $language);
}
pll_save_post_translations($water_ids);
$water_terms = array();
foreach ($water_ids as $language => $water_id) {
    $term = get_term_by('slug', 'water-' . $language, 'sv_product_category');
    if (!$term) {
        $created = wp_insert_term('Вода', 'sv_product_category', array('slug' => 'water-' . $language));
        if (is_wp_error($created)) { WP_CLI::error($created->get_error_message()); }
        $term = get_term($created['term_id'], 'sv_product_category');
    }
    $water_terms[$language] = (int) $term->term_id;
    if (function_exists('pll_set_term_language')) { pll_set_term_language($term->term_id, $language); }
    wp_set_object_terms($water_id, array($term->term_id), 'sv_product_category');
}
if (function_exists('pll_save_term_translations')) { pll_save_term_translations($water_terms); }
$constructor = acf_get_field('field_project_theme_constructor');
project_theme_import_water_field(array('key' => 'field_ct_water_pricing_water_product', 'name' => 'water_product', 'label' => 'Товар води', 'type' => 'post_object',
    'post_type' => array('sv_product'), 'return_format' => 'id', 'required' => 1, 'ui' => 1, 'multiple' => 0, 'menu_order' => 4,
    'parent_layout' => 'layout_ct_water-pricing', 'instructions' => 'Цінові рівні редагуються у вибраному товарі.'), $constructor['ID']);
foreach ($pages as $page) {
    $language = pll_get_post_language($page->ID);
    foreach ((array) get_field('constructor', $page->ID) as $index => $row) {
        if (($row['acf_fc_layout'] ?? '') !== 'template-water-pricing' || !empty($row['water_product'])) { continue; }
        if (!update_sub_field(array('field_project_theme_constructor', $index + 1, 'field_ct_water_pricing_water_product'), $water_ids[$language], $page->ID)) {
            WP_CLI::error('Could not link water on page ' . $page->ID . '. Retry in a fresh request.');
        }
        WP_CLI::log('Linked pricing page ' . $page->ID);
    }
}
// Values remain in post meta and the database backup; only the obsolete editor definition is removed.
if ($original) { acf_delete_field($original['ID']); }
WP_CLI::success('Shared water product and quantity tiers ready.');
