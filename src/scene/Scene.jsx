import { useState, useMemo } from '@wordpress/element';
import * as THREE from 'three';
import { OrbitControls, AccumulativeShadows, RandomizedLight } from '@react-three/drei';
import { EffectComposer, N8AO, Bloom, ToneMapping } from '@react-three/postprocessing';
import { ToneMappingMode } from 'postprocessing';
import { TABLE_URL } from '../config/config';
import Lighting from './Lighting';
import StudioBackdrop from './Backdrop';
import Table from './Table';
import Device from './Device';
import CameraRig from './CameraRig';

const TABLE_SCALE = 0.005; // table GLB is ~200× the device's scale

/**
 * Composes the studio scene. Pure-ish: it owns only the device's measured frame
 * + table height; everything else (the Leva values `ctrl`, screenRotate,
 * presetReady) comes from the app so all settings can be saved/loaded centrally.
 */
export default function Scene( { glbUrl, hdriUrl, showBg, studio, showTable, imageUrl, screenImg, ctrl, screenRotate, presetReady, resetKey, savedCamera = null, exportMode = false } ) {
	const [ rawTableTop, setTableTop ] = useState( 0 );
	const tableTop = showTable ? rawTableTop : 0;
	const [ deviceFrame, setDeviceFrame ] = useState( { radius: 1, center: [ 0, 0.5, 0 ], url: '' } );

	// World position of the screen centre, transformed by the device's
	// scale/rotation/offset so the camera stays locked on the screen.
	const anchorPos = useMemo( () => {
		const p = new THREE.Vector3( ...deviceFrame.center ).multiplyScalar( ctrl.deviceScale );
		p.applyEuler( new THREE.Euler( 0, THREE.MathUtils.degToRad( ctrl.rotationY ), 0 ) );
		p.y += ctrl.offsetY + tableTop;
		return p;
	}, [ deviceFrame, ctrl.deviceScale, ctrl.rotationY, ctrl.offsetY, tableTop ] );

	return (
		<>
			{ ! showBg && <color attach="background" args={ [ '#0e0e11' ] } /> }

			<Lighting
				hdriUrl={ hdriUrl }
				showBg={ showBg }
				env={ ctrl.envIntensity }
				ambient={ ctrl.ambient }
				keyLight={ { pos: ctrl.keyPos, intensity: ctrl.keyIntensity } }
				fill={ { pos: ctrl.fillPos, intensity: ctrl.fillIntensity } }
				rim={ { pos: ctrl.rimPos, intensity: ctrl.rimIntensity, color: ctrl.rimColor } }
			/>

			{ studio && <StudioBackdrop /> }
			{ showTable && <Table url={ TABLE_URL } scale={ TABLE_SCALE } onHeight={ setTableTop } /> }

			<CameraRig
				target={ anchorPos }
				radius={ deviceFrame.radius * ctrl.deviceScale }
				cameraY={ ctrl.cameraY }
				ready={ deviceFrame.url !== '' }
				frameKey={ showTable ? 'table' : 'no-table' }
				resetKey={ resetKey }
				savedCamera={ savedCamera }
			/>

			{ /* Hidden until this device's preset has applied — no flash at the
			   previous device's scale/offset. Still loads + reports framing. */ }
			<group position={ [ 0, tableTop, 0 ] } visible={ presetReady }>
				<group
					position={ [ 0, ctrl.offsetY, 0 ] }
					rotation={ [ 0, THREE.MathUtils.degToRad( ctrl.rotationY ), 0 ] }
					scale={ ctrl.deviceScale }
				>
					<Device
						url={ glbUrl }
						imageUrl={ imageUrl }
						screenRotate={ screenRotate }
						onFrame={ setDeviceFrame }
						screen={ {
							brightness: ctrl.screenBrightness,
							gloss: ctrl.screenGloss,
							reflection: ctrl.screenReflection,
						} }
						screenImg={ screenImg }
					/>
				</group>
			</group>

			{ /* Exports flip to non-temporal: all 100 shadow frames resolve in one
			   go, so the capture can't catch the half-accumulated dark square
			   (GPU renders slower at export dpr, so waiting is a gamble). */ }
			<AccumulativeShadows
				key={ `${ ctrl.shadowRadius }-${ exportMode }` }
				temporal={ ! exportMode }
				frames={ 100 }
				alphaTest={ 0.85 }
				opacity={ ctrl.shadowOpacity }
				scale={ 20 }
				position={ [ 0, 0.002, 0 ] }
				color="#000000"
			>
				<RandomizedLight
					amount={ 8 }
					radius={ ctrl.shadowRadius }
					ambient={ 0.25 }
					intensity={ 1.4 }
					position={ [ -5, 8, -2 ] }
					bias={ 0.001 }
				/>
			</AccumulativeShadows>

			<OrbitControls makeDefault enableDamping />

			{ /* NEUTRAL (Khronos PBR) instead of AGX: AgX lifts blacks and mutes
			   the whole frame, which turned the screen into a washed-out gray.
			   Bloom threshold sits above the screen's emissive so the image
			   itself never glows hazy. */ }
			{ /* key on exportMode: N8AO keeps a stale AO buffer across the big
			   dpr jump, ghosting the old small frame into the export's bottom-left
			   corner — remounting the composer rebuilds every pass at full size. */ }
			<EffectComposer key={ exportMode ? 'export' : 'live' } multisampling={ 4 }>
				<N8AO aoRadius={ 0.4 } intensity={ 2 } distanceFalloff={ 1 } />
				<Bloom luminanceThreshold={ 1.5 } intensity={ 0.2 } mipmapBlur />
				<ToneMapping mode={ ToneMappingMode.NEUTRAL } />
			</EffectComposer>
		</>
	);
}
