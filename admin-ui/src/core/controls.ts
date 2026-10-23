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
	themeFonts?: Array< { value: string; name: string; stack: string } >;
	googleList?: string;
	i18n?: Record< string, string >;
}

type Json = Record< string, unknown >;
type Builder = ( ctx: ControlContext ) => Control;

const DEVICES: Array< [ string, string, string ] > = [
	[ 'desktop', 'Desktop', '<rect x="2" y="4" width="20" height="13" rx="2"/><path d="M8 21h8M12 17v4"/>' ],
	[ 'tablet', 'Tablet (1023px and below)', '<rect x="5" y="2" width="14" height="20" rx="2"/><path d="M11 18h2"/>' ],
	[ 'mobile', 'Phone (767px and below)', '<rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/>' ],
];

let cfg: ControlsConfig = {};
let googleList: Array< [ string, string, number[], number, number[] | null ] > | null = null;
const googleWaiters: Array< ( rows: NonNullable< typeof googleList > ) => void > = [];
const previewed: Record< string, true > = {};
const listeners: Array< () => void > = [];
let currentDevice = 'desktop';

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

export function setDevice( device: string ): void {
	currentDevice = device;
	listeners.forEach( ( fn ) => {
		fn();
	} );
}

function deviceSwitch( ctx: ControlContext ): HTMLElement {
	const box = h( 'span', { class: 'wpec-devices', role: 'group', 'aria-label': t( 'Device' ) } );
	for ( const [ key, label, icon ] of DEVICES ) {
		box.append( h( 'button', { type: 'button', class: 'wpec-devices__btn', 'data-device': key, title: t( label ), 'aria-label': t( label ), html: svg( icon, 13 ), onclick: () => setDevice( key ) } ) );
	}
	const sync = (): void => {
		box.querySelectorAll< HTMLElement >( '.wpec-devices__btn' ).forEach( ( b ) => {
			const on = b.dataset.device === ( ctx.device?.() ?? currentDevice );
			b.classList.toggle( 'is-active', on );
			b.setAttribute( 'aria-pressed', on ? 'true' : 'false' );
		} );
	};
	listeners.push( sync );
	sync();
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
			if ( ! value || /^#?[0-9a-f]{3,8}$/i.test( value ) || /^(rgb|hsl)a?\(/i.test( value ) || /^var\(/.test( value ) ) {
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
	const hasPalette = Object.keys( cfg.palette ?? {} ).length > 0;
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
	for ( const [ name, colour ] of Object.values( cfg.palette ?? {} ) ) {
		globals.append(
			h(
				'button',
				{
					type: 'button',
					class: 'wpec-color__global',
					'data-color': colour.toLowerCase(),
					onclick: () => {
						ctx.set( colour );
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
			chip.style.background = value ? `${ value } padding-box` : '';
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
	const units = o.units ?? ( o.unit !== undefined && o.unit !== '' ? [ o.unit ] : [] );
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
		const icon = Array.isArray( choice ) ? choice[ 1 ] : null;
		group.append( h( 'button', { type: 'button', role: 'radio', class: 'wpec-seg__btn' + ( icon ? ' is-icon' : '' ), 'data-v': key, title: label, 'aria-label': label, text: icon ?? label, onclick: () => ctx.set( key ) } ) );
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
				h( 'span', { class: 'wpec-font__caret', 'aria-hidden': 'true', text: '▾' } )
			);
		},
	};
};

types.fonts = ( ctx ) => {
	const chips = h( 'div', { class: 'wpec-fonts__chips' } );
	const holder = h( 'div', { class: 'wpec-font__holder' } );
	const list = (): string[] => ctx.get().split( '|' ).filter( Boolean );
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
						h( 'button', { type: 'button', 'aria-label': `${ t( 'Remove' ) } ${ family }`, text: '×', onclick: () => ctx.set( list().filter( ( f ) => f !== family ).join( '|' ) ) } ),
					] )
				);
			}
		},
	};
};

types.dimensions = ( ctx ) => {
	const units = ctx.opt.units ?? [ 'px' ];
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
				part( ctx, 'family', 'font', {}, t( 'Family' ) ),
				part( ctx, 'size', 'range', { responsive: true, min: 8, max: 120, units: [ 'px', 'em', 'rem', 'vw' ] }, t( 'Size' ) ),
				part( ctx, 'weight', 'select', { choices: weights() }, t( 'Weight' ) ),
				part( ctx, 'transform', 'choose', { choices: { '': [ t( 'Default' ), '–' ], uppercase: [ 'UPPERCASE', 'AA' ], lowercase: [ 'lowercase', 'aa' ], capitalize: [ 'Capitalize', 'Aa' ], none: [ t( 'As typed' ), '✕' ] } }, t( 'Transform' ) ),
				part( ctx, 'style', 'choose', { choices: { '': [ t( 'Default' ), '–' ], normal: [ t( 'Normal' ), 'I' ], italic: [ t( 'Italic' ), '𝐼' ] } }, t( 'Style' ) ),
				part( ctx, 'decoration', 'choose', { choices: { '': [ t( 'Default' ), '–' ], none: [ t( 'None' ), '✕' ], underline: [ t( 'Underline' ), 'U̲' ], 'line-through': [ t( 'Line through' ), 'S̶' ] } }, t( 'Decoration' ) ),
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
				part( ctx, 'inset', 'choose', { choices: { '': [ t( 'Outline' ), t( 'Outline' ) ], 1: [ t( 'Inset' ), t( 'Inset' ) ] } }, t( 'Position' ) ),
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
		part( ctx, 'attachment', 'buttons', { choices: { '': t( 'Scroll' ), fixed: t( 'Fixed' ) } }, t( 'Attachment' ) ),
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

/** Builds a control; responsive options wrap it with per-device values. */
export function create( type: string, ctx: ControlContext ): Control {
	ctx.device = ctx.device ?? ( () => currentDevice );
	return ctx.opt.responsive ? responsive( type, ctx ) : build( type, ctx );
}
