<?php
/**
 * Snel Badge — a text pill. Colour, border, bg and weight adapt to the section
 * background via .snel-badge / .is-dark .snel-badge (theme.css).
 *
 * @var array    $attributes
 * @var string   $content
 * @var WP_Block $block
 */

defined('ABSPATH') || exit;

$label = $attributes['label'] ?? '';

// Opt-in: show the category in scope instead of the typed label. get_term is
// filtered by Snel Translations, so the name is already in the current language.
if ( ! empty( $attributes['useCategory'] ) ) {
	if ( is_category() || is_tax() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$label = $term->name;
		}
	} elseif ( is_singular( 'post' ) ) {
		$cats = get_the_category( $block->context['postId'] ?? get_the_ID() );
		if ( ! empty( $cats ) ) {
			$label = $cats[0]->name;
		}
	}
}
$color = $attributes['color'] ?? 'violet';
$allowed = ['teal', 'sky', 'violet', 'pink', 'red'];
$color = in_array($color, $allowed, true) ? $color : 'violet';
?>
<span class="snel-badge snel-badge--<?php echo esc_attr($color); ?> inline-flex h-8 items-center rounded-md px-3 text-sm backdrop-blur-md"><?php echo esc_html($label); ?></span>
