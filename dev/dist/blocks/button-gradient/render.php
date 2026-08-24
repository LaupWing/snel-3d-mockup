<?php
/**
 * Button (Gradient) — wraps template-parts/gradient-button. From the hero.
 *
 * @var array    $attributes
 * @var string   $content
 * @var WP_Block $block
 */

defined('ABSPATH') || exit;

$label      = $attributes['label']     ?? '';
$url        = $attributes['url']       ?? '#';
$show_arrow = $attributes['showArrow'] ?? true;

// Arrow that slides up-and-out on hover while a second slides in from below.
$arrow_path = '<path fill-rule="evenodd" d="M14.78 5.22a.75.75 0 0 0-1.06 0L6.5 12.44V6.75a.75.75 0 0 0-1.5 0v7.5c0 .414.336.75.75.75h7.5a.75.75 0 0 0 0-1.5H7.56l7.22-7.22a.75.75 0 0 0 0-1.06Z" clip-rule="evenodd"/>';
$icon = $show_arrow
	? '<span class="relative block size-5 overflow-hidden text-slate-950">'
		. '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="absolute inset-0 size-5 transition-transform duration-300 ease-out group-hover:-translate-y-[150%]">' . $arrow_path . '</svg>'
		. '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="absolute inset-0 size-5 translate-y-[150%] transition-transform duration-300 ease-out group-hover:translate-y-0">' . $arrow_path . '</svg>'
		. '</span>'
	: '';

get_template_part('template-parts/gradient-button', null, [
	'href'        => $url,
	'label'       => $label,
	'icon'        => $icon,
	'face_class'  => 'px-6 text-base',
	'outer_class' => 'h-12',
]);
