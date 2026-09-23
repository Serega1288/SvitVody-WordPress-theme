<?php
$section_id = project_theme_constructor_anchor(get_row_layout(), (string) $args);
$water = project_theme_get_water_product((int) get_sub_field('water_product'));
?>
<section id="<?php echo esc_attr($section_id); ?>" class="constructor-pricing py-24 px-6 md:px-16 bg-brand-darkblue">
    <div class="max-w-screen-xl mx-auto">
        <h2 class="text-4xl md:text-5xl font-bold mb-16 text-brand-blue text-center uppercase"><?php echo project_theme_section_heading('title'); ?></h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto mb-24">
            <?php foreach ($water['tiers'] ?? array() as $tier) :
                $featured = (bool) $tier['label'];
                $quantity = $tier['min_bottles'];
            ?>
            <div class="rounded-[40px] p-10 flex flex-col justify-between transition-all <?php echo $featured ? 'bg-brand-blue shadow-2xl text-white relative transform hover:scale-105' : 'bg-white border border-brand-line text-center hover:border-brand-blue shadow-xl'; ?>">
                <?php if ($featured) : ?><div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-brand-orange px-6 py-2 rounded-full text-[10px] font-bold uppercase tracking-widest shadow-lg"><?php echo esc_html($tier['label']); ?></div><?php endif; ?>
                <div>
                    <h3 class="text-xl font-bold mb-4 <?php echo $featured ? '' : 'text-brand-blue'; ?>"><?php echo esc_html($tier['title']); ?></h3>
                    <div class="text-5xl font-black mb-1 <?php echo $featured ? '' : 'text-brand-blue'; ?>"><?php echo esc_html(number_format_i18n($tier['price'], floor($tier['price']) === $tier['price'] ? 0 : 2)); ?><span class="text-xl font-bold ml-1"><?php echo esc_html($water['currency']); ?></span></div>
                    <div class="constructor-copy text-[10px] font-bold mb-6 uppercase tracking-widest <?php echo $featured ? 'text-white/60' : 'text-brand-blue/40'; ?>"><?php echo wp_kses_post($tier['unit']); ?></div>
                </div>
                <button data-modal-open="<?php echo esc_attr(project_theme_constructor_modal_id()); ?>" data-cart-item="<?php echo esc_attr($water['cart_key']); ?>" data-constructor-price="<?php echo esc_attr($tier['price']); ?>" data-constructor-quantity="<?php echo esc_attr($quantity); ?>" class="w-full py-3.5 rounded-2xl font-bold transition-all uppercase tracking-widest text-xs <?php echo $featured ? 'bg-brand-orange text-white hover:bg-orange-600 shadow-lg' : 'border-2 border-brand-blue text-brand-blue hover:bg-brand-blue hover:text-white'; ?>"><?php echo esc_html($tier['button_text']); ?></button>
            </div>
            <?php endforeach; ?>
        </div>
        <?php get_template_part('inc/template/additional', 'products', $args); ?>
    </div>
</section>
