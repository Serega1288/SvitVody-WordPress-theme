<section id="<?php echo esc_attr(project_theme_constructor_anchor(get_row_layout(), (string) $args)); ?>" class="water-quality py-24 px-6 md:px-16 bg-white relative">
    <div class="max-w-screen-xl mx-auto relative z-10">
        <h2 class="text-3xl md:text-4xl font-black text-center mb-24 text-brand-blue uppercase tracking-tight"><?php echo project_theme_section_heading('title'); ?></h2>
        <div class="grid md:grid-cols-3 gap-y-20 gap-x-12">
            <?php while (have_rows('items')) : the_row(); ?>
            <div class="flex flex-col items-center text-center group">
                <div class="mb-8"><i class="<?php echo esc_attr(project_theme_section_icon()); ?> text-4xl text-brand-blue group-hover:scale-110 transition-transform" aria-hidden="true"></i></div>
                <h3 class="text-sm font-bold text-brand-blue mb-6 uppercase tracking-wider leading-tight h-10 flex items-center justify-center"><?php echo project_theme_section_heading('title'); ?></h3>
                <div class="constructor-copy text-[12px] text-brand-blue/60 leading-relaxed px-4"><?php echo project_theme_section_rich('description'); ?></div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>
