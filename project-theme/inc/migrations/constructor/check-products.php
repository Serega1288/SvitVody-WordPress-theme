<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$mode = $args[0] ?? 'verify';
if ('create' === $mode) {
    $ids = array();
    foreach (array('uk' => 'Перевірка нового товару', 'ru' => 'Проверка нового товара') as $language => $title) {
        $id = wp_insert_post(array('post_type' => 'sv_product', 'post_status' => 'publish', 'post_title' => $title, 'menu_order' => 10, 'meta_input' => array('_product_audit' => 1)), true);
        if (is_wp_error($id)) { WP_CLI::error($id->get_error_message()); }
        pll_set_post_language($id, $language);
        $values = array('label' => $language === 'uk' ? 'Новий товар' : 'Новый товар', 'price' => 123.45, 'currency' => '₴', 'button_text' => $language === 'uk' ? 'Додати' : 'Добавить', 'image' => 737, 'image_alt' => $title);
        foreach ($values as $name => $value) { update_field('field_ct_product_product_' . $name, $value, $id); }
        $ids[$language] = $id;
    }
    pll_save_post_translations($ids);
    WP_CLI::log(wp_json_encode($ids));
} elseif ('cleanup' === $mode) {
    foreach (get_posts(array('post_type' => 'sv_product', 'post_status' => 'any', 'numberposts' => -1, 'lang' => '', 'meta_key' => '_product_audit', 'meta_value' => 1)) as $product) {
        wp_delete_post($product->ID, true);
        WP_CLI::log('Removed test product ' . $product->ID);
    }
} else {
    if (!acf_get_field_group('group_project_theme_product')) { WP_CLI::error('Missing product fields.'); }
    $type = get_post_type_object('sv_product');
    $taxonomy = get_taxonomy('sv_product_category');
    if ($type->public || $type->publicly_queryable || $type->has_archive || $type->rewrite || $taxonomy->publicly_queryable || $taxonomy->rewrite) { WP_CLI::error('Public product routes enabled.'); }
    foreach (pll_languages_list() as $language) {
        $products = project_theme_get_products($language);
        if (count($products) < 2) { WP_CLI::error('Missing products: ' . $language); }
        foreach ($products as $product) {
            if (pll_get_post_language($product['id']) !== $language) { WP_CLI::error('Wrong product language.'); }
            if (!$product['image_id'] || null === $product['price']) { WP_CLI::error('Incomplete product ' . $product['id']); }
            foreach (pll_languages_list() as $other) {
                if (!pll_get_post($product['id'], $other)) { WP_CLI::error('Missing product translation.'); }
            }
        }
        WP_CLI::log($language . ': ' . count($products) . ' products; translations, images and prices verified.');
    }
    WP_CLI::success('Shared product catalogue verified.');
}
