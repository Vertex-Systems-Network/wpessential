import {
	mountAdminColumnsScaffold,
	parseAdminColumnsBootstrap,
} from './columns';

type JsonObject = Record< string, unknown >;
type AjaxRoute = { type: string; nonce: string };
type RuntimeBootstrap = {
	ajaxUrl: string;
	ajaxAction: string;
	routes: {
		save: AjaxRoute;
		list?: AjaxRoute;
		get?: AjaxRoute;
	};
};
type SavedDefinition = {
	id: string;
	revision: number;
	status: string;
	payload: JsonObject;
};
type CanonicalColumn = {
	uuid: string;
	key: string;
	label: string;
	enabled: boolean;
	owner: string;
	reference: string;
	format: string;
	primary: boolean;
	base: JsonObject;
};
type CanonicalView = {
	definition: SavedDefinition;
	viewKey: string;
	name: string;
	enabled: boolean;
	targetType: string;
	targetKey: string;
	columns: CanonicalColumn[];
};
type AjaxEnvelope = {
	success: boolean;
	data?: unknown;
};

const UUID_PATTERN =
	/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/;
const MACHINE_KEY_PATTERN = /^[a-z0-9][a-z0-9_-]{0,63}$/;
const DEFINITION_STATUSES = [ 'draft', 'published', 'disabled', 'archived' ];
const MAX_SAVED_VIEWS = 100;

function isObject( value: unknown ): value is JsonObject {
	return (
		typeof value === 'object' && value !== null && ! Array.isArray( value )
	);
}

function parseRoute( value: unknown, expectedType: string ): AjaxRoute | null {
	if (
		! isObject( value ) ||
		value.type !== expectedType ||
		typeof value.nonce !== 'string' ||
		value.nonce === ''
	) {
		return null;
	}
	return { type: expectedType, nonce: value.nonce };
}

function parseRuntimeBootstrap( value: unknown ): RuntimeBootstrap | null {
	if (
		! isObject( value ) ||
		typeof value.ajaxUrl !== 'string' ||
		value.ajaxUrl === '' ||
		typeof value.ajaxAction !== 'string' ||
		value.ajaxAction === '' ||
		! isObject( value.routes )
	) {
		return null;
	}
	const save = parseRoute( value.routes.save, 'admin-columns.save.view' );
	if ( save === null ) {
		return null;
	}
	const list = parseRoute( value.routes.list, 'admin-columns.list.views' );
	const get = parseRoute( value.routes.get, 'admin-columns.get.view' );
	return {
		ajaxUrl: value.ajaxUrl,
		ajaxAction: value.ajaxAction,
		routes: {
			save,
			...( list !== null && get !== null ? { list, get } : {} ),
		},
	};
}

function parseDefinition( value: unknown ): SavedDefinition | null {
	if (
		! isObject( value ) ||
		value.type !== 'admin_columns_view' ||
		value.owner_surface_id !== 8 ||
		typeof value.id !== 'string' ||
		! UUID_PATTERN.test( value.id ) ||
		typeof value.revision !== 'number' ||
		! Number.isInteger( value.revision ) ||
		value.revision < 1 ||
		typeof value.status !== 'string' ||
		! DEFINITION_STATUSES.includes( value.status ) ||
		! isObject( value.payload )
	) {
		return null;
	}
	return {
		id: value.id,
		revision: value.revision,
		status: value.status,
		payload: value.payload,
	};
}

