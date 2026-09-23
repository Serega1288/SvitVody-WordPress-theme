<section id="<?php echo esc_attr(project_theme_constructor_anchor(get_row_layout(), (string) $args)); ?>" class="water-composition py-24 px-6 md:px-16 blue-gradient-bg text-white overflow-hidden">
    <div class="absolute inset-0 water-texture opacity-20" style="<?php echo esc_attr(project_theme_section_background()); ?>"></div>
    <div class="absolute inset-0 molecule-pattern opacity-10"></div>
    <div class="max-w-screen-xl mx-auto relative z-10">
        <h2 class="text-3xl md:text-4xl font-black text-center mb-24 uppercase tracking-widest"><?php echo project_theme_section_heading('title'); ?></h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12 mb-20 max-w-5xl mx-auto">
            <?php while (have_rows('primary')) : the_row(); ?>
            <div class="flex flex-col items-center text-center group"><div class="w-48 h-48 composition-circle rounded-full flex flex-col items-center justify-center mb-6">
                <span class="text-[10px] font-bold uppercase tracking-widest mb-2 px-4 leading-tight"><?php echo project_theme_section_text('label'); ?></span>
                <div class="text-xl italic font-medium"><?php echo project_theme_section_text('value'); ?></div>
                <div class="text-[10px] uppercase font-bold tracking-widest mt-1"><?php echo project_theme_section_text('unit'); ?></div>
            </div></div>
            <?php endwhile; ?>
        </div>
        <div class="flex flex-wrap justify-center gap-8 mb-24">
            <?php while (have_rows('minerals')) : the_row(); ?>
            <div class="flex flex-col items-center text-center group"><div class="w-36 h-36 composition-circle rounded-full flex flex-col items-center justify-center">
                <span class="text-sm font-bold mb-1"><?php echo project_theme_section_text('label'); ?></span>
                <div class="text-base italic"><?php echo project_theme_section_text('value'); ?></div>
                <div class="text-[8px] uppercase font-bold tracking-widest mt-1"><?php echo project_theme_section_text('unit'); ?></div>
            </div></div>
            <?php endwhile; ?>
        </div>
        <div class="max-w-4xl mx-auto text-center px-6">
            <div class="constructor-copy text-lg md:text-xl font-medium leading-relaxed mb-8"><?php echo project_theme_section_rich('description'); ?></div>
            <div class="text-2xl md:text-3xl font-black uppercase tracking-tighter"><?php echo project_theme_section_heading('closing'); ?></div>
        </div>
    </div>
    <div class="absolute bottom-10 left-10 w-24 h-24 opacity-20 hidden lg:block" aria-hidden="true"><svg viewBox="0 0 100 100" class="w-full h-full"><path d="M50 0 C70 40 90 60 90 80 A40 40 0 0 1 10 80 C10 60 30 40 50 0" fill="white" /></svg></div>
    <div class="absolute top-20 right-10 w-32 h-32 opacity-10 hidden lg:block" aria-hidden="true"><svg viewBox="0 0 100 100" class="w-full h-full rotate-45"><path d="M50 0 C70 40 90 60 90 80 A40 40 0 0 1 10 80 C10 60 30 40 50 0" fill="white" /></svg></div>
</section>
