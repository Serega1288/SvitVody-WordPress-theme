<?php
/** Shared, administrator-only delivery settings and an order notification outbox. */
add_filter('cron_request', function (array $request): array {
    $url = wp_parse_url($request['url']);
    $home = wp_parse_url(home_url('/'));
    // Docker publishes Apache's port 80 as 8080 on the host, not inside the PHP container.
    if (getenv('WORDPRESS_DB_HOST') && in_array($home['host'] ?? '', array('localhost', '127.0.0.1'), true)
        && ($url['host'] ?? '') === $home['host'] && ($url['path'] ?? '') === '/wp-cron.php') {
        $request['url'] = 'http://127.0.0.1/wp-cron.php' . (isset($url['query']) ? '?' . $url['query'] : '');
        $request['args']['headers']['Host'] = $home['host'] . (isset($home['port']) ? ':' . $home['port'] : '');
    }
    return $request;
});

function project_theme_notification_field(array $field): bool {
    return str_starts_with($field['key'] ?? '', 'field_sv_notify_');
}

add_filter('acf/prepare_field', function ($field) {
    if (!$field || !project_theme_notification_field($field)) { return $field; }
    if (!current_user_can('manage_options')) { return false; }
    if (($field['name'] ?? '') === 'sv_notify_token') {
        $field['value'] = '';
        $field['placeholder'] = get_option($field['name']) ? __('Збережено. Порожнє поле залишить значення без змін.', 'project-theme') : __('Введіть секретний ключ або пароль', 'project-theme');
    }
    return $field;
});
add_filter('acf/load_value', function ($value, $post_id, $field) {
    if (!project_theme_notification_field($field) || empty($field['name'])) { return $value; }
    if (!is_admin() || !current_user_can('manage_options') || in_array($field['name'], array('sv_notify_token', 'sv_notify_clear_token'), true)) { return ''; }
    return get_option($field['name'], $value);
}, 20, 3);
add_filter('acf/update_value', function ($value, $post_id, $field) {
    if (!project_theme_notification_field($field) || empty($field['name'])) { return $value; }
    if (!current_user_can('manage_options')) { return ''; }
    $name = $field['name'];
    if ($name === 'sv_notify_clear_token') {
        if ($value) { delete_option(str_replace('sv_notify_clear_', 'sv_notify_', $name)); }
    } elseif ($name === 'sv_notify_token') {
        if (is_string($value) && trim($value) !== '') { update_option($name, trim($value), false); }
    } else {
        update_option($name, is_scalar($value) ? sanitize_text_field((string) $value) : '', false);
    }
    // Keep technical settings outside the locale-suffixed ACF options, especially the secret.
    return '';
}, 20, 3);
add_filter('acf/validate_value/key=field_sv_notify_token', function ($valid, $value) {
    if ($valid === true && $value !== '' && (!is_string($value) || !preg_match('/^[0-9]+:[A-Za-z0-9_-]{20,}$/D', trim($value)))) {
        return __('Перевірте формат токена Telegram-бота.', 'project-theme');
    }
    return $valid;
}, 10, 2);

add_filter('acf/validate_value/key=field_sv_notify_chat', function ($valid, $value) {
    if ($valid === true && $value !== '' && (!is_string($value) || !preg_match('/^-?[0-9]+$|^@[A-Za-z0-9_]+$/D', trim($value)))) {
        return __('Вкажіть Chat ID групи, наприклад -100…, або її публічне @ім’я.', 'project-theme');
    }
    return $valid;
}, 10, 2);

