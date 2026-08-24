<?php
/**
 * Posts Archive — paginated grid for blog/category pages.
 *
 * @var array $attributes
 */

defined('ABSPATH') || exit;

$per_page = max(1, intval($attributes['perPage'] ?? 9));
$paged    = max(1, (int) (get_query_var('paged') ?: get_query_var('page') ?: 1));

$query_args = [
    'post_type'      => 'post',
    'posts_per_page' => $per_page,
    'paged'          => $paged,
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC',
];

if (is_category()) {
    $query_args['cat'] = get_queried_object_id();
}

$query = new WP_Query($query_args);

if (! $query->have_posts()) {
    if (current_user_can('edit_posts')) {
        echo '<p class="py-10 text-center text-sm text-slate-400">' . esc_html(snel__('Publiceer blogberichten om dit blok te vullen.')) . '</p>';
    }
    return;
}

$arrow_svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4 transition group-hover:rotate-[-45deg] rotate-0"><path fill-rule="evenodd" d="M2 8a.75.75 0 0 1 .75-.75h8.69L8.22 4.03a.75.75 0 0 1 1.06-1.06l4.5 4.5a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 0 1-1.06-1.06l3.22-3.22H2.75A.75.75 0 0 1 2 8Z" clip-rule="evenodd"/></svg>';

$btn = '<span class="inline-flex group space-x-2 px-4 items-center h-11 border border-brand-primary/25 bg-white hover:bg-brand-primary text-brand-primary hover:text-white rounded-md transition-all duration-300">'
     . '<span class="text-sm font-medium antialiased whitespace-nowrap">' . esc_html(snel__('Lees artikel')) . '</span>'
     . '<span class="mt-0.5">' . $arrow_svg . '</span>'
     . '</span>';

