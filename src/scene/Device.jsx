import { useEffect, useRef, useCallback } from '@wordpress/element';
import * as THREE from 'three';
import { useGLTF } from '@react-three/drei';
import { toPhysical, assignMaterial, localBox } from './lib/geometry';
import { findScreenMeshes, screenUVAspect, applyScreenImage } from './lib/screen';

/**
 * The device. Grounds itself (cached once on the scene — useGLTF reuses scenes),
 * measures framing in its OWN local space (so it's scale/order independent), and
 * turns the screen into a glossy lit display showing the website image.
 */
export default function Device( { url, imageUrl, screen, screenImg, screenRotate = 0, onFrame } ) {
	const { scene } = useGLTF( url );
	const screenMats = useRef( [] );
	const screenMeshes = useRef( [] );
	const screenTex = useRef( null );
	const imgAspect = useRef( 1 );

	useEffect( () => {
		scene.updateMatrixWorld( true );

		let data = scene.userData.__mockup;
		if ( ! data ) {
			const screens = findScreenMeshes( scene );
			const box = localBox( scene );
			const screenBox = screens.length ? localBox( scene, screens ) : box;
			const center = box.getCenter( new THREE.Vector3() );
			const screenCenter = screenBox.getCenter( new THREE.Vector3() );
			const framingSize = screenBox.getSize( new THREE.Vector3() );

			// Ground: centre the geometry over the origin, base at y=0 (+lift).
			scene.position.set( -center.x, -box.min.y + 0.08, -center.z );
			scene.traverse( ( o ) => {
				if ( o.isMesh ) {
					o.castShadow = true;
					o.receiveShadow = true;
				}
			} );

			data = {
				radius: 0.5 * framingSize.length(),
				center: [ screenCenter.x - center.x, screenCenter.y - box.min.y, screenCenter.z - center.z ],
				screens,
				screenAspect: screens.length ? screenUVAspect( screens[ 0 ] ) : 1,
			};
			scene.userData.__mockup = data;
		}

		screenMeshes.current = data.screens;
		onFrame?.( { radius: data.radius, center: data.center, url } );
	}, [ scene, onFrame, url ] );

	const applyImg = useCallback(
		( tex ) => {
			const as = scene.userData.__mockup?.screenAspect ?? 1;
			applyScreenImage( tex, imgAspect.current, as, screenImg );
		},
		[ scene, screenImg.scale, screenImg.x, screenImg.y ]
	);

	// Live pan/zoom — re-apply transform without reloading the texture.
	useEffect( () => {
		if ( screenTex.current ) {
			applyImg( screenTex.current );
		}
	}, [ applyImg ] );

	useEffect( () => {
		if ( ! imageUrl ) {
			return;
		}
		// Some GLBs have vertically-flipped screen UVs → flipY fixes it.
		const flip = screenRotate === 180;
		const onTexError = () => {
			// eslint-disable-next-line no-console
			console.error( '[snel-mockup] screen image failed to load:', imageUrl );
		};
		const tex = new THREE.TextureLoader().load( imageUrl, () => {
			// Anti-moiré: near-1:1 texture-to-screen sampling beats against the
			// render grid as subtle diagonal bands. Supersampling small sources
			// to ~2x pushes sampling into clean mipmap minification instead.
			const img = tex.image;
			if ( img && img.width && img.width < 2200 ) {
				const factor = Math.ceil( 2200 / img.width );
				const c = document.createElement( 'canvas' );
				c.width = img.width * factor;
				c.height = img.height * factor;
				const cctx = c.getContext( '2d' );
				cctx.imageSmoothingEnabled = true;
				cctx.imageSmoothingQuality = 'high';
				cctx.drawImage( img, 0, 0, c.width, c.height );
				tex.image = c;
			}
			tex.colorSpace = THREE.SRGBColorSpace;
			tex.anisotropy = 16; // keeps screen text crisp at viewing angles
			tex.flipY = flip;
			imgAspect.current = ( tex.image?.width || 1 ) / ( tex.image?.height || 1 );
			applyImg( tex );
			tex.needsUpdate = true;
		}, undefined, onTexError );
		screenTex.current = tex;

		screenMats.current = [];
		screenMeshes.current.forEach( ( mesh ) => {
			const mats = Array.isArray( mesh.material ) ? mesh.material : [ mesh.material ];
			mats.forEach( ( mat, i ) => {
				if ( ! mat ) {
					return;
				}
				const m = toPhysical( mat );
				m.map = tex;
				m.emissive = new THREE.Color( 0xffffff );
				m.emissiveMap = tex;
				m.roughness = 0.2;
				m.metalness = 0;
				m.clearcoat = 1;
				// Skip tone mapping: the screen should show the screenshot's own
				// contrast, not the ACES-compressed (grayed) version of it.
				m.toneMapped = false;
				m.needsUpdate = true;
				assignMaterial( mesh, i, m );
				screenMats.current.push( m );
			} );
		} );

		// Polished glass panels reflect the studio.
		const screenSet = new Set( screenMeshes.current );
		scene.traverse( ( mesh ) => {
			if ( ! mesh.isMesh || screenSet.has( mesh ) ) {
				return;
			}
			const mats = Array.isArray( mesh.material ) ? mesh.material : [ mesh.material ];
			mats.forEach( ( mat, i ) => {
				if ( mat && mat.name && mat.name.toLowerCase().includes( 'glass' ) ) {
					const m = toPhysical( mat );
					m.roughness = 0.05;
					m.metalness = 0;
					// Keep this near zero: the front glass sits OVER the LCD, and
					// any envmap strength here veils the screen with the white
					// studio no matter what the screen sliders are set to.
					m.envMapIntensity = 0.2;
					m.clearcoat = 1;
					m.clearcoatRoughness = 0.04;
					m.needsUpdate = true;
					assignMaterial( mesh, i, m );
				}
			} );
		} );

		return () => tex.dispose();
	}, [ scene, imageUrl, screenRotate ] );

	// Live gloss tuning — nudge the material without reloading the texture.
	// Brightness CROSSFADES instead of adds: the image is on the screen twice
	// (lit map + emissive layer). Adding them blows the whites out, so as the
	// emissive comes up the lit layer fades out — 0 = lit photo look,
	// 1 = the screenshot's exact colors, anything between blends.
	useEffect( () => {
		const b = Math.min( 1, screen.brightness );
		screenMats.current.forEach( ( m ) => {
			m.emissiveIntensity = b;
			m.color.setScalar( 1 - b );
			m.clearcoatRoughness = screen.gloss;
			m.envMapIntensity = screen.reflection;
			m.needsUpdate = true;
		} );
	}, [ screen.brightness, screen.gloss, screen.reflection ] );

	return <primitive object={ scene } />;
}
