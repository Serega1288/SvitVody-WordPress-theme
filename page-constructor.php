<?php
/**
 * Template Name: Constructor
 *
 * @package ProjectTheme
 */
get_header();
?>
<main id="swup" class="transition-fade" role="main">
    <div class="template-page constructor">
        <?php while (have_posts()) : the_post(); ?>
            <?php get_template_part('inc/box', 'constructor'); ?>
        <?php endwhile; ?>
    </div>
</main>
<?php get_footer(); ?>
