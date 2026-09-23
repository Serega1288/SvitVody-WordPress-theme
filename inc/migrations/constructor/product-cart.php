<?php
/** Add editable product descriptions without replacing existing ACF definitions. */
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$group = acf_get_field_group('group_project_theme_product');
if (!$group || empty($group['ID'])) { WP_CLI::error('Import the product fields first.'); }
$fields = array(
    array('key' => 'field_ct_product_cart_message', 'name' => '', 'label' => 'Мінікошик', 'type' => 'message',
        'message' => 'Додатковий опис товару в кошику.', 'menu_order' => 20),
    array('key' => 'field_ct_product_product_cart_description', 'name' => 'product_cart_description',
        'label' => 'Опис товару в кошику', 'type' => 'textarea', 'rows' => 2, 'new_lines' => '', 'menu_order' => 21),
);
foreach ($fields as $field) {
    if (!acf_get_field($field['key'])) {
        acf_update_field(array_merge($field, array('parent' => $group['ID'], 'wrapper' => array('width' => '100'))));
    }
}
$descriptions = array(
    'bottle-deposit' => array('uk' => 'Повертається після здачі тари', 'ru' => 'Возвращается после сдачи тары'),
    'pump' => array('uk' => 'Ручна помпа для бутля', 'ru' => 'Ручная помпа для бутыли'),
);
foreach (get_posts(array('post_type' => 'sv_product', 'post_status' => 'any', 'numberposts' => -1, 'lang' => '')) as $product) {
    if (metadata_exists('post', $product->ID, 'product_cart_description')) { continue; }
    $key = get_post_meta($product->ID, '_constructor_product_key', true);
    $language = function_exists('pll_get_post_language') ? pll_get_post_language($product->ID) : 'uk';
    if (isset($descriptions[$key][$language])) {
        update_field('field_ct_product_product_cart_description', $descriptions[$key][$language], $product->ID);
        WP_CLI::log('Filled product description: ' . $product->ID);
    }
}
WP_CLI::success('Product cart descriptions ready; existing fields and values preserved.');
