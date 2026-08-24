import { useEffect, useRef } from '@wordpress/element';
import * as THREE from 'three';
import { useThree } from '@react-three/fiber';
import { DEFAULT_CAMERA } from '../config/config';

/**
 * Frames the device's screen on initial load and again whenever `frameKey`
 * changes (e.g. toggling the table, which moves the device vertically). It does
 * NOT re-frame on a device switch — those stay in the fixed frame via their
 * preset offset/scale. `cameraY` nudges it (a vertical pedestal).
 */
export default function CameraRig( { target, radius, cameraY, ready, frameKey, resetKey = 0 } ) {
	const camera = useThree( ( s ) => s.camera );
	const controls = useThree( ( s ) => s.controls );
	const prevKey = useRef( null );
	const prevReset = useRef( resetKey );
	const appliedY = useRef( 0 );

	useEffect( () => {
		if ( ! controls || ! ready ) {
			return;
		}
		const frameChanged = prevKey.current !== frameKey;
		const resetChanged = prevReset.current !== resetKey;
		if ( ! frameChanged && ! resetChanged ) {
			return;
		}
		// Default head-on direction on first frame OR on a Reset; otherwise keep
		// the current orbit (e.g. just a table toggle).
		const useDefault = prevKey.current === null || resetChanged;
		prevKey.current = frameKey;
		prevReset.current = resetKey;
		const dir = useDefault
			? new THREE.Vector3( ...DEFAULT_CAMERA )
			: camera.position.clone().sub( controls.target );
		if ( dir.lengthSq() < 1e-6 ) {
			dir.set( ...DEFAULT_CAMERA );
		}
		dir.normalize();
		const dist = ( radius * 1.3 ) / Math.sin( ( camera.fov * Math.PI ) / 360 );
		camera.position.copy( target ).addScaledVector( dir, dist );
		camera.position.y += cameraY;
		controls.target.copy( target );
		controls.target.y += cameraY;
		controls.update();
		appliedY.current = cameraY;
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ controls, ready, frameKey, resetKey ] );

	// cameraY pedestal — shift camera + target by the same delta (no rotation).
	useEffect( () => {
		if ( ! controls ) {
			return;
		}
		const delta = cameraY - appliedY.current;
		if ( delta !== 0 ) {
			camera.position.y += delta;
			controls.target.y += delta;
			controls.update();
			appliedY.current = cameraY;
		}
	}, [ cameraY, controls ] );

	return null;
}
