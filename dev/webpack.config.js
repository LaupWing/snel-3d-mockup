/**
 * Dev-only bundle for the standalone mockup test page: same code as the admin
 * tool but with React bundled in (no WP externals) and @wordpress/element
 * aliased to react so it runs on a plain HTML page. Output goes to dist/ —
 * webpack cleans its output dir, so it must not be this source dir.
 */
const path = require( 'path' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	entry: { entry: path.resolve( __dirname, 'entry.jsx' ) },
	output: { path: path.resolve( __dirname, 'dist' ), filename: '[name].js' },
	plugins: defaultConfig.plugins.filter(
		( p ) => ! [ 'DependencyExtractionWebpackPlugin', 'CleanWebpackPlugin' ].includes( p.constructor.name )
	),
	externals: {},
	resolve: {
		...defaultConfig.resolve,
		alias: {
			...( defaultConfig.resolve || {} ).alias,
			'@wordpress/element': require.resolve( 'react' ),
		},
	},
};
