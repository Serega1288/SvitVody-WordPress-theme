<section id="<?php echo esc_attr(project_theme_constructor_anchor(get_row_layout(), (string) $args)); ?>" class="constructor-work-steps py-24 px-6 md:px-16 bg-white">
    <div class="max-w-screen-xl mx-auto text-center">
        <h2 class="text-4xl md:text-5xl font-bold mb-20 text-brand-blue uppercase tracking-tight"><?php echo project_theme_section_heading('title'); ?></h2>
        <div class="grid md:grid-cols-4 gap-12">
            <?php while (have_rows('items')) : the_row(); ?>
            <div class="group">
                <div class="w-24 h-24 mx-auto bg-brand-darkblue rounded-[32px] flex items-center justify-center mb-8 shadow-xl transform group-hover:scale-110 transition-transform duration-500"><i class="<?php echo esc_attr(project_theme_section_icon()); ?> text-3xl text-brand-orange" aria-hidden="true"></i></div>
                <h3 class="text-xl font-bold mb-3 text-brand-blue"><?php echo project_theme_section_heading('title'); ?></h3>
                <div class="constructor-copy text-sm text-brand-blue/60 leading-relaxed px-2"><?php echo project_theme_section_rich('description'); ?></div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>
