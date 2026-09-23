<?php
/** One-time import definitions. Runtime uses the editable ACF database definitions. */
if (!defined('ABSPATH')) { exit; }

function project_theme_constructor_field(string $name, string $label, string $type = 'text', int $width = 100, array $settings = array()): array {
    $field = array('name' => $name, 'label' => $label, 'type' => $type, 'wrapper' => array('width' => (string) $width));
    if ('textarea' === $type) { $field += array('rows' => 3, 'new_lines' => 'br'); }
    if ('wysiwyg' === $type) { $field += array('tabs' => 'all', 'toolbar' => 'basic', 'media_upload' => 0, 'delay' => 1); }
    if ('image' === $type) { $field += array('return_format' => 'id', 'preview_size' => 'medium', 'library' => 'all'); }
    if ('number' === $type) { $field += array('min' => 0, 'step' => 'any'); }
    if ('repeater' === $type) { $field += array('layout' => 'block', 'button_label' => 'Додати елемент'); }
    if ('page_link' === $type) { $field += array('post_type' => array('page'), 'allow_null' => 1, 'allow_archives' => 0, 'multiple' => 0); }
    return array_replace($field, $settings);
}

function project_theme_constructor_message(string $name, string $label): array {
    return project_theme_constructor_field($name, $label, 'message', 100, array('message' => '', 'new_lines' => '', 'esc_html' => 1));
}

function project_theme_constructor_image_fields(string $name = 'image', string $label = 'Зображення'): array {
    return array(project_theme_constructor_field($name, $label, 'image', 50), project_theme_constructor_field($name . '_alt', 'Опис зображення (alt)', 'text', 50));
}

function project_theme_constructor_key_fields(array $fields, string $path): array {
    foreach ($fields as &$field) {
        $field['key'] = 'field_ct_' . $path . '_' . $field['name'];
        if (isset($field['sub_fields'])) {
            $field['sub_fields'] = project_theme_constructor_key_fields($field['sub_fields'], $path . '_' . $field['name']);
        }
        if ('message' === $field['type']) { $field['name'] = ''; }
    }
    return $fields;
}

