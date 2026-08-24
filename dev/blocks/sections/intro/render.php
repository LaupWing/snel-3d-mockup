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
<?php $full_h = ! empty($attributes['fullHeight']); ?>
<?php
// Spacer: 4 horizontal wires from each side, diagonal step inward, synced beams + stop dots.
$_sp_colors  = ['#38bdf8', '#a78bfa', '#f472b6', '#2dd4bf'];
$_sp_h_a     = 290; // straight segment from edge
$_sp_h_d     = 10;  // diagonal drop
$_sp_h_b     = 200; // second straight — meets mirror at x=500 (center)
$_sp_ys      = [14, 26, 38, 50];
$_sp_stop_xs_r = []; // precomputed stop x positions per right wire
$_sp_stop_xs_l = [];
$_sp_tr = '';
$_sp_st = '';
$_sp_bm = '';
$_sp_def = '<defs><filter id="snel-sp-glow" x="-100%" y="-100%" width="300%" height="300%"><feGaussianBlur stdDeviation="2" result="b"/><feMerge><feMergeNode in="b"/><feMergeNode in="SourceGraphic"/></feMerge></filter>';
foreach ($_sp_ys as $k => $y) {
	$c  = $_sp_colors[$k];
	$ry = $y + $_sp_h_d;
	// Right wire: M 1000 y → L rx1 y → L rx2 ry → L rx3 ry
	$rx1 = 1000 - $_sp_h_a;
	$rx2 = $rx1 - $_sp_h_d;
	$rx3 = $rx2 - $_sp_h_b;
	$rd  = "M 1000 $y L $rx1 $y L $rx2 $ry L $rx3 $ry";
	$_sp_tr .= '<path class="snel-bg-trace" d="' . $rd . '"/>';
	$_sp_tr .= '<circle class="snel-bg-pad" cx="' . $rx3 . '" cy="' . $ry . '" r="2"/>';
	// Left wire: M 0 y → L lx1 y → L lx2 ry → L lx3 ry
	$lx1 = $_sp_h_a;
	$lx2 = $lx1 + $_sp_h_d;
	$lx3 = $lx2 + $_sp_h_b;
	$ld  = "M 0 $y L $lx1 $y L $lx2 $ry L $lx3 $ry";
	$_sp_tr .= '<path class="snel-bg-trace" d="' . $ld . '"/>';
	$_sp_tr .= '<circle class="snel-bg-pad" cx="' . $lx3 . '" cy="' . $ry . '" r="2"/>';
	// Stop dots — 6 per wire, all snel-bg-pad circles
	$stop_config = [
		['t' => 0.1,  'class' => 'snel-bg-pad', 'r' => 2],
		['t' => 0.25, 'class' => 'snel-bg-pad', 'r' => 2],
		['t' => 0.45, 'class' => 'snel-bg-pad', 'r' => 2],
		['t' => 0.6,  'class' => 'snel-bg-pad', 'r' => 2],
		['t' => 0.75, 'class' => 'snel-bg-pad', 'r' => 2],
		['t' => 0.9,  'class' => 'snel-bg-pad', 'r' => 2],
	];
	foreach ($stop_config as $sc) {
		$total_r = $_sp_h_a + $_sp_h_d + $_sp_h_b;
		$dist    = $sc['t'] * $total_r;
		// Right wire
		if ($dist <= $_sp_h_a) {
			$sx = round(1000 - $dist);
			$sy = $y;
		} elseif ($dist <= $_sp_h_a + $_sp_h_d) {
			$p = ($dist - $_sp_h_a) / $_sp_h_d;
			$sx = round($rx1 - $p * $_sp_h_d);
			$sy = round($y + $p * $_sp_h_d);
		} else {
			$sx = round($rx2 - ($dist - $_sp_h_a - $_sp_h_d));
			$sy = $ry;
		}
		$_sp_st .= '<circle class="' . $sc['class'] . '" cx="' . $sx . '" cy="' . $sy . '" r="' . $sc['r'] . '"/>';
		// Left wire (mirrored)
		if ($dist <= $_sp_h_a) {
			$sx = round($dist);
			$sy = $y;
		} elseif ($dist <= $_sp_h_a + $_sp_h_d) {
			$p = ($dist - $_sp_h_a) / $_sp_h_d;
			$sx = round($lx1 + $p * $_sp_h_d);
			$sy = round($y + $p * $_sp_h_d);
		} else {
			$sx = round($lx2 + ($dist - $_sp_h_a - $_sp_h_d));
			$sy = $ry;
		}
		$_sp_st .= '<circle class="' . $sc['class'] . '" cx="' . $sx . '" cy="' . $sy . '" r="' . $sc['r'] . '"/>';
	}
	// Synced beams — same duration/delay on all wires (right→left, left→right)
	$_sp_def .= '<linearGradient id="snel-sp-R' . $k . '" x1="100%" y1="0%" x2="0%" y2="0%"><stop offset="0%" stop-color="' . $c . '" stop-opacity="1"/><stop offset="100%" stop-color="' . $c . '" stop-opacity="0"/></linearGradient>';
	$_sp_def .= '<linearGradient id="snel-sp-L' . $k . '" x1="0%" y1="0%" x2="100%" y2="0%"><stop offset="0%" stop-color="' . $c . '" stop-opacity="1"/><stop offset="100%" stop-color="' . $c . '" stop-opacity="0"/></linearGradient>';
	$_sp_bm .= '<path class="snel-bg-beam" d="' . $rd . '" pathLength="1" stroke="url(#snel-sp-R' . $k . ')" filter="url(#snel-sp-glow)" style="animation-duration:7s;animation-delay:-1s"/>';
	$_sp_bm .= '<path class="snel-bg-beam" d="' . $ld . '" pathLength="1" stroke="url(#snel-sp-L' . $k . ')" filter="url(#snel-sp-glow)" style="animation-duration:7s;animation-delay:-1s"/>';
	$_sp_bm .= '<path class="snel-bg-beam" d="' . $rd . '" pathLength="1" stroke="url(#snel-sp-R' . $k . ')" filter="url(#snel-sp-glow)" style="animation-duration:7s;animation-delay:-4.5s"/>';
	$_sp_bm .= '<path class="snel-bg-beam" d="' . $ld . '" pathLength="1" stroke="url(#snel-sp-L' . $k . ')" filter="url(#snel-sp-glow)" style="animation-duration:7s;animation-delay:-4.5s"/>';
}
$_sp_def .= '</defs>';
?>
<div class="relative h-24 overflow-hidden">
	<svg class="pointer-events-none absolute inset-0 h-full w-full mt-3" viewBox="0 0 1000 80" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
		<?php echo $_sp_def . $_sp_tr . $_sp_st . $_sp_bm; ?>
	</svg>
