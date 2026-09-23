<?php
/** Private order inbox and server-authoritative checkout. */
add_action('init', function (): void {
    register_post_type('sv_order', array(
        'labels' => array('name' => __('Заявки', 'project-theme'), 'singular_name' => __('Заявка', 'project-theme'), 'menu_name' => __('Заявки', 'project-theme'), 'edit_item' => __('Переглянути заявку', 'project-theme')),
        'public' => false, 'publicly_queryable' => false, 'show_ui' => true, 'show_in_menu' => true,
        'show_in_rest' => false, 'show_in_nav_menus' => false, 'exclude_from_search' => true,
        'has_archive' => false, 'rewrite' => false, 'query_var' => false, 'supports' => array('title'), 'menu_icon' => 'dashicons-clipboard',
        'map_meta_cap' => false, 'capabilities' => array(
            'edit_post' => 'manage_options', 'read_post' => 'manage_options', 'delete_post' => 'manage_options',
            'edit_posts' => 'manage_options', 'edit_others_posts' => 'manage_options', 'publish_posts' => 'manage_options',
            'read_private_posts' => 'manage_options', 'delete_posts' => 'manage_options', 'delete_private_posts' => 'manage_options',
            'delete_published_posts' => 'manage_options', 'delete_others_posts' => 'manage_options',
            'edit_private_posts' => 'manage_options', 'edit_published_posts' => 'manage_options', 'create_posts' => 'do_not_allow',
        ),
    ));
});

add_filter('wp_insert_post_data', function (array $data): array {
    if ($data['post_type'] === 'sv_order' && !in_array($data['post_status'], array('trash', 'auto-draft'), true)) {
        $data['post_status'] = 'private';
    }
    return $data;
});

