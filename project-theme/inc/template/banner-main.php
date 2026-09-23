<?php $section_id = project_theme_constructor_anchor(get_row_layout(), (string) $args); ?>
<section id="<?php echo esc_attr($section_id); ?>" class="constructor-banner-main relative min-h-[600px] bg-brand-lightblue overflow-hidden px-6 md:px-16 py-20">
    <div class="hero-inner w-full mx-auto min-h-[600px] flex flex-col lg:flex-row items-center">
        <div class="w-full lg:w-1/2 z-10">
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/50 backdrop-blur-sm rounded-full border border-brand-line mb-6">
                <span class="w-2 h-2 bg-brand-orange rounded-full"></span>
                <span class="text-[10px] font-bold uppercase tracking-wider text-brand-blue"><?php echo project_theme_section_text('eyebrow'); ?></span>
            </div>
            <h1 class="text-5xl md:text-7xl font-bold leading-[1.1] mb-6 text-brand-blue">
                <span class="desktop-hero-title"><?php echo project_theme_section_rich('title'); ?><br><span class="text-brand-orange"><?php echo project_theme_section_text('title_accent'); ?></span></span>
                <span class="mobile-hero-title"><?php echo project_theme_section_rich('title_mobile'); ?><br><span class="text-brand-orange"><?php echo project_theme_section_text('title_accent_mobile'); ?></span></span>
            </h1>
            <div class="constructor-copy text-lg text-brand-blue/70 max-w-md mb-10 leading-relaxed"><?php echo project_theme_section_rich('description'); ?></div>
            <div class="flex flex-wrap gap-4">
                <button data-modal-open="<?php echo esc_attr(project_theme_constructor_modal_id()); ?>" class="px-10 py-4 bg-brand-blue text-white rounded-2xl font-bold hover:bg-blue-700 transition-all shadow-xl text-white shadow-brand-blue/20 flex items-center gap-3">
                    <?php echo project_theme_section_text('button_text'); ?> <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </button>
            </div>
        </div>
        <div class="w-full lg:w-1/2 relative mt-12 lg:mt-0 flex justify-center lg:justify-end">
            <div class="relative w-full max-w-[500px]">
                <?php echo project_theme_section_image('image', 'banner-bottle-hover w-full h-auto drop-shadow-2xl'); ?>
                <div class="absolute top-1/4 -right-4 md:-right-8 bg-brand-orange text-white p-6 rounded-3xl shadow-2xl rotate-12 flex flex-col items-center">
                    <span class="text-xs font-bold uppercase opacity-80 mb-1"><?php echo project_theme_section_text('price_label'); ?></span>
                    <div class="text-4xl font-black"><?php echo project_theme_section_text('price'); ?><span class="text-xl"><?php echo project_theme_section_text('currency'); ?></span></div>
                    <div class="mt-2 text-[10px] font-medium text-center border-t border-white/20 pt-2"><?php echo project_theme_section_rich('price_note'); ?></div>
                </div>
            </div>
        </div>
    </div>
</section>
