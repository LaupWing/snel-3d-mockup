<?php
/**
 * Snel Button — simple brand button, outline or filled. Extracted from the hero.
 *
 * @var array    $attributes
 * @var string   $content
 * @var WP_Block $block
 */

defined('ABSPATH') || exit;

$label   = $attributes['label']   ?? '';
$url     = $attributes['url']     ?? '#';
$variant = $attributes['variant'] ?? 'outline';

// One brand colour; swap fill vs outline.
$variant_class = $variant === 'filled'
	? 'border-2 border-brand-primary bg-brand-primary text-white hover:bg-brand-primary/90'
	: 'border-2 border-brand-primary/40 bg-white text-brand-primary hover:bg-brand-primary hover:text-white';

$work_path = '<path fill-rule="evenodd" d="M2 8a.75.75 0 0 1 .75-.75h8.69L8.22 4.03a.75.75 0 0 1 1.06-1.06l4.5 4.5a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 0 1-1.06-1.06l3.22-3.22H2.75A.75.75 0 0 1 2 8Z" clip-rule="evenodd"/>';
?>
<a href="<?php echo esc_url($url); ?>" class="group inline-flex h-[46px] items-center gap-2 rounded-md px-4 transition-all duration-300 <?php echo esc_attr($variant_class); ?>">
	<span class="whitespace-nowrap font-medium"><?php echo esc_html($label); ?></span>
	<span class="relative block size-4 overflow-hidden">
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="absolute inset-0 size-4 transition-transform duration-300 ease-out group-hover:translate-x-[200%]"><?php echo $work_path; ?></svg>
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="absolute inset-0 size-4 translate-y-[150%] transition-transform duration-300 ease-out group-hover:translate-y-0"><?php echo $work_path; ?></svg>
	</span>
</a>