add_action('wp_enqueue_scripts', function (): void {
    wp_add_inline_script('project-theme-main', 'window.svitvodyCheckout = ' . wp_json_encode(array(
        'url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('sv_submit_order'),
        'language' => function_exists('pll_current_language') ? (pll_current_language() ?: pll_default_language()) : 'uk',
    ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';', 'before');
}, 20);

function project_theme_order_error(string $message, int $status = 422): WP_Error {
    return new WP_Error('order_error', $message, array('status' => $status));
}

function project_theme_resolve_delivery_date(string $value): ?DateTimeImmutable {
    $timezone = wp_timezone();
    $today = new DateTimeImmutable('today', $timezone);

    if (preg_match('/^(\d{2})\.(\d{2})$/D', $value)) {
        $date = DateTimeImmutable::createFromFormat('!d.m.Y', $value . '.' . $today->format('Y'), $timezone);
        if (!$date || $date->format('d.m') !== $value) { return null; }
        return $date < $today ? $date->modify('+1 year') : $date;
    }

    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value, $timezone);
    return $date && $date->format('Y-m-d') === $value && $date >= $today ? $date : null;
}

function project_theme_order_quote(array $items, string $language) {
    if (!$items || count($items) > 50) { return project_theme_order_error(__('Додайте товари до кошика.', 'project-theme')); }
    $catalogue = array_column(project_theme_get_catalogue($language), null, 'cart_key');
    $lines = array();
    $seen = array();
    $total = 0;
    $currency = null;
    foreach ($items as $item) {
        if (!is_array($item) || !is_string($item['id'] ?? null) || !isset($catalogue[$item['id']])) {
            return project_theme_order_error(__('Товар недоступний. Оновіть сторінку та перевірте кошик.', 'project-theme'));
        }
        $product = $catalogue[$item['id']];
        $amount = $item['amount'] ?? null;
        if (!is_int($amount) || $amount < 1 || $amount > 99 || isset($seen[$item['id']])) {
            return project_theme_order_error(__('Некоректна кількість товару.', 'project-theme'));
        }
        $seen[$item['id']] = true;
        $tier_min = null;
        $price = $product['price'];
        if ($product['type'] === 'water') {
            $price = null;
            foreach ($product['tiers'] as $tier) {
                if ($amount >= $tier['min_bottles']) { $price = $tier['price']; $tier_min = $tier['min_bottles']; }
            }
        }
        if ($price === null || $price < 0 || !$product['currency']) {
            return project_theme_order_error(__('Перевірте мінімальну кількість і доступність ціни товару.', 'project-theme'));
        }
        if ($currency !== null && $currency !== $product['currency']) { return project_theme_order_error(__('Товари повинні мати одну валюту.', 'project-theme')); }
        $currency = $product['currency'];
        $unit_cents = (int) round($price * 100);
        $subtotal = $unit_cents * $amount;
        $total += $subtotal;
        $lines[] = array('product_id' => $product['id'], 'cart_key' => $item['id'], 'title' => $product['title'],
            'type' => $product['type'], 'amount' => $amount, 'tier_min' => $tier_min, 'unit_cents' => $unit_cents,
            'subtotal_cents' => $subtotal, 'currency' => $currency);
    }
    return array('items' => $lines, 'total_cents' => $total, 'currency' => $currency);
}

function project_theme_create_order(array $payload) {
    global $wpdb;
    $language = $payload['language'] ?? '';
    $languages = function_exists('pll_languages_list') ? pll_languages_list() : array('uk');
    if (!is_string($language) || !in_array($language, $languages, true)) { return project_theme_order_error(__('Невідома мова замовлення.', 'project-theme')); }
    $request_id = $payload['request_id'] ?? '';
    if (!is_string($request_id) || !preg_match('/^[a-f0-9-]{36}$/D', $request_id)) { return project_theme_order_error(__('Невірний ідентифікатор запиту.', 'project-theme')); }
    $customer = array();
    foreach (array('name' => 100, 'address' => 300, 'phone' => 40, 'date' => 10, 'message' => 2000) as $field => $limit) {
        $value = $payload[$field] ?? '';
        if (!is_string($value) || mb_strlen($value) > $limit || ($field !== 'message' && trim($value) === '')) {
            return project_theme_order_error(__('Перевірте заповнення контактів та доставки.', 'project-theme'));
        }
        $customer[$field] = $field === 'message' ? sanitize_textarea_field($value) : sanitize_text_field($value);
        if ($field !== 'message' && $customer[$field] === '') { return project_theme_order_error(__('Заповніть обов’язкові поля.', 'project-theme')); }
    }
    $digits = preg_replace('/\D/', '', $customer['phone']);
    if (strlen($digits) < 10 || strlen($digits) > 15) { return project_theme_order_error(__('Вкажіть коректний номер телефону.', 'project-theme')); }
    $date = project_theme_resolve_delivery_date($customer['date']);
    if (!$date) {
        return project_theme_order_error(__('Перевірте дату доставки.', 'project-theme'));
    }
    $customer['date'] = $date->format('Y-m-d');
    if (!is_array($payload['items'] ?? null)) { return project_theme_order_error(__('Некоректний кошик.', 'project-theme')); }
    $hash = hash('sha256', wp_json_encode(array($customer, $language, $payload['items'], $payload['expected_total_cents'] ?? null)));
    // Serialize retries with the same request key; a dropped HTTP response must not duplicate an order.
    $lock = 'sv_order_' . substr(hash('sha256', $wpdb->prefix . $request_id), 0, 48);
    if ((int) $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 3)', $lock)) !== 1) { return project_theme_order_error(__('Заявка ще обробляється. Спробуйте повторити надсилання.', 'project-theme'), 409); }
    try {
        $existing = get_posts(array('post_type' => 'sv_order', 'post_status' => array('private', 'trash'), 'numberposts' => 1,
            'meta_key' => '_sv_order_request', 'meta_value' => $request_id, 'lang' => ''));
        if ($existing) {
            if (get_post_meta($existing[0]->ID, '_sv_order_hash', true) !== $hash) { return project_theme_order_error(__('Цей запит уже використано для іншого замовлення.', 'project-theme'), 409); }
            return array('order_id' => $existing[0]->ID);
        }
        $quote = project_theme_order_quote($payload['items'], $language);
        if (is_wp_error($quote)) { return $quote; }
        if (!is_int($payload['expected_total_cents'] ?? null) || $payload['expected_total_cents'] !== $quote['total_cents']) {
            return project_theme_order_error(__('Ціни змінилися. Оновіть сторінку та перевірте суму замовлення.', 'project-theme'), 409);
        }
        $id = wp_insert_post(array('post_type' => 'sv_order', 'post_status' => 'private',
            'post_title' => __('Заявка', 'project-theme') . ' ' . current_time('d.m.Y H:i:s'),
            'meta_input' => array('_sv_order_request' => $request_id, '_sv_order_hash' => $hash,
                '_sv_order_data' => array('customer' => $customer, 'language' => $language, 'quote' => $quote),
                '_sv_order_status' => 'new')), true);
        if (is_wp_error($id)) { return project_theme_order_error(__('Не вдалося зберегти заявку. Спробуйте ще раз.', 'project-theme'), 500); }
        if (!get_post_meta($id, '_sv_order_data', true) || get_post_meta($id, '_sv_order_request', true) !== $request_id) {
            wp_delete_post($id, true);
            return project_theme_order_error(__('Не вдалося зберегти дані заявки.', 'project-theme'), 500);
        }
        try { do_action('project_theme_order_created', $id); }
        catch (Throwable $error) {
            update_post_meta($id, '_sv_order_notifications', array(
                'email' => array('status' => 'failed', 'attempts' => 0, 'detail' => __('Не вдалося поставити сповіщення в чергу.', 'project-theme')),
                'telegram' => array('status' => 'failed', 'attempts' => 0, 'detail' => __('Не вдалося поставити сповіщення в чергу.', 'project-theme')),
            ));
        }
        return array('order_id' => $id);
    } finally {
        $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock));
    }
}

