<?php
/**
 * Snel Process — scroll-driven chip chain.
 *
 * Steps come from $attributes['steps'] (editable in the sidebar). Each step:
 *   n, title, heading, body, btn_label, btn_url
 *
 * Geometry auto-computes from the number of steps so you can add/remove freely.
 *
 * @var array    $attributes
 * @var string   $content
 * @var WP_Block $block
 */

defined('ABSPATH') || exit;

// Read steps from attributes — fall back to empty so the block renders nothing
// rather than crashing when the attribute is missing.
$steps_raw = $attributes['steps'] ?? [];
$steps = array_values(array_map(function ($s) {
	return [
		'n'         => $s['n']         ?? '',
		'title'     => $s['title']     ?? '',
		'heading'   => $s['heading']   ?? '',
		'body'      => $s['body']      ?? '',
		'btn_label' => $s['btn_label'] ?? 'Meer info',
		'btn_url'   => $s['btn_url']   ?? '#',
	];
}, $steps_raw));

if (empty($steps)) return;

$n = count($steps);

$palette = ['#38bdf8', '#a78bfa', '#f472b6', '#2dd4bf'];
$xs      = [56, 72, 88, 104];
$chip_x  = 40;
$chip_w  = 84;
$chip_h  = 84;
$top     = 24;

// Arrow SVG shared by every button (dual-slide animation).
$arrow_path = '<path fill-rule="evenodd" d="M2 8a.75.75 0 0 1 .75-.75h8.69L8.22 4.03a.75.75 0 0 1 1.06-1.06l4.5 4.5a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 0 1-1.06-1.06l3.22-3.22H2.75A.75.75 0 0 1 2 8Z" clip-rule="evenodd"/>';
$arrow_svg  = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="absolute inset-0 size-3 transition-transform duration-300 ease-out group-hover:translate-x-[200%]">' . $arrow_path . '</svg>'
            . '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="absolute inset-0 size-3 translate-y-[150%] transition-transform duration-300 ease-out group-hover:translate-y-0">' . $arrow_path . '</svg>';

