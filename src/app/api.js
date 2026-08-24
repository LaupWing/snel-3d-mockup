import { REST } from '../config/config';

/** Persist the one settings blob to the global option (explicit Save only). */
export async function saveConfig( config ) {
	const res = await fetch( REST.url + 'config', {
		method: 'POST',
		headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': REST.nonce },
		body: JSON.stringify( config ),
	} );
	if ( ! res.ok ) {
		throw new Error( 'Save failed (' + res.status + ')' );
	}
	return res.json();
}

/** Save/update one named global preset. Returns { ok, presets }. */
export async function savePreset( name, config ) {
	const res = await fetch( REST.url + 'presets', {
		method: 'POST',
		headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': REST.nonce },
		body: JSON.stringify( { name, config } ),
	} );
	if ( ! res.ok ) {
		throw new Error( 'Preset save failed (' + res.status + ')' );
	}
	return res.json();
}

/**
 * Upload a captured PNG (data URL) to the Media Library, optionally setting it as
 * the product's featured image. Returns { id, url, featured }.
 */
export async function uploadThumbnail( dataUrl, productId, setFeatured, filename, alt ) {
	const res = await fetch( REST.url + 'thumbnail', {
		method: 'POST',
		headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': REST.nonce },
		body: JSON.stringify( { image: dataUrl, productId, setFeatured, filename, alt } ),
	} );
	if ( ! res.ok ) {
		throw new Error( 'Upload failed (' + res.status + ')' );
	}
	return res.json();
}
