<?php
/**
 * Panel — Server-side render.
 *
 * Wraps the block's InnerBlocks ($content) in the shared framed card
 * (snel_panel_open/close) on the shared beams + mesh background
 * (snel_background_open/close) — the exact same shell as the hero.
 *
 * When rounded corners is active the section gets the OPPOSITE background for
 * contrast, and the panel content sits in a rounded inner wrapper with its own bg.
 *
 * @var array    $attributes
 * @var string   $content
 * @var WP_Block $block
 */

defined('ABSPATH') || exit;

$snel_theme    = $attributes['theme'] ?? 'white';
$snel_is_dark  = in_array($snel_theme, ['dark', 'canvas'], true);
$snel_rounded  = ! empty($attributes['rounded']);
$snel_beams    = $attributes['showBeams']    ?? true;
$snel_gradient = $attributes['showGradient'] ?? true;

$theme_bg = ['dark' => '#2e1065', 'canvas' => '#020617', 'white' => '#ffffff'][$snel_theme] ?? '#ffffff';

if ($snel_theme === 'canvas')    $snel_panel_fade = 'from-[#020617]';
elseif ($snel_theme === 'dark')  $snel_panel_fade = 'from-[#2e1065]';
else                             $snel_panel_fade = 'from-white';

if ($snel_rounded) {
	$section_bg    = $snel_is_dark ? '#ffffff' : '#2e1065';
	$section_class = $snel_is_dark ? 'bg-white' : '';
	$inner_class   = 'rounded-t-2xl overflow-hidden ' . ($snel_is_dark ? 'is-dark' : 'bg-white');
	$inner_style   = 'background-color:' . ($snel_is_dark ? $theme_bg : '#ffffff');
} else {
	$section_bg    = $snel_is_dark ? $theme_bg : '#ffffff';
	if ($snel_theme === 'canvas')   $section_class = 'is-dark bg-canvas';
	elseif ($snel_is_dark)          $section_class = 'is-dark';
	else                            $section_class = 'bg-white';
	$inner_class = '';
	$inner_style = '';
}
?>
<section data-seo-content class="snel-panel relative <?php echo esc_attr($section_class); ?>" style="background-color:<?php echo esc_attr($section_bg); ?>">
	<?php if ($snel_rounded) : ?>
	<div class="<?php echo esc_attr($inner_class); ?>" style="<?php echo esc_attr($inner_style); ?>">
	<?php endif; ?>
		<?php snel_background_open(['position' => 'absolute', 'backdrop' => 'transparent', 'fade' => $snel_panel_fade, 'beams' => $snel_beams, 'gradient' => $snel_gradient]); ?>
			<div class="px-4 md:px-8 <?php echo snel_section_padding($attributes); ?>">
				<?php
			$cw = ($attributes['contentWidth'] ?? 'none') !== 'none' ? ' snel-cw-' . esc_attr($attributes['contentWidth']) : '';
			snel_panel_open(['dark' => $snel_is_dark, 'inner_class' => 'gap-8 xl:gap-12 snel-justify-' . esc_attr($attributes['justify'] ?? 'start') . $cw]);
			?>
					<?php echo $content; ?>
				<?php snel_panel_close(); ?>
			</div>
		<?php snel_background_close(); ?>
	<?php if ($snel_rounded) : ?>
	</div>
	<?php endif; ?>
</section>
