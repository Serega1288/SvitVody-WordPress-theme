<?php
/**
 * ACF integration.
 *
 * @package ProjectTheme
 */

function project_theme_register_acf_options(): void
{
    if ( ! function_exists( 'acf_add_options_page' ) ) {
        return;
    }

    acf_add_options_page(
        array(
            'page_title' => 'Глобальні налаштування',
            'menu_title' => 'Налаштування теми',
            'menu_slug'  => 'theme-settings',
            'capability' => 'edit_posts',
            'redirect'   => false,
        )
    );
}
add_action( 'acf/init', 'project_theme_register_acf_options' );

function project_theme_get_acf_header_field_group(): array
{
    return array(
            'key'      => 'group_project_theme_header_settings',
            'title'    => 'Глобальні налаштування',
            'fields'   => array(
                array(
                    'key'       => 'field_project_theme_header_tab',
                    'label'     => 'Header',
                    'name'      => '',
                    'type'      => 'tab',
                    'placement' => 'top',
                ),
                array(
                    'key'     => 'field_project_theme_header_logo_message',
                    'label'   => 'Логотип header',
                    'name'    => '',
                    'type'    => 'message',
                    'message' => 'Поля логотипу у верхній частині сайту.',
                    'wrapper' => array( 'width' => '100' ),
                ),
                array(
                    'key'           => 'field_project_theme_header_logo',
                    'label'         => 'Логотип header',
                    'name'          => 'header_logo',
                    'type'          => 'image',
                    'return_format' => 'array',
                    'preview_size'  => 'medium',
                    'library'       => 'all',
                    'wrapper'       => array( 'width' => '50' ),
                ),
                array(
                    'key'           => 'field_project_theme_header_logo_alt',
                    'label'         => 'Alt логотипу',
                    'name'          => 'header_logo_alt',
                    'type'          => 'text',
                    'default_value' => "Світ Води - доставка здоров'я",
                    'wrapper'       => array( 'width' => '50' ),
                ),
                array(
                    'key'     => 'field_project_theme_header_contacts_message',
                    'label'   => 'Контакти header',
                    'name'    => '',
                    'type'    => 'message',
                    'message' => 'Поля кнопки контактів, блоку телефонів і контактного dropdown.',
                    'wrapper' => array( 'width' => '100' ),
                ),
                array(
                    'key'           => 'field_project_theme_header_contacts_button_text',
                    'label'         => 'Текст кнопки контактів',
                    'name'          => 'header_contacts_button_text',
                    'type'          => 'text',
                    'default_value' => 'Контакти',
                    'wrapper'       => array( 'width' => '20' ),
                ),
                array(
                    'key'           => 'field_project_theme_header_phone_group_title',
                    'label'         => 'Заголовок блоку телефонів',
                    'name'          => 'header_phone_group_title',
                    'type'          => 'text',
                    'default_value' => 'Наші телефони',
                    'wrapper'       => array( 'width' => '20' ),
                ),
                array(
                    'key'          => 'field_project_theme_header_phones',
                    'label'        => 'Телефони',
                    'name'         => 'header_phones',
                    'type'         => 'repeater',
                    'layout'       => 'row',
                    'button_label' => 'Додати телефон',
                    'wrapper'      => array( 'width' => '60' ),
                    'sub_fields'   => array(
                        array(
                            'key'     => 'field_project_theme_header_phone_label',
                            'label'   => 'Телефон',
                            'name'    => 'phone_label',
                            'type'    => 'text',
                            'wrapper' => array( 'width' => '50' ),
                        ),
                        array(
                            'key'     => 'field_project_theme_header_phone_url',
                            'label'   => 'Посилання телефону',
                            'name'    => 'phone_url',
                            'type'    => 'text',
                            'wrapper' => array( 'width' => '50' ),
                        ),
                    ),
                ),
                array(
                    'key'     => 'field_project_theme_header_schedule_message',
                    'label'   => 'Графік роботи header',
                    'name'    => '',
                    'type'    => 'message',
                    'message' => 'Поля графіка роботи у контактному блоці header.',
                    'wrapper' => array( 'width' => '100' ),
                ),
                array(
                    'key'           => 'field_project_theme_header_schedule_group_title',
                    'label'         => 'Заголовок графіка роботи',
                    'name'          => 'header_schedule_group_title',
                    'type'          => 'text',
                    'default_value' => 'Графік роботи',
                    'wrapper'       => array( 'width' => '25' ),
                ),
                array(
                    'key'          => 'field_project_theme_header_schedule_rows',
                    'label'        => 'Рядки графіка роботи',
                    'name'         => 'header_schedule_rows',
                    'type'         => 'repeater',
                    'layout'       => 'row',
                    'button_label' => 'Додати рядок',
                    'wrapper'      => array( 'width' => '75' ),
                    'sub_fields'   => array(
                        array(
                            'key'   => 'field_project_theme_header_schedule_text',
                            'label' => 'Текст рядка',
                            'name'  => 'schedule_text',
                            'type'  => 'text',
                        ),
                    ),
                ),
                array(
                    'key'     => 'field_project_theme_header_order_message',
                    'label'   => 'Кнопки header',
                    'name'    => '',
                    'type'    => 'message',
                    'message' => 'Поля кнопок замовлення у верхній частині сайту.',
                    'wrapper' => array( 'width' => '100' ),
                ),
                array(
                    'key'           => 'field_project_theme_header_order_button_text',
                    'label'         => 'Текст кнопки замовлення',
                    'name'          => 'header_order_button_text',
                    'type'          => 'text',
                    'default_value' => 'Замовити воду',
                    'wrapper'       => array( 'width' => '33' ),
                ),
                array(
                    'key'           => 'field_project_theme_header_mobile_order_main',
                    'label'         => 'Мобільна кнопка: основний текст',
                    'name'          => 'header_mobile_order_main',
                    'type'          => 'text',
                    'default_value' => 'Замовити',
                    'wrapper'       => array( 'width' => '33' ),
                ),
                array(
                    'key'           => 'field_project_theme_header_mobile_order_extra',
                    'label'         => 'Мобільна кнопка: додатковий текст',
                    'name'          => 'header_mobile_order_extra',
                    'type'          => 'text',
                    'default_value' => 'воду',
                    'wrapper'       => array( 'width' => '33' ),
                ),
                array(
                    'key'           => 'field_project_theme_header_language_switcher_enabled',
                    'label'         => 'Показувати перемикач мови',
                    'name'          => 'header_language_switcher_enabled',
                    'type'          => 'true_false',
                    'default_value' => 1,
                    'ui'            => 1,
                    'wrapper'       => array( 'width' => '34' ),
                ),
                array(
                    'key'       => 'field_project_theme_order_popup_tab',
                    'label'     => 'Попап заявки',
                    'name'      => '',
                    'type'      => 'tab',
                    'placement' => 'top',
                ),
                array(
                    'key'     => 'field_project_theme_order_popup_settings_message',
                    'label'   => 'Налаштування попапу',
                    'name'    => '',
                    'type'    => 'message',
                    'message' => 'Базові параметри відкриття попапу заявки.',
                    'wrapper' => array( 'width' => '100' ),
                ),
                array(
                    'key'           => 'field_project_theme_order_popup_modal_id',
                    'label'         => 'Modal ID попапу заявки',
                    'name'          => 'order_popup_modal_id',
                    'type'          => 'text',
                    'default_value' => 'orderModal',
                    'wrapper'       => array( 'width' => '33' ),
                ),
                array(
                    'key'           => 'field_project_theme_order_popup_close_label',
                    'label'         => 'Підпис кнопки закриття',
                    'name'          => 'order_popup_close_label',
                    'type'          => 'text',
                    'default_value' => 'Закрити форму',
                    'wrapper'       => array( 'width' => '33' ),
                ),
                array(
                    'key'           => 'field_project_theme_order_popup_contact_heading',
                    'label'         => 'Заголовок контактного блоку',
                    'name'          => 'order_popup_contact_heading',
                    'type'          => 'textarea',
                    'rows'          => 2,
                    'default_value' => "Ви можете зв'язатися з нами телефоном або у месенджерах.",
                    'wrapper'       => array( 'width' => '34' ),
                ),
                array(
                    'key'     => 'field_project_theme_order_popup_contacts_message',
                    'label'   => 'Контакти попапу',
                    'name'    => '',
                    'type'    => 'message',
                    'message' => 'Телефони та месенджери, які відображаються у верхній частині попапу заявки.',
                    'wrapper' => array( 'width' => '100' ),
                ),
                array(
                    'key'          => 'field_project_theme_order_popup_phones',
                    'label'        => 'Телефони попапу',
                    'name'         => 'order_popup_phones',
                    'type'         => 'repeater',
                    'layout'       => 'row',
                    'button_label' => 'Додати телефон',
                    'wrapper'      => array( 'width' => '50' ),
                    'sub_fields'   => array(
                        array(
                            'key'     => 'field_project_theme_order_popup_contact_phone_label',
                            'label'   => 'Телефон',
                            'name'    => 'phone_label',
                            'type'    => 'text',
                            'wrapper' => array( 'width' => '50' ),
                        ),
                        array(
                            'key'     => 'field_project_theme_order_popup_contact_phone_url',
                            'label'   => 'Посилання телефону',
                            'name'    => 'phone_url',
                            'type'    => 'text',
                            'wrapper' => array( 'width' => '50' ),
                        ),
                    ),
                ),
                array(
                    'key'          => 'field_project_theme_order_popup_messengers',
                    'label'        => 'Месенджери попапу',
                    'name'         => 'order_popup_messengers',
                    'type'         => 'repeater',
                    'layout'       => 'row',
                    'button_label' => 'Додати месенджер',
                    'wrapper'      => array( 'width' => '50' ),
                    'sub_fields'   => array(
                        array(
                            'key'           => 'field_project_theme_order_popup_messenger_icon',
                            'label'         => 'Іконка',
                            'name'          => 'messenger_icon',
                            'type'          => 'select',
                            'choices'       => array(
                                'telegram' => 'Telegram',
                                'viber'    => 'Viber',
                            ),
                            'allow_null'    => 0,
                            'ui'            => 1,
                            'wrapper'       => array( 'width' => '30' ),
                        ),
                        array(
                            'key'     => 'field_project_theme_order_popup_messenger_name',
                            'label'   => 'Назва',
                            'name'    => 'messenger_name',
                            'type'    => 'text',
                            'wrapper' => array( 'width' => '35' ),
                        ),
                        array(
                            'key'     => 'field_project_theme_order_popup_messenger_url',
                            'label'   => 'URL',
                            'name'    => 'messenger_url',
                            'type'    => 'url',
                            'wrapper' => array( 'width' => '35' ),
                        ),
                    ),
                ),
                array(
                    'key'       => 'field_project_theme_footer_tab',
                    'label'     => 'Footer',
                    'name'      => '',
                    'type'      => 'tab',
                    'placement' => 'top',
                ),
                array(
                    'key'     => 'field_project_theme_footer_brand_message',
                    'label'   => 'Бренд footer',
                    'name'    => '',
                    'type'    => 'message',
                    'message' => 'Поля логотипу та короткого опису у footer.',
                    'wrapper' => array( 'width' => '100' ),
                ),
                array(
                    'key'           => 'field_project_theme_footer_logo',
                    'label'         => 'Логотип footer',
                    'name'          => 'footer_logo',
                    'type'          => 'image',
                    'return_format' => 'array',
                    'preview_size'  => 'medium',
                    'library'       => 'all',
                    'wrapper'       => array( 'width' => '50' ),
                ),
                array(
                    'key'           => 'field_project_theme_footer_logo_alt',
                    'label'         => 'Alt логотипу footer',
                    'name'          => 'footer_logo_alt',
                    'type'          => 'text',
                    'default_value' => "Світ Води - доставка здоров'я",
                    'wrapper'       => array( 'width' => '50' ),
                ),
                array(
                    'key'           => 'field_project_theme_footer_description',
                    'label'         => 'Опис footer',
                    'name'          => 'footer_description',
                    'type'          => 'textarea',
                    'rows'          => 3,
                    'default_value' => "Чиста вода для вашого здоров'я з 2010 року. Свіжість природи у кожній краплі.",
                    'wrapper'       => array( 'width' => '50' ),
                ),
                array(
                    'key'     => 'field_project_theme_footer_social_message',
                    'label'   => 'Соціальні мережі footer',
                    'name'    => '',
                    'type'    => 'message',
                    'message' => 'Список соціальних мереж з іконкою, назвою та посиланням.',
                    'wrapper' => array( 'width' => '100' ),
                ),
                array(
                    'key'          => 'field_project_theme_footer_social_links',
                    'label'        => 'Соціальні мережі',
                    'name'         => 'footer_social_links',
                    'type'         => 'repeater',
                    'layout'       => 'row',
                    'button_label' => 'Додати соцмережу',
                    'wrapper'      => array( 'width' => '50' ),
                    'sub_fields'   => array(
                        array(
                            'key'           => 'field_project_theme_footer_social_icon',
                            'label'         => 'Іконка',
                            'name'          => 'social_icon',
                            'type'          => 'select',
                            'choices'       => array(
                                'facebook'  => 'Facebook',
                                'instagram' => 'Instagram',
                                'telegram'  => 'Telegram',
                                'viber'     => 'Viber',
                            ),
                            'default_value' => 'facebook',
                            'ui'            => 1,
                            'wrapper'       => array( 'width' => '33' ),
                        ),
                        array(
                            'key'     => 'field_project_theme_footer_social_name',
                            'label'   => 'Назва',
                            'name'    => 'social_name',
                            'type'    => 'text',
                            'wrapper' => array( 'width' => '33' ),
                        ),
                        array(
                            'key'     => 'field_project_theme_footer_social_url',
                            'label'   => 'Посилання',
                            'name'    => 'social_url',
                            'type'    => 'text',
                            'wrapper' => array( 'width' => '34' ),
                        ),
                    ),
                ),
                array(
                    'key'     => 'field_project_theme_footer_columns_message',
                    'label'   => 'Заголовки колонок footer',
                    'name'    => '',
                    'type'    => 'message',
                    'message' => 'Заголовки основних колонок footer.',
                    'wrapper' => array( 'width' => '100' ),
                ),
                array(
                    'key'           => 'field_project_theme_footer_menu_title',
                    'label'         => 'Заголовок меню footer',
                    'name'          => 'footer_menu_title',
                    'type'          => 'text',
                    'default_value' => 'Меню',
                    'wrapper'       => array( 'width' => '33' ),
                ),
                array(
                    'key'           => 'field_project_theme_footer_contacts_title',
                    'label'         => 'Заголовок контактів footer',
                    'name'          => 'footer_contacts_title',
                    'type'          => 'text',
                    'default_value' => 'Контакти',
                    'wrapper'       => array( 'width' => '33' ),
                ),
                array(
                    'key'           => 'field_project_theme_footer_messengers_title',
                    'label'         => 'Заголовок месенджерів footer',
                    'name'          => 'footer_messengers_title',
                    'type'          => 'text',
                    'default_value' => 'Меседжери',
                    'wrapper'       => array( 'width' => '34' ),
                ),
                array(
                    'key'     => 'field_project_theme_footer_contacts_message',
                    'label'   => 'Контакти footer',
                    'name'    => '',
                    'type'    => 'message',
                    'message' => 'Телефон, підпис і контактні дані у footer.',
                    'wrapper' => array( 'width' => '100' ),
                ),
                array(
                    'key'           => 'field_project_theme_footer_phone_label',
                    'label'         => 'Телефон footer',
                    'name'          => 'footer_phone_label',
                    'type'          => 'text',
                    'default_value' => '+380 (67) 123 45 67',
                    'wrapper'       => array( 'width' => '33' ),
                ),
                array(
                    'key'           => 'field_project_theme_footer_phone_url',
                    'label'         => 'Посилання телефону footer',
                    'name'          => 'footer_phone_url',
                    'type'          => 'text',
                    'default_value' => 'tel:+380671234567',
                    'wrapper'       => array( 'width' => '33' ),
                ),
                array(
                    'key'           => 'field_project_theme_footer_phone_caption',
                    'label'         => 'Підпис телефону footer',
                    'name'          => 'footer_phone_caption',
                    'type'          => 'text',
                    'default_value' => 'Цілодобовий прийом заявок',
                    'wrapper'       => array( 'width' => '34' ),
                ),
                array(
                    'key'     => 'field_project_theme_footer_schedule_message',
                    'label'   => 'Графік роботи footer',
                    'name'    => '',
                    'type'    => 'message',
                    'message' => 'Рядки графіка роботи у footer.',
                    'wrapper' => array( 'width' => '100' ),
                ),
                array(
                    'key'           => 'field_project_theme_footer_schedule_title',
                    'label'         => 'Заголовок графіка footer',
                    'name'          => 'footer_schedule_title',
                    'type'          => 'text',
                    'default_value' => 'Графік роботи:',
                    'wrapper'       => array( 'width' => '25' ),
                ),
                array(
                    'key'          => 'field_project_theme_footer_schedule_rows',
                    'label'        => 'Рядки графіка footer',
                    'name'         => 'footer_schedule_rows',
                    'type'         => 'repeater',
                    'layout'       => 'row',
                    'button_label' => 'Додати рядок',
                    'wrapper'      => array( 'width' => '75' ),
                    'sub_fields'   => array(
                        array(
                            'key'   => 'field_project_theme_footer_schedule_text',
                            'label' => 'Текст рядка',
                            'name'  => 'schedule_text',
                            'type'  => 'text',
                        ),
                    ),
                ),
                array(
                    'key'     => 'field_project_theme_footer_messengers_message',
                    'label'   => 'Месенджери footer',
                    'name'    => '',
                    'type'    => 'message',
                    'message' => 'Список месенджерів з іконкою, назвою та посиланням.',
                    'wrapper' => array( 'width' => '100' ),
                ),
                array(
                    'key'          => 'field_project_theme_footer_messenger_links',
                    'label'        => 'Месенджери footer',
                    'name'         => 'footer_messenger_links',
                    'type'         => 'repeater',
                    'layout'       => 'row',
                    'button_label' => 'Додати месенджер',
                    'sub_fields'   => array(
                        array(
                            'key'           => 'field_project_theme_footer_messenger_icon',
                            'label'         => 'Іконка',
                            'name'          => 'messenger_icon',
                            'type'          => 'select',
                            'choices'       => array(
                                'telegram'  => 'Telegram',
                                'viber'     => 'Viber',
                                'facebook'  => 'Facebook',
                                'instagram' => 'Instagram',
                            ),
                            'default_value' => 'telegram',
                            'ui'            => 1,
                            'wrapper'       => array( 'width' => '33' ),
                        ),
                        array(
                            'key'     => 'field_project_theme_footer_messenger_name',
                            'label'   => 'Назва',
                            'name'    => 'messenger_name',
                            'type'    => 'text',
                            'wrapper' => array( 'width' => '33' ),
                        ),
                        array(
                            'key'     => 'field_project_theme_footer_messenger_url',
                            'label'   => 'Посилання',
                            'name'    => 'messenger_url',
                            'type'    => 'text',
                            'wrapper' => array( 'width' => '34' ),
                        ),
                    ),
                ),
                array(
                    'key'     => 'field_project_theme_footer_bottom_message',
                    'label'   => 'Нижній рядок footer',
                    'name'    => '',
                    'type'    => 'message',
                    'message' => 'Копірайт і нижній слоган footer.',
                    'wrapper' => array( 'width' => '100' ),
                ),
                array(
                    'key'           => 'field_project_theme_footer_copyright',
                    'label'         => 'Копірайт footer',
                    'name'          => 'footer_copyright',
                    'type'          => 'text',
                    'default_value' => '© 2024 SvitVody. Всі права захищені.',
                    'wrapper'       => array( 'width' => '50' ),
                ),
                array(
                    'key'           => 'field_project_theme_footer_tagline',
                    'label'         => 'Нижній слоган footer',
                    'name'          => 'footer_tagline',
                    'type'          => 'text',
                    'default_value' => "Доставка здоров'я",
                    'wrapper'       => array( 'width' => '50' ),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param'    => 'options_page',
                        'operator' => '==',
                        'value'    => 'theme-settings',
                    ),
                ),
            ),
        );
}