function project_theme_submit_order(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !check_ajax_referer('sv_submit_order', 'nonce', false)) {
        wp_send_json_error(array('message' => __('Оновіть сторінку перед надсиланням заявки.', 'project-theme')), 403);
    }
    $raw = isset($_POST['payload']) && is_string($_POST['payload']) ? wp_unslash($_POST['payload']) : '';
    if (strlen($raw) > 20000) { wp_send_json_error(array('message' => __('Завеликий запит.', 'project-theme')), 413); }
    $payload = json_decode($raw, true);
    if (!is_array($payload) || !empty($payload['website'])) { wp_send_json_error(array('message' => __('Некоректний запит.', 'project-theme')), 422); }
    if (($payload['language'] ?? '') === 'ru') { switch_to_locale('ru_RU'); }
    elseif (($payload['language'] ?? '') === 'uk') { switch_to_locale('uk'); }
    $rate_key = 'sv_order_rate_' . hash_hmac('sha256', $_SERVER['REMOTE_ADDR'] ?? '', wp_salt('nonce'));
    $attempts = (int) get_transient($rate_key);
    if ($attempts >= 30) { wp_send_json_error(array('message' => __('Забагато спроб. Спробуйте пізніше.', 'project-theme')), 429); }
    set_transient($rate_key, $attempts + 1, 10 * MINUTE_IN_SECONDS);
    $result = project_theme_create_order($payload);
    if (is_wp_error($result)) { wp_send_json_error(array('message' => $result->get_error_message()), $result->get_error_data()['status'] ?? 500); }
    wp_send_json_success($result);
}
add_action('wp_ajax_sv_submit_order', 'project_theme_submit_order');
add_action('wp_ajax_nopriv_sv_submit_order', 'project_theme_submit_order');

function project_theme_order_statuses(): array {
    return array('new' => __('Нова', 'project-theme'), 'processing' => __('В обробці', 'project-theme'), 'completed' => __('Виконана', 'project-theme'), 'cancelled' => __('Скасована', 'project-theme'));
}

add_filter('manage_sv_order_posts_columns', function (array $columns): array {
    return array('cb' => $columns['cb'], 'title' => __('Заявка', 'project-theme'), 'order_customer' => __('Клієнт', 'project-theme'), 'order_total' => __('Сума', 'project-theme'), 'order_delivery' => __('Доставка', 'project-theme'), 'order_status' => __('Стан', 'project-theme'), 'date' => __('Створено', 'project-theme'));
});
add_action('manage_sv_order_posts_custom_column', function (string $column, int $id): void {
    $data = get_post_meta($id, '_sv_order_data', true);
    if (!is_array($data)) { return; }
    if ($column === 'order_customer') { echo esc_html($data['customer']['name']) . '<br>' . esc_html($data['customer']['phone']); }
    if ($column === 'order_total') { echo esc_html(number_format_i18n($data['quote']['total_cents'] / 100, 2) . ' ' . $data['quote']['currency']); }
    if ($column === 'order_delivery') { echo esc_html($data['customer']['date']) . '<br>' . esc_html($data['customer']['address']); }
    if ($column === 'order_status') { echo esc_html(project_theme_order_statuses()[get_post_meta($id, '_sv_order_status', true)] ?? __('Нова', 'project-theme')); }
}, 10, 2);

