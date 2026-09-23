<section id="<?php echo esc_attr(project_theme_constructor_anchor(get_row_layout(), (string) $args)); ?>" class="office-usecases py-24 px-6 md:px-16 blue-gradient-bg text-white overflow-hidden">
    <div class="absolute inset-0 water-texture" style="<?php echo esc_attr(project_theme_section_background()); ?>"></div>
    <div class="max-w-screen-xl mx-auto relative z-10">
        <h2 class="text-2xl md:text-3xl font-black text-center mb-20 uppercase tracking-widest"><?php echo project_theme_section_heading('title'); ?></h2>
        <div class="grid md:grid-cols-3 gap-y-20 gap-x-12">
            <?php while (have_rows('items')) : the_row(); ?>
            <div class="flex flex-col items-center text-center group">
                <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center mb-8 shadow-2xl group-hover:scale-110 transition-all duration-500"><i class="<?php echo esc_attr(project_theme_section_icon()); ?> text-4xl text-brand-blue" aria-hidden="true"></i></div>
                <h3 class="text-lg font-bold mb-4 uppercase tracking-wider"><?php echo project_theme_section_heading('title'); ?></h3>
                <div class="constructor-copy text-sm text-white/80 leading-relaxed px-4"><?php echo project_theme_section_rich('description'); ?></div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>
