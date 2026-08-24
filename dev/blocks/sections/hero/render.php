<?php
/**
 * Hero 2 — framed shell with 3 fixed slots (eyebrow / middle / lower).
 *
 * The frame (beams bg + card + stack corners) is the shared hero/panel shell
 * (snel_background_open + snel_panel_open). The 3 slots are InnerBlocks groups
 * persisted in $content; the user fills each with any blocks.
 *
 * @var array    $attributes
 * @var string   $content
 * @var WP_Block $block
 */

defined('ABSPATH') || exit;
?>
<section data-seo-content class="snel-hero relative<?php echo ! empty($attributes['fullHeight']) ? ' snel-hero--full' : ''; ?>">
	<?php snel_background_open([
		'position' => 'absolute',
		'backdrop' => 'white',
		'beams'    => $attributes['showBeams']    ?? true,
		'gradient' => $attributes['showGradient'] ?? true,
	]); ?>
		<?php
		$padding = ! empty($attributes['fullHeight'])
			? 'px-4 md:px-8'
			: 'px-4 md:px-8 pt-40 lg:pt-44 ' . snel_section_padding(array_merge($attributes, ['disableTop' => true]));
		?>
		<div class="<?php echo esc_attr($padding); ?>">
			<?php
		$cw = ($attributes['contentWidth'] ?? 'none') !== 'none' ? ' snel-cw-' . esc_attr($attributes['contentWidth']) : '';
		snel_panel_open(['inner_class' => 'gap-8 xl:gap-12 snel-justify-' . esc_attr($attributes['justify'] ?? 'start') . $cw]);
		?>
				<?php echo $content; ?>
			<?php snel_panel_close(); ?>
		</div>
	<?php snel_background_close(); ?>
</section>