</div>
<?php
$visual        = sanitize_key($attributes['visual'] ?? '');
$image_id      = (int) ($attributes['imageId'] ?? 0);
$image_ratio   = ['square' => 'aspect-square', 'landscape' => 'aspect-[4/3]', 'portrait' => 'aspect-[3/4]', 'wide' => 'aspect-video'][$attributes['imageRatio'] ?? 'original'] ?? '';
$image_class   = 'w-full rounded-2xl' . ($image_ratio ? ' object-cover ' . $image_ratio : '');
$show_beams    = $attributes['showBeams']    ?? true;
$show_gradient = $attributes['showGradient'] ?? true;

$snel_theme   = $attributes['theme'] ?? 'white';
$snel_is_dark = in_array($snel_theme, ['dark', 'canvas'], true);
$theme_bg     = ['dark' => '#2e1065', 'canvas' => '#020617', 'white' => '#ffffff'][$snel_theme] ?? '#ffffff';
$theme_fade   = ['dark' => 'from-[#2e1065]', 'canvas' => 'from-[#020617]'][$snel_theme] ?? 'from-white';
if ($snel_theme === 'canvas')   $theme_class = 'is-dark bg-canvas';
elseif ($snel_is_dark)          $theme_class = 'is-dark';
else                            $theme_class = 'bg-white';
?>
<section data-seo-content class="snel-hero relative rounded-t-3xl overflow-hidden <?php echo esc_attr($theme_class); ?><?php echo $full_h ? ' snel-hero--full' : ''; ?>" style="background-color:<?php echo esc_attr($theme_bg); ?>">
	<?php snel_background_open(['position' => 'absolute', 'backdrop' => 'transparent', 'fade' => $theme_fade, 'beams' => $show_beams, 'gradient' => $show_gradient]); ?>
	<div class="<?php echo $full_h ? 'px-4 py-20 md:px-8' : 'px-4 pt-16 pb-20 md:px-8 lg:pt-20'; ?>">
		<?php snel_panel_open(['dark' => $snel_is_dark]); ?>
		<?php if ($image_id || $visual) : ?>
			<div class="grid lg:grid-cols-2 gap-16 xl:gap-32 items-center">
				<div class="flex flex-col items-start gap-8 lg:gap-10"><?php echo $content; ?></div>
				<div class="relative flex items-center justify-center">
					<?php
					if ($image_id) echo wp_get_attachment_image($image_id, 'large', false, ['class' => $image_class]);
					else           get_template_part('template-parts/service-visuals/' . $visual);
					?>
				</div>
			</div>
		<?php else : ?>
			<div class="flex flex-col items-start gap-8 lg:gap-10"><?php echo $content; ?></div>
		<?php endif; ?>
		<?php snel_panel_close(); ?>
	</div>
	<?php snel_background_close(); ?>
</section>