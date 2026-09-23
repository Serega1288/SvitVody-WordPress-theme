<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$group = acf_get_field_group('group_project_theme_header_settings');
if (!$group) { WP_CLI::error('Missing theme options group.'); }
$fields = array(
    array('tab', '', 'Сповіщення заявок', 'tab', 100, array('placement' => 'top')),
    array('intro', '', 'Доставка сповіщень', 'message', 100, array('message' => 'Спільні налаштування для всіх мов. Заявка спочатку зберігається в адмінці, потім надсилаються сповіщення. Доступ лише адміністратору.')),
    array('email_message', '', 'Email одержувача заявок', 'message', 100, array('message' => 'Надсилання через поштовий транспорт WordPress. Для гарантованого налаштування вихідної пошти використайте SMTP.')),
    array('email_enabled', 'sv_notify_email_enabled', 'Надсилати на пошту', 'true_false', 25, array('ui' => 1, 'default_value' => 0)),
    array('email', 'sv_notify_email', 'Пошта для нових заявок', 'email', 75, array()),
    array('telegram_message', '', 'Telegram-група', 'message', 100, array('message' => 'Додайте бота до групи та дозвольте надсилання повідомлень. Вкажіть токен від BotFather та Chat ID групи (часто починається з -100). Для групи з темами можна вказати ID теми.')),
    array('telegram_enabled', 'sv_notify_telegram_enabled', 'Надсилати в Telegram', 'true_false', 25, array('ui' => 1, 'default_value' => 0)),
    array('token', 'sv_notify_token', 'Токен Telegram-бота', 'password', 75, array('instructions' => 'Після збереження токен не показується. Щоб замінити, введіть новий; порожнє поле залишає збережений токен.')),
    array('chat', 'sv_notify_chat', 'Chat ID групи', 'text', 50, array()),
    array('topic', 'sv_notify_topic', 'ID теми (необов’язково)', 'number', 25, array('min' => 1, 'step' => 1)),
    array('clear_token', 'sv_notify_clear_token', 'Видалити збережений токен', 'true_false', 25, array('ui' => 1, 'default_value' => 0)),
);
// SMTP is delegated to the user's mail plugin; remove only our withdrawn settings.
foreach (array('smtp_message', 'smtp_enabled', 'smtp_host', 'smtp_port', 'smtp_security', 'smtp_user', 'smtp_password', 'sender_email', 'sender_name', 'clear_smtp_password') as $name) {
    $field = acf_get_field('field_sv_notify_' . $name);
    if ($field) { acf_delete_field($field['ID']); }
    delete_option('sv_notify_' . $name);
}
foreach ($fields as $index => [$key, $name, $label, $type, $width, $settings]) {
    $key = 'field_sv_notify_' . $key;
    $existing = acf_get_field($key);
    if ($existing) {
        if ((int) $existing['menu_order'] !== 1000 + $index) { $existing['menu_order'] = 1000 + $index; acf_update_field($existing); }
        continue;
    }
    acf_update_field(acf_get_valid_field(array_merge(array('key' => $key, 'name' => $name, 'label' => $label, 'type' => $type,
        'parent' => $group['ID'], 'menu_order' => 1000 + $index, 'wrapper' => array('width' => (string) $width)), $settings)));
}
WP_CLI::success('Notification settings added to theme options; existing values preserved.');
