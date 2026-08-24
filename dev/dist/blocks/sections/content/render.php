<?php
/**
 * Content (Prose) — wraps InnerBlocks in a Tailwind Typography prose container.
 *
 * @var string $content
 * @var array  $attributes
 */

defined('ABSPATH') || exit;

$size_class  = ($attributes['size'] ?? 'base') === 'lg' ? 'lg:prose-lg' : '';
$is_dark     = in_array($attributes['bg'] ?? 'white', ['dark', 'canvas'], true);
$invert      = $is_dark ? 'prose-invert' : '';
?>
<section data-seo-content class="snel-content <?php echo esc_attr(snel_section_class($attributes)); ?>"<?php echo snel_section_style($attributes); ?>>
    <div class="mx-auto w-full max-w-3xl px-4 md:px-8 <?php echo snel_section_padding(array_merge($attributes, ['size' => $attributes['paddingSize'] ?? 'md'])); ?>">
        <div class="prose prose-slate max-w-none <?php echo esc_attr(trim("$invert $size_class")); ?>">
            <?php echo $content; ?>
        </div>
    </div>
</section>