function project_theme_notification_money(int $cents, string $currency): string {
    $currency = html_entity_decode($currency, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if (in_array(trim($currency), array('₴', 'UAH', 'грн.', 'грн'), true)) { $currency = __('грн', 'project-theme'); }
    return number_format($cents / 100, 2, ',', ' ') . ' ' . $currency;
}

function project_theme_order_notification_link(int $id): string {
    return add_query_arg(
        array('action' => 'sv_open_order', 'order_id' => $id),
        admin_url('admin-post.php')
    );
}

function project_theme_open_order_from_notification(): void {
    $id = isset($_GET['order_id']) ? absint($_GET['order_id']) : 0;
    $return_url = project_theme_order_notification_link($id);

    if (!is_user_logged_in()) {
        wp_safe_redirect(wp_login_url($return_url));
        exit;
    }

    if (!$id || get_post_type($id) !== 'sv_order' || !current_user_can('edit_post', $id)) {
        wp_safe_redirect(admin_url('edit.php?post_type=sv_order'));
        exit;
    }

    wp_safe_redirect(admin_url('post.php?post=' . $id . '&action=edit'));
    exit;
}
add_action('admin_post_sv_open_order', 'project_theme_open_order_from_notification');
add_action('admin_post_nopriv_sv_open_order', 'project_theme_open_order_from_notification');

function project_theme_notification_text(int $id, array $data): string {
    $customer = $data['customer'];
    $quote = $data['quote'];
    $text = (!empty($data['test']) ? __('ТЕСТ TELEGRAM — НЕ ВИКОНУВАТИ. Заявка #', 'project-theme') : __('Нова заявка #', 'project-theme')) . $id . "\n" . __('Разом:', 'project-theme') . ' ' . project_theme_notification_money($quote['total_cents'], $quote['currency']);
    foreach (array('name' => __('Ім’я', 'project-theme'), 'phone' => __('Телефон', 'project-theme'), 'address' => __('Адреса', 'project-theme'), 'date' => __('Дата доставки', 'project-theme')) as $key => $label) {
        $text .= "\n" . $label . ': ' . $customer[$key];
    }
    $text .= "\n" . __('Мова:', 'project-theme') . ' ' . $data['language'] . "\n\n" . __('Товари:', 'project-theme');
    foreach ($quote['items'] as $item) {
        $text .= "\n" . $item['title'] . ' x ' . $item['amount'] . ' — ' . project_theme_notification_money($item['unit_cents'], $item['currency']) . ' / ' . __('од.;', 'project-theme') . ' ' . project_theme_notification_money($item['subtotal_cents'], $item['currency']);
    }
    if ($customer['message'] !== '') { $text .= "\n\n" . __('Повідомлення:', 'project-theme') . ' ' . $customer['message']; }
    return $text;
}

function project_theme_send_order_channel(string $channel, int $id, array $data): array {
    $locale = ($data['language'] ?? '') === 'ru' ? 'ru_RU' : 'uk';
    $switched = determine_locale() !== $locale && switch_to_locale($locale);
    try {
        $text = project_theme_notification_text($id, $data);
        $link = project_theme_order_notification_link($id);
    if ($channel === 'email') {
        $email = get_option('sv_notify_email', '');
        if (!is_email($email)) { return array('status' => 'failed', 'detail' => __('Не вказана коректна пошта одержувача.', 'project-theme')); }
        $sent = wp_mail($email, __('Нова заявка #', 'project-theme') . $id, $text . "\n\n" . $link, array('Content-Type: text/plain; charset=UTF-8'));
        return $sent ? array('status' => 'accepted', 'detail' => __('Поштовий транспорт прийняв лист. Це не підтверджує доставку до скриньки.', 'project-theme'))
            : array('status' => 'failed', 'detail' => __('Поштовий транспорт відхилив лист. Перевірте SMTP.', 'project-theme'));
    }
    $token = get_option('sv_notify_token', '');
    $chat = get_option('sv_notify_chat', '');
    if (!preg_match('/^[0-9]+:[A-Za-z0-9_-]{20,}$/D', $token) || !preg_match('/^-?[0-9]+$|^@[A-Za-z0-9_]+$/D', $chat)) {
        return array('status' => 'failed', 'detail' => __('Заповніть токен бота й Chat ID у налаштуваннях.', 'project-theme'));
    }
    // Telegram URL buttons reject the single-label localhost hostname.
    if (wp_parse_url($link, PHP_URL_HOST) === 'localhost') {
        $link = str_replace('://localhost', '://127.0.0.1', $link);
    }
    // Conservative bound also fits Telegram's UTF-16 length limit for user-supplied emoji.
    $body = array('chat_id' => $chat, 'text' => mb_substr($text, 0, 1700) . (mb_strlen($text) > 1700 ? "\n…" : ''),
        'reply_markup' => array('inline_keyboard' => array(array(array('text' => __('Перейти до замовлення', 'project-theme'), 'url' => $link)))),
        'link_preview_options' => array('is_disabled' => true));
    $topic = (int) get_option('sv_notify_topic', 0);
    if ($topic > 0) { $body['message_thread_id'] = $topic; }
    $response = wp_remote_post('https://api.telegram.org/bot' . $token . '/sendMessage', array(
        'timeout' => 10, 'redirection' => 0, 'limit_response_size' => 16000,
        'headers' => array('Content-Type' => 'application/json'), 'body' => wp_json_encode($body),
    ));
    // Never persist raw HTTP errors or response bodies: they may contain the secret URL.
    if (is_wp_error($response)) { return array('status' => 'unknown', 'detail' => __('Немає підтвердження Telegram. Перевірте групу перед повтором.', 'project-theme')); }
    $code = wp_remote_retrieve_response_code($response);
    $result = json_decode(wp_remote_retrieve_body($response), true);
    if ($code === 200 && ($result['ok'] ?? false) === true) { return array('status' => 'sent', 'detail' => __('Telegram підтвердив надсилання.', 'project-theme')); }
    if ($code === 401) { return array('status' => 'failed', 'detail' => __('Telegram не прийняв токен бота (HTTP 401). Оновіть токен у налаштуваннях.', 'project-theme')); }
    if ($code === 403) { return array('status' => 'failed', 'detail' => __('Telegram заборонив надсилання (HTTP 403). Перевірте участь і права бота у групі.', 'project-theme')); }
    /* translators: %d: Telegram HTTP status code. */
    return array('status' => $code >= 400 && $code < 500 ? 'failed' : 'unknown', 'detail' => sprintf(__('Telegram: HTTP %d. Перевірте налаштування та дозвіл бота писати у групу.', 'project-theme'), (int) $code));
    } finally {
        if ($switched) { restore_previous_locale(); }
    }
}

function project_theme_queue_order_notifications(int $id): void {
    $state = array();
    $pending = false;
    foreach (array('email', 'telegram') as $channel) {
        $enabled = (bool) get_option('sv_notify_' . $channel . '_enabled', false);
        $state[$channel] = array('status' => $enabled ? 'pending' : 'disabled', 'attempts' => 0);
        $pending = $pending || $enabled;
    }
    if (!add_post_meta($id, '_sv_order_notifications', $state, true)) { return; }
    if ($pending) {
        wp_schedule_single_event(time(), 'sv_notify_order', array($id));
        add_action('shutdown', 'spawn_cron');
    }
}
add_action('project_theme_order_created', 'project_theme_queue_order_notifications');
add_action('sv_notify_order', 'project_theme_process_order_notifications');

function project_theme_process_order_notifications(int $id, ?string $only_channel = null): void {
    global $wpdb;
    if (get_post_type($id) !== 'sv_order' || get_post_status($id) !== 'private') { return; }
    $lock = 'sv_notify_' . substr(hash('sha256', $wpdb->prefix . $id), 0, 48);
    if ((int) $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 0)', $lock)) !== 1) { return; }
    try {
        $data = get_post_meta($id, '_sv_order_data', true);
        $state = get_post_meta($id, '_sv_order_notifications', true);
        if (!is_array($data) || !is_array($state)) { return; }
        foreach (array('email', 'telegram') as $channel) {
            if ($only_channel !== null && $only_channel !== $channel) { continue; }
            $current = $state[$channel] ?? array('status' => 'disabled', 'attempts' => 0);
            if (!in_array($current['status'], array('pending', 'failed'), true) || $current['attempts'] >= 3) { continue; }
            if (!get_option('sv_notify_' . $channel . '_enabled', false)) { $state[$channel]['status'] = 'disabled'; continue; }
            // A crash after delivery must not cause an automatic duplicate on the next worker run.
            $state[$channel] = array('status' => 'unknown', 'attempts' => $current['attempts'] + 1, 'detail' => __('Надсилання почалося; підтвердження ще немає.', 'project-theme'), 'time' => current_time('mysql'));
            update_post_meta($id, '_sv_order_notifications', $state);
            try { $result = project_theme_send_order_channel($channel, $id, $data); }
            catch (Throwable $error) { $result = array('status' => 'unknown', 'detail' => __('Внутрішня помилка доставки. Перевірте канал перед повтором.', 'project-theme')); }
            $state[$channel] = array_merge($state[$channel], $result);
            update_post_meta($id, '_sv_order_notifications', $state);
        }
        update_post_meta($id, '_sv_order_notifications', $state);
        foreach ($state as $channel) {
            if ($channel['status'] === 'failed' && $channel['attempts'] < 3 && !wp_next_scheduled('sv_notify_order', array($id))) {
                wp_schedule_single_event(time() + 300, 'sv_notify_order', array($id));
            }
        }
    } finally { $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock)); }
}

