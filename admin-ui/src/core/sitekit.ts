// Site Kit screen: binds each field wrapper (.wpec[data-wpec]) to a control from ./controls,
// switches groups as tabs without reloading, and saves a group in place over AJAX.
import { ajax, ajaxConfig, AjaxError } from './ajax';
import { configure, create, h, observeDevice, setDevice, updateBreakpoints, type ControlOptions, type ControlsConfig } from './controls';
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
	} );
	sync();
}

// State tabs are shared by admin forms and native Customizer control lists.
const stateGroups = new WeakMap< HTMLElement, { items: HTMLElement[]; select: ( key: string, focus?: boolean ) => void; current: () => string; host: HTMLElement } >();

function stateTabs( container: HTMLElement, items = Array.from( container.querySelectorAll< HTMLElement >( '[data-wpec-tab]' ) ), customizer = false ): void {
	if ( ! items.length ) {
		return;
	}
	const previous = stateGroups.get( container );
	if ( previous && items.length === previous.items.length && items.every( ( item, index ) => item === previous.items[ index ] ) && previous.host.isConnected ) {
		return;
	}
	const selected = previous?.current() ?? 'normal';
	previous?.host.remove();
	const states = ( [ [ 'normal', 'Normal' ], [ 'hover', 'Hover' ], [ 'focus', 'Focus' ], [ 'active', 'Active' ], [ 'disabled', 'Disabled' ] ] as const ).filter( ( [ key ] ) => items.some( ( item ) => item.dataset.wpecTab === key ) );
	const bar = h( 'div', { class: 'wpec-seg wpec-statebar', role: 'tablist', 'aria-label': 'State' } );
	const host = customizer ? h( 'li', { class: 'wpe-ui wpe-sitekit-state-tabs' }, [ bar ] ) : bar;
	let current = states.some( ( [ key ] ) => key === selected ) ? selected : states[ 0 ]?.[ 0 ] ?? 'normal';
	const select = ( key: string, focus = false ): void => {
		current = key;
		items.forEach( ( item ) => {
			item.hidden = item.dataset.wpecTab !== key;
		} );
		bar.querySelectorAll< HTMLButtonElement >( 'button' ).forEach( ( button ) => {
			const active = button.dataset.state === key;
			button.classList.toggle( 'is-active', active );
			button.setAttribute( 'aria-selected', active ? 'true' : 'false' );
			button.tabIndex = active ? 0 : -1;
			if ( active && focus ) {
				button.focus();
			}
		} );
	};
	states.forEach( ( [ key, label ], index ) => {
		const button = h( 'button', {
			type: 'button',
			role: 'tab',
			class: 'wpec-seg__btn',
			'data-state': key,
			text: label,
			onclick: () => select( key ),
		} );
		button.addEventListener( 'keydown', ( event: KeyboardEvent ) => {
			const next = event.key === 'Home' ? 0 : event.key === 'End' ? states.length - 1 : event.key === 'ArrowRight' ? ( index + 1 ) % states.length : event.key === 'ArrowLeft' ? ( index + states.length - 1 ) % states.length : -1;
			const state = states[ next ];
			if ( state ) {
				event.preventDefault();
				select( state[ 0 ], true );
			}
		} );
		bar.append( button );
	} );
	items[ 0 ]?.before( host );
	stateGroups.set( container, { items, select, current: () => current, host } );
	select( current );
}

function customizerStateTabs(): void {
	const groups = new Map< HTMLElement, HTMLElement[] >();
	document.querySelectorAll< HTMLElement >( '.wpe-sitekit-customizer [data-wpec-tab]' ).forEach( ( field ) => {
		const item = field.closest< HTMLElement >( 'li.customize-control' );
		const parent = item?.parentElement;
		if ( ! item || ! parent ) {
			return;
		}
		item.dataset.wpecTab = field.dataset.wpecTab ?? '';
		const group = groups.get( parent ) ?? [];
		group.push( item );
		groups.set( parent, group );
	} );
	for ( const [ parent, items ] of groups ) {
		stateTabs( parent, items, true );
	}
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
type CustomizeApi = ( ( id: string, callback: ( setting: CustomizeSetting ) => void ) => void ) & {
	bind: ( event: string, callback: () => void ) => void;
	previewedDevice?: CustomizeSetting;
};

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
	// WordPress keeps its native device buttons. A shared field's configured
	// width uses the desktop canvas, constrained by the available preview area.
	let syncing = false;
	const preview = document.getElementById( 'customize-preview' );
	const clearPreview = (): void => {
		if ( preview?.dataset.wpeKitPreview ) {
			preview.style.width = '';
			preview.style.left = '';
			preview.style.marginLeft = '';
			delete preview.dataset.wpeKitPreview;
		}
	};
	observeDevice( ( device, width ) => {
		if ( syncing || ! preview ) {
			return;
		}
		syncing = true;
		try {
			if ( api.previewedDevice?.get() !== 'desktop' ) {
				api.previewedDevice?.set( 'desktop' );
			}
			clearPreview();
			if ( device !== 'desktop' && width > 0 ) {
				preview.dataset.wpeKitPreview = device;
				preview.style.width = `min(100%, ${ width }px)`;
				preview.style.left = '50%';
				preview.style.marginLeft = `max(-50%, -${ width / 2 }px)`;
			}
		} finally {
			syncing = false;
		}
	} );
	let nativeBound = false;
	const bindNative = (): void => {
		if ( nativeBound || ! api.previewedDevice ) {
			return;
		}
		nativeBound = true;
		api.previewedDevice.bind( ( value ) => {
			if ( syncing ) {
				return;
			}
			syncing = true;
			try {
				clearPreview();
				setDevice( typeof value === 'string' ? value : 'desktop' );
			} finally {
				syncing = false;
			}
		} );
	};
	bindNative();
	api.bind( 'ready', bindNative );
	// Custom widths use WordPress' desktop mode internally. Clicking its already
	// selected Desktop button does not emit a Value change, so handle that intent.
	document.addEventListener( 'click', ( event ) => {
		if ( preview?.dataset.wpeKitPreview && event.target instanceof Element && event.target.closest( '.wp-full-overlay-footer .devices button[data-device="desktop"]' ) ) {
			setDevice( 'desktop' );
		}
	}, true );
	api( 'wpessential_site_kit[values][breakpoints]', ( setting ) => {
		updateBreakpoints( encode( setting.get() ) );
		setting.bind( ( value ) => updateBreakpoints( encode( value ) ) );
	} );
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
	const refresh = (): void => {
		attach();
		customizerStateTabs();
	};
	refresh();
	new MutationObserver( refresh ).observe( document.body, { childList: true, subtree: true } );
	setDevice( 'desktop' );
}
