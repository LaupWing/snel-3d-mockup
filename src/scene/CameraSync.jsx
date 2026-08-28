import { useEffect } from '@wordpress/element';
import { useThree } from '@react-three/fiber';

/**
 * Exposes the orbit camera to the app: get() returns { position, target },
 * set() restores them (used on preset load). Registered like the Capturer.
 */
export default function CameraSync( { onReady } ) {
	const camera = useThree( ( s ) => s.camera );
	const controls = useThree( ( s ) => s.controls );

	useEffect( () => {
		if ( ! controls ) {
			return;
		}
		onReady( {
			get: () => ( {
				position: camera.position.toArray(),
				target: controls.target.toArray(),
			} ),
			set: ( cam ) => {
				if ( ! cam?.position || ! cam?.target ) {
					return;
				}
				camera.position.set( ...cam.position );
				controls.target.set( ...cam.target );
				controls.update();
			},
		} );
	}, [ controls, camera, onReady ] );

	return null;
}
