import { useState, useEffect, useRef, useCallback, Suspense } from '@wordpress/element';
import { Button } from '@wordpress/components';
import { Canvas } from '@react-three/fiber';
import { Leva } from 'leva';
import * as THREE from 'three';
import { DEFAULT_CAMERA, SAVED_CONFIG, PRODUCT_ID, PRODUCT_TITLE, slugify } from '../config/config';
import { DEFAULT_PANEL, DEFAULT_STUDIO, initialState } from './defaults';
import { presetFor } from '../config/presets';
import { saveConfig, savePreset, uploadThumbnail } from './api';
import { SAVED_PRESETS } from '../config/config';
import { useStudioControls } from '../controls/useStudioControls';
import LeftPanel from '../controls/LeftPanel';
import Scene from '../scene/Scene';
import Capturer from '../scene/Capturer';

// STABLE identity: an inline [1,2] is a new array every render, and r3f
// re-applies the dpr prop whenever it changes — which stomped the temporary
// high dpr the Capturer sets during export (captures came out at canvas size).
const LIVE_DPR = [ 1, 2 ];
import ExportDialog from '../controls/ExportDialog';

/**
 * Top-level: just owns open/closed + the meta-box button. The studio (and Leva)
 * only mount while open, so nothing floats on the product screen when closed.
 */
export default function MockupApp() {
	const [ isOpen, setIsOpen ] = useState( false );

	useEffect( () => {
		const btn = document.getElementById( 'snel-open-3d-mockup' );
		if ( ! btn ) {
			return;
		}
		const open = () => setIsOpen( true );
		btn.addEventListener( 'click', open );
		return () => btn.removeEventListener( 'click', open );
	}, [] );

	return isOpen ? <Studio onClose={ () => setIsOpen( false ) } /> : null;
}

