<?php
/** Live delivery requires explicit invocation; never include in general test suites. */
if (!defined('WP_CLI') || !WP_CLI) { exit; }
if (($args[0] ?? '') !== 'send-authorized-test') { WP_CLI::error('Explicit live Telegram test argument required.'); }
if (!get_option('sv_notify_token') || !get_option('sv_notify_chat') || !get_option('sv_notify_telegram_enabled')) { WP_CLI::error('Telegram settings are incomplete or disabled.'); }
// Suppress email only for this synthetic order, without changing the user's global settings.
add_filter('pre_option_sv_notify_email_enabled', '__return_zero');
$water = project_theme_get_water_product(0, 'uk');
$items = array(array('id' => $water['cart_key'], 'amount' => $water['tiers'][0]['min_bottles']));
$quote = project_theme_order_quote($items, 'uk');
$result = project_theme_create_order(array('request_id' => wp_generate_uuid4(), 'language' => 'uk',
    'name' => 'ТЕСТ Telegram — не виконувати', 'phone' => '+380000000000', 'address' => 'Тест: доставки не потрібно',
    'date' => wp_date('Y-m-d', time() + DAY_IN_SECONDS),
    'message' => 'Перевірка підключення Telegram за запитом власника. Не є реальним замовленням.',
    'items' => $items, 'expected_total_cents' => $quote['total_cents']));
if (is_wp_error($result)) { WP_CLI::error($result->get_error_message()); }
$id = $result['order_id'];
wp_clear_scheduled_hook('sv_notify_order', array($id));
remove_action('shutdown', 'spawn_cron');
$data = get_post_meta($id, '_sv_order_data', true);
$data['test'] = true;
update_post_meta($id, '_sv_order_data', $data);
project_theme_process_order_notifications($id, 'telegram');
wp_clear_scheduled_hook('sv_notify_order', array($id));
$state = get_post_meta($id, '_sv_order_notifications', true);
WP_CLI::log(wp_json_encode(array('order_id' => $id, 'email' => $state['email']['status'], 'telegram' => $state['telegram']['status'], 'detail' => $state['telegram']['detail']), JSON_UNESCAPED_UNICODE));
