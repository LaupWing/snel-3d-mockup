/**
 * Screenshot the standalone mockup page with the installed Chrome.
 * Usage: node dev-mockup-test/shoot.js <out.png> [querystring]
 * Waits for the GLB/HDR to load and the temporal shadows to settle.
 */
const puppeteer = require( 'puppeteer-core' );

const out = process.argv[ 2 ] || 'shot.png';
const query = process.argv[ 3 ] || '';
const url = 'http://localhost:10084/wp-content/plugins/snel-3d-mockup/dev/dist/index.html' + ( query ? '?' + query : '' );

( async () => {
	const browser = await puppeteer.launch( {
		executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
		headless: 'new',
		args: [ '--window-size=1200,800', '--hide-scrollbars' ],
	} );
	const page = await browser.newPage();
	const width = parseInt( process.env.SHOT_W || '1200', 10 );
	const height = parseInt( process.env.SHOT_H || '800', 10 );
	await page.setViewport( { width, height } );
	await page.goto( url, { waitUntil: 'networkidle0', timeout: 90000 } );
	// Let the scene settle: temporal AccumulativeShadows need ~100 frames.
	await new Promise( ( r ) => setTimeout( r, 6000 ) );
	await page.screenshot( { path: out } );
	await browser.close();
	console.log( 'saved', out );
} )();
