<?php
defined('ABSPATH') || exit;

// Slides are editable (block.json default applies when unset). Normalize each.
$slides_raw = ! empty($attributes['slides']) && is_array($attributes['slides']) ? $attributes['slides'] : [];
$slides = array_values(array_map(function ($s) {
	return [
		'title' => $s['title'] ?? '',
		'dot'   => $s['dot']   ?? '#38bdf8',
		'text'  => $s['text']  ?? '',
		'cta'   => $s['cta']   ?? '',
		'url'   => $s['url']   ?? '#',
	];
}, $slides_raw));
?>
<section class="snel-stack-showcase-section <?php echo snel_section_class($attributes); ?> <?php echo snel_section_padding($attributes); ?>"<?php echo snel_section_style($attributes); ?>>
	<div class="px-4 md:px-8">
		<div class="mx-auto w-full max-w-5xl">
			<div class="snel-stack-showcase" data-slides="<?php echo esc_attr(wp_json_encode($slides)); ?>">
				<div class="snel-stack-placeholder group relative flex w-full cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl bg-slate-950 aspect-[3/5] md:aspect-[3/2]">
					<div class="absolute inset-0 bg-gradient-to-br from-slate-900 to-slate-950"></div>
					<div class="relative flex flex-col items-center gap-4 px-4 text-center">
						<div class="flex -space-x-1.5">
							<span class="size-3.5 rounded-full bg-sky-500 ring-2 ring-slate-900"></span>
							<span class="size-3.5 rounded-full bg-violet-500 ring-2 ring-slate-900"></span>
							<span class="size-3.5 rounded-full bg-pink-600 ring-2 ring-slate-900"></span>
						</div>
						<p class="text-xl font-semibold text-white"><?php echo esc_html(snel__('Verken de stack')); ?></p>
						<span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm text-white/70 ring-1 ring-white/20 transition group-hover:bg-white/20">
							<?php echo esc_html(snel__('Klik om te verkennen')); ?>
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4"><path fill-rule="evenodd" d="M5.22 14.78a.75.75 0 0 0 1.06 0l7.22-7.22v5.69a.75.75 0 0 0 1.5 0v-7.5a.75.75 0 0 0-.75-.75h-7.5a.75.75 0 0 0 0 1.5h5.69l-7.22 7.22a.75.75 0 0 0 0 1.06Z" clip-rule="evenodd"/></svg>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
