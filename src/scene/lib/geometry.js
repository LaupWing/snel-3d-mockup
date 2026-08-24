import * as THREE from 'three';

/**
 * Clone a material into a MeshPhysicalMaterial. GLBs often ship the screen as a
 * plain MeshStandardMaterial (no clearcoat), so the glossy glare layer silently
 * never applies. Upgrading is what makes the gloss real. We copy only the fields
 * a standard material has — phys.copy(std) crashes on physical-only props.
 */
export function toPhysical( mat ) {
	if ( mat.isMeshPhysicalMaterial ) {
		return mat;
	}
	const s = mat;
	const phys = new THREE.MeshPhysicalMaterial();
	phys.name = s.name;
	if ( s.color ) phys.color.copy( s.color );
	if ( s.emissive ) phys.emissive.copy( s.emissive );
	phys.map = s.map;
	phys.normalMap = s.normalMap;
	if ( s.normalScale ) phys.normalScale.copy( s.normalScale );
	phys.aoMap = s.aoMap;
	phys.emissiveMap = s.emissiveMap;
	phys.roughness = s.roughness ?? 1;
	phys.metalness = s.metalness ?? 0;
	phys.transparent = s.transparent;
	phys.opacity = s.opacity;
	phys.side = s.side;
	return phys;
}

export function assignMaterial( mesh, index, mat ) {
	if ( Array.isArray( mesh.material ) ) {
		mesh.material[ index ] = mat;
	} else {
		mesh.material = mat;
	}
}

/**
 * Bounding box of a subtree in the ROOT's OWN local space — independent of any
 * parent transform (e.g. the device-scale group). Without this, setFromObject
 * bakes in whatever scale is applied when the device first grounds, so framing
 * varies by switch order.
 */
export function localBox( root, meshes ) {
	root.updateMatrixWorld( true );
	const inv = root.matrixWorld.clone().invert();
	const box = new THREE.Box3();
	const tmp = new THREE.Box3();
	const mat = new THREE.Matrix4();
	const add = ( m ) => {
		if ( ! m.geometry ) {
			return;
		}
		m.geometry.computeBoundingBox();
		const bb = m.geometry.boundingBox;
		if ( ! bb ) {
			return;
		}
		tmp.copy( bb ).applyMatrix4( mat.multiplyMatrices( inv, m.matrixWorld ) );
		box.union( tmp );
	};
	if ( meshes ) {
		meshes.forEach( add );
	} else {
		root.traverse( ( o ) => {
			if ( o.isMesh ) {
				add( o );
			}
		} );
	}
	return box;
}