function project_theme_register_acf_header_fields(): void
{
    if ( ! function_exists( 'acf_import_field_group' ) ) {
        return;
    }

    $existing_group = get_page_by_path( 'group_project_theme_header_settings', OBJECT, 'acf-field-group' );

    if ( $existing_group ) {
        return;
    }

    acf_import_field_group( project_theme_get_acf_header_field_group() );
}
add_action( 'acf/init', 'project_theme_register_acf_header_fields' );

function project_theme_get_option( string $name, $default = '' )
{
    if ( function_exists( 'get_field' ) ) {
        $value = get_field( $name, 'option' );

        if ( null !== $value && false !== $value && '' !== $value && array() !== $value ) {
            return $value;
        }
    }

    return $default;
}

function project_theme_get_header_logo_url(): string
{
    $default = get_template_directory_uri() . '/assets/img/svit-vody-logo-blue.svg';
    $logo    = project_theme_get_option( 'header_logo', null );

    if ( is_array( $logo ) && ! empty( $logo['url'] ) ) {
        return $logo['url'];
    }

    if ( is_numeric( $logo ) ) {
        $url = wp_get_attachment_image_url( (int) $logo, 'full' );
        if ( $url ) {
            return $url;
        }
    }

    if ( is_string( $logo ) && '' !== $logo ) {
        return $logo;
    }

    return $default;
}

