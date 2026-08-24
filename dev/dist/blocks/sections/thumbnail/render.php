<?php
/**
 * Thumbnail — full-width featured image with breadcrumb bar + info cards.
 *
 * @var array $attributes
 */

defined('ABSPATH') || exit;

$post_id   = get_the_ID();
$title     = get_the_title($post_id);
$img_url   = get_the_post_thumbnail_url($post_id, 'full');
$back_url  = $attributes['backUrl']   ?? '';
$back_label = $attributes['backLabel'] ?? 'Terug';

// Only the technology card
$tech_label = trim($attributes['label3'] ?? '');
$tech_value = trim($attributes['value3'] ?? '');

// Breadcrumb: build from current URL path
$breadcrumbs = [];
if ($back_url) {
    // Walk up: Home → parent page → current
    $home = home_url('/');
    $breadcrumbs[] = ['label' => snel__('Home'), 'url' => $home];
    $breadcrumbs[] = ['label' => $back_label, 'url' => $back_url];
    $breadcrumbs[] = ['label' => $title, 'url' => ''];
}
$bg = $attributes['bg'] ?? 'white';
?>
<section class="snel-thumbnail <?php echo esc_attr(snel_section_class(['bg' => $bg])); ?>"<?php echo snel_section_style(['bg' => $bg]); ?>>
<div class="mx-auto w-full max-w-7xl px-4 md:px-8 flex flex-col relative gap-4 lg:gap-8">

    <?php if ($breadcrumbs) : ?>
    <div class="hidden md:block absolute z-10 top-0 left-1/2 -translate-x-1/2 mt-10 md:mt-16 w-full max-w-7xl px-8 md:px-12 lg:px-16">
        <div class="flex justify-between py-2 px-3 lg:py-4 lg:px-6 border border-white/10 rounded-full bg-black/25 backdrop-blur-sm">
            <nav aria-label="Breadcrumb" class="hidden md:flex">
                <ol role="list" class="flex items-center gap-0 text-white/50">
                    <?php foreach ($breadcrumbs as $crumb) : ?>
                        <li class="flex items-center">
                            <?php if ($crumb['url']) : ?>
                                <a href="<?php echo esc_url($crumb['url']); ?>" class="text-sm lg:text-base font-normal hover:text-white transition-colors">
                                    <?php echo esc_html($crumb['label']); ?>
                                </a>
                                <span class="mx-4 text-white/25">/</span>
                            <?php else : ?>
                                <span aria-current="page" class="text-sm lg:text-base font-normal text-white">
                                    <?php echo esc_html($crumb['label']); ?>
                                </span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </nav>
            <nav aria-label="Back">
                <a href="<?php echo esc_url($back_url); ?>" class="text-sm lg:text-base font-normal underline-offset-2 hover:underline text-white transition-colors">
                    <?php echo esc_html(sprintf(snel__('Terug naar %s'), $back_label)); ?>
                </a>
            </nav>
        </div>
    </div>
    <?php endif; ?>

    <div class="relative rounded-xl overflow-hidden shadow-inner shadow min-h-[50dvh] w-full bg-slate-900">
        <?php if ($img_url) : ?>
            <img
                src="<?php echo esc_url($img_url); ?>"
                alt="<?php echo esc_attr($title); ?>"
                class="w-full object-cover object-center aspect-[4/5] md:aspect-auto"
                loading="eager"
                fetchpriority="high"
            />
        <?php endif; ?>

        <?php if ($tech_label && $tech_value) : ?>
        <div class="absolute bottom-0 left-0 right-0 z-10 p-4 lg:p-8">
            <div class="inline-flex rounded-xl p-4 lg:p-6 border border-white/10 bg-black/25 backdrop-blur-sm">
                <div>
                    <span class="block text-xs font-normal text-white/50 sm:text-sm"><?php echo esc_html($tech_label); ?></span>
                    <span class="mt-1 block text-base font-normal text-white sm:text-lg md:text-xl lg:text-2xl"><?php echo esc_html($tech_value); ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

</div>
</section>
