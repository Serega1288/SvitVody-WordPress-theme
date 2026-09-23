<?php
/**
 * Fallback template for content without an assigned page template.
 *
 * @package ProjectTheme
 */
get_header();
?>
<main id="swup" class="transition-fade" role="main">
    <?php while (have_posts()) : the_post(); ?>
        <article <?php post_class('max-w-screen-xl mx-auto px-6 py-24'); ?>>
            <h1 class="text-4xl font-bold mb-8"><?php the_title(); ?></h1>
            <?php the_content(); ?>
        </article>
    <?php endwhile; ?>
</main>
<?php get_footer(); ?>
