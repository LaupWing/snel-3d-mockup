import { useState } from '@wordpress/element';
import { Button } from '@wordpress/components';

const backdrop = {
	position: 'fixed',
	inset: 0,
	zIndex: 1000000, // above the studio overlay (999999)
	background: 'rgba(0,0,0,0.55)',
	display: 'flex',
	alignItems: 'center',
	justifyContent: 'center',
};
const card = {
	width: 420,
	maxWidth: '92vw',
	background: '#fff',
	color: '#1e1e1e',
	borderRadius: 8,
	padding: 20,
	boxShadow: '0 12px 48px rgba(0,0,0,0.4)',
};
const field = { display: 'flex', flexDirection: 'column', gap: 4, marginBottom: 14, fontSize: 12, fontWeight: 600 };
const input = { fontWeight: 400, padding: '6px 8px', border: '1px solid #ccc', borderRadius: 4, fontSize: 13 };

/**
 * Review/edit the file name + alt text before saving the mockup into the Media
 * Library. Both default to the product title in dashes (file name) / the product
 * title (alt) so it's one click if you don't want to tweak anything.
 */
export default function ExportDialog( { defaultName, defaultAlt, busy, onCancel, onConfirm } ) {
	const [ name, setName ] = useState( defaultName );
	const [ alt, setAlt ] = useState( defaultAlt );

	return (
		<div style={ backdrop } onClick={ busy ? undefined : onCancel }>
			<div style={ card } onClick={ ( e ) => e.stopPropagation() }>
				<h2 style={ { margin: '0 0 16px', fontSize: 16 } }>Save mockup to WordPress</h2>

				<label style={ field }>
					File name
					<span style={ { display: 'flex', alignItems: 'center', gap: 4 } }>
						<input
							style={ { ...input, flex: 1 } }
							value={ name }
							onChange={ ( e ) => setName( e.target.value ) }
							placeholder="product-name"
						/>
						<span style={ { fontWeight: 400, color: '#888' } }>.png</span>
					</span>
				</label>

				<label style={ field }>
					Alt text
					<textarea
						style={ { ...input, resize: 'vertical', minHeight: 60 } }
						value={ alt }
						onChange={ ( e ) => setAlt( e.target.value ) }
						placeholder="Describe the image for accessibility / SEO"
					/>
				</label>

				<div style={ { display: 'flex', justifyContent: 'flex-end', gap: 8, marginTop: 4 } }>
					<Button variant="tertiary" onClick={ onCancel } disabled={ busy }>
						Cancel
					</Button>
					<Button
						variant="primary"
						isBusy={ busy }
						disabled={ busy }
						onClick={ () => onConfirm( { name: name.trim() || defaultName, alt: alt.trim() } ) }
					>
						{ busy ? 'Saving…' : 'Save to WordPress' }
					</Button>
				</div>
			</div>
		</div>
	);
}