function project_theme_constructor_group(): array {
    $f = 'project_theme_constructor_field';
    $m = 'project_theme_constructor_message';
    $images = 'project_theme_constructor_image_fields';
    $layouts = array();
    $define = function (string $name, string $label, array $fields) use (&$layouts, $f, $m): void {
        $layouts['layout_ct_' . $name] = array(
            'key' => 'layout_ct_' . $name, 'name' => 'template-' . $name, 'label' => $label, 'display' => 'block',
            'sub_fields' => project_theme_constructor_key_fields(array_merge(array(
                $m('settings_message', $label),
                $f('disable_block', 'Приховати секцію', 'true_false', 100, array('ui' => 1, 'default_value' => 0)),
            ), $fields), str_replace('-', '_', $name)),
        );
    };
    $define('banner-main', 'Головний банер', array_merge(array(
        $m('content_message', 'Тексти банера'), $f('eyebrow', 'Надзаголовок'),
        $f('title', 'Заголовок', 'textarea', 50), $f('title_mobile', 'Заголовок на мобільному', 'textarea', 50),
        $f('title_accent', 'Виділена частина заголовка', 'text', 50), $f('title_accent_mobile', 'Виділена частина на мобільному', 'text', 50),
        $f('description', 'Опис', 'wysiwyg', 75), $f('button_text', 'Текст кнопки', 'text', 25),
        $m('image_message', 'Зображення банера'),
    ), $images(), array(
        $m('price_message', 'Цінова примітка'), $f('price_label', 'Підпис ціни', 'text', 25), $f('price', 'Ціна', 'number', 25), $f('currency', 'Валюта', 'text', 15), $f('price_note', 'Умова ціни', 'textarea', 35),
    )));
    $define('banner-inner', 'Внутрішній банер', array_merge(array(
        $m('content_message', 'Тексти банера'), $f('eyebrow', 'Надзаголовок', 'text', 35), $f('title', 'Заголовок', 'textarea', 65),
        $f('description', 'Опис', 'wysiwyg', 75), $f('button_text', 'Текст кнопки', 'text', 25),
        $m('stats_message', 'Показники'), $f('stats', 'Показники', 'repeater', 100, array('sub_fields' => array($f('value', 'Значення', 'text', 30), $f('label', 'Підпис', 'text', 70)))),
        $m('image_message', 'Зображення банера'),
    ), $images(), array($m('background_message', 'Фонове зображення')), $images('background', 'Фон')));
    $define('promotions', 'Акції та пропозиції', array(
        $m('promo_message', 'Акції'), $f('title', 'Заголовок'), $f('description', 'Опис', 'wysiwyg', 50), $f('description_mobile', 'Опис на мобільному', 'wysiwyg', 50),
        $f('promotions', 'Акційні пропозиції', 'repeater', 100, array('sub_fields' => array_merge(array(
            $m('content_message', 'Умови акції'), $f('label', 'Короткий підпис', 'text', 20), $f('title', 'Заголовок', 'textarea', 50), $f('title_mobile', 'Заголовок на мобільному', 'text', 30),
            $f('price', 'Ціна', 'number', 20), $f('currency', 'Валюта', 'text', 15), $f('unit', 'Одиниця', 'text', 20), $f('note', 'Умова', 'wysiwyg', 45),
            $m('image_message', 'Зображення акції'),
        ), $images()))),
        $m('offers_message', 'Пропозиції для сторінок'), $f('offers_heading', 'Заголовок'), $f('offers_intro', 'Опис', 'wysiwyg'),
        $f('offers', 'Пропозиції', 'repeater', 100, array('sub_fields' => array_merge(array(
            $m('content_message', 'Контент пропозиції'), $f('label', 'Короткий підпис', 'text', 25), $f('title', 'Заголовок', 'textarea', 75), $f('description', 'Опис', 'wysiwyg'),
            $m('link_message', 'Перехід на сторінку'), $f('page', 'Сторінка', 'page_link', 70), $f('button_text', 'Текст кнопки', 'text', 30),
            $m('image_message', 'Зображення пропозиції'),
        ), $images()))),
    ));
    foreach (array('water-quality' => 'Наша вода', 'home-features' => 'Переваги води для дому', 'office-usecases' => 'Вода для організацій', 'work-steps' => 'Як ми працюємо') as $name => $label) {
        $fields = array($m('content_message', 'Зміст секції'), $f('title', 'Заголовок', 'textarea'),
            $m('items_message', 'Елементи списку'), $f('items', 'Елементи', 'repeater', 100, array('sub_fields' => array(
                $f('icon', 'Іконка', 'select', 25, array('choices' => project_theme_constructor_icons(), 'ui' => 1, 'allow_null' => 1)),
                $f('title', 'Заголовок', 'textarea', 75), $f('description', 'Опис', 'wysiwyg'),
            ))));
        if ('office-usecases' === $name) { $fields = array_merge($fields, array($m('background_message', 'Фон')), $images('background', 'Фонове зображення')); }
        $define($name, $label, $fields);
    }
    $measure = array($f('label', 'Назва', 'text', 40), $f('value', 'Значення', 'text', 30), $f('unit', 'Одиниця виміру', 'text', 30));
    $define('water-composition', 'Склад води', array_merge(array(
        $m('content_message', 'Заголовок'), $f('title', 'Заголовок'),
        $m('primary_message', 'Основні показники'), $f('primary', 'Показники', 'repeater', 100, array('sub_fields' => $measure)),
        $m('minerals_message', 'Мінерали'), $f('minerals', 'Мінеральний склад', 'repeater', 100, array('sub_fields' => $measure)),
        $m('description_message', 'Пояснення'), $f('description', 'Опис', 'wysiwyg'), $f('closing', 'Завершальний заголовок', 'textarea'),
        $m('background_message', 'Фон'),
    ), $images('background', 'Фонове зображення')));
    $define('water-pricing', 'Вартість води', array(
        $m('prices_message', 'Ціни на воду'), $f('title', 'Заголовок'),
        $f('prices', 'Варіанти вартості', 'repeater', 100, array('sub_fields' => array(
            $f('label', 'Позначка пропозиції', 'text', 30), $f('title', 'Заголовок', 'text', 70),
            $f('price', 'Ціна за бутель', 'number', 25), $f('min_bottles', 'Мінімальна кількість бутлів', 'number', 25, array('min' => 1, 'step' => 1)), $f('currency', 'Валюта', 'text', 15), $f('button_text', 'Текст кнопки', 'text', 35), $f('unit', 'Підпис одиниці', 'wysiwyg'),
        ))),
        $m('products_message', 'Додаткові товари та послуги'), $f('products_title', 'Заголовок', 'textarea', 50), $f('products_title_mobile', 'Заголовок на мобільному', 'text', 50),
    ));
    return array(
        'key' => 'group_project_theme_constructor', 'title' => 'Конструктор сторінки',
        'fields' => array(array('key' => 'field_project_theme_constructor', 'name' => 'constructor', 'label' => 'Секції сторінки', 'type' => 'flexible_content', 'layouts' => $layouts, 'button_label' => 'Додати секцію')),
        'location' => array(array(array('param' => 'page_template', 'operator' => '==', 'value' => 'page-constructor.php'))),
        'hide_on_screen' => array('the_content'), 'active' => true, 'style' => 'default', 'position' => 'normal',
    );
}
