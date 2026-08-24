import { createRoot } from '@wordpress/element';
import MockupApp from './app/MockupApp';

// Mount once on a detached node appended to the admin body; the modal opens from
// the "Open 3D Mockup" button in the product meta box.
const mount = document.createElement( 'div' );
mount.id = 'snel-3d-mockup-root';
document.body.appendChild( mount );
createRoot( mount ).render( <MockupApp /> );
