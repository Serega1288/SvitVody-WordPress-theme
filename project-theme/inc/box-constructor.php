<?php
if (!function_exists('have_rows')) {
    return;
}
$constructor_name = is_array($args) ? (string) ($args['constructor_name'] ?? '') : (is_scalar($args) ? (string) $args : '');
$step_section = 0;
while (have_rows('constructor')) :
    the_row();
    $section_index = $step_section++;
    $section_suffix = (string) $section_index . ('' !== $constructor_name ? '-' . $constructor_name : '');
    if (get_sub_field('disable_block')) {
        continue;
    }
    if ('template-banner-main' === get_row_layout()) {
        get_template_part('inc/template/banner', 'main', $section_suffix);
    } elseif ('template-promotions' === get_row_layout()) {
        get_template_part('inc/template/promotions', null, $section_suffix);
    } elseif ('template-banner-inner' === get_row_layout()) {
        get_template_part('inc/template/banner', 'inner', $section_suffix);
    } elseif ('template-water-quality' === get_row_layout()) {
        get_template_part('inc/template/water', 'quality', $section_suffix);
    } elseif ('template-water-composition' === get_row_layout()) {
        get_template_part('inc/template/water', 'composition', $section_suffix);
    } elseif ('template-home-features' === get_row_layout()) {
        get_template_part('inc/template/home', 'features', $section_suffix);
    } elseif ('template-office-usecases' === get_row_layout()) {
        get_template_part('inc/template/office', 'usecases', $section_suffix);
    } elseif ('template-water-pricing' === get_row_layout()) {
        get_template_part('inc/template/water', 'pricing', $section_suffix);
    } elseif ('template-work-steps' === get_row_layout()) {
        get_template_part('inc/template/work', 'steps', $section_suffix);
    }
endwhile;
