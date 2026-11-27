// WPEssential controls: Elementor-style form controls (ported from hi-wpessential
// assets/js/wpe-controls.js). Each type builds { el, refresh } from a context
// { opt, label, get(), set( value ), device() }. Values are strings; composite controls
// (dimensions, typography, shadow, gradient, background, responsive) store JSON strings.

export interface ControlOptions {
	type?: string;
	min?: number;
	max?: number;
	step?: number;
	unit?: string;
	units?: string[];
	choices?: Record< string, string | [ string, string ] >;
	responsive?: boolean;
	placeholder?: string;
	tab?: string;
	literalColour?: boolean;
	defineColour?: boolean;
	defineTypography?: boolean;
}

export interface ControlContext {
	opt: ControlOptions;
	label?: string | undefined;
	get: () => string;
	set: ( value: string ) => void;
	device?: ( () => string ) | undefined;
}

export interface Control {
	el: HTMLElement;
	refresh: () => void;
	devices?: HTMLElement;
}

export interface ControlsConfig {
	palette?: Record< string, [ string, string ] >;
	typographyTokens?: Record< string, string >;
	themeFonts?: Array< { value: string; name: string; stack: string } >;
	googleList?: string;
	i18n?: Record< string, string >;
	/** Enabled breakpoints in cascade order, from Site Tools Kit → Layout → Breakpoints. */
	breakpoints?: Array< { key: string; label: string; width: number; dir: string } >;
	allBreakpoints?: Record< string, { label: string; width: number; on: boolean; dir: string } >;
	/** Site Tools Kit → Layout → Units: enabled built-in units and custom unit names. */
	units?: { builtin: string[]; on: string[]; custom: string[] };
}

type Json = Record< string, unknown >;
type Builder = ( ctx: ControlContext ) => Control;

const DEVICE_ICONS: Record< string, string > = {
	widescreen: '<rect x="1" y="4" width="22" height="13" rx="2"/><path d="M8 21h8M12 17v4"/>',
	desktop: '<rect x="2" y="4" width="20" height="13" rx="2"/><path d="M8 21h8M12 17v4"/>',
	laptop: '<rect x="4" y="5" width="16" height="11" rx="1.5"/><path d="M2 19h20"/>',
	tablet_extra: '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M18 12h.01"/>',
	tablet: '<rect x="5" y="2" width="14" height="20" rx="2"/><path d="M11 18h2"/>',
	mobile_extra: '<rect x="2" y="7" width="20" height="10" rx="2"/><path d="M18 12h.01"/>',
	mobile: '<rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/>',
	mobile_small: '<rect x="8" y="4" width="8" height="16" rx="2"/><path d="M11 17h2"/>',
};

/** A field's units narrowed to the site's enabled units; custom units join any length field. */
function siteUnits( units: string[] ): string[] {
	const site = cfg.units;
	if ( ! site || units.length === 0 ) {
		return units;
	}
	const kept = units.filter( ( u ) => u === '' || ! site.builtin.includes( u ) || site.on.includes( u ) );
	const out = kept.length ? kept : units.slice( 0, 1 );
	return units.includes( 'px' ) ? [ ...out, ...site.custom.filter( ( c ) => ! out.includes( c ) ) ] : out;
}

/** Enabled devices: [key, tooltip label, icon]. Falls back to desktop/tablet/phone. */
function devices(): Array< [ string, string, string ] > {
	const list = cfg.breakpoints?.length
		? cfg.breakpoints
		: [
				{ key: 'desktop', label: 'Desktop', width: 0, dir: '' },
				{ key: 'tablet', label: 'Tablet', width: 1023, dir: 'max' },
				{ key: 'mobile', label: 'Phone', width: 767, dir: 'max' },
			];
	return list.map( ( b ) => [
		b.key,
		b.dir === '' ? t( b.label ) : `${ t( b.label ) } · ${ b.dir === 'min' ? '≥' : '≤' } ${ b.width }px`,
		DEVICE_ICONS[ b.key ] ?? DEVICE_ICONS.desktop ?? '',
	] );
}

let cfg: ControlsConfig = {};
let googleList: Array< [ string, string, number[], number, number[] | null ] > | null = null;
const googleWaiters: Array< ( rows: NonNullable< typeof googleList > ) => void > = [];
const previewed: Record< string, true > = {};
const listeners: Array< () => void > = [];
let currentDevice = 'desktop';
let deviceObserver: ( ( device: string, width: number ) => void ) | undefined;

const t = ( text: string ): string => cfg.i18n?.[ text ] ?? text;

export function configure( config: ControlsConfig ): void {
	cfg = config;
}

/* ------------------------------------------------------------------ helpers */

type Attrs = Record< string, string | number | boolean | null | undefined | EventListener >;

export function h( tag: string, attrs: Attrs = {}, children: Array< Node | string | null | undefined > = [] ): HTMLElement {
	const el = document.createElement( tag );
	for ( const [ key, value ] of Object.entries( attrs ) ) {
		if ( key === 'class' ) {
			el.className = String( value );
		} else if ( key === 'text' ) {
			el.textContent = String( value );
		} else if ( key === 'html' ) {
			el.innerHTML = String( value );
		} else if ( key.startsWith( 'on' ) && typeof value === 'function' ) {
			el.addEventListener( key.slice( 2 ), value );
		} else if ( value !== false && value !== null && value !== undefined ) {
			el.setAttribute( key, value === true ? '' : String( value ) );
		}
	}
	for ( const child of children ) {
		if ( child ) {
			el.append( child );
		}
	}
	return el;
}

function svg( body: string, size = 14 ): string {
	return `<svg viewBox="0 0 24 24" width="${ size }" height="${ size }" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${ body }</svg>`;
}

// Flat line icons (24×24, stroke) used by the controls; never text glyphs.
const ICONS: Record< string, string > = {
	'chevron-down': '<path d="m6 9 6 6 6-6"/>',
	'chevron-left': '<path d="m15 18-6-6 6-6"/>',
	'chevron-right': '<path d="m9 18 6-6-6-6"/>',
	'chevrons-left': '<path d="m11 17-5-5 5-5"/><path d="m18 17-5-5 5-5"/>',
	'chevrons-right': '<path d="m6 17 5-5-5-5"/><path d="m13 17 5-5-5-5"/>',
	x: '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
	minus: '<path d="M5 12h14"/>',
	'case-upper': '<path d="m3 15 4-8 4 8"/><path d="M4 13h6"/><path d="M15 11h4.5a2 2 0 0 1 0 4H15V7h4a2 2 0 0 1 0 4"/>',
	'case-lower': '<circle cx="7" cy="12" r="3"/><path d="M10 9v6"/><circle cx="17" cy="12" r="3"/><path d="M14 7v8"/>',
	'case-sensitive': '<path d="m3 15 4-8 4 8"/><path d="M4 13h6"/><circle cx="18" cy="12" r="3"/><path d="M21 9v6"/>',
	type: '<path d="M4 7V4h16v3"/><path d="M9 20h6"/><path d="M12 4v16"/>',
	italic: '<path d="M19 4h-9"/><path d="M14 20H5"/><path d="M15 4 9 20"/>',
	underline: '<path d="M6 4v6a6 6 0 0 0 12 0V4"/><path d="M4 20h16"/>',
	strikethrough: '<path d="M16 4H9a3 3 0 0 0-2.83 4"/><path d="M14 12a4 4 0 0 1 0 8H6"/><path d="M4 12h16"/>',
	square: '<rect x="4" y="4" width="16" height="16" rx="2"/>',
	inset: '<rect x="3" y="3" width="18" height="18" rx="2"/><rect x="8" y="8" width="8" height="8" rx="1"/>',
};

export function icon( name: string, size = 14 ): string {
	return svg( ICONS[ name ] ?? ICONS.minus ?? '', size );
}

