<?php
/**
 * Category Nav — scrollable category filter bar with archive links.
 * Active state is auto-detected: highlights current category on archive pages.
 *
 * @var array $attributes
 */

defined('ABSPATH') || exit;

$all_label = $attributes['allLabel'] ?? 'Alle';
$post_type = $attributes['postType'] ?? 'post';

// Get all categories that have published posts of this post type.
$cats = get_categories([
    'taxonomy'   => 'category',
    'hide_empty' => true,
    'orderby'    => 'name',
    'order'      => 'ASC',
]);

if (empty($cats)) {
    return;
}

// For 'post' the archive is the posts page (Settings → Reading), which the
// translations plugin keeps language-aware. Don't hardcode /blog/ as a fallback —
// that ignores both a renamed posts page and the language prefix.
$posts_page  = (int) get_option('page_for_posts');
$archive_url = ($post_type === 'post' && $posts_page)
    ? get_permalink($posts_page)
    : (get_post_type_archive_link($post_type) ?: home_url('/'));
$current_cat_id = is_category() ? get_queried_object_id() : 0;

$inactive_class = 'scroll-mx-2 snap-start shrink-0 whitespace-nowrap font-medium antialiased text-sm px-4 h-11 flex items-center transition duration-300 rounded-md text-slate-700 hover:bg-white hover:shadow-[0px_4px_8px_rgba(34,42,53,0.05),0px_0px_0px_1px_rgba(34,42,53,0.04),0px_1px_5px_-4px_rgba(19,19,22,0.7)]';
$active_class   = 'scroll-mx-2 snap-start shrink-0 whitespace-nowrap font-medium antialiased text-sm px-4 h-11 flex items-center transition duration-300 rounded-md bg-slate-950 text-teal-400';
?>
<nav class="snel-category-nav flex overflow-x-auto snap-x w-full max-w-max rounded-lg bg-slate-100 p-1 space-x-1">

    <a href="<?php echo esc_url($archive_url); ?>"
       class="<?php echo esc_attr($current_cat_id === 0 ? $active_class : $inactive_class); ?>">
        <?php echo esc_html($all_label); ?>
    </a>

    <?php foreach ($cats as $cat) :
        $is_active = $current_cat_id === $cat->term_id;
    ?>
    <a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>"
       class="<?php echo esc_attr($is_active ? $active_class : $inactive_class); ?>">
        <?php echo esc_html($cat->name); ?>
    </a>
    <?php endforeach; ?>

</nav>
