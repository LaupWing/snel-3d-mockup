<?php
/**
 * Partners — server render.
 *
 * Auto-scrolling marquee of logos from the Partners CPT. The track holds the
 * logo set twice and animates -50% (CSS `marquee`) for a seamless loop; it
 * pauses on hover. Each logo links to its partner URL. Edges fade via a mask.
 *
 * @var array $attributes
 */

defined('ABSPATH') || exit;

$animated = ! empty($attributes['animated']);
$count    = (int) ($attributes['count'] ?? 0);

$all_partners = function_exists('snel_get_partners') ? snel_get_partners() : [];
$partners     = ($count > 0) ? array_slice($all_partners, 0, $count) : $all_partners;

// Build logo items (skip partners without a logo).
$items = '';
foreach ($partners as $p) {
	$img = get_the_post_thumbnail_url($p, 'medium');
	if (! $img) continue;
	$name  = get_the_title($p);
	$url   = snel_partner_url($p->ID);
	$cls   = $animated
		? 'flex h-8 w-max shrink-0 items-center justify-center px-6 opacity-60 grayscale transition duration-300 hover:opacity-100 hover:grayscale-0 lg:px-10'
		: 'flex h-10 items-center justify-center px-6 opacity-60 grayscale transition duration-300 hover:opacity-100 hover:grayscale-0';
	$inner = '<img class="h-full w-auto object-contain" src="' . esc_url($img) . '" alt="' . esc_attr($name) . '" fetchpriority="low" />';
	$wrap  = $url
		? '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer" title="' . esc_attr($name) . '" class="' . $cls . '">' . $inner . '</a>'
		: '<div class="' . $cls . '">' . $inner . '</div>';
	$items .= $wrap;
}

if ($items === '') {
	if (current_user_can('edit_posts')) {
		echo '<p class="py-10 text-center text-sm text-gray-400">' . esc_html(snel__('Add some Partners (with a logo) under the Partners menu to fill this marquee.')) . '</p>';
	}
	return;
}
?>
<section data-seo-content class="snel-partners <?php echo snel_section_padding($attributes); ?> <?php echo snel_section_class($attributes); ?>"<?php echo snel_section_style($attributes); ?>>
<?php if ($animated) : ?>
	<div class="group relative overflow-hidden" style="-webkit-mask-image:linear-gradient(to right,transparent,#000 8%,#000 92%,transparent);mask-image:linear-gradient(to right,transparent,#000 8%,#000 92%,transparent)">
		<div class="flex w-max animate-marquee items-center group-hover:[animation-play-state:paused]">
			<?php echo $items; // first copy ?>
			<?php echo $items; // second copy → seamless loop ?>
		</div>
	</div>
<?php else : ?>
	<div class="flex flex-wrap items-center justify-center gap-y-4">
		<?php echo $items; ?>
	</div>
<?php endif; ?>
</section>
