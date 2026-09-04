<?php
/**
 * Main theme template.
 *
 * @package ProjectTheme
 */

get_header();
?>
<main id="swup" class="transition-fade" role="main">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
                </header>

                <p>!!!!!!! test !!!!!!!! 222</p>

                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
            </article>
        <?php endwhile; ?>
    <?php else : ?>
        <section class="no-results">
                <h1><?php esc_html_e('No content found.', 'project-theme'); ?></h1>
        </section>
    <?php endif; ?>
</main>
<?php
get_footer();