function project_theme_get_header_phones(): array
{
    return project_theme_get_option(
        'header_phones',
        array(
            array(
                'phone_label' => '+380 (67) 123 45 67',
                'phone_url'   => 'tel:+380671234567',
            ),
        )
    );
}

function project_theme_get_header_schedule_rows(): array
{
    return project_theme_get_option(
        'header_schedule_rows',
        array(
            array( 'schedule_text' => 'Пн-Пт: 08:00 - 20:00' ),
            array( 'schedule_text' => 'Сб-Нд: 09:00 - 18:00' ),
        )
    );
}

function project_theme_get_order_popup_phones(): array
{
    $phones = project_theme_get_option( 'order_popup_phones', array() );

    if ( is_array( $phones ) && ! empty( $phones ) ) {
        return $phones;
    }

    return project_theme_get_header_phones();
}

function project_theme_get_order_popup_messengers(): array
{
    return project_theme_get_option(
        'order_popup_messengers',
        array(
            array(
                'messenger_icon' => 'telegram',
                'messenger_name' => 'Telegram',
                'messenger_url'  => '#',
            ),
            array(
                'messenger_icon' => 'viber',
                'messenger_name' => 'Viber',
                'messenger_url'  => '#',
            ),
        )
    );
}

function project_theme_get_footer_logo_url(): string
{
    $default = get_template_directory_uri() . '/assets/img/svit-vody-logo-white.svg';
    $logo    = project_theme_get_option( 'footer_logo', null );

    if ( is_array( $logo ) && ! empty( $logo['url'] ) ) {
        return $logo['url'];
    }

    if ( is_numeric( $logo ) ) {
        $url = wp_get_attachment_image_url( (int) $logo, 'full' );
        if ( $url ) {
            return $url;
        }
    }

    if ( is_string( $logo ) && '' !== $logo ) {
        return $logo;
    }

    return $default;
}

