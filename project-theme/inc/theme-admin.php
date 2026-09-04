<?php
add_action( 'after_setup_theme', 'hortiqa_theme_setup', 5 );
function hortiqa_theme_setup() : void {
    /* 1. Підтримка перекладів */
    load_theme_textdomain(
        'themehortiqa',                         // має збігатися з Text Domain
        get_template_directory() . '/languages'   // той самий каталог, що й у style.css
    );
    add_theme_support( 'woocommerce' );
}



// ініціалізація динамісних слав пееркладу (фільтр)

function remove_wp_logo() {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('wp-logo');
}
add_action( 'wp_before_admin_bar_render', 'remove_wp_logo' );
add_theme_support('align-wide');
add_theme_support( 'menus' );
function register_my_menus() {
    register_nav_menus(
        array(
            'header-mobile-menu' => 'header mobile menu',
            'header-menu-top' => 'header menu up',
            'footer-menu-1' => 'footer menu 1',
        )
    );
}
add_action( 'init', 'register_my_menus' );