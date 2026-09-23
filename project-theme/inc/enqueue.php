<?php
/**
 * Theme assets.
 *
 * @package ProjectTheme
 */

function project_theme_enqueue_assets(): void
{
    $theme_version = wp_get_theme()->get('Version');
    $theme_uri = get_template_directory_uri();

    wp_enqueue_style(
        'project-theme-tailwind',
        $theme_uri . '/assets/css/tailwind.css',
        array(),
        $theme_version
    );

    wp_enqueue_style(
        'project-theme-fonts',
        $theme_uri . '/assets/css/fonts.css',
        array('project-theme-tailwind'),
        $theme_version
    );

    wp_enqueue_style(
        'project-theme-fontawesome',
        $theme_uri . '/assets/css/fontawesome.min.css',
        array('project-theme-fonts'),
        $theme_version
    );

    wp_enqueue_style(
        'project-theme-swiper',
        $theme_uri . '/assets/css/swiper-bundle.min.css',
        array('project-theme-fontawesome'),
        $theme_version
    );

    wp_enqueue_style(
        'project-theme-style',
        $theme_uri . '/assets/css/style.css',
        array('project-theme-swiper'),
        $theme_version
    );

    wp_enqueue_script(
        'project-theme-swiper',
        $theme_uri . '/assets/js/swiper-bundle.min.js',
        array(),
        $theme_version,
        true
    );

    wp_enqueue_script(
        'project-theme-main',
        $theme_uri . '/assets/js/main.js',
        array('project-theme-swiper'),
        $theme_version,
        true
    );

    wp_add_inline_script(
        'project-theme-main',
        'window.svitvodyThemeUri = ' . wp_json_encode($theme_uri) . ';' .
        'window.svitvodyI18n = ' . wp_json_encode(array(
            'home' => __('Головна', 'project-theme'),
            'aboutWater' => __('Про воду', 'project-theme'),
            'forHome' => __('Для дому', 'project-theme'),
            'forOffice' => __('Для офісу', 'project-theme'),
            'pricing' => __('Вартість', 'project-theme'),
            'siteLanguage' => __('Мова сайту', 'project-theme'),
            'brandHome' => __('SvitVody, головна', 'project-theme'),
            'openContacts' => __('Відкрити контакти', 'project-theme'),
            'openMenu' => __('Відкрити меню', 'project-theme'),
            'contacts' => __('Контакти', 'project-theme'),
            'closeContacts' => __('Закрити контакти', 'project-theme'),
            'siteMenu' => __('Меню сайту', 'project-theme'),
            'closeMenu' => __('Закрити меню', 'project-theme'),
            'checkoutDivider' => __('Або оформіть замовлення нижче', 'project-theme'),
            'checkoutTitle' => __('Оформлення замовлення', 'project-theme'),
            'nameLabel' => __("Ваше ім'я*", 'project-theme'),
            'namePlaceholder' => __('Як ми можемо до Вас звертатися', 'project-theme'),
            'addressLabel' => __('Адреса доставки*', 'project-theme'),
            'addressPlaceholder' => __('Вулиця, будинок, квартира', 'project-theme'),
            'phoneLabel' => __('Номер телефону*', 'project-theme'),
            'dateLabel' => __('Дата доставки*', 'project-theme'),
            'messageLabel' => __('Ваше повідомлення', 'project-theme'),
            'messagePlaceholder' => __('Додаткова інформація до замовлення', 'project-theme'),
            'submit' => __('Замовити', 'project-theme'),
            'miniCart' => __('Мінікошик', 'project-theme'),
            'yourOrder' => __('Ваше замовлення', 'project-theme'),
            'addWater' => __('Додати воду', 'project-theme'),
            'relatedProducts' => __('Супутні товари', 'project-theme'),
            'add' => __('Додати', 'project-theme'),
            'added' => __('Додано', 'project-theme'),
            'total' => __('Разом', 'project-theme'),
            'freeDelivery' => __('Доставка безкоштовна при замовленні від 2-ох бутлів.', 'project-theme'),
            'openCart' => __('Відкрити кошик', 'project-theme'),
            'scrollTop' => __('Повернутися нагору', 'project-theme'),
            'bottleOne' => __('бутель', 'project-theme'),
            'bottleFew' => __('бутлі', 'project-theme'),
            'bottleMany' => __('бутлів', 'project-theme'),
            'inOrder' => __('у замовленні', 'project-theme'),
            'itemUnit' => __('шт.', 'project-theme'),
            'decrease' => __('Зменшити кількість', 'project-theme'),
            'quantity' => __('Кількість', 'project-theme'),
            'increase' => __('Збільшити кількість', 'project-theme'),
            'remove' => __('Прибрати товар', 'project-theme'),
            'emptyCart' => __("Додайте воду або товари, і вони з'являться тут.", 'project-theme'),
            'cartRequired' => __('Додайте товари до кошика.', 'project-theme'),
            'sending' => __('Надсилаємо заявку…', 'project-theme'),
            'refresh' => __('Оновіть сторінку перед надсиланням заявки.', 'project-theme'),
            'sendFailed' => __('Не вдалося надіслати заявку. Спробуйте ще раз.', 'project-theme'),
            /* translators: %s: saved order ID. */
            'success' => __('Дякуємо! Заявку №%s збережено. Наш менеджер зв’яжеться з Вами.', 'project-theme'),
            'successTitle' => __('Заявку прийнято!', 'project-theme'),
            'successAutoClose' => __('Вікно автоматично закриється через 8 секунд.', 'project-theme'),
            'uncertain' => __('Немає підтвердження від сервера. Дані збережені у формі. Повторіть надсилання.', 'project-theme'),
            'numberLocale' => str_starts_with(determine_locale(), 'ru') ? 'ru-RU' : 'uk-UA',
        ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';',
        'before'
    );
}
add_action('wp_enqueue_scripts', 'project_theme_enqueue_assets');