function parseCanonicalView(
	definition: SavedDefinition,
	bootstrap: NonNullable< ReturnType< typeof parseAdminColumnsBootstrap > >
): CanonicalView | null {
	const payload = definition.payload;
	if (
		typeof payload.view_key !== 'string' ||
		! MACHINE_KEY_PATTERN.test( payload.view_key ) ||
		typeof payload.name !== 'string' ||
		payload.name.trim() === '' ||
		payload.name.trim().length > 191 ||
		typeof payload.enabled !== 'boolean' ||
		! isObject( payload.target ) ||
		typeof payload.target.type !== 'string' ||
		typeof payload.target.key !== 'string' ||
		! Array.isArray( payload.columns ) ||
		payload.columns.length === 0 ||
		payload.columns.length > 100
	) {
		return null;
	}
	const target = bootstrap.targets.find(
		( candidate ) =>
			candidate.type === payload.target.type &&
			candidate.key === payload.target.key
	);
	if ( ! target ) {
		return null;
	}

	const columns: CanonicalColumn[] = [];
	const uuids = new Set< string >();
	const keys = new Set< string >();
	for ( const value of payload.columns ) {
		if (
			! isObject( value ) ||
			typeof value.uuid !== 'string' ||
			! UUID_PATTERN.test( value.uuid ) ||
			typeof value.key !== 'string' ||
			! MACHINE_KEY_PATTERN.test( value.key ) ||
			typeof value.label !== 'string' ||
			value.label.trim() === '' ||
			value.label.trim().length > 191 ||
			typeof value.enabled !== 'boolean' ||
			! isObject( value.source ) ||
			typeof value.source.owner !== 'string' ||
			typeof value.source.reference !== 'string' ||
			typeof value.format !== 'string' ||
			typeof value.primary !== 'boolean' ||
			uuids.has( value.uuid ) ||
			keys.has( value.key )
		) {
			return null;
		}
		const source = bootstrap.sources.find(
			( candidate ) =>
				candidate.owner === value.source.owner &&
				candidate.reference === value.source.reference
		);
		if ( ! source || ! source.formats.includes( value.format ) ) {
			return null;
		}
		uuids.add( value.uuid );
		keys.add( value.key );
		columns.push( {
			uuid: value.uuid,
			key: value.key,
			label: value.label.trim(),
			enabled: value.enabled,
			owner: value.source.owner,
			reference: value.source.reference,
			format: value.format,
			primary: value.primary,
			base: value,
		} );
	}

	return {
		definition,
		viewKey: payload.view_key,
		name: payload.name.trim(),
		enabled: payload.enabled,
		targetType: payload.target.type,
		targetKey: payload.target.key,
		columns,
	};
}

function uuidV4(): string {
	const cryptoApi = globalThis.crypto;
	if ( ! cryptoApi?.getRandomValues ) {
		throw new Error( 'Secure browser UUID generation is unavailable.' );
	}
	const bytes = new Uint8Array( 16 );
	cryptoApi.getRandomValues( bytes );
	bytes[ 6 ] = ( bytes[ 6 ]! % 16 ) + 64;
	bytes[ 8 ] = ( bytes[ 8 ]! % 64 ) + 128;
	const hex = Array.from( bytes, ( byte ) =>
		byte.toString( 16 ).padStart( 2, '0' )
	).join( '' );
	return `${ hex.slice( 0, 8 ) }-${ hex.slice( 8, 12 ) }-${ hex.slice(
		12,
		16
	) }-${ hex.slice( 16, 20 ) }-${ hex.slice( 20 ) }`;
}

function newViewKey(): string {
	return `view_${ uuidV4().replaceAll( '-', '' ).slice( 0, 24 ) }`;
}

async function postRoute(
	bootstrap: RuntimeBootstrap,
	route: AjaxRoute,
	payload: JsonObject
): Promise< unknown > {
	const body = new URLSearchParams();
	body.set( 'action', bootstrap.ajaxAction );
	body.set( 'type', route.type );
	body.set( 'nonce', route.nonce );
	body.set( 'payload_json', JSON.stringify( payload ) );

	const response = await fetch( bootstrap.ajaxUrl, {
		method: 'POST',
		credentials: 'same-origin',
		headers: {
			'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
		},
		body: body.toString(),
	} );
	const decoded: unknown = await response.json();
	if (
		! response.ok ||
		! isObject( decoded ) ||
		decoded.success !== true
	) {
		throw new Error( 'Admin Columns request failed.' );
	}
	return ( decoded as AjaxEnvelope ).data;
}

function failBootstrap( root: HTMLElement ): void {
	root.dataset.wpessentialEnhanced = 'invalid-bootstrap';
	root.textContent =
		'Admin Columns editor is unavailable because its bootstrap contract is invalid.';
}

function draftIdFromCard( card: HTMLElement ): number {
	const input = card.querySelector( 'input[id^="wpessential-columns-label-"]' );
	if ( ! ( input instanceof HTMLInputElement ) ) {
		return 0;
	}
	const match = input.id.match( /-(\d+)$/ );
	return match ? Number.parseInt( match[ 1 ]!, 10 ) : 0;
}

