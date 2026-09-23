<?php
$section_id = project_theme_constructor_anchor(get_row_layout(), (string) $args);
$has_stats = (bool) get_sub_field('stats');
?>
<section id="<?php echo esc_attr($section_id); ?>" class="inner-hero bg-brand-lightblue py-24 px-6 md:px-16 overflow-hidden relative <?php echo $has_stats ? 'min-h-[500px]' : 'min-h-[600px]'; ?> flex items-center">
    <div class="absolute inset-0 z-0"><?php echo project_theme_section_image('background', 'w-full h-full object-cover opacity-20'); ?></div>
    <div class="max-w-screen-xl mx-auto flex flex-col md:flex-row items-center gap-16 relative z-10">
        <div class="flex-1 text-center md:text-left">
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/50 backdrop-blur-sm rounded-full border border-brand-line mb-6">
                <span class="w-2 h-2 bg-brand-orange rounded-full"></span>
                <span class="text-[10px] font-bold uppercase tracking-wider text-brand-blue"><?php echo project_theme_section_text('eyebrow'); ?></span>
            </div>
            <h1 class="text-5xl md:text-7xl font-black text-brand-blue mb-8 leading-[1.1]"><?php echo project_theme_section_rich('title'); ?></h1>
            <div class="constructor-copy text-lg text-brand-blue/60 leading-relaxed max-w-lg mb-10"><?php echo project_theme_section_rich('description'); ?></div>
            <?php if (have_rows('stats')) : ?>
                <div class="about-water-stats">
                    <?php while (have_rows('stats')) : the_row(); ?>
                        <div><span><?php echo project_theme_section_text('value'); ?></span><strong><?php echo project_theme_section_text('label'); ?></strong></div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
            <button data-modal-open="<?php echo esc_attr(project_theme_constructor_modal_id()); ?>" class="<?php echo $has_stats ? 'about-water-cta' : 'px-10 py-5 bg-brand-blue text-white rounded-2xl font-bold shadow-2xl shadow-brand-blue/20 hover:bg-blue-700 transition-all hover:scale-105 active:scale-95 flex items-center gap-3 mx-auto md:mx-0'; ?>">
                <?php echo project_theme_section_text('button_text'); ?><?php if (!$has_stats) : ?> <i class="fa-solid fa-arrow-right" aria-hidden="true"></i><?php endif; ?>
            </button>
        </div>
        <div class="flex-1 relative flex justify-center"><div class="relative w-full max-w-lg"><?php echo project_theme_section_image('image', 'banner-bottle-hover w-full h-auto drop-shadow-3xl'); ?></div></div>
    </div>
</section>
