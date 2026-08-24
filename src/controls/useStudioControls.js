import { useControls, folder } from 'leva';
import { DEFAULT_STUDIO as D } from '../app/defaults';

/**
 * The Leva panel — device transform, camera, lighting, shadow, screen. Returns
 * `[ values, set ]` (function form) so the app can hydrate from a saved config
 * and reset to defaults.
 */
export function useStudioControls() {
	return useControls( () => ( {
		Device: folder( {
			rotationY: { value: D.rotationY, min: -180, max: 180, step: 1, label: 'rotation Y°' },
			offsetY: { value: D.offsetY, min: -8, max: 8, step: 0.01, label: 'offset Y' },
			deviceScale: { value: D.deviceScale, min: 0.001, max: 10, step: 0.001, label: 'scale' },
		} ),
		Camera: folder( {
			cameraY: { value: D.cameraY, min: -6, max: 6, step: 0.05, label: 'camera Y' },
		} ),
		Environment: folder( {
			envIntensity: { value: D.envIntensity, min: 0, max: 3, step: 0.05, label: 'env intensity' },
			ambient: { value: D.ambient, min: 0, max: 1, step: 0.01 },
		} ),
		'Key light': folder( {
			keyIntensity: { value: D.keyIntensity, min: 0, max: 6, step: 0.1, label: 'intensity' },
			keyPos: { value: D.keyPos, step: 0.5, label: 'position' },
		} ),
		'Fill light': folder( {
			fillIntensity: { value: D.fillIntensity, min: 0, max: 6, step: 0.1, label: 'intensity' },
			fillPos: { value: D.fillPos, step: 0.5, label: 'position' },
		} ),
		'Rim light': folder( {
			rimIntensity: { value: D.rimIntensity, min: 0, max: 6, step: 0.1, label: 'intensity' },
			rimColor: { value: D.rimColor, label: 'color' },
			rimPos: { value: D.rimPos, step: 0.5, label: 'position' },
		} ),
		Shadow: folder( {
			shadowOpacity: { value: D.shadowOpacity, min: 0, max: 1, step: 0.05, label: 'opacity' },
			shadowRadius: { value: D.shadowRadius, min: 0, max: 12, step: 0.5, label: 'softness' },
		} ),
		Screen: folder( {
			// 0 = lit photo look, 1 = the screenshot's exact colors (crossfade).
			screenBrightness: { value: D.screenBrightness, min: 0, max: 1, step: 0.05, label: 'brightness' },
			screenGloss: { value: D.screenGloss, min: 0, max: 0.4, step: 0.01, label: 'gloss (lower=shinier)' },
			// Capped low: reflection layers the (white) studio envmap OVER the
			// image — anything past ~0.5 reads as a washed-out screen.
			screenReflection: { value: D.screenReflection, min: 0, max: 0.5, step: 0.05, label: 'reflection' },
		} ),
	} ) );
}
