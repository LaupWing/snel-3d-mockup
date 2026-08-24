<?php
/**
 * Snel Statement — two-column layout with word-reveal animation.
 *
 * Left column: large heading. Right column: paragraph — view.js walks the DOM,
 * wraps every word in <span class="wr-word">, and cascades them in/out on scroll
 * via IntersectionObserver.
 *
 * @var array    $attributes
 * @var string   $content
 * @var WP_Block $block
 */

defined('ABSPATH') || exit;

$heading   = $attributes['heading']   ?? '';
$paragraph = $attributes['paragraph'] ?? '';
$level     = in_array($attributes['level'] ?? 'h3', ['h2', 'h3', 'h4'], true)
	? $attributes['level']
	: 'h3';
?>
<section data-seo-content class="snel-statement relative <?php echo snel_section_class($attributes); ?>"<?php echo snel_section_style($attributes); ?>>
	<div class="mx-auto w-full max-w-5xl px-4 md:px-8 <?php echo snel_section_padding($attributes); ?>">
		<div class="flex flex-col gap-16 lg:flex-row lg:gap-32">
			<div class="flex-none lg:w-72">
				<<?php echo $level; ?> class="snel-heading snel-h-2xl"><?php echo wp_kses_post($heading); ?></<?php echo $level; ?>>
			</div>
			<div class="grow">
				<div class="snel-wr-body snel-text snel-text-xl [&_p+p]:mt-8"><?php echo wp_kses_post($paragraph); ?></div>
			</div>
		</div>
	</div>
</section>
