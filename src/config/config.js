/**
 * Studio config, sourced from the data PHP localises onto `window.snelMockup`.
 * Models/HDRIs/table all come from the theme's /assets via that data.
 */
const DATA = ( typeof window !== 'undefined' && window.snelMockup ) || {};

export const MODELS = DATA.models || [];
export const HDRIS = DATA.hdris || [];
export const TABLE_URL = DATA.tableUrl || '';
export const DEFAULT_IMAGE_URL = DATA.imageUrl || '';
export const PRODUCT_ID = DATA.productId || 0;
export const PRODUCT_TITLE = DATA.productTitle || '';
export const SAVED_CONFIG = DATA.config || null;
export const SAVED_PRESETS = DATA.presets || {};

/** "Case titel" → "snelstack-case" — product title in dashes. */
export const slugify = ( s ) =>
	( s || '' )
		.toString()
		.toLowerCase()
		.trim()
		.replace( /[^a-z0-9]+/g, '-' )
		.replace( /^-+|-+$/g, '' );
export const REST = { url: DATA.restUrl || '', nonce: DATA.nonce || '' };

// View direction (target → camera), X zeroed for a head-on shot. The rig fits
// the distance along this direction per device.
export const DEFAULT_CAMERA = [ 0, 0.416, 5.217 ];

/** `…/imac.glb` → `imac` — the stable id used for presets + persistence. */
export const modelId = ( url ) => ( url || '' ).split( '/' ).pop().replace( /\.glb$/i, '' );

export const modelUrl = ( id ) => {
	const m = MODELS.find( ( x ) => modelId( x.url ) === id );
	return m ? m.url : MODELS[ 0 ] ? MODELS[ 0 ].url : '';
};

export const hdriUrlByLabel = ( label ) => {
	const h = HDRIS.find( ( x ) => x.label === label );
	return h ? h.url : HDRIS[ 0 ] ? HDRIS[ 0 ].url : '';
};
