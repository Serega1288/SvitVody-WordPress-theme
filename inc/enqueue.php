<?php

function project_theme_enqueue_assets(): void
{
    $theme_version = wp_get_theme()->get('Version');

    wp_enqueue_style(
        'project-theme-main',
        get_template_directory_uri() . '/assets/css/main.css',
        array(),
        $theme_version
    );

    wp_enqueue_script(
        'swup',
        'https://unpkg.com/swup@4',
        array(),
        null,
        true
    );

    wp_enqueue_script(
        'project-theme-main',
        get_template_directory_uri() . '/assets/js/main.js',
        array('swup'),
        $theme_version,
        true
    );
}
add_action('wp_enqueue_scripts', 'project_theme_enqueue_assets');