function wirePersistence(
	root: HTMLElement,
	bootstrap: NonNullable< ReturnType< typeof parseAdminColumnsBootstrap > >,
	runtime: RuntimeBootstrap
): void {
	const save = root.querySelector( '.button-primary' );
	const saveHelp = document.getElementById( 'wpessential-columns-save-help' );
	const status = root.querySelector( '.wpessential-columns__status' );
	const form = root.querySelector( '.wpessential-columns__form' );
	if (
		! ( save instanceof HTMLButtonElement ) ||
		! ( saveHelp instanceof HTMLElement ) ||
		! ( status instanceof HTMLElement ) ||
		! ( form instanceof HTMLFormElement )
	) {
		return;
	}

	const columnUuids = new Map< number, string >();
	const columnKeys = new Map< number, string >();
	const columnPrimary = new Map< number, boolean >();
	const columnBase = new Map< number, JsonObject >();
	let definitionId: string | null = null;
	let revision: number | null = null;
	let definitionStatus = 'draft';
	let viewKey: string | null = null;
	let basePayload: JsonObject | null = null;
	let inFlight = false;

	const draftUuid = ( draftId: number ): string => {
		const existing = columnUuids.get( draftId );
		if ( existing ) {
			return existing;
		}
		const generated = uuidV4();
		columnUuids.set( draftId, generated );
		return generated;
	};

	const payload = (): JsonObject => {
		const name = document.getElementById( 'wpessential-columns-view-name' );
		const targetSelect = document.getElementById(
			'wpessential-columns-target'
		);
		if (
			! ( name instanceof HTMLInputElement ) ||
			! ( targetSelect instanceof HTMLSelectElement ) ||
			name.value.trim() === ''
		) {
			throw new Error( 'Column Set name is required.' );
		}
		const target = bootstrap.targets.find(
			( candidate ) => candidate.key === targetSelect.value
		);
		if ( ! target ) {
			throw new Error( 'Selected target is unavailable.' );
		}

		const columns: JsonObject[] = [];
		for ( const card of root.querySelectorAll< HTMLElement >(
			'.wpessential-columns__column'
		) ) {
			const labelInput = card.querySelector(
				'input[id^="wpessential-columns-label-"]'
			);
			const sourceSelect = card.querySelector(
				'select[id^="wpessential-columns-source-"]'
			);
			const formatSelect = card.querySelector(
				'select[id^="wpessential-columns-format-"]'
			);
			const enabled = card.querySelector(
				'input[type="checkbox"][id^="wpessential-columns-enabled-"]'
			);
			const draftId = draftIdFromCard( card );
			if (
				! ( labelInput instanceof HTMLInputElement ) ||
				! ( sourceSelect instanceof HTMLSelectElement ) ||
				! ( formatSelect instanceof HTMLSelectElement ) ||
				! ( enabled instanceof HTMLInputElement ) ||
				draftId < 1 ||
				labelInput.value.trim() === ''
			) {
				throw new Error(
					'Every Column requires a valid authored state.'
				);
			}
			const source = bootstrap.sources.find(
				( candidate ) => candidate.reference === sourceSelect.value
			);
			if ( ! source || ! source.formats.includes( formatSelect.value ) ) {
				throw new Error( 'Column source or format is unavailable.' );
			}
			columns.push( {
				...( columnBase.get( draftId ) ?? {} ),
				uuid: draftUuid( draftId ),
				key: columnKeys.get( draftId ) ?? `column_${ draftId }`,
				label: labelInput.value.trim(),
				enabled: enabled.checked,
				source: {
					owner: source.owner,
					reference: source.reference,
				},
				format: formatSelect.value,
				primary: columnPrimary.get( draftId ) ?? false,
			} );
		}
		if ( columns.length === 0 ) {
			throw new Error( 'At least one Column is required.' );
		}
		viewKey ??= newViewKey();
		return {
			...( basePayload ?? {} ),
			view_key: viewKey,
			name: name.value.trim(),
			enabled:
				typeof basePayload?.enabled === 'boolean'
					? basePayload.enabled
					: true,
			target: { type: target.type, key: target.key },
			columns,
		};
	};

	const hydrate = ( view: CanonicalView ): boolean => {
		const name = document.getElementById( 'wpessential-columns-view-name' );
		const targetSelect = document.getElementById(
			'wpessential-columns-target'
		);
		const columnsList = root.querySelector( '.wpessential-columns__list' );
		const addColumn = columnsList?.nextElementSibling;
		if (
			! ( name instanceof HTMLInputElement ) ||
			! ( targetSelect instanceof HTMLSelectElement ) ||
			! ( columnsList instanceof HTMLElement ) ||
			! ( addColumn instanceof HTMLButtonElement )
		) {
			return false;
		}

		let cards = Array.from(
			root.querySelectorAll< HTMLElement >( '.wpessential-columns__column' )
		);
		for ( let guard = 0; cards.length < view.columns.length && guard < 100; guard++ ) {
			addColumn.click();
			cards = Array.from(
				root.querySelectorAll< HTMLElement >(
					'.wpessential-columns__column'
				)
			);
		}
		for ( let guard = 0; cards.length > view.columns.length && guard < 100; guard++ ) {
			const last = cards.at( -1 );
			const buttons = last?.querySelectorAll< HTMLButtonElement >(
				'.wpessential-columns__actions button'
			);
			const remove = buttons?.item( 2 );
			if ( ! remove ) {
				return false;
			}
			remove.click();
			cards = Array.from(
				root.querySelectorAll< HTMLElement >(
					'.wpessential-columns__column'
				)
			);
		}
		if ( cards.length !== view.columns.length ) {
			return false;
		}

		name.value = view.name;
		name.dispatchEvent( new Event( 'input', { bubbles: true } ) );
		targetSelect.value = view.targetKey;
		targetSelect.dispatchEvent( new Event( 'change', { bubbles: true } ) );

		columnUuids.clear();
		columnKeys.clear();
		columnPrimary.clear();
		columnBase.clear();
		for ( const [ index, card ] of cards.entries() ) {
			const canonical = view.columns[ index ];
			const draftId = draftIdFromCard( card );
			const labelInput = card.querySelector(
				'input[id^="wpessential-columns-label-"]'
			);
			const sourceSelect = card.querySelector(
				'select[id^="wpessential-columns-source-"]'
			);
			const enabled = card.querySelector(
				'input[type="checkbox"][id^="wpessential-columns-enabled-"]'
			);
			if (
				! canonical ||
				draftId < 1 ||
				! ( labelInput instanceof HTMLInputElement ) ||
				! ( sourceSelect instanceof HTMLSelectElement ) ||
				! ( enabled instanceof HTMLInputElement )
			) {
				return false;
			}
			columnUuids.set( draftId, canonical.uuid );
			columnKeys.set( draftId, canonical.key );
			columnPrimary.set( draftId, canonical.primary );
			columnBase.set( draftId, canonical.base );

			labelInput.value = canonical.label;
			labelInput.dispatchEvent( new Event( 'input', { bubbles: true } ) );
			sourceSelect.value = canonical.reference;
			sourceSelect.dispatchEvent( new Event( 'change', { bubbles: true } ) );
			const formatSelect = card.querySelector(
				'select[id^="wpessential-columns-format-"]'
			);
			if ( ! ( formatSelect instanceof HTMLSelectElement ) ) {
				return false;
			}
			formatSelect.value = canonical.format;
			formatSelect.dispatchEvent( new Event( 'change', { bubbles: true } ) );
			enabled.checked = canonical.enabled;
			enabled.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		}

		definitionId = view.definition.id;
		revision = view.definition.revision;
		definitionStatus = view.definition.status;
		viewKey = view.viewKey;
		basePayload = view.definition.payload;
		status.textContent = `Loaded saved Column Set revision ${ revision }.`;
		return true;
	};

	save.disabled = false;
	save.textContent = 'Save Column Set';
	saveHelp.textContent =
		'Saves only this revisioned Column Set definition through the shared WPEssential Ability/AJAX layer. Row data is never mutated here.';

	save.addEventListener( 'click', async () => {
		if ( inFlight ) {
			return;
		}
		try {
			const request: JsonObject = {
				payload: payload(),
				status: definitionStatus,
			};
			if ( definitionId !== null && revision !== null ) {
				request.id = definitionId;
				request.expected_revision = revision;
			}

			inFlight = true;
			save.disabled = true;
			save.textContent = 'Saving…';
			status.textContent = 'Saving revisioned Column Set definition…';
			const data = await postRoute( runtime, runtime.routes.save, request );
			if ( ! isObject( data ) ) {
				throw new Error( 'Invalid save response.' );
			}
			const definition = parseDefinition( data.definition );
			const returned = definition
				? parseCanonicalView( definition, bootstrap )
				: null;
			if (
				returned === null ||
				returned.viewKey !== viewKey ||
				( definitionId !== null && returned.definition.id !== definitionId )
			) {
				throw new Error( 'Invalid revisioned definition response.' );
			}
			definitionId = returned.definition.id;
			revision = returned.definition.revision;
			definitionStatus = returned.definition.status;
			basePayload = returned.definition.payload;
			status.textContent = `Column Set saved as revision ${ revision }.`;
			window.dispatchEvent(
				new CustomEvent( 'wpessential:columns-view-saved', {
					detail: { id: definitionId },
				} )
			);
		} catch {
			status.textContent =
				'Column Set could not be saved. Review the authored values, refresh server state if needed, and retry.';
		} finally {
			inFlight = false;
			save.disabled = false;
			save.textContent = 'Save Column Set';
		}
	} );

	const listRoute = runtime.routes.list;
	const getRoute = runtime.routes.get;
	if ( ! listRoute || ! getRoute ) {
		return;
	}

	const browser = document.createElement( 'div' );
	browser.className = 'wpessential-columns__field';
	const label = document.createElement( 'label' );
	label.className = 'wpessential-columns__label';
	label.htmlFor = 'wpessential-columns-saved-view';
	label.textContent = 'Saved Column Set';
	const select = document.createElement( 'select' );
	select.id = 'wpessential-columns-saved-view';
	const load = document.createElement( 'button' );
	load.type = 'button';
	load.className = 'button';
	load.textContent = 'Load saved Column Set';
	load.disabled = true;
	browser.append( label, select, load );
	form.parentNode?.insertBefore( browser, form );

	const refreshList = async (): Promise< void > => {
		try {
			const data = await postRoute( runtime, listRoute, {} );
			if ( ! isObject( data ) || ! Array.isArray( data.definitions ) ) {
				throw new Error( 'Invalid saved View list.' );
			}
			const views = data.definitions
				.map( parseDefinition )
				.filter(
					( definition ): definition is SavedDefinition =>
						definition !== null &&
						parseCanonicalView( definition, bootstrap ) !== null
				)
				.slice( 0, MAX_SAVED_VIEWS );
			select.replaceChildren();
			const empty = document.createElement( 'option' );
			empty.value = '';
			empty.textContent =
				views.length === 0
					? 'No saved Column Sets'
					: 'Choose a saved Column Set';
			select.append( empty );
			for ( const definition of views ) {
				const item = document.createElement( 'option' );
				item.value = definition.id;
				item.textContent = `${ String(
					definition.payload.name
				) } — revision ${ definition.revision }`;
				select.append( item );
			}
			load.disabled = true;
		} catch {
			select.replaceChildren();
			const item = document.createElement( 'option' );
			item.value = '';
			item.textContent = 'Saved Column Sets unavailable';
			select.append( item );
			load.disabled = true;
			status.textContent =
				'Saved Column Sets could not be listed. The current draft remains unchanged.';
		}
	};

	select.addEventListener( 'change', () => {
		load.disabled = select.value === '';
	} );
	load.addEventListener( 'click', async () => {
		if ( select.value === '' || load.disabled ) {
			return;
		}
		const selectedId = select.value;
		load.disabled = true;
		try {
			const data = await postRoute( runtime, getRoute, { id: selectedId } );
			if ( ! isObject( data ) ) {
				throw new Error( 'Invalid saved View response.' );
			}
			const definition = parseDefinition( data.definition );
			const view = definition
				? parseCanonicalView( definition, bootstrap )
				: null;
			if ( view === null || view.definition.id !== selectedId || ! hydrate( view ) ) {
				throw new Error( 'Saved View hydration failed.' );
			}
		} catch {
			status.textContent =
				'Saved Column Set could not be loaded. The current canonical revision was not replaced.';
		} finally {
			load.disabled = select.value === '';
		}
	} );
	window.addEventListener( 'wpessential:columns-view-saved', () => {
		void refreshList();
	} );
	void refreshList();
}

function boot(): void {
	const root = document.getElementById( 'wpessential-columns-root' );
	const script = document.getElementById( 'wpessential-columns-bootstrap' );
	if (
		! ( root instanceof HTMLElement ) ||
		! ( script instanceof HTMLScriptElement )
	) {
		return;
	}

	try {
		const raw: unknown = JSON.parse( script.textContent ?? '{}' );
		const bootstrap = parseAdminColumnsBootstrap( raw );
		if ( bootstrap === null ) {
			failBootstrap( root );
			return;
		}
		mountAdminColumnsScaffold( root, bootstrap );
		const runtime = parseRuntimeBootstrap( raw );
		if ( runtime !== null ) {
			wirePersistence( root, bootstrap, runtime );
		}
		window.dispatchEvent(
			new CustomEvent( 'wpessential:admin-ready', {
				detail: { surface: 'columns', payload: bootstrap },
			} )
		);
	} catch {
		failBootstrap( root );
	}
}

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', boot, { once: true } );
} else {
	boot();
}
