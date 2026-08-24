/**
 * Standalone test harness for the 3D mockup scene — NO wp-admin needed.
 * Renders the exact Scene with DEFAULT_STUDIO values (or ?overrides via query
 * string) so headless Chrome can screenshot it. Dev-only, never deployed.
 */
import { createRoot } from 'react-dom/client';
import { Suspense, useState, useCallback } from 'react';
import * as THREE from 'three';
import { Canvas } from '@react-three/fiber';
import Scene from '../src/scene/Scene';
import Capturer from '../src/scene/Capturer';
import { DEFAULT_STUDIO } from '../src/app/defaults';
import { DEFAULT_CAMERA } from '../src/config/config';
import { presetFor } from '../src/config/presets';

const qs = new URLSearchParams( window.location.search );
const num = ( key, fallback ) => ( qs.has( key ) ? parseFloat( qs.get( key ) ) : fallback );

const DATA = window.snelMockup;
const glbUrl = qs.get( 'model' )
	? DATA.models.find( ( m ) => m.id === qs.get( 'model' ) )?.url || DATA.models[ 0 ].url
	: DATA.models[ 0 ].url;
const preset = presetFor( glbUrl ) || {};

const ctrl = {
	...DEFAULT_STUDIO,
	deviceScale: preset.scale ?? DEFAULT_STUDIO.deviceScale,
	offsetY: ( preset.offset && preset.offset[ 1 ] ) ?? DEFAULT_STUDIO.offsetY,
	rotationY: ( preset.rotation && preset.rotation[ 1 ] ) ?? DEFAULT_STUDIO.rotationY,
};
// Any studio value can be overridden from the URL: ?screenBrightness=1.2&envIntensity=0.4
Object.keys( ctrl ).forEach( ( key ) => {
	if ( qs.has( key ) && typeof ctrl[ key ] === 'number' ) {
		ctrl[ key ] = num( key, ctrl[ key ] );
	}
} );

function App() {
	// Same mechanism as MockupApp: the dpr PROP is the source of truth; the
	// export switches it, waits for the composer + shadows, grabs, restores.
	const [ dpr, setDpr ] = useState( 1 );
	const onReady = useCallback( ( reg ) => {
		if ( ! reg ) {
			return;
		}
		window.__snelCapture = async () => {
			const target = Math.min( 4, Math.max( 2, 2560 / Math.max( 1, reg.canvas.clientWidth ) ) );
			console.log( '[snel-mockup] capture v3: dpr →', target, '| canvas', reg.canvas.clientWidth + 'px css' );
			setDpr( target );
			await new Promise( ( r ) => setTimeout( r, 1600 ) );
			const url = reg.grab();
			console.log( '[snel-mockup] captured frame:', reg.canvas.width, 'x', reg.canvas.height, 'px' );
			setDpr( 1 );
			return url;
		};
	}, [] );
	return (
		<div style={ { position: 'fixed', inset: 0 } }>
			<Canvas
				camera={ { position: DEFAULT_CAMERA, fov: 28 } }
				dpr={ dpr }
				shadows
				gl={ {
					preserveDrawingBuffer: true,
					antialias: true,
					toneMapping: THREE.NoToneMapping,
				} }
			>
				<Capturer onReady={ onReady } />
				<Suspense fallback={ null }>
					<Scene
						exportMode={ dpr !== 1 }
						glbUrl={ glbUrl }
						hdriUrl={ DATA.hdris[ 0 ].url }
						showBg={ false }
						studio={ ! qs.has( 'noStudio' ) }
						showTable={ ! qs.has( 'noTable' ) }
						imageUrl={ DATA.imageUrl }
						screenImg={ { scale: 1, x: 0, y: 0 } }
						ctrl={ ctrl }
						screenRotate={ preset.screenRotate || 0 }
						presetReady={ true }
						resetKey={ 0 }
					/>
				</Suspense>
			</Canvas>
		</div>
	);
}

createRoot( document.getElementById( 'root' ) ).render( <App /> );