export function parse( value: unknown ): Json {
	if ( value && typeof value === 'object' ) {
		return value as Json;
	}
	if ( typeof value === 'string' && value.startsWith( '{' ) ) {
		try {
			return ( JSON.parse( value ) as Json ) ?? {};
		} catch {
			return {};
		}
	}
	return {};
}

function clean( obj: Json ): Json {
	const out: Json = {};
	for ( const [ key, raw ] of Object.entries( obj ) ) {
		let value = raw;
		if ( value && typeof value === 'object' ) {
			value = clean( value as Json );
			if ( Object.keys( value as Json ).length ) {
				out[ key ] = value;
			}
		} else if ( value !== '' && value !== null && value !== undefined && value !== false ) {
			out[ key ] = value;
		}
	}
	return out;
}

export function stringify( obj: Json ): string {
	const cleaned = clean( obj );
	return Object.keys( cleaned ).length ? JSON.stringify( cleaned ) : '';
}

const asString = ( value: unknown ): string => {
	if ( value && typeof value === 'object' ) {
		return JSON.stringify( value );
	}
	return value === undefined || value === null ? '' : String( value );
};

// A child control that reads and writes one key of a JSON value.
function child( ctx: ControlContext, key: string, opt: ControlOptions = {}, label?: string ): ControlContext {
	return {
		opt,
		label,
		device: ctx.device,
		get: () => asString( parse( ctx.get() )[ key ] ),
		set: ( value ) => {
			const obj = parse( ctx.get() );
			obj[ key ] = value.startsWith( '{' ) ? parse( value ) : value;
			ctx.set( stringify( obj ) );
		},
	};
}

function build( type: string, ctx: ControlContext ): Control {
	const builder = types[ type ] ?? types.text;
	if ( ! builder ) {
		throw new Error( `Unknown control type ${ type }` );
	}
	return builder( ctx );
}

/* ------------------------------------------------------------------ fonts */

function loadGoogle( done: ( rows: NonNullable< typeof googleList > ) => void ): void {
	if ( googleList ) {
		done( googleList );
		return;
	}
	googleWaiters.push( done );
	if ( googleWaiters.length > 1 || ! cfg.googleList ) {
		return;
	}
	window
		.fetch( cfg.googleList )
		.then( ( response ) => response.json() )
		.then( ( rows: NonNullable< typeof googleList > ) => {
			googleList = rows;
			googleWaiters.forEach( ( fn ) => {
				fn( rows );
			} );
		} )
		.catch( () => {
			googleList = [];
		} );
}

function preview( family: string, isGoogle: boolean ): void {
	if ( ! family || previewed[ family ] || ! isGoogle ) {
		return;
	}
	previewed[ family ] = true;
	document.head.append(
		h( 'link', {
			rel: 'stylesheet',
			href: `https://fonts.googleapis.com/css2?family=${ encodeURIComponent( family ).replace( /%20/g, '+' ) }&text=${ encodeURIComponent( family + 'Aa' ) }&display=swap`,
		} )
	);
}

const themeFont = ( value: string ) => ( cfg.themeFonts ?? [] ).find( ( font ) => font.value === value );

function fontLabel( value: string ): string {
	if ( ! value ) {
		return t( 'Theme default' );
	}
	return themeFont( value )?.name ?? value.replace( /^google:/, '' );
}

function fontStack( value: string ): string {
	if ( ! value ) {
		return 'inherit';
	}
	if ( value.startsWith( 'google:' ) ) {
		return `"${ value.slice( 7 ) }", system-ui`;
	}
	return themeFont( value )?.stack ?? value;
}

function fontPicker( current: string, onPick: ( value: string ) => void, multi = false ): HTMLElement {
	const cats: Array< [ string, string ] > = [
		[ 'all', t( 'All' ) ],
		[ 'theme', t( 'In theme' ) ],
		[ 'sans', 'Sans' ],
		[ 'serif', 'Serif' ],
		[ 'display', 'Display' ],
		[ 'hand', t( 'Handwriting' ) ],
		[ 'mono', 'Mono' ],
	];
	const state = { q: '', cat: 'all', limit: 60 };
	const list = h( 'div', { class: 'wpec-font__list', role: 'listbox', 'aria-label': t( 'Fonts' ) } );
	const search = h( 'input', { type: 'search', class: 'wpec-font__search', placeholder: t( 'Search 1,700+ fonts…' ), 'aria-label': t( 'Search fonts' ) } ) as HTMLInputElement;
	const chips = h( 'div', { class: 'wpec-font__cats' } );
	const observer =
		'IntersectionObserver' in window
			? new IntersectionObserver(
					( entries ) => {
						for ( const entry of entries ) {
							if ( entry.isIntersecting ) {
								const target = entry.target as HTMLElement;
								preview( target.dataset.family ?? '', target.dataset.google === '1' );
								observer?.unobserve( target );
							}
						}
					},
					{ root: list }
			  )
			: null;

	const row = ( value: string, family: string, meta: string, isGoogle: boolean, stack: string ): HTMLElement => {
		const button = h(
			'button',
			{
				type: 'button',
				role: 'option',
				class: 'wpec-font__item' + ( value === current ? ' is-active' : '' ),
				'aria-selected': value === current ? 'true' : 'false',
				'data-family': family,
				'data-google': isGoogle ? '1' : '0',
				onclick: () => onPick( value ),
			},
			[ h( 'span', { class: 'wpec-font__name', style: `font-family:${ stack }`, text: family } ), h( 'span', { class: 'wpec-font__meta', text: meta } ) ]
		);
		if ( observer ) {
			observer.observe( button );
		} else {
			preview( family, isGoogle );
		}
		return button;
	};

	const draw = (): void => {
		const q = state.q.trim().toLowerCase();
		list.replaceChildren();
		if ( ! multi ) {
			list.append( row( '', t( 'Theme default' ), t( 'From your theme' ), false, 'inherit' ) );
			if ( state.cat === 'all' || state.cat === 'theme' ) {
				for ( const font of cfg.themeFonts ?? [] ) {
					if ( ! q || font.name.toLowerCase().includes( q ) ) {
						list.append( row( font.value, font.name, t( 'In theme' ), false, font.stack ) );
					}
				}
			}
		}
		if ( state.cat === 'theme' ) {
			return;
		}
		loadGoogle( ( rows ) => {
			let shown = 0;
			let total = 0;
			for ( const font of rows ) {
				if ( ( state.cat !== 'all' && font[ 1 ] !== state.cat ) || ( q && ! font[ 0 ].toLowerCase().includes( q ) ) ) {
					continue;
				}
				total++;
				if ( shown < state.limit ) {
					shown++;
					list.append( row( 'google:' + font[ 0 ], font[ 0 ], 'Google · ' + ( font[ 4 ] ? t( 'variable' ) : `${ font[ 2 ].length } ${ t( 'weights' ) }` ), true, `"${ font[ 0 ] }", system-ui` ) );
				}
			}
			if ( total > shown ) {
				list.append(
					h( 'button', {
						type: 'button',
						class: 'wpec-font__more',
						text: `${ t( 'Show more' ) } (${ total - shown })`,
						onclick: () => {
							state.limit += 120;
							draw();
						},
					} )
				);
			}
			if ( ! list.children.length ) {
				list.append( h( 'p', { class: 'wpec-font__empty', text: t( 'No font found.' ) } ) );
			}
		} );
	};

	for ( const [ key, label ] of cats ) {
		chips.append(
			h( 'button', {
				type: 'button',
				class: 'wpec-chip' + ( key === 'all' ? ' is-active' : '' ),
				text: label,
				onclick: ( event: Event ) => {
					state.cat = key;
					state.limit = 60;
					chips.querySelectorAll( '.wpec-chip' ).forEach( ( b ) => {
						b.classList.toggle( 'is-active', b === event.currentTarget );
					} );
					draw();
				},
			} )
		);
	}
	search.addEventListener( 'input', () => {
		state.q = search.value;
		state.limit = 60;
		draw();
	} );
	draw();
	window.setTimeout( () => search.focus(), 0 );
	return h( 'div', { class: 'wpec-font__panel' }, [ search, chips, list ] );
}