function Studio( { onClose } ) {
	const init = useRef( initialState() ).current;
	const [ panel, setPanel ] = useState( init.panel );
	const [ imageUrl, setImageUrl ] = useState( init.imageUrl );
	const [ studioValues, setStudio ] = useStudioControls();
	const [ screenRotate, setScreenRotate ] = useState( 0 );
	const [ presetUrl, setPresetUrl ] = useState( '' );
	const [ saving, setSaving ] = useState( false );
	const [ presets, setPresets ] = useState( SAVED_PRESETS );
	const [ activePreset, setActivePreset ] = useState( '' );
	const [ resetKey, setResetKey ] = useState( 0 );
	const [ exporting, setExporting ] = useState( false );
	const [ showExport, setShowExport ] = useState( false );
	// Canvas dpr lives in state: the PROP is r3f's source of truth (it re-applies
	// it on every re-render), so exports switch this value instead of fighting it.
	const [ canvasDpr, setCanvasDpr ] = useState( LIVE_DPR );
	const hydrated = useRef( false );

	// Capturer (inside the Canvas) registers { canvas, grab } — grab returns the
	// current frame as a PNG data URL.
	const captureRef = useRef( null );
	const registerCapture = useCallback( ( fn ) => {
		captureRef.current = fn;
	}, [] );

	// Re-render the scene at export resolution (~2560px), grab the frame,
	// restore the live dpr. The wait lets the composer resize and the temporal
	// shadows re-converge (they restart on resize and need ~100 frames).
	const captureHighRes = useCallback( async () => {
		const reg = captureRef.current;
		if ( ! reg ) {
			return null;
		}
		const target = Math.min( 4, Math.max( 2, 2560 / Math.max( 1, reg.canvas.clientWidth ) ) );
		// eslint-disable-next-line no-console
		console.log( '[snel-mockup] capture v3: dpr →', target, '| canvas', reg.canvas.clientWidth + 'px css' );
		setCanvasDpr( target );
		await new Promise( ( r ) => setTimeout( r, 2200 ) );
		const dataUrl = reg.grab();
		// eslint-disable-next-line no-console
		console.log( '[snel-mockup] captured frame:', reg.canvas.width, 'x', reg.canvas.height, 'px' );
		setCanvasDpr( LIVE_DPR );
		return dataUrl;
	}, [] );

	// Hydrate Leva from the saved blob once.
	useEffect( () => {
		if ( SAVED_CONFIG && SAVED_CONFIG.studio ) {
			setStudio( { ...DEFAULT_STUDIO, ...SAVED_CONFIG.studio } );
		}
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [] );

	// Apply the per-device preset on a device switch / table toggle. First run
	// (mount) only sets screenRotate — the transform stays at the hydrated/saved
	// values so reopening shows exactly what was saved.
	useEffect( () => {
		const preset = presetFor( panel.glbUrl );
		if ( preset ) {
			setScreenRotate( preset.screenRotate ?? 0 );
			if ( hydrated.current ) {
				const off = ! panel.showTable && preset.offsetNoTable ? preset.offsetNoTable : preset.offset;
				setStudio( { rotationY: preset.rotation[ 1 ], offsetY: off[ 1 ], deviceScale: preset.scale } );
			}
		}
		setPresetUrl( panel.glbUrl );
		hydrated.current = true;
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ panel.glbUrl, panel.showTable ] );

	const pickImage = useCallback( () => {
		if ( ! window.wp?.media ) {
			window.alert( 'WordPress media library is not loaded on this screen.' );
			return;
		}
		// The studio overlay sits at z-index 999999; the WP media modal defaults
		// to ~160000 and would open invisibly BEHIND it. Lift it above, once.
		if ( ! document.getElementById( 'snel-mockup-media-z' ) ) {
			const style = document.createElement( 'style' );
			style.id = 'snel-mockup-media-z';
			style.textContent = '.media-modal{z-index:1000001}.media-modal-backdrop{z-index:1000000}';
			document.head.appendChild( style );
		}
		const frame = window.wp.media( {
			title: 'Choose screen image',
			button: { text: 'Use this image' },
			multiple: false,
		} );
		frame.on( 'select', () => {
			const att = frame.state().get( 'selection' ).first().toJSON();
			// Always the ORIGINAL: any WP-generated size caps the screen texture
			// (large = 1024px) and the whole export inherits that blur.
			setImageUrl( ( att.sizes && att.sizes.full ? att.sizes.full.url : att.url ) );
		} );
		frame.open();
	}, [] );

	// Save = update the active preset (with an overwrite confirm), or ask a name
	// for a new one. Always also writes the global "last state" blob so
	// reopening shows what you saved.
	const onSave = useCallback( async () => {
		let name = activePreset;
		if ( name ) {
			if ( ! window.confirm( `Preset "${ name }" wordt overschreven met de huidige settings. Doorgaan?` ) ) {
				return;
			}
		} else {
			name = ( window.prompt( 'Naam voor deze preset:' ) || '' ).trim();
			if ( ! name ) {
				return;
			}
			if ( presets[ name ] && ! window.confirm( `Preset "${ name }" bestaat al en wordt overschreven. Doorgaan?` ) ) {
				return;
			}
		}
		setSaving( true );
		try {
			const config = { panel, studio: studioValues };
			const r = await savePreset( name, config );
			setPresets( r.presets || { ...presets, [ name ]: config } );
			setActivePreset( name );
			await saveConfig( config );
		} catch ( e ) {
			window.alert( e.message );
		} finally {
			setSaving( false );
		}
	}, [ panel, studioValues, activePreset, presets ] );

	// Load a preset: apply its panel + studio on top of the defaults.
	const onSelectPreset = useCallback( ( name ) => {
		setActivePreset( name );
		const p = presets[ name ];
		if ( ! p ) {
			return;
		}
		setPanel( ( prev ) => ( { ...DEFAULT_PANEL, ...p.panel, glbUrl: p.panel?.glbUrl || prev.glbUrl } ) );
		setStudio( { ...DEFAULT_STUDIO, ...( p.studio || {} ) } );
	}, [ presets, setStudio ] );

	// Reset = revert LIVE state to defaults only (keeps the chosen device/HDRI).
	// Never writes — reopening still shows the last saved state.
	const onReset = useCallback( () => {
		setPanel( ( p ) => ( { ...DEFAULT_PANEL, glbUrl: p.glbUrl, hdriUrl: p.hdriUrl } ) );
		setStudio( { ...DEFAULT_STUDIO } );
		setResetKey( ( k ) => k + 1 ); // re-frame the camera to the default angle
	}, [ setStudio ] );

	// Download the current frame as a PNG (capture is async: it re-renders at
	// export resolution first).
	const onDownload = useCallback( async () => {
		const dataUrl = await captureHighRes();
		if ( ! dataUrl ) {
			return;
		}
		const a = document.createElement( 'a' );
		a.href = dataUrl;
		a.download = 'mockup.png';
		a.click();
	}, [] );

	// Confirm from the dialog → capture the current frame and upload it to the
	// Media Library with the chosen file name + alt text, set as the product image.
	const onConfirmExport = useCallback( async ( { name, alt } ) => {
		setExporting( true );
		const dataUrl = await captureHighRes();
		if ( ! dataUrl ) {
			setExporting( false );
			return;
		}
		try {
			const r = await uploadThumbnail( dataUrl, PRODUCT_ID, true, name, alt );
			setShowExport( false );
			window.alert( r.featured ? 'Saved to the Media Library and set as the product image.' : 'Saved to the Media Library.' );
		} catch ( e ) {
			window.alert( e.message );
		} finally {
			setExporting( false );
		}
	}, [] );

	// Esc to close.
	useEffect( () => {
		const onKey = ( e ) => e.key === 'Escape' && onClose();
		window.addEventListener( 'keydown', onKey );
		return () => window.removeEventListener( 'keydown', onKey );
	}, [ onClose ] );

	// A real fullscreen overlay (above the admin bar) — the WP Modal never goes
	// truly edge-to-edge even with isFullScreen.
	return (
		<div style={ { position: 'fixed', inset: 0, zIndex: 999999, background: '#0e0e11', display: 'flex', flexDirection: 'column' } }>
			<header
				style={ {
					display: 'flex',
					alignItems: 'center',
					justifyContent: 'space-between',
					padding: '6px 14px',
					background: '#181c20',
					color: '#fff',
					borderBottom: '1px solid #2a2a32',
					flex: '0 0 auto',
				} }
			>
				<strong style={ { fontSize: 13 } }>3D Mockup</strong>
				<div style={ { display: 'flex', gap: 8, alignItems: 'center' } }>
					<Button variant="secondary" onClick={ onDownload }>
						Download PNG
					</Button>
					<Button variant="primary" onClick={ () => setShowExport( true ) }>
						Save to WordPress
					</Button>
					<Button variant="tertiary" onClick={ onClose }>
						Close
					</Button>
				</div>
			</header>

			<div style={ { flex: 1, display: 'flex', minHeight: 0 } }>
				<LeftPanel
					panel={ panel }
					setPanel={ setPanel }
					pickImage={ pickImage }
					onSave={ onSave }
					onReset={ onReset }
					saving={ saving }
					dirty={ false }
					presetNames={ Object.keys( presets ) }
					activePreset={ activePreset }
					onSelectPreset={ onSelectPreset }
				/>

				{ /* Square work area, centred. */ }
				<main style={ { flex: 1, display: 'flex', alignItems: 'center', justifyContent: 'center', background: '#0e0e11' } }>
					<div style={ { width: 'min(95vh, 100%)', aspectRatio: '1 / 1', maxWidth: '100%' } }>
						<Canvas
							camera={ { position: DEFAULT_CAMERA, fov: 28 } }
							dpr={ canvasDpr }
							shadows
							gl={ {
								preserveDrawingBuffer: true,
								antialias: true,
								toneMapping: THREE.NoToneMapping,
							} }
						>
							<Suspense fallback={ null }>
								<Scene
									exportMode={ canvasDpr !== LIVE_DPR }
									glbUrl={ panel.glbUrl }
									hdriUrl={ panel.hdriUrl }
									showBg={ panel.showBg }
									studio={ panel.studio }
									showTable={ panel.showTable }
									imageUrl={ imageUrl }
									screenImg={ { scale: panel.imgScale, x: panel.imgX, y: panel.imgY } }
									ctrl={ studioValues }
									screenRotate={ screenRotate }
									presetReady={ presetUrl === panel.glbUrl }
									resetKey={ resetKey }
								/>
							</Suspense>
							<Capturer onReady={ registerCapture } />
						</Canvas>
					</div>
				</main>

				{ /* Leva rendered INLINE (fill) on the right — no floating panel. */ }
				<div style={ { width: 300, overflowY: 'auto', background: '#181c20' } }>
					<Leva fill flat titleBar={ { drag: false, title: 'Studio' } } />
				</div>
			</div>

			{ showExport && (
				<ExportDialog
					defaultName={ slugify( PRODUCT_TITLE ) || 'mockup' }
					defaultAlt={ PRODUCT_TITLE }
					busy={ exporting }
					onCancel={ () => setShowExport( false ) }
					onConfirm={ onConfirmExport }
				/>
			) }
		</div>
	);
}
