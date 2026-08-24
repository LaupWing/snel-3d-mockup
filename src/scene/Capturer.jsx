import { useEffect } from '@wordpress/element';
import { useThree } from '@react-three/fiber';

/**
 * Hands the app the live canvas + a grab() that returns the current WebGL
 * frame as a PNG data URL. Export resolution is orchestrated by the APP (it
 * owns the Canvas dpr prop): r3f re-applies that prop on every re-render, so
 * bumping dpr from inside the scene gets reverted mid-capture.
 * preserveDrawingBuffer keeps the composited frame, so the export includes
 * postprocessing (AO/bloom/tone mapping).
 */
export default function Capturer( { onReady } ) {
	const gl = useThree( ( s ) => s.gl );
	useEffect( () => {
		onReady( {
			canvas: gl.domElement,
			grab: () => gl.domElement.toDataURL( 'image/png' ),
		} );
		return () => onReady( null );
	}, [ gl, onReady ] );
	return null;
}
