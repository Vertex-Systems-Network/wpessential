// Site Kit screen: binds each field wrapper (.wpec[data-wpec]) to a control from ./controls,
// switches groups as tabs without reloading, and saves a group in place over AJAX.
import { ajax, ajaxConfig, AjaxError } from './ajax';
import { configure, create, h, setDevice, type ControlOptions, type ControlsConfig } from './controls';
import { toast } from './toast';

function bindField( root: HTMLElement ): void {
	const input = root.querySelector< HTMLInputElement >( '.wpec__value' );
	const head = root.querySelector< HTMLElement >( '.wpec__head' );
	const reset = root.querySelector< HTMLButtonElement >( '.wpec__reset' );
	if ( ! input || ! head || root.dataset.ready ) {
		return;
	}
	root.dataset.ready = '1';
	const opt = JSON.parse( root.dataset.wpec ?? '{}' ) as ControlOptions;
	const label = root.querySelector( '.wpec__label' )?.textContent ?? '';
	const sync = (): void => {
		control.refresh();
		if ( reset ) {
			reset.hidden = input.value === '';
		}
		root.classList.toggle( 'is-set', input.value !== '' );
	};
	const control = create( opt.type ?? 'text', {
		opt,
		label,
		get: () => input.value,
		set: ( value ) => {
			input.value = value;
			input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
			sync();
		},
	} );
	if ( control.devices ) {
		head.insertBefore( control.devices, reset );
	}
	if ( opt.type === 'toggle' ) {
		head.insertBefore( control.el, reset );
	} else {
		root.querySelector( '.wpec__ui' )?.append( control.el );
	}
	input.addEventListener( 'change', sync );
	reset?.addEventListener( 'click', () => {
		input.value = '';
		input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		sync();
	} );
	sync();
}

// State bar for fields marked with a tab (like Elementor).
function stateTabs( form: HTMLElement ): void {
	const items = Array.from( form.querySelectorAll< HTMLElement >( '[data-wpec-tab]' ) );
	if ( ! items.length ) {
		return;
	}
	const show = ( tab: string ): void => {
		items.forEach( ( item ) => {
			item.hidden = item.dataset.wpecTab !== tab;
		} );
	};
	const bar = h( 'div', { class: 'wpec-seg wpec-statebar', role: 'tablist', 'aria-label': 'State' } );
	const states = ( [ [ 'normal', 'Normal' ], [ 'hover', 'Hover' ], [ 'focus', 'Focus' ], [ 'active', 'Active' ], [ 'disabled', 'Disabled' ] ] as const ).filter( ( [ key ] ) => items.some( ( item ) => item.dataset.wpecTab === key ) );
	states.forEach( ( [ key, label ], index ) => {
		bar.append(
			h( 'button', {
				type: 'button',
				role: 'tab',
				class: 'wpec-seg__btn' + ( index === 0 ? ' is-active' : '' ),
				'aria-selected': index === 0 ? 'true' : 'false',
				text: label,
				onclick: ( event: Event ) => {
					bar.querySelectorAll( 'button' ).forEach( ( b ) => {
						b.classList.toggle( 'is-active', b === event.currentTarget );
						b.setAttribute( 'aria-selected', b === event.currentTarget ? 'true' : 'false' );
					} );
					show( key );
				},
			} )
		);
	} );
	items[ 0 ]?.before( bar );
	show( states[ 0 ]?.[ 0 ] ?? 'normal' );
}

function bindTabs( root: HTMLElement ): void {
	const tabs = Array.from( root.querySelectorAll< HTMLButtonElement >( '[data-wpe-tab]' ) );
	const select = ( tab: HTMLButtonElement, focus = false ): void => {
		for ( const other of tabs ) {
			const on = other === tab;
			other.classList.toggle( 'is-active', on );
			other.setAttribute( 'aria-selected', on ? 'true' : 'false' );
			other.tabIndex = on ? 0 : -1;
			const panel = document.getElementById( other.getAttribute( 'aria-controls' ) ?? '' );
			if ( panel ) {
				panel.hidden = ! on;
			}
		}
		if ( focus ) {
			tab.focus();
		}
		const url = new URL( window.location.href );
		url.searchParams.set( 'group', tab.dataset.wpeTab ?? '' );
		url.searchParams.delete( 'wpe-notice' );
		window.history.replaceState( null, '', url );
	};
	tabs.forEach( ( tab, index ) => {
		tab.addEventListener( 'click', () => select( tab ) );
		tab.addEventListener( 'keydown', ( event ) => {
			const step = { ArrowDown: 1, ArrowRight: 1, ArrowUp: -1, ArrowLeft: -1 }[ event.key ];
			if ( step ) {
				event.preventDefault();
				const next = tabs[ ( index + step + tabs.length ) % tabs.length ];
				if ( next ) {
					select( next, true );
				}
			}
		} );
	} );
}

/** "values[a][b]" style names → nested object; JSON strings are kept as strings. */
function formPayload( form: HTMLFormElement ): Record< string, unknown > {
	const out: Record< string, unknown > = {};
	for ( const [ name, value ] of new FormData( form ).entries() ) {
		if ( typeof value !== 'string' || [ 'action', '_wpnonce', '_wp_http_referer' ].includes( name ) ) {
			continue;
		}
		const keys = name.replace( /\]/g, '' ).split( '[' );
		let node = out;
		keys.forEach( ( key, index ) => {
			if ( index === keys.length - 1 ) {
				node[ key ] = value;
			} else {
				node[ key ] = ( node[ key ] as Record< string, unknown > ) ?? {};
				node = node[ key ] as Record< string, unknown >;
			}
		} );
	}
	return out;
}

