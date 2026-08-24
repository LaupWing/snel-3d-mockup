<?php
/**
 * Service block — one full-height section per service CPT post.
 *
 * Attributes:
 *   showChip (bool) — show the CPU chip badge above the first section
 *
 * @var array    $attributes
 * @var string   $content
 * @var WP_Block $block
 */

defined('ABSPATH') || exit;

$services  = snel_get_services();

if (empty($services)) {
    if (current_user_can('edit_posts')) {
        echo '<p style="padding:40px;text-align:center;color:#a78bfa;border:2px dashed #a78bfa;border-radius:8px;margin:16px;">'
           . esc_html__('Service block — seed diensten via het Tools menu om dit blok te vullen.', 'snel')
           . '</p>';
    }
    return;
}

// ---------------------------------------------------------------------------
// Chip geometry — always rendered on the first section.
// ---------------------------------------------------------------------------
$chip_decorations = '';
if (true) {
	$chip_x = 28; $chip_y_pos = 24; $chip_w = 84; $chip_h = 84;
	$vb_h   = $chip_y_pos + $chip_h + $chip_y_pos; // 132
	$dx     = $chip_x + 14; $dy = $chip_y_pos + 14; $dw = $chip_w - 28; $dh = $chip_h - 28;
	$w_a    = 28; $w_d = 10; $w_b = 28;
	$w_offs = [17, 34, 51, 68];

	// Beam pulses: same brand palette as the intro/process wires, one per wire,
	// all in sync. Wires are short (~66 units) so the dash segment is widened
	// (0.45 of the path) or the beam would be a barely visible speck.
	$beam_colors = ['#38bdf8', '#a78bfa', '#f472b6', '#2dd4bf'];
	$add_wire = function ($path_d, $px, $py, $color) use (&$chip_decorations) {
		$chip_decorations .= '<path class="snel-deco-wire" d="' . $path_d . '" />'
		                   . '<path class="snel-bg-beam" d="' . $path_d . '" pathLength="1" stroke="' . $color . '" filter="url(#snel-svc-glow)" style="animation-duration:3s;stroke-dasharray:0.45 1.55"/>'
		                   . '<circle class="snel-deco-pad" cx="' . round($px, 1) . '" cy="' . round($py, 1) . '" r="2.5" />';
	};

	foreach ($w_offs as $k => $off) { // left
		$wy = $chip_y_pos + $off;
		$x1 = $chip_x - $w_a; $x2 = $x1 - $w_d; $x3 = $x2 - $w_b; $ye = $wy + $w_d;
		$add_wire("M $chip_x $wy L $x1 $wy L $x2 $ye L $x3 $ye", $x3, $ye, $beam_colors[$k]);
	}
	foreach ($w_offs as $k => $off) { // top
		$tx = $chip_x + $off;
		$y1 = $chip_y_pos - $w_a; $y2 = $y1 - $w_d; $y3 = $y2 - $w_b; $xe = $tx + $w_d;
		$add_wire("M $tx $chip_y_pos L $tx $y1 L $xe $y2 L $xe $y3", $xe, $y3, $beam_colors[$k]);
	}
	$bot_y = $chip_y_pos + $chip_h;
	foreach ($w_offs as $k => $off) { // bottom
		$bx = $chip_x + $off;
		$y1 = $bot_y + $w_a; $y2 = $y1 + $w_d; $y3 = $y2 + $w_b; $xe = $bx + $w_d;
		$add_wire("M $bx $bot_y L $bx $y1 L $xe $y2 L $xe $y3", $xe, $y3, $beam_colors[$k]);
	}
}

// ---------------------------------------------------------------------------
// Arrow icon shared by the meer info button.
// ---------------------------------------------------------------------------
$arrow = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-3.5 shrink-0">'
       . '<path fill-rule="evenodd" d="M2 8a.75.75 0 0 1 .75-.75h8.69L8.22 4.03a.75.75 0 0 1 1.06-1.06l4.5 4.5a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 0 1-1.06-1.06l3.22-3.22H2.75A.75.75 0 0 1 2 8Z" clip-rule="evenodd"/>'
       . '</svg>';

// ---------------------------------------------------------------------------
// Sections.
// ---------------------------------------------------------------------------
?>
<div class="bg-canvas">
<?php foreach ($services as $i => $service) :
	$icon     = get_post_meta($service->ID, '_service_icon', true);
	$tagline  = get_post_meta($service->ID, '_service_tagline', true);
	$headline = get_post_meta($service->ID, '_service_headline', true);
	$visual   = get_post_meta($service->ID, '_service_visual', true);
	$is_first = $i === 0;