// Pagination
$pagination = '';
if ($query->max_num_pages > 1) {
    // Build base URL explicitly so it works whether rendered on home or category.
    if (is_category()) {
        $archive_base = trailingslashit(get_category_link(get_queried_object_id()));
    } else {
        $archive_base = trailingslashit(get_permalink(get_option('page_for_posts')));
    }

    $links = paginate_links([
        'base'      => $archive_base . 'page/%#%/',
        'format'    => '',
        'current'   => $paged,
        'total'     => $query->max_num_pages,
        'type'      => 'array',
        'prev_next' => false,
        'mid_size'  => 2,
        'end_size'  => 1,
    ]);

    $nums = '';
    if ($links) {
        foreach ($links as $link) {
            if (strpos($link, 'current') !== false) {
                $nums .= '<span aria-current="page" class="inline-flex items-center bg-slate-100 py-1 lg:py-2 px-3 lg:px-4 rounded-md text-sm lg:text-base text-slate-950">' . strip_tags($link) . '</span>';
            } elseif (strpos($link, 'dots') !== false) {
                $nums .= '<span class="inline-flex items-center py-1 lg:py-2 px-3 lg:px-4 rounded-md text-sm lg:text-base text-slate-400">...</span>';
            } else {
                preg_match('/href="([^"]+)"/', $link, $m);
                $url   = $m[1] ?? '#';
                $nums .= '<a href="' . esc_url($url) . '" class="inline-flex items-center py-1 lg:py-2 px-3 lg:px-4 rounded-md text-sm lg:text-base text-slate-400 border border-transparent hover:border-brand-primary/25 hover:text-brand-primary">' . strip_tags($link) . '</a>';
            }
        }
    }

    $arrow_nav = '<svg viewBox="0 0 20 20" fill="currentColor" class="lg:ml-3 size-5"><path d="M2 10a.75.75 0 0 1 .75-.75h12.59l-2.1-1.95a.75.75 0 1 1 1.02-1.1l3.5 3.25a.75.75 0 0 1 0 1.1l-3.5 3.25a.75.75 0 1 1-1.02-1.1l2.1-1.95H2.75A.75.75 0 0 1 2 10Z"/></svg>';
    $prev_url  = $paged > 1 ? ($paged === 2 ? $archive_base : $archive_base . 'page/' . ($paged - 1) . '/') : null;
    $next_url  = $paged < $query->max_num_pages ? $archive_base . 'page/' . ($paged + 1) . '/' : null;

    $nav_btn_class = 'inline-flex group items-center gap-2 h-[46px] px-4 rounded-md text-sm font-medium text-brand-primary bg-white border-2 border-brand-primary/40 hover:bg-brand-primary hover:text-white hover:border-brand-primary transition-all duration-300 antialiased whitespace-nowrap';

    $prev_btn = $prev_url
        ? '<a href="' . esc_url($prev_url) . '" rel="prev" class="' . $nav_btn_class . '"><svg viewBox="0 0 20 20" fill="currentColor" class="size-4 rotate-180"><path d="M2 10a.75.75 0 0 1 .75-.75h12.59l-2.1-1.95a.75.75 0 1 1 1.02-1.1l3.5 3.25a.75.75 0 0 1 0 1.1l-3.5 3.25a.75.75 0 1 1-1.02-1.1l2.1-1.95H2.75A.75.75 0 0 1 2 10Z"/></svg><span class="hidden sm:inline">' . esc_html(snel__('Vorige')) . '</span></a>'
        : '<div></div>';

    $next_btn = $next_url
        ? '<a href="' . esc_url($next_url) . '" rel="next" class="' . $nav_btn_class . '"><span class="hidden sm:inline">' . esc_html(snel__('Volgende')) . '</span><svg viewBox="0 0 20 20" fill="currentColor" class="size-4"><path d="M2 10a.75.75 0 0 1 .75-.75h12.59l-2.1-1.95a.75.75 0 1 1 1.02-1.1l3.5 3.25a.75.75 0 0 1 0 1.1l-3.5 3.25a.75.75 0 1 1-1.02-1.1l2.1-1.95H2.75A.75.75 0 0 1 2 10Z"/></svg></a>'
        : '<div></div>';

    $pagination = '<nav class="flex items-center justify-between" aria-label="' . esc_attr(snel__('Paginering')) . '">'
        . '<div class="flex items-center">' . $prev_btn . '</div>'
        . '<div class="flex space-x-1 lg:space-x-2">' . $nums . '</div>'
        . '<div class="flex items-center">' . $next_btn . '</div>'
        . '</nav>';
}
?>
<section data-seo-content class="snel-posts-archive relative <?php echo esc_attr(snel_section_class($attributes)); ?>"<?php echo snel_section_style($attributes); ?>>
    <div class="px-4 md:px-8 xl:px-16 2xl:px-32 pb-8 lg:pb-24">

        <?php if ($pagination) : ?>
        <div class="pt-4 xl:pt-0 mb-8 xl:mb-16"><?php echo $pagination; ?></div>
        <?php endif; ?>

        <div class="relative z-10 gap-4 lg:gap-8 grid grid-cols-1 sm:grid-cols-2 2xl:grid-cols-3">

            <?php while ($query->have_posts()) : $query->the_post();
                $img        = get_the_post_thumbnail_url(null, 'large');
                $title      = get_the_title();
                $excerpt    = get_the_excerpt();
                $href       = get_permalink();
                $cats       = get_the_category();
                $cat_labels = implode('&nbsp;&nbsp;·&nbsp;&nbsp;', array_map(
                    fn($c) => esc_html($c->name),
                    array_slice($cats, 0, 2)
                ));
            ?>

            <div class="snel-archive-card flex w-full">
                <div class="snel-archive-inner transition duration-1000 ease-in-out grow flex w-full">

                    <?php if ($img) : ?>
                    <a class="button grow group flex flex-col w-full bg-white border border-slate-200 transition hover:shadow-[0px_4px_8px_rgba(34,42,53,0.05)] rounded-lg"
                       href="<?php echo esc_url($href); ?>"
                       title="<?php echo esc_attr($title); ?>">

                        <div class="aspect-[16/9] relative w-full rounded-t-lg overflow-hidden transition duration-300 group">
                            <img loading="lazy"
                                 class="z-0 object-cover object-center absolute rounded-t-lg w-full h-full scale-100 group-hover:scale-[1.05] transition duration-300"
                                 src="<?php echo esc_url($img); ?>"
                                 alt="<?php echo esc_attr($title); ?>" />
                        </div>

                        <div class="grow flex flex-col justify-between p-8 space-y-8">
                            <div class="space-y-2">
                                <?php if ($cat_labels) : ?>
                                <span class="text-slate-600 block text-xs lg:text-sm antialiased"><?php echo $cat_labels; ?></span>
                                <?php endif; ?>
                                <span class="text-pretty break-words block text-base font-medium group-hover:text-brand-primary transition duration-300 line-clamp-2"><?php echo esc_html($title); ?></span>
                                <?php if ($excerpt) : ?>
                                <p class="text-sm lg:text-base text-slate-600 antialiased line-clamp-2"><?php echo esc_html($excerpt); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="flex flex-none"><?php echo $btn; ?></div>
                        </div>

                    </a>

                    <?php else : ?>

                    <a class="button grow group flex w-full bg-brand-primary/[0.04] border border-slate-200 transition hover:shadow-[0px_4px_8px_rgba(34,42,53,0.05)] rounded-lg"
                       href="<?php echo esc_url($href); ?>"
                       title="<?php echo esc_attr($title); ?>">
                        <div class="py-16 px-8 lg:px-16 space-y-8 flex flex-col text-center items-center justify-center w-full">
                            <div class="space-y-2">
                                <?php if ($cat_labels) : ?>
                                <span class="text-slate-600 block text-xs lg:text-sm antialiased"><?php echo $cat_labels; ?></span>
                                <?php endif; ?>
                                <span class="text-pretty break-words block text-base lg:text-lg font-medium group-hover:text-brand-primary transition duration-300 line-clamp-2"><?php echo esc_html($title); ?></span>
                                <?php if ($excerpt) : ?>
                                <p class="text-sm lg:text-base text-slate-600 antialiased line-clamp-2"><?php echo esc_html($excerpt); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="flex flex-none justify-center"><?php echo $btn; ?></div>
                        </div>
                    </a>

                    <?php endif; ?>

                </div>
            </div>

            <?php endwhile; wp_reset_postdata(); ?>

        </div>

        <?php if ($pagination) : ?>
        <div class="mt-8 xl:mt-16"><?php echo $pagination; ?></div>
        <?php endif; ?>

    </div>
</section>