function bindAjaxForm( form: HTMLFormElement ): void {
	const route = form.dataset.wpeAjaxForm ?? '';
	if ( ! ajaxConfig()?.nonces[ route ] ) {
		return; // No route available: the admin-post fallback submits normally.
	}
	form.addEventListener( 'submit', ( event ) => {
		const submitter = ( event as SubmitEvent ).submitter as HTMLButtonElement | null;
		const isReset = submitter?.hasAttribute( 'data-wpe-reset' ) ?? false;
		const type = isReset ? form.dataset.wpeResetRoute ?? '' : route;
		if ( ! type ) {
			return;
		}
		event.preventDefault();
		form.classList.add( 'is-busy' );
		ajax< { changed?: Record< string, unknown >; reload?: boolean } >( type, formPayload( form ) )
			.then( ( result ) => {
				if ( result?.reload ) {
					window.location.reload();
					return;
				}
				const count = Object.keys( result?.changed ?? {} ).length;
				toast( count ? form.dataset.saved ?? 'Saved.' : form.dataset.unchanged ?? 'Nothing changed.', count ? 'success' : 'info' );
			} )
			.catch( ( error ) => toast( error instanceof AjaxError ? error.message : 'Something went wrong.', 'error' ) )
			.finally( () => form.classList.remove( 'is-busy' ) );
	} );
}

/** Clone the inert server-rendered blank row, never a row with saved code or live dropdown state. */
function bindSnippetManager( root: HTMLElement ): void {
	const template = root.querySelector< HTMLTemplateElement >( '[data-wpe-snippet-template]' );
	const add = root.querySelector< HTMLButtonElement >( '[data-wpe-add-snippet]' );
	if ( ! template || ! add ) {
		return;
	}
	let next = Number( template.dataset.nextIndex ?? '0' );
	add.hidden = false;
	add.addEventListener( 'click', () => {
		const fragment = template.content.cloneNode( true ) as DocumentFragment;
		const index = String( next++ );
		for ( const element of fragment.querySelectorAll( '[name], [id], [for]' ) ) {
			for ( const attr of [ 'name', 'id', 'for' ] ) {
				const value = element.getAttribute( attr );
				if ( value?.includes( '__index__' ) ) {
					element.setAttribute( attr, value.replace( '__index__', index ) );
				}
			}
		}
		const title = fragment.querySelector< HTMLInputElement >( 'input[name$="[title]"]' );
		template.before( fragment );
		title?.focus();
	} );
}

export function initSiteKit(): void {
	const root = document.querySelector< HTMLElement >( '.wpe-ui-sitekit' );
	if ( ! root ) {
		return;
	}
	try {
		configure( JSON.parse( document.getElementById( 'wpe-controls-config' )?.textContent ?? '{}' ) as ControlsConfig );
	} catch {
		configure( {} );
	}
	root.querySelectorAll< HTMLElement >( '.wpec[data-wpec]' ).forEach( bindField );
	root.querySelectorAll< HTMLFormElement >( 'form[data-wpe-ajax-form]' ).forEach( ( form ) => {
		stateTabs( form );
		bindAjaxForm( form );
	} );
	bindTabs( root );
	bindSnippetManager( root );
	setDevice( 'desktop' );
}


type CustomizeSetting = {
	get: () => unknown;
	set: ( value: string ) => void;
	bind: ( callback: ( value: unknown ) => void ) => void;
};
type CustomizeApi = ( id: string, callback: ( setting: CustomizeSetting ) => void ) => void;

/** WordPress owns changesets, preview and publishing; controls only edit its settings. */
export function initSiteKitCustomizer(): void {
	const api = ( window as unknown as { wp?: { customize?: CustomizeApi } } ).wp?.customize;
	if ( ! api ) {
		return;
	}
	try {
		configure( JSON.parse( document.getElementById( 'wpe-controls-config' )?.textContent ?? '{}' ) as ControlsConfig );
	} catch {
		configure( {} );
	}
	const encode = ( value: unknown ): string => {
		if ( value === true ) {
			return '1';
		}
		if ( value === false || value === null || value === undefined ) {
			return '';
		}
		return typeof value === 'object' ? JSON.stringify( value ) : String( value );
	};
	const attach = (): void => {
		document.querySelectorAll< HTMLElement >( '.wpe-sitekit-customizer[data-wpe-setting]' ).forEach( ( wrapper ) => {
			if ( wrapper.dataset.bound ) {
				return;
			}
			const root = wrapper.querySelector< HTMLElement >( '.wpec[data-wpec]' );
			const input = root?.querySelector< HTMLInputElement >( '.wpec__value' );
			if ( ! root || ! input ) {
				return;
			}
			wrapper.dataset.bound = '1';
			api( wrapper.dataset.wpeSetting ?? '', ( setting ) => {
				input.value = encode( setting.get() );
				bindField( root );
				input.addEventListener( 'change', () => {
					if ( input.value !== encode( setting.get() ) ) {
						setting.set( input.value );
					}
				} );
				setting.bind( ( value ) => {
					const next = encode( value );
					if ( input.value !== next ) {
						input.value = next;
						input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
					}
				} );
			} );
		} );
	};
	attach();
	new MutationObserver( attach ).observe( document.body, { childList: true, subtree: true } );
	setDevice( 'desktop' );
}
