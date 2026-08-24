import { Environment } from '@react-three/drei';

/**
 * Studio lighting: HDRI fill + a 3-point directional rig. The key light casts a
 * real shadow (back-right); fill lifts the dark side; rim pops the edges.
 */
export default function Lighting( { hdriUrl, showBg, env, ambient, keyLight, fill, rim } ) {
	return (
		<>
			<Environment files={ hdriUrl } environmentIntensity={ env } background={ !! showBg } />
			<ambientLight intensity={ ambient } />
			<directionalLight
				position={ keyLight.pos }
				intensity={ keyLight.intensity }
				castShadow
				shadow-mapSize-width={ 2048 }
				shadow-mapSize-height={ 2048 }
				shadow-bias={ -0.0002 }
				shadow-camera-near={ 0.5 }
				shadow-camera-far={ 50 }
				shadow-camera-left={ -12 }
				shadow-camera-right={ 12 }
				shadow-camera-top={ 12 }
				shadow-camera-bottom={ -12 }
			/>
			<directionalLight position={ fill.pos } intensity={ fill.intensity } />
			<directionalLight position={ rim.pos } intensity={ rim.intensity } color={ rim.color } />
		</>
	);
}
