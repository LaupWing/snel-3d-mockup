import { Button } from '@wordpress/components';
import { MODELS, HDRIS } from '../config/config';

const row = { display: 'flex', flexDirection: 'column', gap: 4, marginBottom: 12, fontSize: 12 };
const check = { display: 'flex', flexDirection: 'row', alignItems: 'center', gap: 8, marginBottom: 8 };

/**
 * Left sidebar: device, screen image (WP media), HDRI, image transform, scene
 * toggles, and the Save / Reset actions. Pure presentational — state lives in
 * the app.
 */
export default function LeftPanel( { panel, setPanel, pickImage, onSave, onReset, saving, dirty, presetNames = [], activePreset = '', onSelectPreset } ) {
	const set = ( key ) => ( v ) => setPanel( ( p ) => ( { ...p, [ key ]: v } ) );

	return (
		<aside style={ { width: 240, padding: 16, overflowY: 'auto', borderRight: '1px solid #ddd' } }>
			<label style={ row }>
				Preset
				<select value={ activePreset } onChange={ ( e ) => onSelectPreset?.( e.target.value ) }>
					<option value="">— nieuw —</option>
					{ presetNames.map( ( n ) => (
						<option key={ n } value={ n }>
							{ n }
						</option>
					) ) }
				</select>
			</label>

			<div style={ { display: 'flex', gap: 8, marginBottom: 16 } }>
				<Button variant="primary" onClick={ onSave } isBusy={ saving } disabled={ saving }>
					{ saving ? 'Saving…' : activePreset ? 'Update preset' : 'Save preset' }
				</Button>
				<Button variant="secondary" onClick={ onReset }>
					Reset position
				</Button>
			</div>
			{ dirty && <p style={ { fontSize: 11, color: '#b26a00', marginTop: -8 } }>Unsaved changes</p> }

			<label style={ row }>
				Device
				<select value={ panel.glbUrl } onChange={ ( e ) => set( 'glbUrl' )( e.target.value ) }>
					{ MODELS.map( ( m ) => (
						<option key={ m.url } value={ m.url }>
							{ m.label }
						</option>
					) ) }
				</select>
			</label>

			<label style={ row }>
				Environment
				<select value={ panel.hdriUrl } onChange={ ( e ) => set( 'hdriUrl' )( e.target.value ) }>
					{ HDRIS.map( ( h ) => (
						<option key={ h.url } value={ h.url }>
							{ h.label }
						</option>
					) ) }
				</select>
			</label>

			<div style={ { marginBottom: 12 } }>
				<Button variant="secondary" onClick={ pickImage }>
					Choose screen image
				</Button>
			</div>

			<label style={ row }>
				Image scale { panel.imgScale.toFixed( 2 ) }
				<input
					type="range"
					min={ 0.2 }
					max={ 3 }
					step={ 0.01 }
					value={ panel.imgScale }
					onChange={ ( e ) => set( 'imgScale' )( parseFloat( e.target.value ) ) }
				/>
			</label>
			<label style={ row }>
				Image X { panel.imgX.toFixed( 2 ) }
				<input
					type="range"
					min={ -0.5 }
					max={ 0.5 }
					step={ 0.01 }
					value={ panel.imgX }
					onChange={ ( e ) => set( 'imgX' )( parseFloat( e.target.value ) ) }
				/>
			</label>
			<label style={ row }>
				Image Y { panel.imgY.toFixed( 2 ) }
				<input
					type="range"
					min={ -0.5 }
					max={ 0.5 }
					step={ 0.01 }
					value={ panel.imgY }
					onChange={ ( e ) => set( 'imgY' )( parseFloat( e.target.value ) ) }
				/>
			</label>

			<label style={ check }>
				<input type="checkbox" checked={ panel.studio } onChange={ ( e ) => set( 'studio' )( e.target.checked ) } />
				Studio backdrop
			</label>
			<label style={ check }>
				<input type="checkbox" checked={ panel.showBg } onChange={ ( e ) => set( 'showBg' )( e.target.checked ) } />
				Environment as background
			</label>
			<label style={ check }>
				<input type="checkbox" checked={ panel.showTable } onChange={ ( e ) => set( 'showTable' )( e.target.checked ) } />
				Show table
			</label>

			<p style={ { fontSize: 11, color: '#888', marginTop: 12 } }>
				Drag to orbit · scroll to zoom · right-drag to pan
			</p>
		</aside>
	);
}
