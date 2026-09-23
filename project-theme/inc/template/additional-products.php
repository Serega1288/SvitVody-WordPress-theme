<?php $products = project_theme_get_products(); ?>
<div class="max-w-5xl mx-auto pt-12" data-additional-products<?php if (!$products) { echo ' hidden'; } ?>>
    <div class="text-center mb-16">
        <h3 class="text-4xl md:text-5xl font-black text-brand-blue mb-4 uppercase" data-mobile-title="<?php echo esc_attr((string) get_sub_field('products_title_mobile')); ?>"><?php echo project_theme_section_rich('products_title'); ?></h3>
        <div class="w-24 h-1 bg-brand-orange mx-auto rounded-full"></div>
    </div>
    <div class="grid md:grid-cols-2 gap-8">
        <?php foreach ($products as $index => $product) : $alternate = $index % 2 === 1; ?>
        <div data-product-id="<?php echo esc_attr($product['id']); ?>" class="group relative bg-white rounded-[48px] p-10 flex items-center justify-between shadow-lg border border-brand-line overflow-hidden hover:border-brand-blue transition-all duration-500 <?php echo $alternate ? 'product-card-orange-wash' : 'product-card-blue-wash'; ?>">
            <div class="relative z-10 w-3/5">
                <span class="inline-block px-3 py-1 bg-brand-blue/5 text-brand-blue text-[10px] font-bold rounded-full mb-4 uppercase tracking-[0.2em]"><?php echo esc_html($product['label']); ?></span>
                <h4 class="text-2xl font-black text-brand-blue mb-4 leading-tight"><?php echo esc_html($product['title']); ?></h4>
                <?php if (null !== $product['price']) : ?>
                <div class="flex items-baseline gap-2 mb-8">
                    <span class="text-5xl font-black text-brand-blue"><?php echo esc_html(number_format_i18n($product['price'], floor($product['price']) === $product['price'] ? 0 : 2)); ?></span>
                    <span class="text-xl font-bold text-brand-blue"><?php echo esc_html($product['currency']); ?></span>
                </div>
                <button data-modal-open="<?php echo esc_attr(project_theme_constructor_modal_id()); ?>" data-cart-item="<?php echo esc_attr($product['cart_key']); ?>" class="px-6 py-3 bg-brand-blue text-white rounded-xl font-bold hover:bg-blue-700 transition-all shadow-lg text-xs uppercase tracking-widest"><?php echo esc_html($product['button_text']); ?></button>
                <?php endif; ?>
            </div>
            <div class="absolute w-1/2 pointer-events-none group-hover:scale-110 transition-transform duration-700 <?php echo $alternate ? '-right-8 -bottom-4 h-[80%] group-hover:rotate-6' : '-right-12 -bottom-8 h-[90%] group-hover:-rotate-6'; ?>">
                <?php echo wp_get_attachment_image($product['image_id'], 'full', false, array(
                    'class' => 'w-full h-full object-contain drop-shadow-xl' . ('pump' === $product['cart_key'] ? ' pump-product-image' : ''),
                    'alt' => $product['image_alt'],
                )); ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
