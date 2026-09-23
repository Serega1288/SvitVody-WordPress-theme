<?php
/**
 * Theme footer.
 *
 * @package ProjectTheme
 */

$footer_logo_url         = project_theme_get_footer_logo_url();
$footer_logo_alt         = project_theme_get_option( 'footer_logo_alt', "Світ Води - доставка здоров'я" );
$footer_description      = project_theme_get_option( 'footer_description', "Чиста вода для вашого здоров'я з 2010 року. Свіжість природи у кожній краплі." );
$footer_social_links     = project_theme_get_footer_social_links();
$footer_menu_title       = project_theme_get_option( 'footer_menu_title', 'Меню' );
$footer_contacts_title   = project_theme_get_option( 'footer_contacts_title', 'Контакти' );
$footer_phone_label      = project_theme_get_option( 'footer_phone_label', '+380 (67) 123 45 67' );
$footer_phone_url        = project_theme_get_option( 'footer_phone_url', 'tel:+380671234567' );
$footer_phone_caption    = project_theme_get_option( 'footer_phone_caption', 'Цілодобовий прийом заявок' );
$footer_schedule_title   = project_theme_get_option( 'footer_schedule_title', 'Графік роботи:' );
$footer_schedule_rows    = project_theme_get_footer_schedule_rows();
$footer_messengers_title = project_theme_get_option( 'footer_messengers_title', 'Меседжери' );
$footer_messenger_links  = project_theme_get_footer_messenger_links();
$footer_copyright        = project_theme_get_option( 'footer_copyright', '© 2024 SvitVody. Всі права захищені.' );
$footer_tagline          = project_theme_get_option( 'footer_tagline', "Доставка здоров'я" );
?>

    <footer id="footer" class="site-footer text-white pt-24 pb-12 px-6 md:px-16">
        <div class="max-w-screen-2xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16 border-b border-white/10 pb-16">
                <div class="space-y-6">
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="site-logo-link footer-logo-link group transition-all" aria-label="<?php echo esc_attr($footer_logo_alt); ?>"><img class="site-logo site-logo-footer" src="<?php echo esc_url($footer_logo_url); ?>" alt="<?php echo esc_attr($footer_logo_alt); ?>"></a>
                    <p class="text-white/40 text-sm leading-relaxed max-w-xs"><?php echo esc_html($footer_description); ?></p>
                    <div class="flex gap-4">
                        <?php foreach ( $footer_social_links as $social_link ) : ?>
                            <?php
                            $social_name = $social_link['social_name'] ?? '';
                            $social_url  = $social_link['social_url'] ?? '';
                            if ( '' === $social_name ) {
                                continue;
                            }
                            $social_icon = project_theme_get_footer_icon_value( $social_link, 'social_icon', 'social_name' );
                            ?>
                            <a href="<?php echo esc_url($social_url ?: '#'); ?>" class="w-10 h-10 bg-white/5 rounded-full flex items-center justify-center hover:bg-brand-orange transition-colors text-white/50 hover:text-white" aria-label="<?php echo esc_attr($social_name); ?>"><i class="<?php echo esc_attr(project_theme_get_brand_icon_class($social_icon)); ?> text-lg"></i></a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div>
                    <h4 class="text-[10px] font-bold uppercase tracking-[0.3em] text-white/20 mb-8"><?php echo esc_html($footer_menu_title); ?></h4>
                    <ul class="space-y-4 text-sm font-semibold text-white/60">
                        <?php
                        wp_nav_menu(
                            array(
                                'theme_location' => 'footer-menu-1',
                                'container'      => false,
                                'items_wrap'     => '%3$s',
                                'fallback_cb'    => false,
                            )
                        );
                        ?>
                    </ul>
                </div>

                <div>
                    <h4 class="text-[10px] font-bold uppercase tracking-[0.3em] text-white/20 mb-8"><?php echo esc_html($footer_contacts_title); ?></h4>
                    <div class="space-y-6">
                        <div>
                            <a href="<?php echo esc_url($footer_phone_url ?: '#'); ?>" class="block font-bold text-2xl hover:text-brand-orange transition-colors tracking-tighter"><?php echo esc_html($footer_phone_label); ?></a>
                            <p class="text-white/30 text-[10px] font-bold uppercase tracking-widest mt-1"><?php echo esc_html($footer_phone_caption); ?></p>
                        </div>
                        <div class="text-sm text-white/40">
                            <p class="text-white/60 font-bold mb-1 uppercase tracking-widest text-[10px]"><?php echo esc_html($footer_schedule_title); ?></p>
                            <?php foreach ( $footer_schedule_rows as $schedule_row ) : ?>
                                <?php
                                $schedule_text = $schedule_row['schedule_text'] ?? '';
                                if ( '' === $schedule_text ) {
                                    continue;
                                }
                                ?>
                                <p><?php echo esc_html($schedule_text); ?></p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-[10px] font-bold uppercase tracking-[0.3em] text-white/20 mb-8"><?php echo esc_html($footer_messengers_title); ?></h4>
                    <div class="grid grid-cols-1 gap-3">
                        <?php foreach ( $footer_messenger_links as $messenger_link ) : ?>
                            <?php
                            $messenger_name = $messenger_link['messenger_name'] ?? '';
                            $messenger_url  = $messenger_link['messenger_url'] ?? '';
                            if ( '' === $messenger_name ) {
                                continue;
                            }
                            $messenger_icon = project_theme_get_footer_icon_value( $messenger_link, 'messenger_icon', 'messenger_name' );
                            ?>
                            <a href="<?php echo esc_url($messenger_url ?: '#'); ?>" class="flex items-center gap-3 px-5 py-4 bg-white/5 rounded-2xl <?php echo esc_attr(project_theme_get_messenger_hover_class($messenger_icon)); ?> transition-all group">
                                <i class="<?php echo esc_attr(project_theme_get_brand_icon_class($messenger_icon) . ' ' . project_theme_get_messenger_text_class($messenger_icon)); ?> text-xl group-hover:text-white"></i>
                                <span class="font-bold text-xs uppercase tracking-widest"><?php echo esc_html($messenger_name); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <p class="text-white/10 text-[10px] font-bold uppercase tracking-widest"><?php echo esc_html($footer_copyright); ?></p>
                <div class="text-white/10 text-[10px] font-bold uppercase tracking-widest italic"><?php echo esc_html($footer_tagline); ?></div>
            </div>
        </div>
    </footer>

    <!-- Order Modal -->
    <div id="orderModal" data-modal-backdrop="orderModal" class="fixed inset-0 z-[200] hidden items-center justify-center bg-brand-blue/60 backdrop-blur-md p-4">
        <div class="order-modal-dialog"></div>
    </div>
<?php wp_footer(); ?>
</body>
</html>