add_action('add_meta_boxes_sv_order', function (): void {
    add_meta_box('sv_order_details', __('Дані заявки та склад замовлення', 'project-theme'), function (WP_Post $post): void {
        if (!current_user_can('manage_options')) { return; }
        $data = get_post_meta($post->ID, '_sv_order_data', true);
        if (!is_array($data)) { return; }
        echo '<h3>' . esc_html__('Заявка', 'project-theme') . ' #' . esc_html($post->ID) . '</h3><table class="widefat striped"><tbody>';
        $labels = array('name' => __('Ім’я', 'project-theme'), 'phone' => __('Телефон', 'project-theme'), 'address' => __('Адреса', 'project-theme'), 'date' => __('Дата доставки', 'project-theme'), 'message' => __('Повідомлення', 'project-theme'));
        foreach ($labels as $key => $label) {
            $value = $data['customer'][$key];
            echo '<tr><th>' . esc_html($label) . '</th><td>' . nl2br(esc_html($value)) . '</td></tr>';
        }
        echo '<tr><th>' . esc_html__('Мова', 'project-theme') . '</th><td>' . esc_html($data['language']) . '</td></tr></tbody></table>';
        echo '<h3>' . esc_html__('Товари', 'project-theme') . '</h3><div style="overflow-x:auto"><table class="widefat striped"><thead><tr><th>' . esc_html__('Товар', 'project-theme') . '</th><th>' . esc_html__('Кількість', 'project-theme') . '</th><th>' . esc_html__('Рівень від', 'project-theme') . '</th><th>' . esc_html__('Ціна за одиницю', 'project-theme') . '</th><th>' . esc_html__('Сума', 'project-theme') . '</th></tr></thead><tbody>';
        foreach ($data['quote']['items'] as $item) {
            echo '<tr><td>' . esc_html($item['title']) . ' (#' . esc_html($item['product_id']) . ')</td><td>' . esc_html($item['amount']) . '</td><td>' . esc_html($item['tier_min'] ?? '-') . '</td><td>' . esc_html(number_format_i18n($item['unit_cents'] / 100, 2) . ' ' . $item['currency']) . '</td><td>' . esc_html(number_format_i18n($item['subtotal_cents'] / 100, 2) . ' ' . $item['currency']) . '</td></tr>';
        }
        echo '</tbody></table></div><h3>' . esc_html__('Разом:', 'project-theme') . ' ' . esc_html(number_format_i18n($data['quote']['total_cents'] / 100, 2) . ' ' . $data['quote']['currency']) . '</h3>';
        wp_nonce_field('sv_order_status_' . $post->ID, 'sv_order_status_nonce');
        echo '<label for="sv_order_status">' . esc_html__('Стан заявки', 'project-theme') . ' </label><select name="sv_order_status" id="sv_order_status">';
        foreach (project_theme_order_statuses() as $value => $label) {
            echo '<option value="' . esc_attr($value) . '" ' . selected(get_post_meta($post->ID, '_sv_order_status', true), $value, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
    }, 'sv_order', 'normal', 'high');
});
add_action('save_post_sv_order', function (int $id): void {
    if (!current_user_can('manage_options') || wp_is_post_autosave($id) || wp_is_post_revision($id)
        || !isset($_POST['sv_order_status_nonce']) || !is_string($_POST['sv_order_status_nonce'])
        || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['sv_order_status_nonce'])), 'sv_order_status_' . $id)) { return; }
    $status = isset($_POST['sv_order_status']) && is_string($_POST['sv_order_status']) ? sanitize_key($_POST['sv_order_status']) : '';
    if (isset(project_theme_order_statuses()[$status])) { update_post_meta($id, '_sv_order_status', $status); }
});
