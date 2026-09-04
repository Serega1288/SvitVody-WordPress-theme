<?php
/**
 * Theme footer.
 *
 * @package ProjectTheme
 */
?>
    <footer style="background-color: #f8f9fa; padding: 20px; text-align: center;" class="site-footer">

        <h1>footer-menu-1</h1>
        <ul>
            <?php
            wp_nav_menu([
                    'theme_location' => 'footer-menu-1',
                    'menu' => '',
                    'container' => '',
                    'container_class' => '',
                    'container_id' => '',
                    'menu_class' => 'menu',
                    'menu_id' => '',
                    'echo' => true,
                    'fallback_cb' => 'wp_page_menu',
                    'before' => '',
                    'after' => '',
                    'link_before' => '',
                    'link_after' => '',
                    'items_wrap' => '%3$s',
                    'depth' => 0,
                    'walker' => '',
            ]);
            ?>
        </ul>

        <p>
            <?php echo esc_html(get_bloginfo('name')); ?>
        </p>
    </footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
