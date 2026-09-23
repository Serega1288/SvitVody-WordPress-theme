<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$mode = $args[0] ?? 'verify';
if ($mode === 'cleanup') {
    foreach (get_posts(array('post_type' => 'sv_order', 'post_status' => 'any', 'numberposts' => -1, 'lang' => '')) as $order) {
        $data = get_post_meta($order->ID, '_sv_order_data', true);
        if (str_starts_with($data['customer']['message'] ?? '', '[AUTOTEST-ORDERS]')) {
            wp_delete_post($order->ID, true);
            WP_CLI::log('Removed test order ' . $order->ID);
        }
    }
    return;
}
foreach (pll_languages_list() as $language) {
    $water = project_theme_get_water_product(0, $language);
    if (!$water || count($water['tiers']) !== 3) { WP_CLI::error('Water tiers missing: ' . $language); }
    foreach (array(2 => 180, 5 => 180, 6 => 170, 11 => 170, 12 => 160, 20 => 160) as $amount => $unit) {
        $quote = project_theme_order_quote(array(array('id' => $water['cart_key'], 'amount' => $amount)), $language);
        if (is_wp_error($quote) || $quote['total_cents'] !== $amount * $unit * 100) { WP_CLI::error('Incorrect tier for ' . $amount); }
    }
    foreach (array(0, 1, -2, 1.5, 100, '6') as $amount) {
        if (!is_wp_error(project_theme_order_quote(array(array('id' => $water['cart_key'], 'amount' => $amount)), $language))) { WP_CLI::error('Invalid quantity accepted.'); }
    }
    if (count(project_theme_get_products($language)) !== 2) { WP_CLI::error('Water leaked into extras.'); }
    WP_CLI::log($language . ': water ' . $water['id'] . ' / ' . $water['cart_key'] . '; tiers and quantities verified.');
}
$type = get_post_type_object('sv_order');
foreach (get_posts(array('post_type' => 'page', 'post_status' => 'publish', 'numberposts' => -1, 'lang' => '')) as $page) {
    foreach ((array) get_field('constructor', $page->ID) as $row) {
        if (($row['acf_fc_layout'] ?? '') !== 'template-water-pricing') { continue; }
        if (empty($row['water_product']) || !project_theme_get_water_product((int) $row['water_product'], pll_get_post_language($page->ID))) {
            WP_CLI::error('Pricing page has no explicit localized water link: ' . $page->ID);
        }
        WP_CLI::log('Verified water link on page ' . $page->ID);
    }
}
if ($type->public || $type->publicly_queryable || $type->show_in_rest || $type->rewrite || $type->cap->read_post !== 'manage_options') { WP_CLI::error('Order privacy configuration invalid.'); }
foreach (get_posts(array('post_type' => 'sv_order', 'post_status' => 'any', 'numberposts' => -1, 'lang' => '')) as $order) {
    $data = get_post_meta($order->ID, '_sv_order_data', true);
    if (!str_starts_with($data['customer']['message'] ?? '', '[AUTOTEST-ORDERS]')) { continue; }
    if ($order->post_status !== 'private') { WP_CLI::error('Test order is not private.'); }
    $sum = 0;
    foreach ($data['quote']['items'] as $item) { $sum += $item['amount'] * $item['unit_cents']; }
    if ($sum !== $data['quote']['total_cents']) { WP_CLI::error('Saved order total mismatch.'); }
    WP_CLI::log('Verified test order ' . $order->ID . ': ' . $sum . ' cents; ' . $data['language']);
}
WP_CLI::success('Order and water checks passed.');
