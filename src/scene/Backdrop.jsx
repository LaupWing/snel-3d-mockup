import { useMemo } from '@wordpress/element';
import * as THREE from 'three';
import { Backdrop as DreiBackdrop } from '@react-three/drei';

/** Vertical gradient texture — a seamless studio cyclorama (dark wall → light floor). */
function useGradientTexture() {
	return useMemo( () => {
		const c = document.createElement( 'canvas' );
		c.width = 16;
		c.height = 256;
		const ctx = c.getContext( '2d' );
		const g = ctx.createLinearGradient( 0, 0, 0, 256 );
		g.addColorStop( 0, '#26262b' );
		g.addColorStop( 0.6, '#5a5a62' );
		g.addColorStop( 1, '#a9a9b0' );
		ctx.fillStyle = g;
		ctx.fillRect( 0, 0, 16, 256 );
		const t = new THREE.CanvasTexture( c );
		t.colorSpace = THREE.SRGBColorSpace;
		return t;
	}, [] );
}

export default function StudioBackdrop() {
	const gradient = useGradientTexture();
	return (
		<DreiBackdrop floor={ 1.5 } segments={ 20 } scale={ [ 60, 30, 12 ] } position={ [ 0, 0, -9 ] } receiveShadow>
			<meshStandardMaterial map={ gradient } roughness={ 0.6 } metalness={ 0.6 } envMapIntensity={ 1 } />
		</DreiBackdrop>
	);
}
