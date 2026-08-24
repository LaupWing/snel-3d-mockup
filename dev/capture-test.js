/**
 * End-to-end test of the export path: load the standalone page at modal-ish
 * canvas size, run the real Capturer, save the produced PNG.
 * Usage: node dev-mockup-test/capture-test.js <out.png> [querystring]
 */
const fs = require( 'fs' );
const puppeteer = require( 'puppeteer-core' );

const out = process.argv[ 2 ] || 'capture.png';
const query = process.argv[ 3 ] || '';
const url = 'http://localhost:10084/wp-content/plugins/snel-3d-mockup/dev/dist/index.html' + ( query ? '?' + query : '' );

( async () => {
	const browser = await puppeteer.launch( {
		executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
		headless: 'new',
		args: [ '--window-size=800,800', '--hide-scrollbars' ],
	} );
	const page = await browser.newPage();
	page.on( 'console', ( m ) => console.log( '[page]', m.text() ) );
	await page.setViewport( { width: 800, height: 800 } );
	await page.goto( url, { waitUntil: 'networkidle0', timeout: 90000 } );
	await new Promise( ( r ) => setTimeout( r, 5000 ) ); // scene settle
	const dataUrl = await page.evaluate( () => window.__snelCapture() );
	fs.writeFileSync( out, Buffer.from( dataUrl.split( ',' )[ 1 ], 'base64' ) );
	await browser.close();
	console.log( 'saved', out );
} )();