function project_theme_get_footer_social_links(): array
{
    return project_theme_get_option(
        'footer_social_links',
        array(
            array(
                'social_name' => 'Facebook',
                'social_url'  => '#',
            ),
            array(
                'social_name' => 'Instagram',
                'social_url'  => '#',
            ),
        )
    );
}

function project_theme_get_footer_schedule_rows(): array
{
    return project_theme_get_option(
        'footer_schedule_rows',
        array(
            array( 'schedule_text' => 'Пн-Пт: 08:00 - 20:00' ),
            array( 'schedule_text' => 'Сб-Нд: 09:00 - 18:00' ),
        )
    );
}

function project_theme_get_footer_messenger_links(): array
{
    return project_theme_get_option(
        'footer_messenger_links',
        array(
            array(
                'messenger_name' => 'Telegram',
                'messenger_url'  => '#',
            ),
            array(
                'messenger_name' => 'Viber',
                'messenger_url'  => '#',
            ),
        )
    );
}

function project_theme_get_brand_icon_class( string $name ): string
{
    $normalized = strtolower( trim( $name ) );
    $icons      = array(
        'facebook'  => 'fa-brands fa-facebook-f',
        'instagram' => 'fa-brands fa-instagram',
        'telegram'  => 'fa-brands fa-telegram',
        'viber'     => 'fa-brands fa-viber',
    );

    return $icons[ $normalized ] ?? 'fa-solid fa-link';
}