/* ------------------------------------------------------------------ devices */

/** Customizer uses the same enabled device model as the shared controls. */
export function observeDevice( callback: ( device: string, width: number ) => void ): void {
	deviceObserver = callback;
}

export function setDevice( device: string ): string {
	currentDevice = devices().some( ( d ) => d[ 0 ] === device ) ? device : 'desktop';
	listeners.forEach( ( fn ) => {
		fn();
	} );
	deviceObserver?.( currentDevice, cfg.breakpoints?.find( ( b ) => b.key === currentDevice )?.width ?? 0 );
	return currentDevice;
}

/** Recompute the menu from an unpublished breakpoint setting, preserving valid selection. */
export function updateBreakpoints( value: string ): void {
	const stored = parse( value ) as Record< string, { on?: boolean; w?: number } >;
	if ( ! cfg.allBreakpoints ) {
		return;
	}
	cfg.breakpoints = Object.entries( cfg.allBreakpoints ).flatMap( ( [ key, base ] ) => {
		const row = stored[ key ] ?? {};
		if ( key !== 'desktop' && ! ( row.on ?? base.on ) ) {
			return [];
		}
		const n = Number( row.w ?? base.width );
		const width = key === 'desktop' ? 0 : Number.isFinite( n ) ? Math.max( 240, Math.min( 3840, Math.trunc( n ) ) ) : base.width;
		return [ { key, label: base.label, width, dir: base.dir } ];
	} ).sort( ( a, b ) => {
		const rank = ( dir: string ): number => dir === '' ? 0 : dir === 'min' ? 1 : 2;
		return rank( a.dir ) - rank( b.dir ) || b.width - a.width;
	} );
	setDevice( currentDevice );
}

// One device button per field: it shows the current device and opens a menu of enabled breakpoints.
function deviceSwitch( ctx: ControlContext ): HTMLElement {
	const box = h( 'span', { class: 'wpec-devices' } );
	const menu = h( 'span', { class: 'wpec-devices__menu', role: 'menu', hidden: true } );
	const trigger = h( 'button', {
		type: 'button',
		class: 'wpec-devices__btn wpec-devices__trigger',
		'aria-haspopup': 'menu',
		'aria-expanded': 'false',
		onclick: () => toggle(),
	} ) as HTMLButtonElement;
	const toggle = ( force?: boolean ): void => {
		const open = force ?? menu.hidden;
		menu.hidden = ! open;
		trigger.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		if ( open ) {
			menu.querySelector< HTMLElement >( '.is-active' )?.focus();
		}
	};
	let signature = '';
	const rebuild = (): void => {
		const next = JSON.stringify( devices() );
		if ( signature === next ) {
			return;
		}
		signature = next;
		menu.replaceChildren();
		for ( const [ key, label, icon ] of devices() ) {
			menu.append(
				h( 'button', {
					type: 'button',
					role: 'menuitemradio',
					class: 'wpec-devices__item',
					'data-device': key,
					title: label,
					html: `${ svg( icon, 14 ) }<span>${ label.replace( /[<>&]/g, '' ) }</span>`,
					onclick: () => {
						setDevice( key );
						toggle( false );
						trigger.focus();
					},
				} )
			);
		}
	};
	menu.addEventListener( 'keydown', ( event: KeyboardEvent ) => {
		const items = Array.from( menu.querySelectorAll< HTMLElement >( '.wpec-devices__item' ) );
		const at = items.indexOf( document.activeElement as HTMLElement );
		if ( event.key === 'ArrowDown' || event.key === 'ArrowUp' ) {
			event.preventDefault();
			items[ ( at + ( event.key === 'ArrowDown' ? 1 : -1 ) + items.length ) % items.length ]?.focus();
		} else if ( event.key === 'Escape' ) {
			toggle( false );
			trigger.focus();
		}
	} );
	document.addEventListener( 'click', ( event ) => {
		if ( ! box.contains( event.target as Node ) ) {
			toggle( false );
		}
	} );
	const sync = (): void => {
		rebuild();
		const current = ctx.device?.() ?? currentDevice;
		const found = devices().find( ( d ) => d[ 0 ] === current ) ?? devices()[ 0 ];
		if ( found ) {
			trigger.innerHTML = svg( found[ 2 ], 13 );
			trigger.title = found[ 1 ];
			trigger.setAttribute( 'aria-label', `${ t( 'Device' ) }: ${ found[ 1 ] }` );
			trigger.classList.toggle( 'is-active', found[ 0 ] !== 'desktop' );
		}
		menu.querySelectorAll< HTMLElement >( '.wpec-devices__item' ).forEach( ( b ) => {
			const on = b.dataset.device === current;
			b.classList.toggle( 'is-active', on );
			b.setAttribute( 'aria-checked', on ? 'true' : 'false' );
		} );
	};
	listeners.push( sync );
	sync();
	box.append( trigger, menu );
	return box;
}

// Wrap any control so it stores one value per device: { desktop, tablet, mobile }.
function responsive( type: string, ctx: ControlContext ): Control {
	const device = (): string => ctx.device?.() ?? currentDevice;
	const plain = (): boolean => ctx.get() !== '' && ! ctx.get().startsWith( '{' );
	const inner: ControlContext = {
		opt: { ...ctx.opt, responsive: false },
		label: ctx.label,
		device,
		get: () => {
			// A plain old value counts as the desktop value.
			if ( plain() ) {
				return device() === 'desktop' ? ctx.get() : '';
			}
			return asString( parse( ctx.get() )[ device() ] );
		},
		set: ( value ) => {
			const all = plain() ? { desktop: ctx.get() } : parse( ctx.get() );
			all[ device() ] = value.startsWith( '{' ) ? parse( value ) : value;
			ctx.set( stringify( all ) );
		},
	};
	const control = build( type, inner );
	listeners.push( control.refresh );
	return { el: control.el, refresh: control.refresh, devices: deviceSwitch( ctx ) };
}

/* ------------------------------------------------------------------ types */

const types: Record< string, Builder > = {};

types.text = ( ctx ) => {
	const input = h( 'input', {
		type: 'text',
		class: 'wpec-input',
		placeholder: ctx.opt.placeholder ?? t( 'Theme default' ),
		'aria-label': ctx.label,
		onchange: ( event: Event ) => ctx.set( ( event.target as HTMLInputElement ).value ),
	} ) as HTMLInputElement;
	return { el: input, refresh: () => ( input.value = ctx.get() ) };
};

