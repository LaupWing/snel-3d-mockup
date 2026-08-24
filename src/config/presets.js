import { modelId } from './config';

/**
 * Per-device transform presets, keyed by model id (the GLB basename). Applied to
 * the Leva Device controls on device switch. `offsetNoTable` overrides offset
 * when the table is hidden; `screenRotate: 180` flips an upside-down screen.
 */
export const DEVICE_PRESETS = {
	imac: {
		rotation: [ 0, 0, 0 ],
		scale: 3,
		offset: [ 0, -0.25, 0 ],
		offsetNoTable: [ 0, 0, 0 ],
		screenRotate: 180,
	},
	'macbook-pro-m3': {
		rotation: [ 0, 0, 0 ],
		scale: 0.05,
		offset: [ 0, 0, 0 ],
		screenRotate: 180,
	},
	'iphone-13-pro-max': {
		rotation: [ 0, -180, 0 ],
		scale: 1.21,
		offset: [ 0, 0, 0 ],
		screenRotate: 180,
	},
};

export const presetFor = ( glbUrl ) => DEVICE_PRESETS[ modelId( glbUrl ) ] || null;
