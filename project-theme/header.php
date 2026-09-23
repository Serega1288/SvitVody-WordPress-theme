<?php
/**
 * Theme header.
 *
 * @package ProjectTheme
 */

$header_logo_url             = project_theme_get_header_logo_url();
$header_logo_alt             = project_theme_get_option( 'header_logo_alt', "Світ Води - доставка здоров'я" );
$contacts_button_text        = project_theme_get_option( 'header_contacts_button_text', 'Контакти' );
$phone_group_title           = project_theme_get_option( 'header_phone_group_title', 'Наші телефони' );
$phones                      = project_theme_get_header_phones();
$schedule_group_title        = project_theme_get_option( 'header_schedule_group_title', 'Графік роботи' );
$schedule_rows               = project_theme_get_header_schedule_rows();
$order_button_text           = project_theme_get_option( 'header_order_button_text', 'Замовити воду' );
$order_button_modal          = project_theme_get_option( 'order_popup_modal_id', project_theme_get_option( 'header_order_button_modal', 'orderModal' ) );
$mobile_order_main           = project_theme_get_option( 'header_mobile_order_main', 'Замовити' );
$mobile_order_extra          = project_theme_get_option( 'header_mobile_order_extra', 'воду' );
$order_popup_options         = array(
    'modalId'             => $order_button_modal,
    'closeLabel'          => project_theme_get_option( 'order_popup_close_label', 'Закрити форму' ),
    'contactHeading'      => project_theme_get_option( 'order_popup_contact_heading', "Ви можете зв'язатися з нами телефоном або у месенджерах." ),
    'phones'              => project_theme_get_order_popup_phones(),
    'messengers'          => project_theme_get_order_popup_messengers(),
);
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="<?php echo esc_url(get_template_directory_uri()); ?>/assets/favicon/favicon.svg" type="image/svg+xml">
    <?php wp_head(); ?>
</head>
<body <?php body_class('antialiased page-home'); ?>>
<?php wp_body_open(); ?>
<!-- Header -->
    <header id="main-header" class="sticky top-0 z-[100] w-full px-6 py-4 md:px-16 bg-white border-b border-brand-line transition-all duration-300">
        <div class="site-header-inner w-full mx-auto flex items-center justify-between">
        <div class="flex items-center gap-12">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="site-logo-link group transition-all" aria-label="<?php echo esc_attr__('Світ Води, головна', 'project-theme'); ?>"><img class="site-logo site-logo-header" src="<?php echo esc_url($header_logo_url); ?>" alt="<?php echo esc_attr($header_logo_alt); ?>"></a>
            <nav class="hidden lg:flex items-center gap-8 text-[13px] font-bold uppercase tracking-wider">
                <?php
                wp_nav_menu(
                    array(
                        'theme_location' => 'header-menu-top',
                        'container'      => false,
                        'items_wrap'     => '%3$s',
                        'fallback_cb'    => false,
                        'walker'         => new Project_Theme_Header_Walker(),
                    )
                );
                ?>
            </nav>
        </div>

        <div class="flex items-center gap-4">
            <!-- Contacts Dropdown -->
            <div class="relative group">
                <button class="flex items-center gap-3 px-6 py-3 bg-brand-darkblue text-brand-blue rounded-xl text-sm font-bold border border-brand-line hover:border-brand-blue transition-all">
                    <i class="fa-solid fa-phone-volume text-brand-orange text-lg"></i>
                    <?php echo esc_html($contacts_button_text); ?>
                    <i class="fa-solid fa-chevron-down text-[10px] opacity-50 ml-1"></i>
                </button>
                <div class="absolute right-0 top-full mt-2 w-72 bg-white border border-brand-line shadow-2xl rounded-2xl p-6 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform translate-y-2 group-hover:translate-y-0 z-50">
                    <div class="space-y-4">
                        <div>
                            <p class="text-[10px] uppercase tracking-widest text-gray-400 font-bold mb-2"><?php echo esc_html($phone_group_title); ?></p>
                            <?php foreach ( $phones as $phone ) : ?>
                                <?php
                                $phone_label = $phone['phone_label'] ?? '';
                                $phone_url   = $phone['phone_url'] ?? '';
                                if ( '' === $phone_label ) {
                                    continue;
                                }
                                ?>
                                <a href="<?php echo esc_url($phone_url ?: '#'); ?>" class="block text-brand-blue text-lg font-bold hover:text-brand-orange transition-colors"><?php echo esc_html($phone_label); ?></a>
                            <?php endforeach; ?>
                        </div>
                        <div class="pt-4 border-t border-brand-line">
                            <p class="text-[10px] uppercase tracking-widest text-gray-400 font-bold mb-1"><?php echo esc_html($schedule_group_title); ?></p>
                            <?php foreach ( $schedule_rows as $schedule_row ) : ?>
                                <?php
                                $schedule_text = $schedule_row['schedule_text'] ?? '';
                                if ( '' === $schedule_text ) {
                                    continue;
                                }
                                ?>
                                <p class="text-xs text-brand-blue font-medium"><?php echo esc_html($schedule_text); ?></p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Button -->
            <button data-modal-open="<?php echo esc_attr($order_button_modal); ?>" class="px-8 py-3 bg-brand-orange text-white rounded-xl text-sm font-bold shadow-lg shadow-brand-orange/30 hover:bg-orange-600 transition-all hover:scale-105 active:scale-95">
                <?php echo esc_html($order_button_text); ?>
            </button>

            <!-- Language Switcher -->
            <?php project_theme_render_language_switcher( 'flex items-center gap-1 p-1 bg-brand-darkblue border border-brand-line rounded-xl text-[11px] font-bold', 'px-3 py-1.5 rounded-lg text-gray-400 hover:text-brand-blue transition-colors', 'bg-white text-brand-blue shadow-sm' ); ?>
        </div>
        </div>
    </header>

    <script>
        window.svitvodyHomeUrl = <?php echo wp_json_encode( home_url( '/' ) ); ?>;
        window.svitvodyHeaderLogo = <?php echo wp_json_encode( $header_logo_url ); ?>;
        window.svitvodyHeaderOptions = <?php echo wp_json_encode(
            array(
                'contactsTitle'    => $contacts_button_text,
                'logoAlt'          => $header_logo_alt,
                'phoneGroupTitle'  => $phone_group_title,
                'phones'           => $phones,
                'scheduleTitle'    => $schedule_group_title,
                'scheduleRows'     => $schedule_rows,
                'orderButtonText'  => $order_button_text,
                'orderButtonModal' => $order_button_modal,
                'mobileOrderMain'  => $mobile_order_main,
                'mobileOrderExtra' => $mobile_order_extra,
            )
        ); ?>;
        window.svitvodyOrderPopupOptions = <?php echo wp_json_encode( $order_popup_options ); ?>;
    </script>
    <template id="mobile-menu-links-template">
        <?php
        wp_nav_menu(
            array(
                'theme_location' => 'header-mobile-menu',
                'container'      => false,
                'items_wrap'     => '%3$s',
                'fallback_cb'    => false,
                'walker'         => new Project_Theme_Header_Walker(),
            )
        );
        ?>
    </template>
    <template id="mobile-language-switcher-template">
        <?php project_theme_render_language_switcher( 'mobile-language-switcher', '', 'is-active' ); ?>
    </template>
