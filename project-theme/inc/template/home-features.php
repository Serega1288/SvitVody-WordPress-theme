<section id="<?php echo esc_attr(project_theme_constructor_anchor(get_row_layout(), (string) $args)); ?>" class="feature-slider-section py-24 px-6 md:px-16 bg-white relative overflow-hidden">
    <div class="absolute top-0 left-0 w-full h-full opacity-[0.03] pointer-events-none" aria-hidden="true"><svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M50 0 C70 40 90 60 90 80 A40 40 0 0 1 10 80 C10 60 30 40 50 0" fill="#0066CC" /></svg></div>
    <div class="max-w-screen-xl mx-auto relative z-10">
        <div class="text-center mb-20">
            <h2 class="text-3xl md:text-4xl font-black text-brand-blue uppercase tracking-tight mb-4"><?php echo project_theme_section_heading('title'); ?></h2>
            <div class="w-24 h-1 bg-brand-orange mx-auto rounded-full"></div>
        </div>
        <div class="grid md:grid-cols-3 gap-x-12 gap-y-20">
            <?php while (have_rows('items')) : the_row(); ?>
            <div class="flex flex-col items-center text-center group">
                <div class="w-24 h-24 bg-brand-darkblue rounded-full flex items-center justify-center mb-8 shadow-xl border border-brand-line group-hover:scale-110 transition-transform duration-500 relative">
                    <i class="<?php echo esc_attr(project_theme_section_icon()); ?> text-4xl text-brand-blue" aria-hidden="true"></i>
                    <?php if ('certificate' === get_sub_field('icon')) : ?><i class="fa-solid fa-check text-sm text-white absolute bg-brand-orange w-6 h-6 rounded-full flex items-center justify-center border-2 border-white top-2 right-2" aria-hidden="true"></i><?php endif; ?>
                </div>
                <h3 class="text-lg font-bold text-brand-blue mb-4 uppercase leading-tight"><?php echo project_theme_section_heading('title'); ?></h3>
                <div class="constructor-copy text-[13px] text-brand-blue/60 leading-relaxed"><?php echo project_theme_section_rich('description'); ?></div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>