types.color = ( ctx ) => {
	const wrap = h( 'div', { class: 'wpec-color' } );
	const chip = h( 'button', { type: 'button', class: 'wpec-color__chip', 'aria-haspopup': 'true', 'aria-expanded': 'false', 'aria-label': `${ ctx.label ?? '' }: ${ t( 'choose colour' ) }` } );
	const hex = h( 'input', {
		type: 'text',
		class: 'wpec-input wpec-color__hex',
		placeholder: t( 'Theme default' ),
		spellcheck: 'false',
		'aria-label': ctx.label,
		onchange: ( event: Event ) => {
			const value = ( event.target as HTMLInputElement ).value.trim();
			const validHex = /^#?(?:[0-9a-f]{3}|[0-9a-f]{4}|[0-9a-f]{6}|[0-9a-f]{8})$/i.test( value );
			if ( ! value || validHex || ( ! ctx.opt.literalColour && ( /^(rgb|hsl)a?\(/i.test( value ) || /^var\(/.test( value ) ) ) ) {
				ctx.set( value && /^[0-9a-f]{3,8}$/i.test( value ) ? '#' + value : value );
			}
		},
	} ) as HTMLInputElement;
	const pop = h( 'div', { class: 'wpec-pop', hidden: true } );
	const tabs = h( 'div', { class: 'wpec-tabs', role: 'tablist' } );
	const globals = h( 'div', { class: 'wpec-color__globals' } );
	const picker = h( 'input', { type: 'color', class: 'wpec-color__native', 'aria-label': t( 'Custom colour' ) } ) as HTMLInputElement;
	const alpha = h( 'input', { type: 'range', min: 0, max: 100, step: 1, class: 'wpec-color__alpha', 'aria-label': t( 'Opacity' ) } ) as HTMLInputElement;
	const custom = h( 'div', { class: 'wpec-color__custom', hidden: true }, [ picker, h( 'label', { class: 'wpec-color__alpha-row' }, [ h( 'span', { text: t( 'Opacity' ) } ), alpha ] ) ] );
	const setCustom = (): void => {
		const a = Math.round( Number( alpha.value ) * 2.55 );
		ctx.set( a === 255 ? picker.value : picker.value + ( a < 16 ? '0' : '' ) + a.toString( 16 ) );
	};
	picker.addEventListener( 'input', setCustom );
	alpha.addEventListener( 'input', setCustom );
	const hasPalette = ! ctx.opt.literalColour && Object.keys( cfg.palette ?? {} ).length > 0;
	( hasPalette ? [ [ 'globals', t( 'Global' ) ], [ 'custom', t( 'Custom' ) ] ] : [ [ 'custom', t( 'Custom' ) ] ] ).forEach( ( [ key, label ], index ) => {
		tabs.append(
			h( 'button', {
				type: 'button',
				role: 'tab',
				class: 'wpec-tabs__btn' + ( index === 0 ? ' is-active' : '' ),
				'aria-selected': index === 0 ? 'true' : 'false',
				text: label,
				onclick: ( event: Event ) => {
					tabs.querySelectorAll( '.wpec-tabs__btn' ).forEach( ( b ) => {
						b.classList.toggle( 'is-active', b === event.currentTarget );
						b.setAttribute( 'aria-selected', b === event.currentTarget ? 'true' : 'false' );
					} );
					globals.hidden = key !== 'globals';
					custom.hidden = key !== 'custom';
				},
			} )
		);
	} );
	globals.hidden = ! hasPalette;
	custom.hidden = hasPalette;
	for ( const [ slug, [ name, colour ] ] of Object.entries( ctx.opt.literalColour ? {} : cfg.palette ?? {} ) ) {
		const reference = ! ctx.opt.defineColour && slug.startsWith( 'wpe-custom-' ) ? `var(--wp--preset--color--${ slug })` : colour;
		globals.append(
			h(
				'button',
				{
					type: 'button',
					class: 'wpec-color__global',
					'data-color': reference.toLowerCase(),
					onclick: () => {
						ctx.set( reference );
						pop.hidden = true;
						chip.setAttribute( 'aria-expanded', 'false' );
					},
				},
				[ h( 'span', { class: 'wpec-color__dot', style: `background:${ colour }` } ), h( 'span', { text: name } ), h( 'code', { text: colour } ) ]
			)
		);
	}
	pop.append( tabs, globals, custom );
	chip.addEventListener( 'click', () => {
		pop.hidden = ! pop.hidden;
		chip.setAttribute( 'aria-expanded', pop.hidden ? 'false' : 'true' );
	} );
	document.addEventListener( 'click', ( event ) => {
		if ( ! wrap.contains( event.target as Node ) ) {
			pop.hidden = true;
			chip.setAttribute( 'aria-expanded', 'false' );
		}
	} );
	wrap.append( h( 'div', { class: 'wpec-color__row' }, [ chip, hex ] ), pop );
	return {
		el: wrap,
		refresh: () => {
			const value = ctx.get();
			const slug = /^var\(--wp--preset--color--([a-z0-9-]+)\)$/.exec( value )?.[ 1 ];
			const previewColour = slug ? cfg.palette?.[ slug ]?.[ 1 ] ?? '' : value;
			chip.style.background = previewColour ? `${ previewColour } padding-box` : '';
			chip.classList.toggle( 'is-empty', ! value );
			hex.value = value;
			const match = /^#([0-9a-f]{6})([0-9a-f]{2})?$/i.exec( value );
			if ( match ) {
				picker.value = '#' + match[ 1 ];
				alpha.value = String( match[ 2 ] ? Math.round( parseInt( match[ 2 ], 16 ) / 2.55 ) : 100 );
			}
			globals.querySelectorAll< HTMLElement >( '.wpec-color__global' ).forEach( ( g ) => {
				g.classList.toggle( 'is-active', g.dataset.color === value.toLowerCase() );
			} );
		},
	};
};

types.range = ( ctx ) => {
	const o = ctx.opt;
	const units = siteUnits( o.units ?? ( o.unit !== undefined && o.unit !== '' ? [ o.unit ] : [] ) );
	const withUnit = units.length > 1;
	const min = Number( o.min ?? 0 );
	const max = Number( o.max ?? 100 );
	const slider = h( 'input', { type: 'range', class: 'wpec-range__slider', min, max, step: o.step ?? 1, 'aria-label': ctx.label } ) as HTMLInputElement;
	const num = h( 'input', { type: 'number', class: 'wpec-input wpec-range__num', min, max, step: o.step ?? 1, placeholder: '–', 'aria-label': ctx.label } ) as HTMLInputElement;
	const unitSelect = withUnit ? ( h( 'select', { class: 'wpec-range__units', 'aria-label': t( 'Unit' ) }, units.map( ( u ) => h( 'option', { value: u, text: u || '—' } ) ) ) as HTMLSelectElement ) : null;
	const read = (): { size: unknown; unit: string } => {
		const value = ctx.get();
		const p = parse( value );
		return value.startsWith( '{' ) ? { size: p.size, unit: String( p.unit ?? units[ 0 ] ?? '' ) } : { size: value, unit: units[ 0 ] ?? '' };
	};
	const write = ( size: string ): void => {
		if ( size === '' ) {
			ctx.set( '' );
			return;
		}
		ctx.set( withUnit && unitSelect && unitSelect.value !== units[ 0 ] ? JSON.stringify( { size: Number( size ), unit: unitSelect.value } ) : String( size ) );
	};
	slider.addEventListener( 'input', () => write( slider.value ) );
	num.addEventListener( 'input', () => write( num.value ) );
	unitSelect?.addEventListener( 'change', () => write( num.value ) );
	const box = h( 'span', { class: 'wpec-range__box' + ( withUnit ? ' has-units' : '' ) }, [ num, withUnit ? unitSelect : units[ 0 ] ? h( 'span', { class: 'wpec-range__unit', text: units[ 0 ] } ) : null ] );
	return {
		el: h( 'div', { class: 'wpec-range__row' }, [ slider, box ] ),
		refresh: () => {
			const r = read();
			const value = r.size === undefined || r.size === null ? '' : String( r.size );
			num.value = value;
			if ( unitSelect ) {
				unitSelect.value = r.unit || ( units[ 0 ] ?? '' );
			}
			slider.value = value === '' ? String( ( min + max ) / 2 ) : value;
			slider.classList.toggle( 'is-empty', value === '' );
			const pct = value === '' ? 0 : ( ( Number( value ) - min ) / ( max - min ) ) * 100;
			slider.style.setProperty( '--wpec-fill', `${ Math.max( 0, Math.min( 100, pct ) ) }%` );
		},
	};
};

// Site Tools Kit → Layout → Breakpoints: a switch and a width per screen size; desktop is always on.
types.breakpoints = ( ctx ) => {
	const all = cfg.allBreakpoints ?? {};
	const rows: Array< () => void > = [];
	const read = (): Record< string, { on?: boolean; w?: number } > => parse( ctx.get() ) as Record< string, { on?: boolean; w?: number } >;
	const write = ( key: string, patch: { on?: boolean; w?: number } ): void => {
		const value = read();
		value[ key ] = { ...( value[ key ] ?? {} ), ...patch };
		ctx.set( stringify( value ) );
	};
	const list = h( 'div', { class: 'wpec-bps' } );
	for ( const [ key, b ] of Object.entries( all ) ) {
		const fixed = key === 'desktop';
		const sw = h( 'input', { type: 'checkbox', role: 'switch', class: 'wpec-switch__input', 'aria-label': t( b.label ), disabled: fixed, onchange: ( e: Event ) => write( key, { on: ( e.target as HTMLInputElement ).checked } ) } ) as HTMLInputElement;
		const width = h( 'input', { type: 'number', class: 'wpec-input wpec-bps__w', min: 240, max: 3840, step: 1, 'aria-label': `${ t( b.label ) } ${ t( 'width' ) }`, disabled: fixed, onchange: ( e: Event ) => write( key, { w: Number( ( e.target as HTMLInputElement ).value ) || b.width } ) } ) as HTMLInputElement;
		list.append(
			h( 'div', { class: 'wpec-bps__row' }, [
				h( 'span', { class: 'wpec-bps__icon', html: svg( DEVICE_ICONS[ key ] ?? '', 16 ) } ),
				h( 'span', { class: 'wpec-bps__label', text: t( b.label ) } ),
				fixed ? h( 'span', { class: 'wpec-bps__note', text: t( 'Base' ) } ) : h( 'span', { class: 'wpec-bps__dir', text: b.dir === 'min' ? '≥' : '≤' } ),
				fixed ? h( 'span' ) : width,
				fixed ? h( 'span' ) : h( 'span', { text: 'px' } ),
				fixed ? h( 'span' ) : h( 'label', { class: 'wpec-switch' }, [ sw, h( 'span', { class: 'wpec-switch__track', 'aria-hidden': 'true' } ) ] ),
			] )
		);
		rows.push( () => {
			const v = read()[ key ] ?? {};
			sw.checked = fixed || ( v.on ?? b.on );
			width.value = String( v.w ?? b.width );
		} );
	}
	return {
		el: list,
		refresh: () => {
			for ( const r of rows ) {
				r();
			}
		},
	};
};

// Site Tools Kit → Layout → Units: which CSS units appear, plus custom units "1 name = factor base".
types.units = ( ctx ) => {
	const builtin = cfg.units?.builtin ?? [ 'px', 'em', 'rem', '%', 'vw', 'vh' ];
	type Custom = { n: string; f: number | string; b: string };
	const read = (): { on: string; custom: Custom[] } => {
		const v = parse( ctx.get() ) as { on?: string; custom?: Custom[] | Record< string, Custom > };
		const custom = Array.isArray( v.custom ) ? v.custom : Object.values( v.custom ?? {} );
		return { on: v.on ?? ( cfg.units?.on ?? [] ).join( ',' ), custom };
	};
	const write = ( patch: Partial< { on: string; custom: Custom[] } > ): void => ctx.set( stringify( { ...read(), ...patch } ) );
	const chips = h( 'div', { class: 'wpec-units__chips', role: 'group', 'aria-label': t( 'Units' ) } );
	const boxes: HTMLInputElement[] = builtin.map( ( u ) => {
		const box = h( 'input', {
			type: 'checkbox',
			value: u,
			onchange: () => write( { on: boxes.filter( ( b ) => b.checked ).map( ( b ) => b.value ).join( ',' ) } ),
		} ) as HTMLInputElement;
		chips.append( h( 'label', { class: 'wpec-units__chip' }, [ box, h( 'span', { text: u } ) ] ) );
		return box;
	} );
	const rows = h( 'div', { class: 'wpec-units__custom' } );
	const draw = (): void => {
		rows.textContent = '';
		read().custom.forEach( ( c, index ) => {
			const update = ( patch: Partial< Custom > ): void => {
				const list = read().custom;
				list[ index ] = { ...( list[ index ] ?? { n: '', f: 1, b: 'px' } ), ...patch };
				write( { custom: list } );
			};
			const base = h( 'select', { class: 'wpec-input', 'aria-label': t( 'Base unit' ), onchange: ( e: Event ) => update( { b: ( e.target as HTMLSelectElement ).value } ) }, builtin.map( ( u ) => h( 'option', { value: u, text: u } ) ) ) as HTMLSelectElement;
			base.value = c.b || 'px';
			rows.append(
				h( 'div', { class: 'wpec-units__row' }, [
					h( 'span', { text: '1' } ),
					h( 'input', { class: 'wpec-input', 'aria-label': t( 'Unit name' ), placeholder: 'hm', maxlength: 8, value: c.n, onchange: ( e: Event ) => update( { n: ( e.target as HTMLInputElement ).value.toLowerCase().replace( /[^a-z]/g, '' ) } ) } ),
					h( 'span', { text: '=' } ),
					h( 'input', { class: 'wpec-input', type: 'number', min: 0.0001, step: 'any', 'aria-label': t( 'Value' ), value: String( c.f ), onchange: ( e: Event ) => update( { f: Number( ( e.target as HTMLInputElement ).value ) } ) } ),
					base,
					h( 'button', {
						type: 'button',
						class: 'wpec-units__remove',
						'aria-label': t( 'Remove unit' ),
						html: svg( '<path d="M18 6 6 18M6 6l12 12"/>', 14 ),
						onclick: () => {
							const list = read().custom;
							list.splice( index, 1 );
							write( { custom: list } );
							draw();
						},
					} ),
				] )
			);
		} );
	};
	const add = h( 'button', {
		type: 'button',
		class: 'wpec-units__add',
		text: t( 'Add custom unit' ),
		onclick: () => {
			write( { custom: [ ...read().custom, { n: '', f: 1, b: 'px' } ] } );
			draw();
		},
	} );
	return {
		el: h( 'div', { class: 'wpec-units' }, [ chips, rows, add ] ),
		refresh: () => {
			const on = read().on.split( ',' );
			for ( const box of boxes ) {
				box.checked = on.includes( box.value );
			}
			draw();
		},
	};
};

types.toggle = ( ctx ) => {
	const input = h( 'input', {
		type: 'checkbox',
		role: 'switch',
		class: 'wpec-switch__input',
		'aria-label': ctx.label,
		onchange: ( event: Event ) => ctx.set( ( event.target as HTMLInputElement ).checked ? '1' : '' ),
	} ) as HTMLInputElement;
	return {
		el: h( 'label', { class: 'wpec-switch' }, [ input, h( 'span', { class: 'wpec-switch__track', 'aria-hidden': 'true' } ) ] ),
		refresh: () => {
			const value = ctx.get();
			input.checked = !! value && value !== '0' && value !== 'false';
		},
	};
};

types.buttons = ( ctx ) => {
	const group = h( 'div', { class: 'wpec-seg', role: 'radiogroup', 'aria-label': ctx.label } );
	for ( const [ key, choice ] of Object.entries( ctx.opt.choices ?? {} ) ) {
		const label = Array.isArray( choice ) ? choice[ 0 ] : choice;
		const iconName = Array.isArray( choice ) ? choice[ 1 ] : null; // An ICONS name.
		const glyph = iconName && ICONS[ iconName ] ? { html: icon( iconName, 16 ) } : { text: label };
		group.append( h( 'button', { type: 'button', role: 'radio', class: 'wpec-seg__btn' + ( iconName ? ' is-icon' : '' ), 'data-v': key, title: label, 'aria-label': label, ...glyph, onclick: () => ctx.set( key ) } ) );
	}
	return {
		el: group,
		refresh: () => {
			const value = ctx.get();
			group.querySelectorAll< HTMLElement >( '.wpec-seg__btn' ).forEach( ( b ) => {
				const on = b.dataset.v === value;
				b.classList.toggle( 'is-active', on );
				b.setAttribute( 'aria-checked', on ? 'true' : 'false' );
			} );
		},
	};
};

types.choose = types.buttons;

types.select = ( ctx ) => {
	const select = h(
		'select',
		{ class: 'wpec-input wpec-select', 'aria-label': ctx.label, onchange: ( event: Event ) => ctx.set( ( event.target as HTMLSelectElement ).value ) },
		Object.entries( ctx.opt.choices ?? {} ).map( ( [ key, choice ] ) => h( 'option', { value: key, text: Array.isArray( choice ) ? choice[ 0 ] : choice } ) )
	) as HTMLSelectElement;
	return { el: select, refresh: () => ( select.value = ctx.get() ) };
};

types.font = ( ctx ) => {
	const button = h( 'button', { type: 'button', class: 'wpec-font__current', 'aria-expanded': 'false' } );
	const holder = h( 'div', { class: 'wpec-font__holder' } );
	const close = (): void => {
		holder.replaceChildren();
		button.setAttribute( 'aria-expanded', 'false' );
	};
	button.addEventListener( 'click', () => {
		if ( holder.firstChild ) {
			close();
			return;
		}
		button.setAttribute( 'aria-expanded', 'true' );
		holder.append(
			fontPicker( ctx.get(), ( value ) => {
				ctx.set( value );
				close();
			} )
		);
	} );
	return {
		el: h( 'div', {}, [ button, holder ] ),
		refresh: () => {
			const value = ctx.get();
			if ( value.startsWith( 'google:' ) ) {
				preview( value.slice( 7 ), true );
			}
			const source = ! value ? t( 'From your theme' ) : value.startsWith( 'google:' ) ? 'Google Fonts' : t( 'In theme' );
			button.replaceChildren(
				h( 'span', { class: 'wpec-font__sample', style: `font-family:${ fontStack( value ) }`, text: 'Aa' } ),
				h( 'span', { class: 'wpec-font__label' }, [ h( 'strong', { text: fontLabel( value ) } ), h( 'small', { text: source } ) ] ),
				h( 'span', { class: 'wpec-font__caret', 'aria-hidden': 'true', html: icon( 'chevron-down', 16 ) } )
			);
		},
	};
};

types.fonts = ( ctx ) => {
	const chips = h( 'div', { class: 'wpec-fonts__chips' } );
	const holder = h( 'div', { class: 'wpec-font__holder' } );
	const list = (): string[] => Array.from( new Set( ctx.get().split( '|' ).map( ( family ) => family.trim().replace( /^google:/, '' ) ).filter( Boolean ) ) );
	const add = h( 'button', {
		type: 'button',
		class: 'wpec-btn',
		text: `+ ${ t( 'Add a Google font' ) }`,
		onclick: () => {
			if ( holder.firstChild ) {
				holder.replaceChildren();
				return;
			}
			holder.append(
				fontPicker(
					'',
					( value ) => {
						const family = value.replace( /^google:/, '' );
						if ( family && ! list().includes( family ) ) {
							ctx.set( [ ...list(), family ].join( '|' ) );
						}
						holder.replaceChildren();
					},
					true
				)
			);
		},
	} );
	return {
		el: h( 'div', {}, [ chips, add, holder ] ),
		refresh: () => {
			chips.replaceChildren();
			for ( const family of list() ) {
				preview( family, true );
				chips.append(
					h( 'span', { class: 'wpec-fonts__chip', style: `font-family:"${ family }", system-ui` }, [
						family,
						h( 'button', { type: 'button', 'aria-label': `${ t( 'Remove' ) } ${ family }`, html: icon( 'x', 12 ), onclick: () => ctx.set( list().filter( ( f ) => f !== family ).join( '|' ) ) } ),
					] )
				);
			}
		},
	};
};

types.dimensions = ( ctx ) => {
	const units = siteUnits( ctx.opt.units ?? [ 'px', 'em', 'rem', '%' ] );
	let linked = true;
	const inputs: Partial< Record< string, HTMLInputElement > > = {};
	const row = h( 'div', { class: 'wpec-dims' } );
	const unitSelect = h( 'select', { class: 'wpec-dims__unit', 'aria-label': t( 'Unit' ) }, units.map( ( u ) => h( 'option', { value: u, text: u } ) ) ) as HTMLSelectElement;
	const sides: Array< [ string, string ] > = [
		[ 'top', t( 'Top' ) ],
		[ 'right', t( 'Right' ) ],
		[ 'bottom', t( 'Bottom' ) ],
		[ 'left', t( 'Left' ) ],
	];
	const write = (): void => {
		const out: Json = { unit: unitSelect.value };
		let any = false;
		for ( const [ side ] of sides ) {
			const input = inputs[ side ];
			if ( input && input.value !== '' ) {
				out[ side ] = Number( input.value );
				any = true;
			}
		}
		ctx.set( any ? JSON.stringify( out ) : '' );
	};
	for ( const [ side, label ] of sides ) {
		inputs[ side ] = h( 'input', {
			type: 'number',
			class: 'wpec-dims__input',
			'aria-label': label,
			oninput: ( event: Event ) => {
				if ( linked ) {
					Object.values( inputs ).forEach( ( input ) => {
						if ( input ) input.value = ( event.target as HTMLInputElement ).value;
					} );
				}
				write();
			},
		} ) as HTMLInputElement;
		row.append( h( 'label', { class: 'wpec-dims__side' }, [ inputs[ side ], h( 'span', { text: label } ) ] ) );
	}
	const link = h( 'button', {
		type: 'button',
		class: 'wpec-dims__link is-linked',
		'aria-pressed': 'true',
		title: t( 'Link values together' ),
		'aria-label': t( 'Link values together' ),
		html: svg( '<path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/>' ),
		onclick: () => {
			linked = ! linked;
			link.classList.toggle( 'is-linked', linked );
			link.setAttribute( 'aria-pressed', linked ? 'true' : 'false' );
		},
	} );
	unitSelect.addEventListener( 'change', write );
	row.append( link );
	return {
		el: h( 'div', {}, [ h( 'div', { class: 'wpec-dims__head' }, [ unitSelect ] ), row ] ),
		refresh: () => {
			const value = parse( ctx.get() );
			for ( const [ side ] of sides ) {
				const input = inputs[ side ];
				if ( input ) {
					input.value = value[ side ] === undefined ? '' : String( value[ side ] );
				}
			}
			unitSelect.value = String( value.unit ?? units[ 0 ] );
			linked = value.top === value.right && value.right === value.bottom && value.bottom === value.left;
			link.classList.toggle( 'is-linked', linked );
			link.setAttribute( 'aria-pressed', linked ? 'true' : 'false' );
		},
	};
};

// A pencil button that opens a group of controls (Elementor's POPOVER_TOGGLE).
function popover( ctx: ControlContext, summary: () => string, makeBody: () => Control ): Control {
	let body: Control | null = null;
	const panel = h( 'div', { class: 'wpec-popover', hidden: true } );
	const label = h( 'span', { class: 'wpec-popover__summary' } );
	const button = h( 'button', {
		type: 'button',
		class: 'wpec-popover__toggle',
		'aria-expanded': 'false',
		'aria-label': `${ t( 'Edit' ) } ${ ctx.label ?? '' }`,
		html: svg( '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4Z"/>' ),
		onclick: () => {
			if ( ! body ) {
				body = makeBody();
				panel.append( body.el );
			}
			panel.hidden = ! panel.hidden;
			button.setAttribute( 'aria-expanded', panel.hidden ? 'false' : 'true' );
			if ( ! panel.hidden ) {
				body.refresh();
			}
		},
	} );
	return {
		el: h( 'div', { class: 'wpec-popover-wrap' }, [ h( 'div', { class: 'wpec-popover__bar' }, [ label, button ] ), panel ] ),
		refresh: () => {
			label.textContent = summary();
			if ( body && ! panel.hidden ) {
				body.refresh();
			}
		},
	};
}

interface Part {
	node: HTMLElement;
	ctl: Control;
}

function group( parts: Part[] ): Control {
	return { el: h( 'div', { class: 'wpec-group' }, parts.map( ( p ) => p.node ) ), refresh: () => {
			parts.forEach( ( p ) => {
				p.ctl.refresh();
			} );
		},
	};
}

function part( ctx: ControlContext, key: string, type: string, opt: ControlOptions, label: string ): Part {
	const c = child( ctx, key, opt, label );
	const ctl = opt.responsive ? responsive( type, c ) : build( type, c );
	const head = h( 'div', { class: 'wpec-field__head' }, [ h( 'span', { class: 'wpec-field__label', text: label } ), ctl.devices ?? null ] );
	return { node: h( 'div', { class: 'wpec-field' }, [ head, ctl.el ] ), ctl };
}

const weights = (): Record< string, string > => ( {
	'': t( 'Default' ),
	100: '100',
	200: '200',
	300: '300 · Light',
	400: '400 · Regular',
	500: '500 · Medium',
	600: '600 · Semibold',
	700: '700 · Bold',
	800: '800',
	900: '900 · Black',
} );

types.typography = ( ctx ) =>
	popover(
		ctx,
		() => {
			const value = parse( ctx.get() );
			const size = parse( value.size );
			const desktop = size.desktop as Json | string | undefined;
			const bits: string[] = [];
			if ( value.preset ) {
				bits.push( cfg.typographyTokens?.[ String( value.preset ) ] ?? t( 'Missing preset' ) );
			}
			if ( value.family ) {
				bits.push( fontLabel( String( value.family ) ) );
			}
			if ( desktop ) {
				bits.push( typeof desktop === 'object' ? `${ String( desktop.size ) }${ String( desktop.unit ?? 'px' ) }` : `${ desktop }px` );
			}
			if ( value.weight ) {
				bits.push( String( value.weight ) );
			}
			return bits.length ? bits.join( ' · ' ) : t( 'Theme default' );
		},
		() =>
			group( [
				...( ! ctx.opt.defineTypography && Object.keys( cfg.typographyTokens ?? {} ).length ? [ part( ctx, 'preset', 'select', { choices: { '': t( 'Theme default / custom' ), ...cfg.typographyTokens } }, t( 'Global typography preset' ) ) ] : [] ),
				part( ctx, 'family', 'font', {}, t( 'Family' ) ),
				part( ctx, 'size', 'range', { responsive: true, min: 8, max: 120, units: [ 'px', 'em', 'rem', 'vw' ] }, t( 'Size' ) ),
				part( ctx, 'weight', 'select', { choices: weights() }, t( 'Weight' ) ),
				part( ctx, 'transform', 'choose', { choices: { '': [ t( 'Default' ), 'minus' ], uppercase: [ 'UPPERCASE', 'case-upper' ], lowercase: [ 'lowercase', 'case-lower' ], capitalize: [ 'Capitalize', 'case-sensitive' ], none: [ t( 'As typed' ), 'type' ] } }, t( 'Transform' ) ),
				part( ctx, 'style', 'choose', { choices: { '': [ t( 'Default' ), 'minus' ], normal: [ t( 'Normal' ), 'type' ], italic: [ t( 'Italic' ), 'italic' ] } }, t( 'Style' ) ),
				part( ctx, 'decoration', 'choose', { choices: { '': [ t( 'Default' ), 'minus' ], none: [ t( 'None' ), 'x' ], underline: [ t( 'Underline' ), 'underline' ], 'line-through': [ t( 'Line through' ), 'strikethrough' ] } }, t( 'Decoration' ) ),
				part( ctx, 'lineHeight', 'range', { responsive: true, min: 0.8, max: 3, step: 0.05, units: [ '', 'px', 'em' ] }, t( 'Line height' ) ),
				part( ctx, 'letterSpacing', 'range', { responsive: true, min: -5, max: 10, step: 0.1, units: [ 'px', 'em' ] }, t( 'Letter spacing' ) ),
			] )
	);

types.shadow = ( ctx ) =>
	popover(
		ctx,
		() => {
			const value = parse( ctx.get() );
			if ( value.y === undefined ) {
				return ctx.get() && ! ctx.get().startsWith( '{' ) ? ctx.get() : t( 'Theme default' );
			}
			return `${ value.inset ? 'inset ' : '' }${ String( value.x ?? 0 ) } ${ String( value.y ?? 0 ) } ${ String( value.blur ?? 0 ) } ${ String( value.spread ?? 0 ) }`;
		},
		() =>
			group( [
				part( ctx, 'color', 'color', {}, t( 'Colour' ) ),
				part( ctx, 'x', 'range', { min: -100, max: 100, unit: 'px' }, t( 'Horizontal' ) ),
				part( ctx, 'y', 'range', { min: -100, max: 100, unit: 'px' }, t( 'Vertical' ) ),
				part( ctx, 'blur', 'range', { min: 0, max: 200, unit: 'px' }, t( 'Blur' ) ),
				part( ctx, 'spread', 'range', { min: -100, max: 100, unit: 'px' }, t( 'Spread' ) ),
				part( ctx, 'inset', 'choose', { choices: { '': [ t( 'Outline' ), 'square' ], 1: [ t( 'Inset' ), 'inset' ] } }, t( 'Position' ) ),
			] )
	);

function gradientCss( g: Json ): string {
	if ( ! g.from && ! g.to ) {
		return '';
	}
	const stops = `${ String( g.from || g.to ) } ${ String( g.fromStop ?? 0 ) }%, ${ String( g.to || g.from ) } ${ String( g.toStop ?? 100 ) }%`;
	return g.type === 'radial' ? `radial-gradient(circle at ${ String( g.position || 'center center' ) }, ${ stops })` : `linear-gradient(${ String( g.angle ?? 180 ) }deg, ${ stops })`;
}

types.gradient = ( ctx ) => {
	const bar = h( 'div', { class: 'wpec-gradient__bar' } );
	const g = group( [
		part( ctx, 'type', 'choose', { choices: { '': [ t( 'Linear' ), t( 'Linear' ) ], radial: [ t( 'Radial' ), t( 'Radial' ) ] } }, t( 'Type' ) ),
		part( ctx, 'from', 'color', {}, t( 'First colour' ) ),
		part( ctx, 'fromStop', 'range', { min: 0, max: 100, unit: '%' }, t( 'Location' ) ),
		part( ctx, 'to', 'color', {}, t( 'Second colour' ) ),
		part( ctx, 'toStop', 'range', { min: 0, max: 100, unit: '%' }, t( 'Location' ) ),
		part( ctx, 'angle', 'range', { min: 0, max: 360, step: 5, unit: 'deg' }, t( 'Angle (linear)' ) ),
		part( ctx, 'position', 'select', { choices: { '': t( 'Center' ), 'top left': 'Top left', top: 'Top', 'top right': 'Top right', left: 'Left', right: 'Right', 'bottom left': 'Bottom left', bottom: 'Bottom', 'bottom right': 'Bottom right' } }, t( 'Position (radial)' ) ),
	] );
	return {
		el: h( 'div', { class: 'wpec-gradient' }, [ bar, g.el ] ),
		refresh: () => {
			const css = gradientCss( parse( ctx.get() ) );
			bar.style.background = css || '';
			bar.classList.toggle( 'is-empty', ! css );
			bar.textContent = css ? '' : t( 'Theme default' );
			g.refresh();
		},
	};
};

interface MediaFrame {
	on: ( event: string, callback: () => void ) => void;
	open: () => void;
	state: () => { get: ( key: string ) => { first: () => { toJSON: () => { id: number; url: string } } } };
}

types.media = ( ctx ) => {
	const box = h( 'div', { class: 'wpec-media' } );
	let frame: MediaFrame | null = null;
	const open = (): void => {
		const media = ( window as unknown as { wp?: { media?: ( options: unknown ) => MediaFrame } } ).wp?.media;
		if ( ! media ) {
			return;
		}
		if ( ! frame ) {
			frame = media( { title: ctx.label ?? t( 'Choose image' ), library: { type: 'image' }, multiple: false } );
			frame.on( 'select', () => {
				const attachment = frame?.state().get( 'selection' ).first().toJSON();
				if ( attachment ) {
					ctx.set( JSON.stringify( { id: attachment.id, url: attachment.url } ) );
				}
			} );
		}
		frame.open();
	};
	return {
		el: box,
		refresh: () => {
			const value = parse( ctx.get() );
			box.replaceChildren();
			if ( value.url ) {
				box.append(
					h( 'button', { type: 'button', class: 'wpec-media__thumb', onclick: open, 'aria-label': t( 'Replace image' ) }, [ h( 'img', { src: String( value.url ), alt: '' } ) ] ),
					h( 'div', { class: 'wpec-media__actions' }, [ h( 'button', { type: 'button', class: 'wpec-link', text: t( 'Replace' ), onclick: open } ), h( 'button', { type: 'button', class: 'wpec-link is-danger', text: t( 'Remove' ), onclick: () => ctx.set( '' ) } ) ] )
				);
			} else {
				box.append( h( 'button', { type: 'button', class: 'wpec-media__drop', onclick: open, html: `${ svg( '<path d="M12 16V4m0 0-5 5m5-5 5 5M4 20h16"/>', 20 ) }<span>${ t( 'Upload or choose an image' ) }</span>` } ) );
			}
		},
	};
};

types.background = ( ctx ) => {
	const typeCtl = part( ctx, 'type', 'choose', { choices: { '': [ t( 'Classic' ), t( 'Classic' ) ], gradient: [ t( 'Gradient' ), t( 'Gradient' ) ] } }, t( 'Type' ) );
	const classic = group( [
		part( ctx, 'color', 'color', {}, t( 'Colour' ) ),
		part( ctx, 'image', 'media', {}, t( 'Image' ) ),
		part( ctx, 'position', 'select', { choices: { '': t( 'Default' ), 'center center': 'Center', 'top center': 'Top', 'bottom center': 'Bottom', 'center left': 'Left', 'center right': 'Right' } }, t( 'Position' ) ),
		part( ctx, 'size', 'buttons', { choices: { '': t( 'Auto' ), cover: t( 'Cover' ), contain: t( 'Contain' ) } }, t( 'Size' ) ),
		part( ctx, 'repeat', 'select', { choices: { '': t( 'Default' ), 'no-repeat': 'No repeat', repeat: 'Repeat', 'repeat-x': 'Repeat X', 'repeat-y': 'Repeat Y' } }, t( 'Repeat' ) ),
		part( ctx, 'attachment', 'buttons', { choices: { '': t( 'Default' ), scroll: t( 'Scroll' ), fixed: t( 'Fixed' ), local: t( 'Local' ) } }, t( 'Attachment' ) ),
		part( ctx, 'origin', 'select', { choices: { '': t( 'Default' ), 'border-box': t( 'Border box' ), 'padding-box': t( 'Padding box' ), 'content-box': t( 'Content box' ) } }, t( 'Origin' ) ),
		part( ctx, 'clip', 'select', { choices: { '': t( 'Default' ), 'border-box': t( 'Border box' ), 'padding-box': t( 'Padding box' ), 'content-box': t( 'Content box' ) } }, t( 'Clip' ) ),
		part( ctx, 'blend', 'select', { choices: { '': t( 'Default' ), normal: t( 'Normal' ), multiply: t( 'Multiply' ), screen: t( 'Screen' ), overlay: t( 'Overlay' ), darken: t( 'Darken' ), lighten: t( 'Lighten' ), 'soft-light': t( 'Soft light' ), 'hard-light': t( 'Hard light' ), difference: t( 'Difference' ), exclusion: t( 'Exclusion' ) } }, t( 'Blend mode' ) ),
	] );
	const gradient = build( 'gradient', child( ctx, 'gradient', {}, t( 'Gradient' ) ) );
	const gradientBox = h( 'div', {}, [ gradient.el ] );
	return {
		el: h( 'div', { class: 'wpec-background' }, [ typeCtl.node, classic.el, gradientBox ] ),
		refresh: () => {
			const isGradient = parse( ctx.get() ).type === 'gradient';
			typeCtl.ctl.refresh();
			classic.el.hidden = isGradient;
			gradientBox.hidden = ! isGradient;
			( isGradient ? gradient : classic ).refresh();
		},
	};
};


const tokenRepeater = ( ctx: ControlContext, typography: boolean, shadow = false ): Control => {
	type Token = { name?: string; slug?: string; color?: string; dark?: string; typography?: Json; shadow?: Json };
	const read = (): Token[] => {
		const value = parse( ctx.get() ).tokens;
		return Array.isArray( value ) ? value as Token[] : Object.values( ( value ?? {} ) as Record< string, Token > );
	};
	const write = ( tokens: Token[] ): void => ctx.set( tokens.length ? JSON.stringify( { tokens } ) : '' );
	const rows = h( 'div', { class: 'wpec-units__custom' } );
	const add = h( 'button', { type: 'button', class: 'wpec-link', text: t( shadow ? 'Add custom shadow' : typography ? 'Add custom typography' : 'Add custom colour' ), onclick: () => write( [ ...read(), {} ] ) } ) as HTMLButtonElement;
	let parts: Part[][] = [];
	const draw = (): void => {
		rows.replaceChildren();
		parts = read().map( ( _token, index ) => {
			const row: ControlContext = {
				opt: {},
				get: () => JSON.stringify( read()[ index ] ?? {} ),
				set: ( value ) => {
					const tokens = read();
					tokens[ index ] = parse( value ) as Token;
					write( tokens );
				},
			};
			const fields = [
				part( row, 'name', 'text', { placeholder: t( shadow ? 'Card shadow' : typography ? 'Display heading' : 'Brand colour' ) }, t( 'Name' ) ),
				part( row, 'slug', 'text', { placeholder: shadow ? 'card-shadow' : typography ? 'display-heading' : 'brand-colour' }, t( 'Stable slug' ) ),
				...( shadow ? [ part( row, 'shadow', 'shadow', {}, t( 'Shadow' ) ) ] : typography ? [ part( row, 'typography', 'typography', { defineTypography: true }, t( 'Typography' ) ) ] : [
					part( row, 'color', 'color', { literalColour: true }, t( 'Light colour' ) ),
					part( row, 'dark', 'color', { literalColour: true }, t( 'Dark colour (optional)' ) ),
				] ),
			];
			rows.append( h( 'div', { class: 'wpec-field', role: 'group', 'aria-label': `${ t( shadow ? 'Custom shadow' : typography ? 'Custom typography' : 'Custom colour' ) } ${ index + 1 }` }, [
				...fields.map( ( field ) => field.node ),
				h( 'button', { type: 'button', class: 'wpec-link is-danger', text: t( 'Remove' ), 'aria-label': `${ t( shadow ? 'Remove custom shadow' : typography ? 'Remove custom typography' : 'Remove custom colour' ) } ${ index + 1 }`, onclick: () => write( read().filter( ( _row, i ) => i !== index ) ) } ),
			] ) );
			return fields;
		} );
	};
	return {
		el: h( 'div', {}, [ rows, add ] ),
		refresh: () => {
			const count = read().length;
			if ( count !== parts.length ) {
				draw();
			}
			for ( const row of parts ) {
				for ( const field of row ) {
					field.ctl.refresh();
				}
			}
			add.disabled = count >= 100;
		},
	};
};

types.colour_tokens = ( ctx ) => tokenRepeater( ctx, false );
types.typography_tokens = ( ctx ) => tokenRepeater( ctx, true );
types.shadow_tokens = ( ctx ) => tokenRepeater( ctx, false, true );

/** Builds a control; responsive options wrap it with per-device values. */
export function create( type: string, ctx: ControlContext ): Control {
	ctx.device = ctx.device ?? ( () => currentDevice );
	return ctx.opt.responsive ? responsive( type, ctx ) : build( type, ctx );
}
