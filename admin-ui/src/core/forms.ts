// Upgrades the toolkit's admin-post forms (switches, colour mode) to AJAX so they update in place.
// Without JavaScript, or when a route is not available, the form submits normally.
import { ajax, ajaxConfig, AjaxError } from './ajax';
import { toast } from './toast';

interface Route {
	type: string;
	payload: ( data: FormData ) => Record< string, unknown >;
}

const flag = ( data: FormData ): boolean => data.get( 'enabled' ) === '1';

const routes: Record< string, ( data: FormData ) => Route | null > = {
	wpessential_module_toggle: () => ( {
		type: 'core.module.toggle',
		payload: ( data ) => ( { module: String( data.get( 'module' ) ?? '' ), enabled: flag( data ) } ),
	} ),
	wpessential_element_toggle: () => ( {
		type: 'core.element.toggle',
		payload: ( data ) => ( { type: String( data.get( 'type' ) ?? '' ), element: String( data.get( 'element' ) ?? '' ), enabled: flag( data ) } ),
	} ),
	wpessential_ai_connect: ( data ) =>
		[ 'power', 'writes' ].includes( String( data.get( 'op' ) ) )
			? { type: 'core.ai.switch', payload: ( d ) => ( { op: String( d.get( 'op' ) ), enabled: flag( d ) } ) }
			: null,
	wpessential_ui_mode: () => ( {
		type: 'core.ui.mode',
		payload: ( data ) => ( { ui_mode: String( data.get( 'mode' ) ?? '' ) } ),
	} ),
};

function flipSwitch( form: HTMLFormElement, on: boolean ): void {
	const button = form.querySelector< HTMLButtonElement >( '[role="switch"]' );
	const next = form.querySelector< HTMLInputElement >( 'input[name="enabled"]' );
	button?.setAttribute( 'aria-checked', on ? 'true' : 'false' );
	const label = button?.querySelector( 'span:last-child' );
	if ( label ) {
		label.textContent = on ? form.dataset.labelOn ?? 'On' : form.dataset.labelOff ?? 'Off';
	}
	if ( next ) {
		next.value = on ? '0' : '1';
	}
	const card = form.closest< HTMLElement >( '[data-wpe-module]' );
	if ( card ) {
		card.dataset.active = on ? '1' : '0';
		card.classList.toggle( 'is-off', ! on );
		const pill = card.querySelector< HTMLElement >( '.wpe-ui-pill--running, .wpe-ui-pill--off' );
		if ( pill ) {
			pill.className = `wpe-ui-pill wpe-ui-pill--${ on ? 'running' : 'off' }`;
			pill.textContent = on ? form.dataset.pillOn ?? 'Running' : form.dataset.pillOff ?? 'Off';
		}
	}
}

function applyMode( form: HTMLFormElement, mode: string ): void {
	const body = document.body;
	body.classList.add( 'is-mode-switching' );
	body.classList.remove( 'wpe-mode-system', 'wpe-mode-light', 'wpe-mode-dark' );
	body.classList.add( `wpe-mode-${ mode }` );
	const target = form.querySelector< HTMLInputElement >( '[data-wpe-mode-target]' );
	if ( target ) {
		target.value = mode === 'dark' ? 'light' : 'dark';
	}
	window.setTimeout( () => body.classList.remove( 'is-mode-switching' ), 300 );
}

async function submit( form: HTMLFormElement, route: Route, data: FormData ): Promise< void > {
	const payload = route.payload( data );
	form.classList.add( 'is-busy' );
	try {
		const result = await ajax< { reload?: boolean } >( route.type, payload );
		if ( route.type === 'core.ui.mode' ) {
			applyMode( form, String( payload.ui_mode ) );
		} else {
			flipSwitch( form, payload.enabled === true );
			toast( payload.enabled === true ? form.dataset.doneOn ?? 'Switched on.' : form.dataset.doneOff ?? 'Switched off.', payload.enabled === true ? 'success' : 'info' );
		}
		// A switch that changes what the current screen shows (AI Connect power, or a module
		// switched on from its own locked screen) asks for a reload; everything else stays in place.
		if ( result?.reload || data.get( 'reload' ) === '1' ) {
			window.location.reload();
		}
	} catch ( error ) {
		toast( error instanceof AjaxError ? error.message : 'Something went wrong.', 'error' );
	} finally {
		form.classList.remove( 'is-busy' );
	}
}

export function initAjaxForms(): void {
	if ( ! ajaxConfig() || typeof window.fetch !== 'function' ) {
		return;
	}
	document.addEventListener( 'submit', ( event ) => {
		const form = event.target;
		if ( ! ( form instanceof HTMLFormElement ) || ! form.closest( '.wpe-ui' ) ) {
			return;
		}
		const data = new FormData( form );
		const build = routes[ String( data.get( 'action' ) ?? '' ) ];
		const route = build ? build( data ) : null;
		if ( ! route ) {
			return;
		}
		event.preventDefault();
		if ( form.classList.contains( 'is-busy' ) ) {
			return; // One request per form at a time; a double click must not undo itself.
		}
		void submit( form, route, data );
	} );
}
