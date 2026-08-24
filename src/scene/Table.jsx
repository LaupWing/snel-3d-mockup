import { useEffect, useRef } from '@wordpress/element';
import * as THREE from 'three';
import { useGLTF } from '@react-three/drei';

/**
 * Noguchi coffee table the device sits on. Grounds itself (base at y=0), makes
 * its glass top glossy/transmissive, and reports its height so the device can be
 * placed on the tabletop.
 */
export default function Table( { url, scale, onHeight } ) {
	const { scene } = useGLTF( url );
	const done = useRef( false );

	useEffect( () => {
		if ( done.current ) {
			return;
		}
		scene.scale.setScalar( scale );
		scene.updateMatrixWorld( true );
		const box = new THREE.Box3().setFromObject( scene );
		const center = box.getCenter( new THREE.Vector3() );
		scene.position.x -= center.x;
		scene.position.z -= center.z;
		scene.position.y -= box.min.y;
		scene.traverse( ( mesh ) => {
			if ( ! mesh.isMesh ) {
				return;
			}
			mesh.castShadow = true;
			mesh.receiveShadow = true;
			const mats = Array.isArray( mesh.material ) ? mesh.material : [ mesh.material ];
			mats.forEach( ( mat ) => {
				if ( mat && mat.name && mat.name.toLowerCase().includes( 'glass' ) ) {
					mat.roughness = 0.02;
					mat.metalness = 0;
					mat.clearcoat = 1;
					mat.clearcoatRoughness = 0.02;
					mat.envMapIntensity = 2.5;
					mat.transmission = Math.max( mat.transmission ?? 0, 0.6 );
					mat.ior = 1.5;
					mat.needsUpdate = true;
				}
			} );
		} );
		done.current = true;
		onHeight( box.max.y - box.min.y );
	}, [ scene, scale, onHeight ] );

	return <primitive object={ scene } />;
}
