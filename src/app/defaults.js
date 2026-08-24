import { MODELS, HDRIS, DEFAULT_IMAGE_URL, modelUrl, hdriUrlByLabel, SAVED_CONFIG } from '../config/config';

/** Left-panel (App-owned) defaults. imageUrl is NOT here — it comes per-product. */
export const DEFAULT_PANEL = {
	glbUrl: MODELS[ 0 ] ? MODELS[ 0 ].url : '',
	hdriUrl: HDRIS[ 0 ] ? HDRIS[ 0 ].url : '',
	imgScale: 1,
	imgX: 0,
	imgY: 0,
	showBg: false,
	studio: true,
	showTable: true,
};

/** Studio (Leva) defaults — mirrors the control schema. */
export const DEFAULT_STUDIO = {
	rotationY: 0,
	offsetY: -0.25,
	deviceScale: 3,
	cameraY: 0,
	envIntensity: 0.8,
	ambient: 0.15,
	keyIntensity: 2,
	keyPos: [ -5, 8, 4 ],
	fillIntensity: 0.8,
	fillPos: [ 6, 4, 5 ],
	rimIntensity: 1.8,
	rimColor: '#cfd8ff',
	rimPos: [ 2, 6, -8 ],
	shadowOpacity: 0.95,
	shadowRadius: 3,
	// The screen is emissive-only (decoupled from studio lights), so 1 shows
	// the screenshot exactly as-is; reflection near zero keeps it unwashed.
	screenBrightness: 1,
	screenGloss: 0.06,
	screenReflection: 0.1,
};

/** Build the initial panel/studio from a saved blob, falling back to defaults. */
export function initialState() {
	const saved = SAVED_CONFIG || {};
	const sp = saved.panel || {};
	const ss = saved.studio || {};

	// Resolve device/hdri back to real URLs (and guard against stale/missing).
	const knownModel = MODELS.some( ( m ) => m.url === sp.glbUrl );
	const knownHdri = HDRIS.some( ( h ) => h.url === sp.hdriUrl );

	const panel = {
		...DEFAULT_PANEL,
		...sp,
		glbUrl: knownModel ? sp.glbUrl : sp.device ? modelUrl( sp.device ) : DEFAULT_PANEL.glbUrl,
		hdriUrl: knownHdri ? sp.hdriUrl : sp.hdri ? hdriUrlByLabel( sp.hdri ) : DEFAULT_PANEL.hdriUrl,
	};
	delete panel.device;
	delete panel.hdri;

	const studio = { ...DEFAULT_STUDIO, ...ss };

	return { panel, studio, imageUrl: DEFAULT_IMAGE_URL };
}
