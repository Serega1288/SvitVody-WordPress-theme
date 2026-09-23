<?php
/** Constructor rendering helpers. */

function project_theme_section_text(string $name): string {
    return esc_html((string) get_sub_field($name, false));
}

function project_theme_section_heading(string $name = 'title'): string {
    return nl2br(project_theme_section_text($name));
}

function project_theme_section_rich(string $name): string {
    return wp_kses_post((string) get_sub_field($name));
}

function project_theme_section_image(string $name, string $class = ''): string {
    $image = get_sub_field($name);
    $id = is_array($image) ? (int) ($image['ID'] ?? 0) : (int) $image;
    if (!$id) {
        return '';
    }
    $alt = get_sub_field($name . '_alt');
    $attributes = array('class' => $class);
    if (is_string($alt)) {
        $attributes['alt'] = $alt;
    }
    return wp_get_attachment_image($id, 'full', false, $attributes);
}

function project_theme_section_background(string $name = 'background'): string {
    $image = get_sub_field($name);
    $id = is_array($image) ? (int) ($image['ID'] ?? 0) : (int) $image;
    $url = $id ? wp_get_attachment_image_url($id, 'full') : '';
    return $url ? 'background-image:url(' . esc_url($url) . ')' : 'background-image:none';
}

function project_theme_constructor_icons(): array {
    return array(
        'file-contract' => 'Документ', 'gem' => 'Мінерали', 'water' => 'Вода',
        'flask' => 'Колба', 'truck-fast' => 'Доставка', 'house-laptop' => 'Дім та офіс',
        'certificate' => 'Сертифікат', 'vials' => 'Очищення', 'faucet-drip' => 'Кран',
        'truck-ramp-box' => 'Розвантаження', 'percent' => 'Відсоток', 'desktop' => 'Монітор',
        'bicycle' => 'Спорт', 'users-rectangle' => 'Конференція', 'mug-hot' => 'Кава',
        'utensils' => 'Ресторан', 'book-open' => 'Освіта', 'mobile-screen' => 'Телефон',
        'clipboard-check' => 'Підтвердження', 'hand-holding-dollar' => 'Оплата',
    );
}

function project_theme_section_icon(string $name = 'icon'): string {
    $key = (string) get_sub_field($name);
    return isset(project_theme_constructor_icons()[$key]) ? 'fa-solid fa-' . $key : '';
}

function project_theme_constructor_modal_id(): string {
    return function_exists('project_theme_get_option')
        ? (string) project_theme_get_option('order_popup_modal_id', 'orderModal')
        : 'orderModal';
}

function project_theme_constructor_anchor(string $layout, string $suffix): string {
    static $used = array();
    $anchors = array(
        'template-banner-main' => 'home', 'template-promotions' => 'promo',
        'template-water-pricing' => 'pricing', 'template-work-steps' => 'how-it-works',
    );
    $anchor = $anchors[$layout] ?? str_replace('template-', '', $layout);
    if ('template-banner-inner' === $layout) {
        $page_id = get_queried_object_id();
        if (function_exists('pll_get_post')) {
            $page_id = pll_get_post($page_id, pll_default_language()) ?: $page_id;
        }
        $slug = get_post_field('post_name', $page_id);
        if (in_array($slug, array('about-water', 'water-for-home', 'water-for-office'), true)) {
            $anchor = $slug;
        }
    }
    $result = isset($used[$anchor]) ? $anchor . '-' . sanitize_title($suffix) : $anchor;
    $used[$anchor] = true;
    return $result;
}

add_filter('body_class', function (array $classes): array {
    if (!is_page_template('page-constructor.php')) {
        return $classes;
    }
    $classes = array_values(array_diff($classes, array('page-home')));
    $id = get_queried_object_id();
    if (function_exists('pll_get_post')) {
        $id = pll_get_post($id, pll_default_language()) ?: $id;
    }
    $mapping = array('home' => 'page-home', 'about-water' => 'page-about-water', 'water-for-home' => 'page-water-home', 'water-for-office' => 'page-water-office');
    $classes[] = $mapping[get_post_field('post_name', $id)] ?? 'page-constructor';
    return $classes;
});

add_filter('use_block_editor_for_post', function ($use, $post) {
    return 'page-constructor.php' === get_page_template_slug($post) ? false : $use;
}, 10, 2);

add_action('wp_enqueue_scripts', function () {
    if (is_page_template('page-constructor.php')) {
        wp_enqueue_style('project-theme-constructor', get_template_directory_uri() . '/assets/css/constructor.css', array('project-theme-style'), (string) filemtime(get_template_directory() . '/assets/css/constructor.css'));
    }
}, 20);

add_action('admin_head-post.php', function () {
    $id = isset($_GET['post']) ? absint($_GET['post']) : 0;
    if ($id && 'page-constructor.php' === get_page_template_slug($id)) {
        echo '<style>#postdivrich{display:none}</style>';
    }
});