add_action('add_meta_boxes_sv_order', function (): void {
    add_meta_box('sv_order_notifications', __('Сповіщення', 'project-theme'), function (WP_Post $post): void {
        if (!current_user_can('manage_options')) { return; }
        $labels = array('pending' => __('У черзі', 'project-theme'), 'disabled' => __('Вимкнено', 'project-theme'), 'sent' => __('Надіслано', 'project-theme'), 'accepted' => __('Прийнято поштовим транспортом', 'project-theme'), 'failed' => __('Помилка', 'project-theme'), 'unknown' => __('Потребує перевірки', 'project-theme'));
        $state = get_post_meta($post->ID, '_sv_order_notifications', true);
        foreach (array('email' => 'Email', 'telegram' => 'Telegram') as $key => $name) {
            $channel = $state[$key] ?? array('status' => 'disabled');
            echo '<p><strong>' . esc_html($name) . ':</strong> ' . esc_html($labels[$channel['status']] ?? $channel['status']) . '<br>' . esc_html($channel['detail'] ?? '') . '</p>';
        }
        echo '<p>' . esc_html__('Для непідтвердженого надсилання спочатку перевірте скриньку або групу, щоб уникнути дубля.', 'project-theme') . '</p>';
        $url = wp_nonce_url(admin_url('admin-post.php?action=sv_retry_notifications&order_id=' . $post->ID), 'sv_retry_notifications_' . $post->ID, '_sv_notify_nonce');
        echo '<button type="submit" class="button" formmethod="post" formaction="' . esc_url($url) . '">' . esc_html__('Повторити ненадіслані', 'project-theme') . '</button>';
    }, 'sv_order', 'side');
});
add_action('admin_post_sv_retry_notifications', function (): void {
    global $wpdb;
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !current_user_can('manage_options')) { wp_die(esc_html__('Недостатньо прав.', 'project-theme'), '', array('response' => 403)); }
    $id = absint($_GET['order_id'] ?? 0);
    check_admin_referer('sv_retry_notifications_' . $id, '_sv_notify_nonce');
    if (get_post_type($id) !== 'sv_order' || get_post_status($id) !== 'private') { wp_die(esc_html__('Заявка недоступна.', 'project-theme')); }
    $lock = 'sv_notify_' . substr(hash('sha256', $wpdb->prefix . $id), 0, 48);
    if ((int) $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 3)', $lock)) !== 1) { wp_die(esc_html__('Сповіщення ще надсилається. Повторіть пізніше.', 'project-theme')); }
    try {
        $state = get_post_meta($id, '_sv_order_notifications', true);
        $state = is_array($state) ? $state : array();
        foreach (array('email', 'telegram') as $channel) {
            if (!in_array($state[$channel]['status'] ?? '', array('sent', 'accepted'), true)) {
                $state[$channel] = array('status' => 'pending', 'attempts' => 0);
            }
        }
        update_post_meta($id, '_sv_order_notifications', $state);
        wp_clear_scheduled_hook('sv_notify_order', array($id));
        wp_schedule_single_event(time(), 'sv_notify_order', array($id));
    } finally { $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock)); }
    add_action('shutdown', 'spawn_cron');
    wp_safe_redirect(admin_url('post.php?post=' . $id . '&action=edit'));
    exit;
});
add_action('before_delete_post', function (int $id): void {
    if (get_post_type($id) === 'sv_order') { wp_clear_scheduled_hook('sv_notify_order', array($id)); }
});