// Generates the .snel-flow-track HTML for a given pitch and unique ID suffix.
$make_track = function (int $pitch, string $id_sfx, string $svg_w, string $ol_left, bool $mobile = false)
	use ($steps, $n, $palette, $xs, $chip_x, $chip_w, $chip_h, $top, $arrow_svg): string
{
	$chip_y = [];
	for ($i = 0; $i < $n; $i++) $chip_y[$i] = $top + $i * $pitch;
	$vb_h = $chip_y[$n - 1] + $chip_h + $top;

	$grad_id = 'snel-proc-grad' . $id_sfx;
	$glow_id = 'snel-glow' . $id_sfx;

	// Decorative PCB wires.
	$decorations = '';
	$add_wire    = function ($path_d, $px, $py) use (&$decorations) {
		$decorations .= '<path class="snel-deco-wire" d="' . $path_d . '" />'
		              . '<circle class="snel-deco-pad" cx="' . round($px, 1) . '" cy="' . round($py, 1) . '" r="2.5" />';
	};

	$w_a = 14; $w_d = 6; $w_b = 14;
	$w_offs  = [17, 34, 51, 68];
	$w_xoffs = [17, 34, 51, 68];

	foreach ($steps as $wi => $ws) {
		foreach ($w_offs as $off) {
			$wy = $chip_y[$wi] + $off;
			$x1 = $chip_x - $w_a; $x2 = $x1 - $w_d; $x3 = $x2 - $w_b;
			$ye = $wy + $w_d;
			$add_wire("M $chip_x $wy L $x1 $wy L $x2 $ye L $x3 $ye", $x3, $ye);
		}
	}
	$top_y = $chip_y[0];
	foreach ($w_xoffs as $off) {
		$tx = $chip_x + $off;
		$y1 = $top_y - $w_a; $y2 = $y1 - $w_d; $y3 = $y2 - $w_b; $xe = $tx + $w_d;
		$add_wire("M $tx $top_y L $tx $y1 L $xe $y2 L $xe $y3", $xe, $y3);
	}
	$bot_y = $chip_y[$n - 1] + $chip_h;
	foreach ($w_xoffs as $off) {
		$bx = $chip_x + $off;
		$y1 = $bot_y + $w_a; $y2 = $y1 + $w_d; $y3 = $y2 + $w_b; $xe = $bx + $w_d;
		$add_wire("M $bx $bot_y L $bx $y1 L $xe $y2 L $xe $y3", $xe, $y3);
	}

	// Traces + stop pads.
	$traces = '';
	$stops  = '';
	foreach ($xs as $k => $x) {
		$jx = $x + 14;
		$d  = sprintf('M %d %d', $x, $chip_y[0] + $chip_h);
		for ($i = 1; $i < $n; $i++) {
			$ya  = $chip_y[$i - 1] + $chip_h;
			$yb  = $chip_y[$i];
			$gap = $yb - $ya;
			$d  .= sprintf(
				' L %d %.1f L %d %.1f L %d %.1f L %d %.1f L %d %d',
				$x, $ya + $gap * 0.30,
				$jx, $ya + $gap * 0.30 + 14,
				$jx, $yb - $gap * 0.30 - 14,
				$x, $yb - $gap * 0.30,
				$x, $yb
			);
			if ($i < $n - 1) $d .= sprintf(' L %d %d', $x, $chip_y[$i] + $chip_h);
			$a1 = $ya + $gap * 0.30; $a2 = $a1 + 14;
			$b1 = $yb - $gap * 0.30 - 14; $b2 = $b1 + 14;
			for ($py = $ya + 34; $py < $yb - 24; $py += 52) {
				if ($py <= $a1)     $sx = $x;
				elseif ($py < $a2)  $sx = $x + ($py - $a1);
				elseif ($py <= $b1) $sx = $jx;
				elseif ($py < $b2)  $sx = $jx - ($py - $b1);
				else                $sx = $x;
				$stops .= '<circle class="snel-flow-stop fill-slate-400/50 in-[.is-dark]:fill-white/35" cx="' . round($sx, 1) . '" cy="' . round($py, 1) . '" r="1.8" />';
			}
		}
		$c = $palette[$k % count($palette)];
		$traces .= '<path class="snel-flow-base" d="' . $d . '" />';
		$traces .= '<path class="snel-flow-pulse" d="' . $d . '" pathLength="1" stroke="' . $c . '" filter="url(#' . $glow_id . ')" />';
	}

	// Chips + rows.
	$chips = '';
	$rows  = '';
	foreach ($steps as $i => $s) {
		$y  = $chip_y[$i];
		$cy = $y + $chip_h / 2;
		$dx = $chip_x + 14; $dy = $y + 14;
		$dw = $chip_w - 28; $dh = $chip_h - 28;

		$chips .= '<g class="snel-proc-chip" data-y="' . $y . '">';
		$chips .= '<rect class="snel-cpu-chip fill-slate-950/3 stroke-violet-500/40 stroke-[1.5] in-[.is-dark]:fill-white/4 in-[.is-dark]:stroke-violet-400/50" x="' . $chip_x . '" y="' . $y . '" width="' . $chip_w . '" height="' . $chip_h . '" rx="12" />';
		$chips .= '<rect class="snel-cpu-die fill-violet-500/6 stroke-violet-500/25 stroke-1 in-[.is-dark]:fill-violet-400/10 in-[.is-dark]:stroke-violet-400/35" x="' . $dx . '" y="' . $dy . '" width="' . $dw . '" height="' . $dh . '" rx="6" />';
		$chips .= '<rect class="snel-proc-glow-die opacity-0 transition-opacity duration-450 in-[.is-active]:opacity-100" x="' . $dx . '" y="' . $dy . '" width="' . $dw . '" height="' . $dh . '" rx="6" fill="url(#' . $grad_id . ')" />';
		$chips .= '<rect class="snel-proc-glow-border opacity-0 transition-opacity duration-450 in-[.is-active]:opacity-100" x="' . $chip_x . '" y="' . $y . '" width="' . $chip_w . '" height="' . $chip_h . '" rx="12" fill="none" stroke="url(#' . $grad_id . ')" stroke-width="2" />';
		$chips .= '<text class="snel-flow-num fill-violet-500 text-[13px] font-bold tracking-[0.04em] in-[.is-active]:fill-white" x="' . ($chip_x + $chip_w / 2) . '" y="' . ($cy + 5) . '" text-anchor="middle">' . esc_html($s['n']) . '</text>';
		$chips .= '</g>';

		if ($mobile) {
			// Normal flow, full container width (the SVG is a background here).
			$rows .= '<div class="grid grid-cols-1 gap-1">';
			$rows .= '<h3 class="snel-heading snel-h-sm">' . esc_html($s['title']) . '</h3>';
			$rows .= '<div class="snel-proc-reveal w-full rounded-2xl border border-violet-500/20 bg-white/70 px-4 py-4 backdrop-blur-sm in-[.is-dark]:border-violet-400/25 in-[.is-dark]:bg-white/5">';
		} else {
			$pct   = round($cy / $vb_h * 100, 3);
			$rows .= '<div data-cy="' . $cy . '" class="absolute left-0 right-0 grid -translate-y-1/2 grid-cols-1 items-start gap-1 md:grid-cols-[150px_1fr] md:items-center md:gap-6" style="top:' . $pct . '%">';
			$rows .= '<h3 class="snel-heading snel-h-sm md:snel-h-md">' . esc_html($s['title']) . '</h3>';
			$rows .= '<div class="snel-proc-reveal max-w-lg rounded-2xl border border-violet-500/20 bg-white/60 px-4 py-4 md:justify-self-end md:px-6 md:py-6 in-[.is-dark]:border-violet-400/25 in-[.is-dark]:bg-white/5">';
		}
		$rows .= '<h4 class="snel-heading snel-h-md"><span class="text-pink-400">' . esc_html($s['n']) . '</span> ' . esc_html($s['heading']) . '</h4>';
		$rows .= '<p class="snel-text snel-text-md mt-3">' . esc_html($s['body']) . '</p>';
		$rows .= '<a href="' . esc_url($s['btn_url']) . '" class="group mt-4 inline-flex md:mt-8 h-8 items-center gap-2 rounded-md border-2 border-teal-400 bg-teal-400 px-3 text-xs font-medium text-violet-950 transition-all duration-300 hover:bg-teal-400/90"><span class="whitespace-nowrap">' . esc_html($s['btn_label']) . '</span><span class="relative block size-3 overflow-hidden">' . $arrow_svg . '</span></a>';
		$rows .= '</div>';
		$rows .= '</div>';
	}

	$svg = '<svg class="snel-flow-svg block h-auto ' . $svg_w . ' overflow-visible font-sans" viewBox="0 0 140 ' . $vb_h . '" style="aspect-ratio:140/' . $vb_h . '" xmlns="http://www.w3.org/2000/svg">'
	      . '<defs>'
	      . '<linearGradient id="' . $grad_id . '" x1="0" y1="0" x2="1" y2="1">'
	      . '<stop offset="0%" stop-color="#38bdf8"/>'
	      . '<stop offset="50%" stop-color="#a78bfa"/>'
	      . '<stop offset="100%" stop-color="#f472b6"/>'
	      . '<animateTransform attributeName="gradientTransform" type="rotate" from="0 0.5 0.5" to="360 0.5 0.5" dur="5s" repeatCount="indefinite"/>'
	      . '</linearGradient>'
	      . '<filter id="' . $glow_id . '" x="-60%" y="-60%" width="220%" height="220%">'
	      . '<feGaussianBlur stdDeviation="2.6" result="b"/>'
	      . '<feMerge><feMergeNode in="b"/><feMergeNode in="SourceGraphic"/></feMerge>'
	      . '</filter>'
	      . '</defs>'
	      . $decorations . $traces . $stops . $chips
	      . '</svg>';

	if ($mobile) {
		// Circuit becomes a decorative background behind the full-width cards.
		return '<div class="snel-flow-track relative">'
		     . '<div class="pointer-events-none absolute inset-0 flex items-start justify-center overflow-hidden opacity-25">' . $svg . '</div>'
		     . '<div class="relative flex flex-col gap-12">' . $rows . '</div>'
		     . '</div>';
	}

	return '<div class="snel-flow-track relative">'
	     . $svg
	     . '<div class="absolute inset-y-0 right-0 ' . $ol_left . '">' . $rows . '</div>'
	     . '</div>';
};

