import * as THREE from 'three';

/**
 * Find the device's screen mesh(es): by material name, else the largest
 * emissive-mapped panel (a baked wallpaper), else the largest flat forward-facing
 * panel for fully-obfuscated models.
 */
export function findScreenMeshes( scene ) {
	const SCREEN_NAMES = [ 'lcd', 'wallpaper', 'wallpeper', 'screen', 'display' ];
	const EXCLUDE = [ 'border', 'frame', 'bezel', 'glass' ];
	const isScreen = ( name ) => {
		if ( ! name ) {
			return false;
		}
		const n = name.toLowerCase();
		if ( EXCLUDE.some( ( e ) => n.includes( e ) ) ) {
			return false;
		}
		return SCREEN_NAMES.some( ( s ) => n.includes( s ) );
	};

	const named = [];
	scene.traverse( ( mesh ) => {
		if ( ! mesh.isMesh ) {
			return;
		}
		const mats = Array.isArray( mesh.material ) ? mesh.material : [ mesh.material ];
		if ( mats.some( ( m ) => m && isScreen( m.name ) ) ) {
			named.push( mesh );
		}
	} );
	if ( named.length ) {
		return named;
	}

	// Lit display = an emissive-mapped panel. Pick the largest to skip LEDs.
	let emiBest = null;
	let emiArea = 0;
	scene.traverse( ( mesh ) => {
		if ( ! mesh.isMesh || ! mesh.geometry ) {
			return;
		}
		const mats = Array.isArray( mesh.material ) ? mesh.material : [ mesh.material ];
		const lit = mats.some(
			( m ) => m && m.emissiveMap && m.emissive && m.emissive.r + m.emissive.g + m.emissive.b > 0.1
		);
		if ( ! lit ) {
			return;
		}
		mesh.geometry.computeBoundingBox();
		const bb = mesh.geometry.boundingBox;
		if ( ! bb ) {
			return;
		}
		const s = bb.getSize( new THREE.Vector3() );
		const dims = [ s.x, s.y, s.z ].sort( ( a, b ) => a - b );
		const area = dims[ 1 ] * dims[ 2 ];
		if ( area > emiArea ) {
			emiArea = area;
			emiBest = mesh;
		}
	} );
	if ( emiBest ) {
		return [ emiBest ];
	}

	// Geometric fallback: biggest flat, forward-facing (not upward) panel.
	let best = null;
	let bestScore = 0;
	const q = new THREE.Quaternion();
	scene.traverse( ( mesh ) => {
		if ( ! mesh.isMesh || ! mesh.geometry ) {
			return;
		}
		mesh.geometry.computeBoundingBox();
		const bb = mesh.geometry.boundingBox;
		if ( ! bb ) {
			return;
		}
		const s = bb.getSize( new THREE.Vector3() );
		const ws = mesh.getWorldScale( new THREE.Vector3() );
		const dims = [ s.x * ws.x, s.y * ws.y, s.z * ws.z ];
		const sorted = [ ...dims ].sort( ( a, b ) => a - b );
		const thin = sorted[ 0 ];
		const big = sorted[ 2 ];
		if ( big <= 0 || thin / big > 0.25 ) {
			return;
		}
		const thinAxis = dims.indexOf( thin );
		const normal = new THREE.Vector3( thinAxis === 0 ? 1 : 0, thinAxis === 1 ? 1 : 0, thinAxis === 2 ? 1 : 0 )
			.applyQuaternion( mesh.getWorldQuaternion( q ) )
			.normalize();
		const score = sorted[ 1 ] * big * ( 0.15 + ( 1 - Math.abs( normal.y ) ) );
		if ( score > bestScore ) {
			bestScore = score;
			best = mesh;
		}
	} );
	return best ? [ best ] : [];
}

/** World width-per-U / height-per-V for a screen mesh — its true display aspect. */
export function screenUVAspect( mesh ) {
	const geo = mesh.geometry;
	const pos = geo.attributes.position;
	const uv = geo.attributes.uv;
	if ( ! pos || ! uv ) {
		return 1;
	}
	const idx = geo.index;
	const a = idx ? idx.getX( 0 ) : 0;
	const b = idx ? idx.getX( 1 ) : 1;
	const c = idx ? idx.getX( 2 ) : 2;
	const p0 = new THREE.Vector3().fromBufferAttribute( pos, a );
	const dp1 = new THREE.Vector3().fromBufferAttribute( pos, b ).sub( p0 );
	const dp2 = new THREE.Vector3().fromBufferAttribute( pos, c ).sub( p0 );
	const u0 = new THREE.Vector2().fromBufferAttribute( uv, a );
	const u1 = new THREE.Vector2().fromBufferAttribute( uv, b );
	const u2 = new THREE.Vector2().fromBufferAttribute( uv, c );
	const du1 = u1.x - u0.x;
	const dv1 = u1.y - u0.y;
	const du2 = u2.x - u0.x;
	const dv2 = u2.y - u0.y;
	const det = du1 * dv2 - du2 * dv1;
	if ( Math.abs( det ) < 1e-9 ) {
		return 1;
	}
	const dPdU = dp1.clone().multiplyScalar( dv2 ).addScaledVector( dp2, -dv1 ).divideScalar( det );
	const dPdV = dp1.clone().multiplyScalar( -du2 ).addScaledVector( dp2, du1 ).divideScalar( det );
	const wv = dPdV.length();
	return wv < 1e-9 ? 1 : dPdU.length() / wv;
}

/** Cover-fit the image (keep aspect) then apply the user's pan/zoom. */
export function applyScreenImage( tex, imgAspect, screenAspect, screenImg ) {
	let ru = 1;
	let rv = 1;
	if ( imgAspect > screenAspect ) {
		ru = screenAspect / imgAspect; // image wider → crop sides
	} else {
		rv = imgAspect / screenAspect; // image taller → crop top/bottom
	}
	ru /= screenImg.scale;
	rv /= screenImg.scale;
	tex.repeat.set( ru, rv );
	tex.offset.set( ( 1 - ru ) / 2 + screenImg.x, ( 1 - rv ) / 2 + screenImg.y );
	tex.needsUpdate = true;
}