?>
<section data-seo-content class="bg-canvas relative flex min-h-svh items-center px-4 py-16 md:px-8<?php echo $is_first ? ' pt-32 md:pt-36 rounded-t-3xl' : ''; ?>">

	<?php if ($is_first) : ?>
	<div class="absolute left-1/2 top-0 z-30 -translate-x-1/2 -translate-y-1/2">
		<svg class="block h-auto w-[150px] overflow-visible" viewBox="0 0 140 <?php echo esc_attr($vb_h); ?>" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
			<defs>
				<linearGradient id="snel-svc-grad-<?php echo $i; ?>" x1="0" y1="0" x2="1" y2="1">
					<stop offset="0%" stop-color="#38bdf8"/>
					<stop offset="50%" stop-color="#a78bfa"/>
					<stop offset="100%" stop-color="#f472b6"/>
					<animateTransform attributeName="gradientTransform" type="rotate" from="0 0.5 0.5" to="360 0.5 0.5" dur="5s" repeatCount="indefinite"/>
				</linearGradient>
				<filter id="snel-svc-glow" x="-100%" y="-100%" width="300%" height="300%">
					<feGaussianBlur stdDeviation="2" result="b"/>
					<feMerge><feMergeNode in="b"/><feMergeNode in="SourceGraphic"/></feMerge>
				</filter>
			</defs>
			<?php echo $chip_decorations; ?>
			<rect class="snel-cpu-chip fill-slate-950/3 stroke-violet-500/40 stroke-[1.5]"
				x="<?php echo $chip_x; ?>" y="<?php echo $chip_y_pos; ?>" width="<?php echo $chip_w; ?>" height="<?php echo $chip_h; ?>" rx="12"/>
			<rect class="snel-cpu-die fill-violet-500/6 stroke-violet-500/25 stroke-1"
				x="<?php echo $dx; ?>" y="<?php echo $dy; ?>" width="<?php echo $dw; ?>" height="<?php echo $dh; ?>" rx="6"/>
			<rect x="<?php echo $dx; ?>" y="<?php echo $dy; ?>" width="<?php echo $dw; ?>" height="<?php echo $dh; ?>" rx="6"
				fill="url(#snel-svc-grad-<?php echo $i; ?>)" opacity="0.45"/>
			<rect x="<?php echo $chip_x; ?>" y="<?php echo $chip_y_pos; ?>" width="<?php echo $chip_w; ?>" height="<?php echo $chip_h; ?>" rx="12"
				fill="none" stroke="url(#snel-svc-grad-<?php echo $i; ?>)" stroke-width="2" opacity="0.6"/>
		</svg>
	</div>
	<?php endif; ?>

	<div class="mx-auto w-full max-w-7xl">
		<div class="grid items-center gap-16 lg:grid-cols-2 xl:gap-32">

			<div class="space-y-8 lg:space-y-10">

				<?php if ($icon || $tagline) : ?>
				<span class="inline-flex items-center gap-2 rounded-full border border-white/15 px-3 py-1 text-sm text-white/80">
					<?php if ($icon) echo esc_html($icon); ?>
					<?php if ($tagline) echo esc_html($tagline); ?>
				</span>
				<?php endif; ?>

				<h2 class="snel-heading snel-h-3xl !text-teal-300"><?php echo nl2br(esc_html($headline ?: $service->post_title)); ?></h2>

				<?php if ($service->post_excerpt) : ?>
				<p class="snel-text snel-text-lg !text-white"><?php echo esc_html($service->post_excerpt); ?></p>
				<?php endif; ?>

				<div class="flex flex-wrap items-center gap-4">
					<a href="<?php echo esc_url(get_permalink($service->ID)); ?>"
					   class="inline-flex items-center gap-2 rounded-full bg-teal-300 px-5 py-2.5 text-sm font-semibold text-canvas transition hover:bg-teal-200">
						Meer info <?php echo $arrow; ?>
					</a>
					<?php get_template_part('template-parts/gradient-button', null, [
						'href'  => 'https://calendly.com/snelstack/30min',
						'label' => 'Maak een plan',
						'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-3.5 shrink-0"><path fill-rule="evenodd" d="M4 1.75a.75.75 0 0 1 1.5 0V3h5V1.75a.75.75 0 0 1 1.5 0V3A2 2 0 0 1 14 5v7a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2V1.75ZM4.5 6a.5.5 0 0 0 0 1h7a.5.5 0 0 0 0-1h-7Z" clip-rule="evenodd"/></svg>',
					]); ?>
				</div>

			</div>

			<div class="flex items-center justify-center">
				<?php if ($visual) {
					get_template_part('template-parts/service-visuals/' . sanitize_key($visual));
				} else { ?>
				<div class="w-full max-w-md aspect-square rounded-3xl border border-white/10 bg-white/5"></div>
				<?php } ?>
			</div>

		</div>
	</div>

</section>
<?php if ($i < count($services) - 1) : ?>
<div class="snel-divider snel-divider--full" aria-hidden="true"><div class="snel-divider-line"></div></div>
<?php endif; ?>
<?php endforeach; ?>
</div>
