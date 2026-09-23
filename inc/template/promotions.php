<?php $section_id = project_theme_constructor_anchor(get_row_layout(), (string) $args); ?>
<section id="<?php echo esc_attr($section_id); ?>" class="constructor-promotions py-24 px-6 md:px-16 bg-white">
    <div class="max-w-screen-2xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-10 gap-6"><div>
            <h2 class="text-4xl md:text-5xl font-bold mb-4 text-brand-blue"><?php echo project_theme_section_heading('title'); ?></h2>
            <div class="constructor-copy text-brand-blue/60 max-w-xl text-lg"><div class="desktop-promo-copy"><?php echo project_theme_section_rich('description'); ?></div><div class="mobile-promo-copy"><?php echo project_theme_section_rich('description_mobile'); ?></div></div>
        </div></div>
        <?php if (have_rows('promotions')) : ?>
        <div class="promo-grid promo-grid-actions grid md:grid-cols-2 gap-8 mb-16">
            <?php while (have_rows('promotions')) : the_row(); $primary = get_row_index() % 2 === 1; ?>
            <div class="group relative overflow-hidden rounded-[40px] p-10 flex flex-col md:flex-row items-center gap-8 shadow-xl <?php echo $primary ? 'bg-brand-blue text-white' : 'bg-brand-darkblue border border-brand-line'; ?>">
                <div class="relative z-10 md:w-3/5">
                    <span class="inline-block px-4 py-1 text-[10px] font-bold rounded-full mb-4 uppercase tracking-widest <?php echo $primary ? 'bg-white/20 text-white' : 'bg-brand-blue/10 text-brand-blue'; ?>"><?php echo project_theme_section_text('label'); ?></span>
                    <h3 class="text-3xl font-bold mb-4 <?php echo $primary ? '' : 'text-brand-blue'; ?>" data-mobile-title="<?php echo esc_attr((string) get_sub_field('title_mobile')); ?>"><?php echo project_theme_section_heading('title'); ?></h3>
                    <div class="flex items-baseline gap-2 mb-4">
                        <span class="text-5xl font-black <?php echo $primary ? '' : 'text-brand-blue'; ?>"><?php echo project_theme_section_text('price'); ?></span>
                        <span class="text-xl font-bold <?php echo $primary ? '' : 'text-brand-blue'; ?>"><?php echo project_theme_section_text('currency'); ?></span>
                        <span class="text-xs font-bold uppercase tracking-widest ml-2 <?php echo $primary ? 'text-white/60' : 'text-brand-blue/40'; ?>"><?php echo project_theme_section_text('unit'); ?></span>
                    </div>
                    <div class="constructor-copy text-sm font-bold p-3 rounded-xl inline-block border <?php echo $primary ? 'text-white/80 bg-white/10 border-white/20' : 'text-brand-blue/60 bg-brand-blue/5 border-brand-blue/10'; ?>"><?php echo project_theme_section_rich('note'); ?></div>
                </div>
                <div class="md:w-2/5 flex justify-center relative z-0"><?php echo project_theme_section_image('image', 'h-64 w-auto drop-shadow-2xl group-hover:scale-110 transition-transform duration-500'); ?></div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
        <div class="mb-10">
            <h2 class="text-4xl md:text-5xl font-bold mb-4 text-brand-blue"><?php echo project_theme_section_text('offers_heading'); ?></h2>
            <div class="constructor-copy text-brand-blue/60 max-w-xl text-lg"><?php echo project_theme_section_rich('offers_intro'); ?></div>
        </div>
        <?php if (have_rows('offers')) : ?>
        <div class="promo-grid promo-grid-offers grid md:grid-cols-2 gap-8">
            <?php while (have_rows('offers')) : the_row(); $light = get_row_index() % 2 === 1; ?>
            <div class="group relative overflow-hidden rounded-[40px] p-10 flex flex-col md:flex-row items-center gap-8 shadow-xl <?php echo $light ? 'bg-white border-2 border-brand-blue/20 hover:border-brand-blue transition-all' : 'bg-brand-orange text-white'; ?>">
                <div class="relative z-10 md:w-3/5">
                    <span class="inline-block px-4 py-1 text-[10px] font-bold rounded-full mb-4 uppercase tracking-widest <?php echo $light ? 'bg-brand-orange/10 text-brand-orange' : 'bg-white/20 text-white'; ?>"><?php echo project_theme_section_text('label'); ?></span>
                    <h3 class="text-3xl font-bold mb-4 <?php echo $light ? 'text-brand-blue' : ''; ?>"><?php echo project_theme_section_heading('title'); ?></h3>
                    <div class="constructor-copy text-sm mb-8 <?php echo $light ? 'text-brand-blue/60' : 'text-white/80'; ?>"><?php echo project_theme_section_rich('description'); ?></div>
                    <?php if (get_sub_field('page')) : ?><a href="<?php echo esc_url(get_sub_field('page')); ?>" class="inline-block px-8 py-3 rounded-xl font-bold transition-colors shadow-lg text-center <?php echo $light ? 'bg-brand-blue text-white hover:bg-blue-700' : 'bg-white text-brand-orange hover:bg-brand-lightblue'; ?>"><?php echo project_theme_section_text('button_text'); ?></a><?php endif; ?>
                </div>
                <div class="md:w-2/5 flex justify-center relative z-0"><?php echo project_theme_section_image('image', 'h-64 w-auto drop-shadow-2xl group-hover:scale-110 transition-transform duration-500 object-contain'); ?></div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
