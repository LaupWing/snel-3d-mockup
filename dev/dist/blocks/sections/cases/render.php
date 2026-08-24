<?php
/**
 * Cases — 2×2 grid of case study cards.
 *
 * Structure per card:
 *   - 1:1 aspect-ratio container
 *   - Corner teardrop icon (top-left, dark/teal)
 *   - Full-bleed image with concave top-left mask
 *   - Bottom gradient + title/meta/result overlay
 *   - Hover: image scales, external arrow slides in
 *   - Mobile: description shown below the card
 *
 * @var array $attributes
 */

defined('ABSPATH') || exit;

$show_all = ! empty($attributes['showAll']);
$cases    = function_exists('snel_get_cases') ? snel_get_cases(['posts_per_page' => $show_all ? -1 : 4]) : [];

if (empty($cases)) {
    if (current_user_can('edit_posts')) {
        echo '<p class="py-10 text-center text-sm text-gray-400">' . esc_html(snel__('Voeg cases toe via het Cases menu om dit blok te vullen.')) . '</p>';
    }
    return;
}

$arrow_corner = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4 lg:size-6"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 4.5 15 15m0 0V8.25m0 11.25H8.25"/></svg>';
$arrow_hover  = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-8 text-white"><path fill-rule="evenodd" d="M5.22 14.78a.75.75 0 0 0 1.06 0l7.22-7.22v5.69a.75.75 0 0 0 1.5 0v-7.5a.75.75 0 0 0-.75-.75h-7.5a.75.75 0 0 0 0 1.5h5.69l-7.22 7.22a.75.75 0 0 0 0 1.06Z" clip-rule="evenodd"/></svg>';
?>
<section data-seo-content class="snel-cases <?php echo esc_attr(snel_section_class($attributes)); ?>"<?php echo snel_section_style($attributes); ?>>
    <div class="mx-auto w-full max-w-5xl px-4 md:px-8 <?php echo snel_section_padding($attributes); ?>">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

            <?php foreach ($cases as $i => $case) :
                $client   = get_post_meta($case->ID, '_case_client', true);
                $services = get_post_meta($case->ID, '_case_services', true) ?: [];
                $result   = get_post_meta($case->ID, '_case_result', true);
                $live_url = get_post_meta($case->ID, '_case_url', true);
                $img      = get_the_post_thumbnail_url($case->ID, 'large');
                $title    = get_the_title($case->ID);
                $href     = get_permalink($case->ID);
                $delay    = $i * 100;

                $meta_line = implode(' — ', array_filter([$client, implode(', ', (array) $services)]));
            ?>
            <div>

                <div class="snel-case-card relative w-full" style="aspect-ratio:1/1;transition-delay:<?php echo esc_attr($delay); ?>ms">

                    <?php /* Corner teardrop icon */ ?>
                    <div class="absolute left-0 top-0 z-[12] flex overflow-hidden rounded-tl-xl transition duration-500">
                        <div class="flex size-12 items-center justify-center rounded-full rounded-tl-xl bg-slate-950 text-teal-400 lg:size-20">
                            <?php echo $arrow_corner; ?>
                        </div>
                    </div>

                    <a href="<?php echo esc_url($href); ?>"
                       title="<?php echo esc_attr($title); ?>"
                       class="snel-case-link group absolute inset-0 overflow-hidden rounded-lg [text-shadow:0_1px_3px_rgba(0,0,0,0.25)]">

                        <?php if ($img) : ?>
                            <img loading="lazy" class="absolute inset-0 z-0 size-full scale-100 object-cover object-center transition duration-500 group-hover:scale-[1.025]" src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($title); ?>"/>
                        <?php else : ?>
                            <img class="absolute inset-0 z-0 size-full object-cover object-center" src="<?php echo esc_url('https://picsum.photos/seed/' . $case->ID . '/800/800'); ?>" alt=""/>
                        <?php endif; ?>

                        <div class="absolute bottom-0 left-0 right-0 z-10 space-y-2 bg-gradient-to-t from-black to-transparent p-8 pt-32 lg:space-y-3 xl:p-12 xl:pt-40">
                            <div class="inline-flex items-end gap-3">
                                <span class="block text-xl/tight font-semibold text-white md:text-2xl/tight xl:text-3xl/tight">
                                    <?php echo esc_html($title); ?>
                                </span>
                                <span class="mb-1 -translate-x-3 translate-y-3 scale-0 opacity-0 transition duration-300 group-hover:translate-x-0 group-hover:translate-y-0 group-hover:scale-100 group-hover:opacity-100">
                                    <?php echo $arrow_hover; ?>
                                </span>
                            </div>
                            <?php if ($meta_line) : ?>
                                <span class="block text-sm text-white/70"><?php echo esc_html($meta_line); ?></span>
                            <?php endif; ?>
                            <?php if ($result) : ?>
                                <span class="hidden text-sm/snug text-white/90 md:block xl:text-base/snug"><?php echo esc_html($result); ?></span>
                            <?php endif; ?>
                        </div>

                    </a>
                </div>

                <?php if ($result) : ?>
                    <p class="mt-4 px-2 text-sm/snug text-white/70 md:hidden"><?php echo esc_html($result); ?></p>
                <?php endif; ?>

            </div>
            <?php endforeach; ?>

        </div>

        <?php if (! $show_all) :
            $cases_url  = get_post_type_archive_link('case') ?: '/cases/';
            $arrow_path = '<path fill-rule="evenodd" d="M5.22 14.78a.75.75 0 0 0 1.06 0l7.22-7.22v5.69a.75.75 0 0 0 1.5 0v-7.5a.75.75 0 0 0-.75-.75h-7.5a.75.75 0 0 0 0 1.5h5.69l-7.22 7.22a.75.75 0 0 0 0 1.06Z" clip-rule="evenodd"/>';
        ?>
        <div class="mt-10 flex justify-center">
            <a href="<?php echo esc_url($cases_url); ?>" class="inline-flex">
                <span class="group relative inline-flex animate-glow-pulse cursor-pointer overflow-hidden rounded-full p-[3px] transition-transform hover:scale-[1.02] active:scale-[0.98] h-12">
                    <span class="snel-gradient-ring absolute inset-0 rounded-full"></span>
                    <span class="relative inline-flex items-center justify-center gap-3 whitespace-nowrap rounded-full bg-slate-950 pl-6 pr-2 text-base font-semibold text-white">
                        <?php echo esc_html(snel__('Meer cases bekijken')); ?>
                        <span class="flex size-8 items-center justify-center rounded-full bg-teal-400 text-slate-950">
                            <span class="relative block size-4 overflow-hidden">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="absolute inset-0 size-4 transition-transform duration-300 ease-out group-hover:-translate-y-[150%]"><?php echo $arrow_path; ?></svg>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="absolute inset-0 size-4 translate-y-[150%] transition-transform duration-300 ease-out group-hover:translate-y-0"><?php echo $arrow_path; ?></svg>
                            </span>
                        </span>
                    </span>
                </span>
            </a>
        </div>
        <?php endif; ?>

    </div>
</section>