// Background SVG uses desktop geometry (decorative only, desktop pitch is fine).
$_dp    = 420;
$_cy    = [];
for ($i = 0; $i < $n; $i++) $_cy[$i] = $top + $i * $_dp;
$_vb_h  = $_cy[$n - 1] + $chip_h + $top;

$bg_vw      = 1000;
$bg_spacing = 12;
$h_d        = 10;
$bg_group_lengths = [];
for ($i = 0; $i < $n - 1; $i++) {
	$bg_group_lengths[] = ['h_a' => mt_rand(80, 200), 'h_b' => mt_rand(40, 140)];
}
$_chip_scale = 15.0 / 14.0;
$_py_px      = 48.0;
$_section_h  = 2.0 * $_py_px + $_vb_h * $_chip_scale;
$bg_ys = [];
for ($i = 0; $i < $n - 1; $i++) {
	$gap_mid = ($_cy[$i] + $chip_h + $_cy[$i + 1]) / 2.0;
	$bg_ys[] = round(($_py_px + $gap_mid * $_chip_scale) / $_section_h * $_vb_h) - round($bg_spacing * 1.5);
}
$_beam_colors = ['#38bdf8', '#a78bfa', '#f472b6', '#2dd4bf'];
$bg_paths = '';
$bg_defs  = '<defs><filter id="snel-bg-glow" x="-100%" y="-100%" width="300%" height="300%"><feGaussianBlur stdDeviation="2" result="b"/><feMerge><feMergeNode in="b"/><feMergeNode in="SourceGraphic"/></feMerge></filter>';
foreach ($bg_ys as $gi => $gy) {
	$h_a = $bg_group_lengths[$gi]['h_a'];
	$h_b = $bg_group_lengths[$gi]['h_b'];
	for ($wi = 0; $wi < 4; $wi++) {
		$y   = $gy + $wi * $bg_spacing;
		$ly  = $y + $h_d;
		$rx1 = $bg_vw - $h_a; $rx2 = $rx1 - $h_d; $rx3 = $rx2 - $h_b;
		$color = $_beam_colors[$wi];
		$gid   = 'snel-bg-bR' . $gi . $wi;
		$bg_paths .= '<path class="snel-bg-trace" d="M ' . $bg_vw . ' ' . $y . ' L ' . $rx1 . ' ' . $y . ' L ' . $rx2 . ' ' . $ly . ' L ' . $rx3 . ' ' . $ly . '"/>';
		$bg_paths .= '<circle class="snel-bg-pad" cx="' . $rx3 . '" cy="' . $ly . '" r="2"/>';
		$bg_defs  .= '<linearGradient id="' . $gid . '" x1="100%" y1="0%" x2="0%" y2="0%"><stop offset="0%" stop-color="' . $color . '" stop-opacity="0"/><stop offset="40%" stop-color="' . $color . '"/><stop offset="100%" stop-color="' . $color . '" stop-opacity="0"/></linearGradient>';
		$bg_paths .= '<path class="snel-bg-beam" d="M ' . $bg_vw . ' ' . $y . ' L ' . $rx1 . ' ' . $y . ' L ' . $rx2 . ' ' . $ly . ' L ' . $rx3 . ' ' . $ly . '" pathLength="1" stroke="url(#' . $gid . ')" filter="url(#snel-bg-glow)" style="animation-duration:9s;animation-delay:-' . ($gi * 2.25) . 's"/>';
	}
}
$bg_defs    .= '</defs>';
$bg_svg_html = '<svg class="pointer-events-none absolute inset-0 h-full w-full hidden md:block" viewBox="0 0 ' . $bg_vw . ' ' . $_vb_h . '" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">' . $bg_defs . $bg_paths . '</svg>';
?>
<section data-seo-content class="snel-process relative <?php echo snel_section_class($attributes, 'theme'); ?>"<?php echo snel_section_style($attributes, 'theme'); ?>>
	<?php echo $bg_svg_html; ?>
	<div class="relative z-10 mx-auto w-full max-w-5xl px-4 md:px-8 <?php echo snel_section_padding($attributes); ?>">
		<div class="md:hidden"><?php echo $make_track(560, '-mob', 'w-28', 'left-32', true); ?></div>
		<div class="hidden md:block"><?php echo $make_track(420, '-desk', 'w-37.5', 'left-42.5'); ?></div>
	</div>
</section>
