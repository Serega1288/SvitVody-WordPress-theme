<?php
/** Shared catalogue for constructor sections and their order buttons. */

add_filter('pll_get_post_types', function (array $types, bool $settings): array {
    if ($settings) { $types['sv_product'] = 'sv_product'; }
    return $types;
}, 10, 2);

add_filter('pll_get_taxonomies', function (array $types, bool $settings): array {
    if ($settings) { $types['sv_product_category'] = 'sv_product_category'; }
    return $types;
}, 10, 2);

function project_theme_get_catalogue(?string $language = null): array {
    if (!function_exists('get_field') || !post_type_exists('sv_product')) { return array(); }
    if (null === $language) {
        $language = function_exists('pll_current_language') ? (pll_current_language() ?: pll_default_language()) : '';
    }
    static $catalogues = array();
    if (isset($catalogues[$language])) { return $catalogues[$language]; }
    $query = new WP_Query(array(
        'post_type' => 'sv_product', 'post_status' => 'publish', 'posts_per_page' => -1,
        'orderby' => array('menu_order' => 'ASC', 'ID' => 'ASC'), 'no_found_rows' => true, 'lang' => $language,
    ));
    $products = array();
    foreach ($query->posts as $product) {
        $id = $product->ID;
        $canonical_id = function_exists('pll_get_post') ? (pll_get_post($id, pll_default_language()) ?: $id) : $id;
        // Preserve existing baskets for the two products imported from the HTML.
        $legacy_key = get_post_meta($canonical_id, '_constructor_product_key', true);
        $cart_key = in_array($legacy_key, array('pump', 'bottle-deposit'), true) ? $legacy_key : 'product-' . $canonical_id;
        $image = get_field('product_image', $id);
        $image_id = is_array($image) ? (int) ($image['ID'] ?? 0) : (int) $image;
        $price = get_field('product_price', $id);
        $kind = get_field('product_kind', $id) === 'water' ? 'water' : 'extra';
        $tiers = array();
        foreach ((array) get_field('water_tiers', $id) as $tier) {
            if (!is_array($tier) || !is_numeric($tier['price'] ?? null) || (float) $tier['price'] < 0 || (int) ($tier['min_bottles'] ?? 0) < 1) { continue; }
            $tiers[] = array_merge($tier, array('min_bottles' => (int) $tier['min_bottles'], 'price' => (float) $tier['price']));
        }
        usort($tiers, fn($a, $b) => $a['min_bottles'] <=> $b['min_bottles']);
        $products[] = array(
            'id' => $id, 'cart_key' => $cart_key, 'title' => get_the_title($id), 'type' => $kind, 'tiers' => $tiers,
            'label' => (string) get_field('product_label', $id),
            'cart_description' => (string) get_field('product_cart_description', $id),
            'price' => is_numeric($price) ? max(0, (float) $price) : null,
            'currency' => (string) get_field('product_currency', $id),
            'button_text' => (string) get_field('product_button_text', $id),
            'image_id' => $image_id, 'image_alt' => (string) get_field('product_image_alt', $id),
        );
    }
    return $catalogues[$language] = $products;
}

function project_theme_get_products(?string $language = null): array {
    return array_values(array_filter(project_theme_get_catalogue($language), fn($product) => $product['type'] === 'extra'));
}

function project_theme_get_water_product(int $id = 0, ?string $language = null): ?array {
    foreach (project_theme_get_catalogue($language) as $product) {
        if ($product['type'] === 'water' && $product['tiers'] && (!$id || $product['id'] === $id)) { return $product; }
    }
    return null;
}

add_filter('acf/fields/post_object/query/key=field_ct_water_pricing_water_product', function (array $query): array {
    $query['meta_query'][] = array('key' => 'product_kind', 'value' => 'water');
    return $query;
});

add_filter('acf/validate_value/key=field_ct_product_water_tiers', function ($valid, $value) {
    if ($valid !== true || !is_array($value)) { return $valid; }
    $quantities = array();
    foreach ($value as $row) {
        $quantity = $row['field_ct_product_water_tier_min_bottles'] ?? 0;
        if (!is_numeric($quantity) || (float) $quantity !== (float) (int) $quantity || $quantity < 1 || $quantity > 99 || in_array((int) $quantity, $quantities, true)) {
            return __('Кількості мають бути різними цілими числами від 1 до 99.', 'project-theme');
        }
        $quantities[] = (int) $quantity;
    }
    return $valid;
}, 10, 2);

add_action('wp_enqueue_scripts', function (): void {
    $catalogue = array();
    foreach (project_theme_get_products() as $product) {
        if (null === $product['price']) { continue; }
        $catalogue[] = array(
            'id' => $product['cart_key'], 'type' => 'extra', 'title' => $product['title'],
            'detail' => $product['cart_description'], 'quantity' => 1, 'unitLabel' => __('шт.', 'project-theme'),
            'unitPrice' => $product['price'], 'currency' => $product['currency'],
        );
    }
    wp_add_inline_script('project-theme-main', 'window.svitvodyExtraProducts = ' . wp_json_encode($catalogue, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';', 'before');
    $water = array();
    foreach (project_theme_get_catalogue() as $product) {
        if ($product['type'] !== 'water' || !$product['tiers']) { continue; }
        $water[] = array('id' => $product['cart_key'], 'type' => 'water', 'title' => $product['title'], 'currency' => $product['currency'],
            'quantity' => $product['tiers'][0]['min_bottles'], 'unitPrice' => $product['tiers'][0]['price'], 'unitLabel' => __('бутлів', 'project-theme'),
            'tiers' => array_map(fn($tier) => array('quantity' => $tier['min_bottles'], 'unitPrice' => $tier['price']), $product['tiers']));
    }
    wp_add_inline_script('project-theme-main', 'window.svitvodyWaterProducts = ' . wp_json_encode($water, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';', 'before');
}, 20);
