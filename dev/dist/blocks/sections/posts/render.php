<?php
/**
 * Posts — horizontal scroll carousel of recent blog posts.
 *
 * @var array $attributes
 */

defined('ABSPATH') || exit;

$count   = max(1, intval($attributes['count'] ?? 6));
$heading = $attributes['heading'] ?? '';
$intro   = $attributes['intro'] ?? '';

$query_args = [
    'post_type'      => 'post',
    'posts_per_page' => $count,
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC',
];

if (is_category()) {
    $query_args['cat'] = get_queried_object_id();
}

$posts = get_posts($query_args);

if (empty($posts)) {
    if (current_user_can('edit_posts')) {
        echo '<p class="py-10 text-center text-sm text-gray-400">' . esc_html(snel__('Publiceer blogberichten om dit blok te vullen.')) . '</p>';
    }
    return;
}

$arrow_svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4 transition duration-300 group-hover:rotate-[-45deg]"><path fill-rule="evenodd" d="M2 8a.75.75 0 0 1 .75-.75h8.69L8.22 4.03a.75.75 0 0 1 1.06-1.06l4.5 4.5a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 0 1-1.06-1.06l3.22-3.22H2.75A.75.75 0 0 1 2 8Z" clip-rule="evenodd"/></svg>';
?>
<section data-seo-content class="snel-posts <?php echo esc_attr(snel_section_class($attributes)); ?> <?php echo snel_section_padding($attributes); ?>"<?php echo snel_section_style($attributes); ?>>

    <?php if ($heading || $intro) : ?>
    <div class="px-4 md:px-8 xl:px-16 2xl:px-32">
        <div class="mx-auto mb-8 w-full max-w-5xl px-4 md:px-8 lg:mb-12 lg:text-balance">
            <?php if ($heading) : ?>
                <h2 class="text-2xl font-semibold text-slate-900 md:text-3xl"><?php echo esc_html($heading); ?></h2>
            <?php endif; ?>
            <?php if ($intro) : ?>
                <p class="mt-3 text-slate-500"><?php echo esc_html($intro); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="snel-posts-carousel relative z-10">

        <div class="snel-posts-track flex gap-4 lg:gap-8 -my-8 overflow-x-auto overflow-y-hidden scroll-smooth snap-x snap-mandatory overscroll-x-contain w-screen"
             style="
                 margin-left: calc(50% - 50vw);
                 margin-right: calc(50% - 50vw);
                 --rail: min(64rem, 100vw);
                 --gutter: calc((100vw - var(--rail)) / 2);
                 --safe: 1rem;
                 padding-top: 2rem;
                 padding-bottom: 2rem;
                 padding-left: max(var(--gutter), var(--safe));
                 padding-right: max(var(--gutter), var(--safe));
                 scroll-padding-left: max(var(--gutter), var(--safe));
                 scroll-padding-right: max(var(--gutter), var(--safe));
             ">

            <?php foreach ($posts as $post) :
                $img     = get_the_post_thumbnail_url($post->ID, 'large');
                $title   = get_the_title($post->ID);
                $excerpt = get_the_excerpt($post->ID);
                $href    = get_permalink($post->ID);
                $cats    = get_the_category($post->ID);
                $cat_labels = implode('&nbsp;&nbsp;·&nbsp;&nbsp;', array_map(
                    fn($c) => esc_html($c->name),
                    array_slice($cats, 0, 2)
                ));
            ?>
            <div class="snel-post-reveal snap-start flex shrink-0 w-[85%] sm:w-[70%] md:w-[44%] lg:w-[420px]">
                <a class="snel-post-card group flex flex-col w-full bg-white border border-slate-200 rounded-lg transition hover:shadow-[0px_8px_24px_rgba(34,42,53,0.08)]"
                   href="<?php echo esc_url($href); ?>"
                   title="<?php echo esc_attr($title); ?>">

                    <div class="aspect-[16/9] relative w-full rounded-t-lg overflow-hidden">
                        <img loading="lazy"
                             class="absolute inset-0 z-0 size-full object-cover object-center scale-100 group-hover:scale-[1.05] transition duration-500"
                             src="<?php echo esc_url($img ?: 'https://picsum.photos/seed/' . $post->ID . '/640/360'); ?>"
                             alt="<?php echo esc_attr($title); ?>" />
                    </div>

                    <div class="grow flex flex-col justify-between p-6 space-y-6 lg:p-8">

                        <div class="space-y-2">
                            <?php if ($cat_labels) : ?>
                                <span class="block text-xs text-slate-400 antialiased lg:text-sm"><?php echo $cat_labels; ?></span>
                            <?php endif; ?>
                            <span class="block text-base font-semibold text-slate-900 group-hover:text-brand-primary transition duration-300 line-clamp-2"><?php echo esc_html($title); ?></span>
                            <?php if ($excerpt) : ?>
                                <p class="text-sm text-slate-500 antialiased line-clamp-2 lg:text-base"><?php echo esc_html($excerpt); ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="flex">
                            <span class="inline-flex items-center gap-2 rounded-md border border-brand-primary/30 bg-white px-4 h-10 text-sm font-medium text-brand-primary antialiased whitespace-nowrap transition-all duration-300 group-hover:bg-brand-primary group-hover:text-white group-hover:border-brand-primary">
                                <?php echo esc_html(snel__('Lees artikel')); ?>
                                <?php echo $arrow_svg; ?>
                            </span>
                        </div>

                    </div>

                </a>
            </div>
            <?php endforeach; ?>

        </div>

        <div class="snel-posts-nav px-4 md:px-8 xl:px-16 2xl:px-32">
            <div class="mx-auto mt-6 w-full max-w-5xl px-4 md:px-8">
                <div class="flex items-center gap-8 lg:gap-16">

                    <button type="button"
                            class="snel-posts-prev shrink-0 size-8 inline-flex items-center justify-center rounded-md border border-brand-primary/30 bg-white text-brand-primary transition hover:bg-brand-primary hover:text-white hover:border-brand-primary disabled:opacity-30 disabled:cursor-not-allowed"
                            aria-label="Vorige" disabled>
                        <svg viewBox="0 0 24 24" class="size-5"><path fill="currentColor" d="M15.41 7.41 14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
                    </button>

                    <div class="snel-posts-indicator grow h-0.5 w-full overflow-hidden rounded-full bg-brand-primary/20">
                        <div class="snel-posts-thumb h-full rounded-full bg-brand-primary will-change-transform" style="width:0;transform:translateX(0)"></div>
                    </div>

                    <button type="button"
                            class="snel-posts-next shrink-0 size-8 inline-flex items-center justify-center rounded-md border border-brand-primary/30 bg-white text-brand-primary transition hover:bg-brand-primary hover:text-white hover:border-brand-primary disabled:opacity-30 disabled:cursor-not-allowed"
                            aria-label="Volgende">
                        <svg viewBox="0 0 24 24" class="size-5"><path fill="currentColor" d="m8.59 16.59 1.41 1.41 6-6-6-6-1.41 1.41L13.17 12z"/></svg>
                    </button>

                </div>
            </div>
        </div>

    </div>

</section>