function project_theme_get_footer_icon_value( array $item, string $icon_key, string $name_key ): string
{
    $icon = $item[ $icon_key ] ?? '';

    if ( is_string( $icon ) && '' !== $icon ) {
        return $icon;
    }

    $name = $item[ $name_key ] ?? '';

    return is_string( $name ) ? $name : '';
}

function project_theme_get_messenger_hover_class( string $name ): string
{
    $normalized = strtolower( trim( $name ) );
    $classes    = array(
        'telegram' => 'hover:bg-[#0088cc]',
        'viber'    => 'hover:bg-[#7360f2]',
    );

    return $classes[ $normalized ] ?? 'hover:bg-brand-orange';
}

function project_theme_get_messenger_text_class( string $name ): string
{
    $normalized = strtolower( trim( $name ) );
    $classes    = array(
        'telegram' => 'text-[#0088cc]',
        'viber'    => 'text-[#7360f2]',
    );

    return $classes[ $normalized ] ?? 'text-brand-orange';
}

function project_theme_render_language_switcher( string $wrapper_class, string $item_class, string $active_class ): void
{
    $enabled = project_theme_get_option( 'header_language_switcher_enabled', true );
    if ( ! $enabled ) {
        return;
    }

    echo '<div class="' . esc_attr( $wrapper_class ) . '" aria-label="' . esc_attr__( 'Мова сайту', 'project-theme' ) . '">';

    if ( function_exists( 'pll_the_languages' ) ) {
        $languages = pll_the_languages(
            array(
                'raw'                    => 1,
                'hide_if_no_translation' => 0,
            )
        );

        if ( is_array( $languages ) && ! empty( $languages ) ) {
            if ( function_exists( 'pll_languages_list' ) ) {
                $language_slugs = pll_languages_list( array( 'fields' => 'slug' ) );

                foreach ( $language_slugs as $language_slug ) {
                    if ( isset( $languages[ $language_slug ] ) ) {
                        continue;
                    }

                    $languages[ $language_slug ] = array(
                        'slug'         => $language_slug,
                        'url'          => function_exists( 'pll_home_url' ) ? pll_home_url( $language_slug ) : home_url( '/' ),
                        'current_lang' => function_exists( 'pll_current_language' ) && pll_current_language() === $language_slug,
                    );
                }
            }

            foreach ( $languages as $language ) {
                $class = $item_class;
                if ( ! empty( $language['current_lang'] ) ) {
                    $class .= ' ' . $active_class;
                }

                printf(
                    '<a class="%1$s" href="%2$s">%3$s</a>',
                    esc_attr( $class ),
                    esc_url( $language['url'] ?? '#' ),
                    esc_html( project_theme_format_language_label( $language['slug'] ?? $language['name'] ?? '' ) )
                );
            }

            echo '</div>';
            return;
        }
    }

    echo '<button type="button" class="' . esc_attr( trim( $item_class . ' ' . $active_class ) ) . '">UA</button>';
    echo '<button type="button" class="' . esc_attr( $item_class ) . '">RU</button>';
    echo '</div>';
}

function project_theme_format_language_label( string $language ): string
{
    $language = strtolower( $language );

    if ( 'uk' === $language || 'ua' === $language ) {
        return 'UA';
    }

    return strtoupper( $language );
}
