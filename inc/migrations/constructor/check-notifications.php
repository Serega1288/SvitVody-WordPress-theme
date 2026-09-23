<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$config = array('email_enabled' => 1, 'telegram_enabled' => 1, 'email' => 'notifications@example.test',
    'token' => '123456:FAKE_TEST_TOKEN_NOT_A_REAL_SECRET', 'chat' => '-100123456789', 'topic' => 7);
foreach (array_keys($config) as $name) {
    add_filter('pre_option_sv_notify_' . $name, function () use (&$config, $name) { return $config[$name]; });
}
// No real notifications or asynchronous test jobs may leave this isolated CLI request.
add_filter('pre_schedule_event', function ($pre, $event) { return $event->hook === 'sv_notify_order' ? true : $pre; }, 10, 2);
$mail_count = 0;
$http_count = 0;
$mail_ok = true;
$http_mode = 'success';
$last_body = array();
add_filter('pre_wp_mail', function ($pre, $atts) use (&$mail_count, &$mail_ok) {
    ++$mail_count;
    if ($atts['to'] !== 'notifications@example.test' || !str_contains($atts['message'], 'Товари:')) { throw new RuntimeException('Invalid mail payload.'); }
    return $mail_ok;
}, 10, 2);
add_filter('pre_http_request', function ($pre, $args, $url) use (&$http_count, &$http_mode, &$last_body) {
    if (!str_starts_with($url, 'https://api.telegram.org/bot123456:FAKE_TEST_TOKEN_NOT_A_REAL_SECRET/')) { return new WP_Error('blocked', 'External traffic blocked by test.'); }
    ++$http_count;
    $last_body = json_decode($args['body'], true);
    if ($last_body['chat_id'] !== '-100123456789' || $last_body['message_thread_id'] !== 7 || mb_strlen($last_body['text']) > 2000 || isset($last_body['parse_mode'])) { throw new RuntimeException('Invalid Telegram payload.'); }
    if ($http_mode === 'transport') { return new WP_Error('timeout', 'Secret URL must never be saved: ' . $url); }
    return array('response' => array('code' => $http_mode === 'success' ? 200 : 400),
        'body' => wp_json_encode(array('ok' => $http_mode === 'success')), 'headers' => array());
}, 10, 3);
$ids = array();
$assert = function ($value, string $message): void { if (!$value) { throw new RuntimeException($message); } };
$make = function () use (&$ids): array {
    $water = project_theme_get_water_product(0, 'uk');
    $items = array(array('id' => $water['cart_key'], 'amount' => 2));
    $quote = project_theme_order_quote($items, 'uk');
    $payload = array('request_id' => wp_generate_uuid4(), 'language' => 'uk', 'name' => 'Тест сповіщень', 'phone' => '+380671234567',
        'address' => 'Тестова адреса', 'date' => wp_date('Y-m-d', time() + DAY_IN_SECONDS), 'time' => 'morning',
        'message' => '[AUTOTEST-NOTIFY] ' . str_repeat('Тест ', 350), 'items' => $items, 'expected_total_cents' => $quote['total_cents']);
    $result = project_theme_create_order($payload);
    if (is_wp_error($result)) { throw new RuntimeException($result->get_error_message()); }
    $ids[] = $result['order_id'];
    return array($result['order_id'], $payload);
};
try {
    [$id, $payload] = $make();
    $assert(get_post_meta($id, '_sv_order_notifications', true)['telegram']['status'] === 'pending', 'Missing queue.');
    project_theme_process_order_notifications($id);
    $state = get_post_meta($id, '_sv_order_notifications', true);
    $assert($state['email']['status'] === 'accepted' && $state['telegram']['status'] === 'sent', 'Delivery failed.');
    $assert(project_theme_create_order($payload)['order_id'] === $id, 'Duplicate order created.');
    project_theme_process_order_notifications($id);
    $assert($mail_count === 1 && $http_count === 1, 'Successful notifications repeated.');
    $assert(str_contains($last_body['text'], 'Повна заявка:'), 'Missing full order link.');
    $mail_ok = false;
    $http_mode = 'reject';
    [$failed] = $make();
    for ($i = 0; $i < 4; ++$i) { project_theme_process_order_notifications($failed); }
    $state = get_post_meta($failed, '_sv_order_notifications', true);
    $assert($state['email']['attempts'] === 3 && $state['telegram']['attempts'] === 3 && $mail_count === 4 && $http_count === 4, 'Retry limit failed.');
    $assert(get_post_status($failed) === 'private' && get_post_meta($failed, '_sv_order_data', true), 'Delivery failure lost order.');
    $mail_ok = true;
    $http_mode = 'transport';
    [$uncertain] = $make();
    project_theme_process_order_notifications($uncertain);
    project_theme_process_order_notifications($uncertain);
    $state = get_post_meta($uncertain, '_sv_order_notifications', true);
    $assert($state['telegram']['status'] === 'unknown' && $http_count === 5, 'Uncertain delivery auto-repeated.');
    $assert(!str_contains(wp_json_encode($state), $config['token']), 'Secret leaked into status.');
    $config['email_enabled'] = 0;
    $config['telegram_enabled'] = 0;
    [$disabled] = $make();
    project_theme_process_order_notifications($disabled);
    $assert($mail_count === 5 && $http_count === 5, 'Disabled channel sent data.');
    $field = acf_get_field('field_sv_notify_token');
    $prepared = apply_filters('acf/prepare_field', $field);
    $assert($prepared['value'] === '', 'Token exposed in admin input.');
    $user = get_current_user_id();
    wp_set_current_user(0);
    $assert(apply_filters('acf/prepare_field', $field) === false, 'Unauthorized user can view settings.');
    wp_set_current_user($user);
    WP_CLI::success('Notification queue, mail, Telegram, topic, truncation, deduplication, bounded retries, uncertain delivery, disabled channels and secret redaction verified with mocked transports.');
} finally {
    foreach ($ids as $id) { wp_delete_post($id, true); }
    remove_action('shutdown', 'spawn_cron');
    WP_CLI::log('Removed notification test orders. No real email or Telegram requests sent.');
}
