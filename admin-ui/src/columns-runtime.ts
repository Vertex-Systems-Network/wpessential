import {
	mountAdminColumnsScaffold,
	parseAdminColumnsBootstrap,
} from './columns';

type JsonObject = Record< string, unknown >;
type AjaxRoute = { type: string; nonce: string };
type SaveBootstrap = {
	ajaxUrl: string;
	ajaxAction: string;
	routes: { save: AjaxRoute };
};
type BrowseBootstrap = {
	ajaxUrl: string;
	ajaxAction: string;
	routes: { list: AjaxRoute; get: AjaxRoute };
};
type ReadBootstrap = {
	ajaxUrl: string;
	ajaxAction: string;
	routes: { read: AjaxRoute };
};
type StatusBootstrap = {
	ajaxUrl: string;
	ajaxAction: string;
	routes: { status: AjaxRoute };
};
type PreviewColumn = {
	key: string;
	label: string;
	format: string;
	primary: boolean;
	sourceOwner: string;
};
type PreviewResult = {
	viewId: string;
	viewRevision: number;
	columns: PreviewColumn[];
	rows: Record< string, string | null >[];
	returned: number;
};
type DefinitionStatus = 'draft' | 'published' | 'disabled' | 'archived';
type DefinitionHeader = {
	id: string;
	revision: number;
	status: DefinitionStatus;
	payload: JsonObject;
};
type LoadedColumn = {
	uuid: string;
	key: string;
	label: string;
	enabled: boolean;
	source: { owner: string; reference: string };
	format: string;
	primary: boolean;
	layout?: JsonObject;
};
type LoadedPayload = {
	viewKey: string;
	name: string;
	enabled: boolean;
	target: { type: string; key: string };
	columns: LoadedColumn[];
	assignment?: JsonObject;
	layout?: JsonObject;
	visibility?: JsonObject;
};
type LoadedDefinition = DefinitionHeader & { loadedPayload: LoadedPayload };
type SavedSummary = {
	id: string;
	revision: number;
	status: DefinitionStatus;
	viewKey: string;
	name: string;
};
type AjaxEnvelope = {
	success: boolean;
	data?: unknown;
};
type ColumnIdentity = {
	uuid: string;
	key: string;
	primary: boolean;
	layout?: JsonObject;
};
type EditorSession = {
	definitionId: string | null;
	revision: number | null;
	viewKey: string | null;
	status: DefinitionStatus;
	viewEnabled: boolean;
	assignment: JsonObject | undefined;
	layout: JsonObject | undefined;
	visibility: JsonObject | undefined;
	columnIdentities: Map< number, ColumnIdentity >;
};

const UUID_PATTERN =
	/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/;
const MACHINE_KEY_PATTERN = /^[a-z0-9][a-z0-9_-]{0,63}$/;
const MAX_SAVED_VIEWS = 100;
const MAX_COLUMNS = 100;
const MAX_PREVIEW_ROWS = 100;
const MAX_PREVIEW_CELL_TEXT = 500;
const DEFINITION_TYPE = 'admin_columns_view';
const OWNER_SURFACE_ID = 8;
const STATUSES: DefinitionStatus[] = [
	'draft',
	'published',
	'disabled',
	'archived',
];

function isObject( value: unknown ): value is JsonObject {
	return (
		typeof value === 'object' && value !== null && ! Array.isArray( value )
	);
}

function isStatus( value: unknown ): value is DefinitionStatus {
	return (
		typeof value === 'string' &&
		STATUSES.includes( value as DefinitionStatus )
	);
}

function machineKey( value: unknown ): value is string {
	return typeof value === 'string' && MACHINE_KEY_PATTERN.test( value );
}

function boundedLabel( value: unknown ): value is string {
	return (
		typeof value === 'string' &&
		value.trim() !== '' &&
		value.trim().length <= 191
	);
}

function hasOnlyKeys( value: JsonObject, allowed: string[] ): boolean {
	return Object.keys( value ).every( ( key ) => allowed.includes( key ) );
}

function boundedObject( value: unknown, maxBytes: number ): JsonObject | null {
	if ( ! isObject( value ) ) {
		return null;
	}
	try {
		if ( JSON.stringify( value ).length > maxBytes ) {
			return null;
		}
	} catch {
		return null;
	}
	return value;
}

function isRoute( value: unknown, expectedType: string ): value is AjaxRoute {
	return (
		isObject( value ) &&
		typeof value.type === 'string' &&
		value.type === expectedType &&
		typeof value.nonce === 'string' &&
		value.nonce !== ''
	);
}

function parseSaveBootstrap( value: unknown ): SaveBootstrap | null {
	if (
		! isObject( value ) ||
		typeof value.ajaxUrl !== 'string' ||
		value.ajaxUrl === '' ||
		typeof value.ajaxAction !== 'string' ||
		value.ajaxAction === '' ||
		! isObject( value.routes ) ||
		! isRoute( value.routes.save, 'admin-columns.save.view' )
	) {
		return null;
	}
	return {
		ajaxUrl: value.ajaxUrl,
		ajaxAction: value.ajaxAction,
		routes: { save: value.routes.save },
	};
}

function parseBrowseBootstrap( value: unknown ): BrowseBootstrap | null {
	if (
		! isObject( value ) ||
		typeof value.ajaxUrl !== 'string' ||
		value.ajaxUrl === '' ||
		typeof value.ajaxAction !== 'string' ||
		value.ajaxAction === '' ||
		! isObject( value.routes ) ||
		! isRoute( value.routes.list, 'admin-columns.list.views' ) ||
		! isRoute( value.routes.get, 'admin-columns.get.view' )
	) {
		return null;
	}
	return {
		ajaxUrl: value.ajaxUrl,
		ajaxAction: value.ajaxAction,
		routes: {
			list: value.routes.list,
			get: value.routes.get,
		},
	};
}

function parseReadBootstrap( value: unknown ): ReadBootstrap | null {
	if (
		! isObject( value ) ||
		typeof value.ajaxUrl !== 'string' ||
		value.ajaxUrl === '' ||
		typeof value.ajaxAction !== 'string' ||
		value.ajaxAction === '' ||
		! isObject( value.routes ) ||
		! isRoute( value.routes.read, 'admin-columns.read.rows' )
	) {
		return null;
	}
	return {
		ajaxUrl: value.ajaxUrl,
		ajaxAction: value.ajaxAction,
		routes: { read: value.routes.read },
	};
}

function parseStatusBootstrap( value: unknown ): StatusBootstrap | null {
	if (
		! isObject( value ) ||
		typeof value.ajaxUrl !== 'string' ||
		value.ajaxUrl === '' ||
		typeof value.ajaxAction !== 'string' ||
		value.ajaxAction === '' ||
		! isObject( value.routes ) ||
		! isRoute( value.routes.status, 'admin-columns.status.view' )
	) {
		return null;
	}
	return {
		ajaxUrl: value.ajaxUrl,
		ajaxAction: value.ajaxAction,
		routes: { status: value.routes.status },
	};
}

function parseDefinitionHeader( value: unknown ): DefinitionHeader | null {
	if (
		! isObject( value ) ||
		typeof value.id !== 'string' ||
		! UUID_PATTERN.test( value.id ) ||
		value.type !== DEFINITION_TYPE ||
		value.owner_surface_id !== OWNER_SURFACE_ID ||
		typeof value.revision !== 'number' ||
		! Number.isInteger( value.revision ) ||
		value.revision < 1 ||
		! isStatus( value.status ) ||
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

function parseSummary( value: unknown ): SavedSummary | null {
	const definition = parseDefinitionHeader( value );
	if ( definition === null ) {
		return null;
	}
	const viewKey = definition.payload.view_key;
	const name = definition.payload.name;
	if ( ! machineKey( viewKey ) || ! boundedLabel( name ) ) {
		return null;
	}
	return {
		id: definition.id,
		revision: definition.revision,
		status: definition.status,
		viewKey,
		name: name.trim(),
	};
}

function parseLoadedDefinition(
	value: unknown,
	bootstrap: NonNullable< ReturnType< typeof parseAdminColumnsBootstrap > >
): LoadedDefinition | null {
	const definition = parseDefinitionHeader( value );
	if ( definition === null ) {
		return null;
	}
	const payload = definition.payload;
	if (
		! hasOnlyKeys( payload, [
			'view_key',
			'name',
			'enabled',
			'target',
			'columns',
			'assignment',
			'layout',
			'visibility',
		] ) ||
		! machineKey( payload.view_key ) ||
		! boundedLabel( payload.name ) ||
		typeof payload.enabled !== 'boolean' ||
		! isObject( payload.target ) ||
		! hasOnlyKeys( payload.target, [ 'type', 'key' ] ) ||
		typeof payload.target.type !== 'string' ||
		typeof payload.target.key !== 'string' ||
		! Array.isArray( payload.columns ) ||
		payload.columns.length === 0 ||
		payload.columns.length > MAX_COLUMNS
	) {
		return null;
	}
	const payloadTarget = payload.target;
	const target = bootstrap.targets.find(
		( candidate ) =>
			candidate.type === payloadTarget.type &&
			candidate.key === payloadTarget.key
	);
	if ( ! target ) {
		return null;
	}

	const columns: LoadedColumn[] = [];
	const uuids = new Set< string >();
	const keys = new Set< string >();
	for ( const candidate of payload.columns ) {
		if (
			! isObject( candidate ) ||
			! hasOnlyKeys( candidate, [
				'uuid',
				'key',
				'label',
				'enabled',
				'source',
				'format',
				'layout',
				'primary',
			] ) ||
			typeof candidate.uuid !== 'string' ||
			! UUID_PATTERN.test( candidate.uuid ) ||
			! machineKey( candidate.key ) ||
			! boundedLabel( candidate.label ) ||
			typeof candidate.enabled !== 'boolean' ||
			! isObject( candidate.source ) ||
			! hasOnlyKeys( candidate.source, [ 'owner', 'reference' ] ) ||
			typeof candidate.source.owner !== 'string' ||
			typeof candidate.source.reference !== 'string' ||
			typeof candidate.format !== 'string' ||
			typeof candidate.primary !== 'boolean' ||
			uuids.has( candidate.uuid ) ||
			keys.has( candidate.key )
		) {
			return null;
		}
		const candidateSource = candidate.source;
		const source = bootstrap.sources.find(
			( available ) =>
				available.owner === candidateSource.owner &&
				available.reference === candidateSource.reference
		);
		if ( ! source || ! source.formats.includes( candidate.format ) ) {
			return null;
		}
		let columnLayout: JsonObject | undefined;
		if ( candidate.layout !== undefined ) {
			const parsedLayout = boundedObject( candidate.layout, 2048 );
			if ( parsedLayout === null ) {
				return null;
			}
			columnLayout = parsedLayout;
		}
		uuids.add( candidate.uuid );
		keys.add( candidate.key );
		columns.push( {
			uuid: candidate.uuid,
			key: candidate.key,
			label: candidate.label.trim(),
			enabled: candidate.enabled,
			source: {
				owner: source.owner,
				reference: source.reference,
			},
			format: candidate.format,
			primary: candidate.primary,
			...( columnLayout ? { layout: columnLayout } : {} ),
		} );
	}

	const extras: {
		assignment?: JsonObject;
		layout?: JsonObject;
		visibility?: JsonObject;
	} = {};
	for ( const key of [ 'assignment', 'layout', 'visibility' ] as const ) {
		if ( payload[ key ] === undefined ) {
			continue;
		}
		const parsed = boundedObject( payload[ key ], 8192 );
		if ( parsed === null ) {
			return null;
		}
		extras[ key ] = parsed;
	}

	return {
		...definition,
		loadedPayload: {
			viewKey: payload.view_key,
			name: payload.name.trim(),
			enabled: payload.enabled,
			target: { type: target.type, key: target.key },
			columns,
			...extras,
		},
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
	ajaxUrl: string,
	ajaxAction: string,
	route: AjaxRoute,
	payload: JsonObject
): Promise< unknown > {
	const body = new URLSearchParams();
	body.set( 'action', ajaxAction );
	body.set( 'type', route.type );
	body.set( 'nonce', route.nonce );
	body.set( 'payload_json', JSON.stringify( payload ) );

	const response = await fetch( ajaxUrl, {
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
		typeof decoded.success !== 'boolean' ||
		decoded.success !== true
	) {
		throw new Error( 'Admin Columns AJAX request failed.' );
	}
	return ( decoded as AjaxEnvelope ).data;
}

function parsePreviewResult(
	value: unknown,
	expectedViewId: string,
	expectedRevision: number,
	pageSize: number
): PreviewResult | null {
	if (
		! isObject( value ) ||
		! hasOnlyKeys( value, [
			'contract_version',
			'ok',
			'view_id',
			'view_revision',
			'columns',
			'rows',
			'returned',
			'error',
		] ) ||
		value.contract_version !== 1 ||
		value.ok !== true ||
		value.view_id !== expectedViewId ||
		value.view_revision !== expectedRevision ||
		value.error !== null ||
		! Array.isArray( value.columns ) ||
		value.columns.length === 0 ||
		value.columns.length > MAX_COLUMNS ||
		! Array.isArray( value.rows ) ||
		value.rows.length > MAX_PREVIEW_ROWS ||
		value.rows.length > pageSize ||
		typeof value.returned !== 'number' ||
		! Number.isInteger( value.returned ) ||
		value.returned !== value.rows.length
	) {
		return null;
	}

	const columns: PreviewColumn[] = [];
	const columnKeys = new Set< string >();
	for ( const candidate of value.columns ) {
		if (
			! isObject( candidate ) ||
			! hasOnlyKeys( candidate, [
				'key',
				'label',
				'format',
				'primary',
				'source_owner',
			] ) ||
			! machineKey( candidate.key ) ||
			! boundedLabel( candidate.label ) ||
			typeof candidate.format !== 'string' ||
			candidate.format === '' ||
			candidate.format.length > 64 ||
			typeof candidate.primary !== 'boolean' ||
			typeof candidate.source_owner !== 'string' ||
			! [ 'native', 'query' ].includes( candidate.source_owner ) ||
			columnKeys.has( candidate.key )
		) {
			return null;
		}
		columnKeys.add( candidate.key );
		columns.push( {
			key: candidate.key,
			label: candidate.label.trim(),
			format: candidate.format,
			primary: candidate.primary,
			sourceOwner: candidate.source_owner,
		} );
	}

	const allowedRowKeys = columns.map( ( column ) => column.key );
	const rows: Record< string, string | null >[] = [];
	for ( const candidate of value.rows ) {
		if (
			! isObject( candidate ) ||
			! hasOnlyKeys( candidate, allowedRowKeys )
		) {
			return null;
		}
		const row: Record< string, string | null > = {};
		for ( const column of columns ) {
			if (
				! Object.prototype.hasOwnProperty.call( candidate, column.key )
			) {
				return null;
			}
			const cell = candidate[ column.key ];
			if ( cell === null ) {
				row[ column.key ] = null;
				continue;
			}
			if ( typeof cell === 'string' ) {
				if ( cell.length > MAX_PREVIEW_CELL_TEXT ) {
					return null;
				}
				row[ column.key ] = cell;
				continue;
			}
			if ( typeof cell === 'number' && Number.isFinite( cell ) ) {
				row[ column.key ] = String( cell );
				continue;
			}
			if ( typeof cell === 'boolean' ) {
				row[ column.key ] = cell ? 'true' : 'false';
				continue;
			}
			return null;
		}
		rows.push( row );
	}

	return {
		viewId: expectedViewId,
		viewRevision: expectedRevision,
		columns,
		rows,
		returned: value.returned,
	};
}

function failBootstrap( root: HTMLElement ): void {
	root.dataset.wpessentialEnhanced = 'invalid-bootstrap';
	root.textContent =
		'Admin Columns editor is unavailable because its bootstrap contract is invalid.';
}

function columnDraftId( card: HTMLElement ): number | null {
	const labelInput = card.querySelector(
		'input[id^="wpessential-columns-label-"]'
	);
	if ( ! ( labelInput instanceof HTMLInputElement ) ) {
		return null;
	}
	const match = labelInput.id.match( /-(\d+)$/ );
	if ( ! match ) {
		return null;
	}
	const id = Number.parseInt( match[ 1 ]!, 10 );
	return Number.isInteger( id ) && id > 0 ? id : null;
}

function hydrateDefinition(
	root: HTMLElement,
	bootstrap: NonNullable< ReturnType< typeof parseAdminColumnsBootstrap > >,
	session: EditorSession,
	definition: LoadedDefinition
): void {
	const name = document.getElementById( 'wpessential-columns-view-name' );
	const target = document.getElementById( 'wpessential-columns-target' );
	const columnsList = root.querySelector( '.wpessential-columns__list' );
	const addColumn = columnsList?.nextElementSibling;
	if (
		! ( name instanceof HTMLInputElement ) ||
		! ( target instanceof HTMLSelectElement ) ||
		! ( columnsList instanceof HTMLElement ) ||
		! ( addColumn instanceof HTMLButtonElement )
	) {
		throw new Error( 'Admin Columns hydration surface is unavailable.' );
	}
	if (
		! bootstrap.targets.some(
			( candidate ) =>
				candidate.type === definition.loadedPayload.target.type &&
				candidate.key === definition.loadedPayload.target.key
		)
	) {
		throw new Error( 'Saved View target is unavailable.' );
	}

	while ( true ) {
		const card = columnsList.querySelector(
			'.wpessential-columns__column'
		);
		if ( ! ( card instanceof HTMLElement ) ) {
			break;
		}
		const actionButtons = card.querySelectorAll< HTMLButtonElement >(
			'.wpessential-columns__actions button'
		);
		const remove = actionButtons.item( 2 );
		if ( ! ( remove instanceof HTMLButtonElement ) ) {
			throw new Error( 'Admin Columns remove action is unavailable.' );
		}
		remove.click();
	}

	name.value = definition.loadedPayload.name;
	name.dispatchEvent( new Event( 'input', { bubbles: true } ) );
	target.value = definition.loadedPayload.target.key;
	target.dispatchEvent( new Event( 'change', { bubbles: true } ) );

	const identities = new Map< number, ColumnIdentity >();
	for ( const column of definition.loadedPayload.columns ) {
		addColumn.click();
		const cards = columnsList.querySelectorAll< HTMLElement >(
			'.wpessential-columns__column'
		);
		const card = cards.item( cards.length - 1 );
		if ( ! ( card instanceof HTMLElement ) ) {
			throw new Error( 'Admin Columns could not add a hydration row.' );
		}
		const draftId = columnDraftId( card );
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
		if (
			draftId === null ||
			! ( labelInput instanceof HTMLInputElement ) ||
			! ( sourceSelect instanceof HTMLSelectElement ) ||
			! ( formatSelect instanceof HTMLSelectElement ) ||
			! ( enabled instanceof HTMLInputElement )
		) {
			throw new Error(
				'Admin Columns hydration controls are unavailable.'
			);
		}

		labelInput.value = column.label;
		labelInput.dispatchEvent( new Event( 'input', { bubbles: true } ) );
		sourceSelect.value = column.source.reference;
		sourceSelect.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		formatSelect.value = column.format;
		formatSelect.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		enabled.checked = column.enabled;
		enabled.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		identities.set( draftId, {
			uuid: column.uuid,
			key: column.key,
			primary: column.primary,
			...( column.layout ? { layout: column.layout } : {} ),
		} );
	}

	session.definitionId = definition.id;
	session.revision = definition.revision;
	session.viewKey = definition.loadedPayload.viewKey;
	session.status = definition.status;
	session.viewEnabled = definition.loadedPayload.enabled;
	session.assignment = definition.loadedPayload.assignment;
	session.layout = definition.loadedPayload.layout;
	session.visibility = definition.loadedPayload.visibility;
	session.columnIdentities = identities;
	root.dispatchEvent(
		new CustomEvent( 'wpessential:columns-session-changed' )
	);
}

function wireSave(
	root: HTMLElement,
	bootstrap: NonNullable< ReturnType< typeof parseAdminColumnsBootstrap > >,
	saveBootstrap: SaveBootstrap,
	session: EditorSession
): void {
	const save = root.querySelector( '.button-primary' );
	const saveHelp = document.getElementById( 'wpessential-columns-save-help' );
	const status = root.querySelector( '.wpessential-columns__status' );
	if (
		! ( save instanceof HTMLButtonElement ) ||
		! ( saveHelp instanceof HTMLElement ) ||
		! ( status instanceof HTMLElement )
	) {
		return;
	}

	let inFlight = false;
	const identityFor = ( draftId: number ): ColumnIdentity => {
		const existing = session.columnIdentities.get( draftId );
		if ( existing ) {
			return existing;
		}
		const generated = {
			uuid: uuidV4(),
			key: `column_${ draftId }`,
			primary: false,
		};
		session.columnIdentities.set( draftId, generated );
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
			const draftId = columnDraftId( card );
			if (
				! ( labelInput instanceof HTMLInputElement ) ||
				! ( sourceSelect instanceof HTMLSelectElement ) ||
				! ( formatSelect instanceof HTMLSelectElement ) ||
				! ( enabled instanceof HTMLInputElement ) ||
				draftId === null ||
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
			const identity = identityFor( draftId );
			const column: JsonObject = {
				uuid: identity.uuid,
				key: identity.key,
				label: labelInput.value.trim(),
				enabled: enabled.checked,
				source: {
					owner: source.owner,
					reference: source.reference,
				},
				format: formatSelect.value,
				primary: identity.primary,
			};
			if ( identity.layout ) {
				column.layout = identity.layout;
			}
			columns.push( column );
		}
		if ( columns.length === 0 ) {
			throw new Error( 'At least one Column is required.' );
		}
		session.viewKey ??= newViewKey();
		const view: JsonObject = {
			view_key: session.viewKey,
			name: name.value.trim(),
			enabled: session.viewEnabled,
			target: { type: target.type, key: target.key },
			columns,
		};
		if ( session.assignment ) {
			view.assignment = session.assignment;
		}
		if ( session.layout ) {
			view.layout = session.layout;
		}
		if ( session.visibility ) {
			view.visibility = session.visibility;
		}
		return view;
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
			const previousRevision = session.revision;
			const request: JsonObject = {
				payload: payload(),
				status: session.status,
			};
			if ( session.definitionId !== null && session.revision !== null ) {
				request.id = session.definitionId;
				request.expected_revision = session.revision;
			}

			inFlight = true;
			save.disabled = true;
			save.textContent = 'Saving…';
			status.textContent = 'Saving revisioned Column Set definition…';
			const data = await postRoute(
				saveBootstrap.ajaxUrl,
				saveBootstrap.ajaxAction,
				saveBootstrap.routes.save,
				request
			);
			if ( ! isObject( data ) ) {
				throw new Error( 'Invalid save response.' );
			}
			const definition = parseDefinitionHeader( data.definition );
			const returnedKey = definition?.payload.view_key;
			if (
				definition === null ||
				typeof returnedKey !== 'string' ||
				returnedKey !== session.viewKey ||
				definition.status !== session.status ||
				( session.definitionId !== null &&
					definition.id !== session.definitionId ) ||
				( previousRevision !== null &&
					definition.revision !== previousRevision + 1 )
			) {
				throw new Error( 'Invalid revisioned definition response.' );
			}
			session.definitionId = definition.id;
			session.revision = definition.revision;
			root.dispatchEvent(
				new CustomEvent( 'wpessential:columns-session-changed' )
			);
			status.textContent = `Column Set saved as revision ${ session.revision }.`;
		} catch {
			status.textContent =
				'Column Set could not be saved. Review the authored values, refresh server state if needed, and retry.';
		} finally {
			inFlight = false;
			save.disabled = false;
			save.textContent = 'Save Column Set';
		}
	} );
}

function wireBrowse(
	root: HTMLElement,
	bootstrap: NonNullable< ReturnType< typeof parseAdminColumnsBootstrap > >,
	browseBootstrap: BrowseBootstrap,
	session: EditorSession
): void {
	const form = root.querySelector( '.wpessential-columns__form' );
	const status = root.querySelector( '.wpessential-columns__status' );
	if (
		! ( form instanceof HTMLFormElement ) ||
		! ( status instanceof HTMLElement )
	) {
		return;
	}

	const field = document.createElement( 'div' );
	field.className = 'wpessential-columns__field';
	const label = document.createElement( 'label' );
	label.className = 'wpessential-columns__label';
	label.htmlFor = 'wpessential-columns-saved-view';
	label.textContent = 'Saved Column Set';
	const select = document.createElement( 'select' );
	select.id = 'wpessential-columns-saved-view';
	select.append( new Option( 'Select a saved Column Set', '' ) );
	const load = document.createElement( 'button' );
	load.type = 'button';
	load.className = 'button';
	load.textContent = 'Open selected';
	load.disabled = true;
	const help = document.createElement( 'p' );
	help.className = 'description';
	help.textContent =
		'Opening reloads only a canonical revisioned View definition. It does not execute Query or mutate row data.';
	field.append( label, select, load, help );
	form.prepend( field );

	let summaries = new Map< string, SavedSummary >();
	let loading = false;
	select.addEventListener( 'change', () => {
		load.disabled = loading || ! summaries.has( select.value );
	} );
	load.addEventListener( 'click', async () => {
		if ( loading ) {
			return;
		}
		const summary = summaries.get( select.value );
		if ( ! summary ) {
			return;
		}
		try {
			loading = true;
			load.disabled = true;
			status.textContent = 'Opening saved Column Set definition…';
			const data = await postRoute(
				browseBootstrap.ajaxUrl,
				browseBootstrap.ajaxAction,
				browseBootstrap.routes.get,
				{ id: summary.id }
			);
			if ( ! isObject( data ) ) {
				throw new Error( 'Invalid saved View response.' );
			}
			const definition = parseLoadedDefinition(
				data.definition,
				bootstrap
			);
			if (
				definition === null ||
				definition.id !== summary.id ||
				definition.revision < summary.revision
			) {
				throw new Error( 'Saved View response is malformed or stale.' );
			}
			hydrateDefinition( root, bootstrap, session, definition );
			status.textContent = `Opened ${ definition.loadedPayload.name } at revision ${ definition.revision }. The next Save is an optimistic update of this definition.`;
			const refreshed: SavedSummary = {
				id: definition.id,
				revision: definition.revision,
				status: definition.status,
				viewKey: definition.loadedPayload.viewKey,
				name: definition.loadedPayload.name,
			};
			summaries.set( definition.id, refreshed );
			const selected = select.selectedOptions.item( 0 );
			if ( selected ) {
				selected.textContent = `${ refreshed.name } — ${ refreshed.status } — r${ refreshed.revision }`;
			}
		} catch {
			status.textContent =
				'Saved Column Set could not be opened. The current authored draft was not replaced by an unvalidated response.';
		} finally {
			loading = false;
			load.disabled = ! summaries.has( select.value );
		}
	} );

	void ( async () => {
		try {
			loading = true;
			load.disabled = true;
			const data = await postRoute(
				browseBootstrap.ajaxUrl,
				browseBootstrap.ajaxAction,
				browseBootstrap.routes.list,
				{}
			);
			if (
				! isObject( data ) ||
				! Array.isArray( data.definitions ) ||
				data.definitions.length > MAX_SAVED_VIEWS
			) {
				throw new Error( 'Invalid saved View list.' );
			}
			const parsed = data.definitions.map( parseSummary );
			if ( parsed.some( ( item ) => item === null ) ) {
				throw new Error( 'Malformed saved View summary.' );
			}
			const valid = ( parsed as SavedSummary[] ).sort( ( left, right ) =>
				left.name.localeCompare( right.name )
			);
			summaries = new Map( valid.map( ( item ) => [ item.id, item ] ) );
			select.replaceChildren(
				new Option( 'Select a saved Column Set', '' )
			);
			for ( const item of valid ) {
				select.append(
					new Option(
						`${ item.name } — ${ item.status } — r${ item.revision }`,
						item.id
					)
				);
			}
			status.textContent =
				valid.length === 0
					? 'No saved Column Sets are available. The new authored draft remains usable.'
					: `${ valid.length } saved Column Set${
							valid.length === 1 ? '' : 's'
					  } available to reopen.`;
		} catch {
			select.disabled = true;
			load.disabled = true;
			status.textContent =
				'Saved Column Sets are unavailable. The new authored draft and certified Save path remain usable.';
		} finally {
			loading = false;
		}
	} )();
}

function wireLifecycle(
	root: HTMLElement,
	statusBootstrap: StatusBootstrap,
	session: EditorSession
): void {
	const section = document.createElement( 'section' );
	section.className = 'wpessential-columns__lifecycle';
	section.setAttribute(
		'aria-labelledby',
		'wpessential-columns-lifecycle-title'
	);
	const title = document.createElement( 'h2' );
	title.id = 'wpessential-columns-lifecycle-title';
	title.textContent = 'View lifecycle';
	const description = document.createElement( 'p' );
	description.textContent =
		'Lifecycle changes update only the saved canonical View definition using optimistic revision control. Row data is never mutated.';

	const label = document.createElement( 'label' );
	label.htmlFor = 'wpessential-columns-lifecycle-status';
	label.textContent = 'Lifecycle status';
	const select = document.createElement( 'select' );
	select.id = 'wpessential-columns-lifecycle-status';
	for ( const candidate of STATUSES ) {
		select.append(
			new Option(
				candidate.charAt( 0 ).toUpperCase() + candidate.slice( 1 ),
				candidate
			)
		);
	}
	const apply = document.createElement( 'button' );
	apply.type = 'button';
	apply.className = 'button';
	apply.textContent = 'Apply lifecycle status';
	const lifecycleStatus = document.createElement( 'p' );
	lifecycleStatus.id = 'wpessential-columns-lifecycle-status-message';
	lifecycleStatus.setAttribute( 'role', 'status' );
	lifecycleStatus.setAttribute( 'aria-live', 'polite' );
	section.append( title, description, label, select, apply, lifecycleStatus );
	root.append( section );

	let inFlight = false;
	const sync = (): void => {
		select.value = session.status;
		const saved =
			session.definitionId !== null && session.revision !== null;
		select.disabled = inFlight || ! saved;
		apply.disabled = inFlight || ! saved;
		if ( ! inFlight ) {
			lifecycleStatus.textContent = saved
				? `Current lifecycle: ${ session.status } at revision ${ session.revision }.`
				: 'Save the Column Set before changing lifecycle status.';
		}
	};
	root.addEventListener( 'wpessential:columns-session-changed', sync );
	sync();

	apply.addEventListener( 'click', async () => {
		if ( inFlight ) {
			return;
		}
		if (
			session.definitionId === null ||
			session.revision === null ||
			session.viewKey === null
		) {
			lifecycleStatus.textContent =
				'Lifecycle change requires a saved canonical Column Set. No request was sent.';
			return;
		}
		if ( ! isStatus( select.value ) ) {
			lifecycleStatus.textContent =
				'Selected lifecycle status is invalid.';
			return;
		}

		const requestedStatus = select.value;
		if ( requestedStatus === session.status ) {
			lifecycleStatus.textContent = `View is already ${ session.status }. No lifecycle request was sent.`;
			return;
		}
		const previousId = session.definitionId;
		const previousRevision = session.revision;
		const previousViewKey = session.viewKey;
		const previousStatus = session.status;

		try {
			inFlight = true;
			select.disabled = true;
			apply.disabled = true;
			lifecycleStatus.textContent = `Changing lifecycle from ${ previousStatus } to ${ requestedStatus }…`;
			const data = await postRoute(
				statusBootstrap.ajaxUrl,
				statusBootstrap.ajaxAction,
				statusBootstrap.routes.status,
				{
					id: previousId,
					expected_revision: previousRevision,
					status: requestedStatus,
				}
			);
			if ( ! isObject( data ) ) {
				throw new Error( 'Invalid lifecycle response.' );
			}
			const definition = parseDefinitionHeader( data.definition );
			const returnedViewKey = definition?.payload.view_key;
			if (
				definition === null ||
				definition.id !== previousId ||
				definition.revision !== previousRevision + 1 ||
				definition.status !== requestedStatus ||
				returnedViewKey !== previousViewKey
			) {
				throw new Error( 'Lifecycle response is malformed or stale.' );
			}
			session.revision = definition.revision;
			session.status = definition.status;
			root.dispatchEvent(
				new CustomEvent( 'wpessential:columns-session-changed' )
			);
			lifecycleStatus.textContent = `Lifecycle changed to ${ session.status } at revision ${ session.revision }. Row preview must be requested again.`;
		} catch {
			select.value = previousStatus;
			lifecycleStatus.textContent =
				'Lifecycle status could not be changed. The current saved revision and local lifecycle state were preserved.';
		} finally {
			inFlight = false;
			select.disabled = false;
			apply.disabled = false;
		}
	} );
}

function wirePreview(
	root: HTMLElement,
	readBootstrap: ReadBootstrap,
	session: EditorSession
): void {
	const status = root.querySelector( '.wpessential-columns__status' );
	if ( ! ( status instanceof HTMLElement ) ) {
		return;
	}

	const section = document.createElement( 'section' );
	section.className = 'wpessential-columns__preview';
	section.setAttribute(
		'aria-labelledby',
		'wpessential-columns-preview-title'
	);
	const title = document.createElement( 'h2' );
	title.id = 'wpessential-columns-preview-title';
	title.textContent = 'Row preview';
	const description = document.createElement( 'p' );
	description.textContent =
		'Preview is read-only and executes only the published saved View through the bounded Query path.';

	const controls = document.createElement( 'div' );
	controls.className = 'wpessential-columns__preview-controls';
	const searchLabel = document.createElement( 'label' );
	searchLabel.htmlFor = 'wpessential-columns-preview-search';
	searchLabel.textContent = 'Search preview';
	const search = document.createElement( 'input' );
	search.id = 'wpessential-columns-preview-search';
	search.type = 'search';
	search.maxLength = 200;

	const pageSizeLabel = document.createElement( 'label' );
	pageSizeLabel.htmlFor = 'wpessential-columns-preview-page-size';
	pageSizeLabel.textContent = 'Rows per page';
	const pageSize = document.createElement( 'select' );
	pageSize.id = 'wpessential-columns-preview-page-size';
	for ( const size of [ 10, 20, 50, 100 ] ) {
		pageSize.append(
			new Option( String( size ), String( size ), false, size === 20 )
		);
	}

	const preview = document.createElement( 'button' );
	preview.type = 'button';
	preview.className = 'button';
	preview.textContent = 'Preview rows';
	const previous = document.createElement( 'button' );
	previous.type = 'button';
	previous.className = 'button';
	previous.textContent = 'Previous';
	previous.disabled = true;
	const next = document.createElement( 'button' );
	next.type = 'button';
	next.className = 'button';
	next.textContent = 'Next';
	next.disabled = true;
	controls.append(
		searchLabel,
		search,
		pageSizeLabel,
		pageSize,
		preview,
		previous,
		next
	);

	const previewStatus = document.createElement( 'p' );
	previewStatus.id = 'wpessential-columns-preview-status';
	previewStatus.setAttribute( 'role', 'status' );
	previewStatus.setAttribute( 'aria-live', 'polite' );
	previewStatus.textContent =
		'Save or open a published, enabled Column Set, then choose Preview rows.';

	const table = document.createElement( 'table' );
	table.className = 'widefat fixed striped';
	table.setAttribute( 'aria-describedby', previewStatus.id );
	table.hidden = true;
	const head = document.createElement( 'thead' );
	const body = document.createElement( 'tbody' );
	table.append( head, body );
	section.append( title, description, controls, previewStatus, table );
	root.append( section );

	let offset = 0;
	let lastReturned = 0;
	let inFlight = false;

	const resetPreview = (): void => {
		offset = 0;
		lastReturned = 0;
		previous.disabled = true;
		next.disabled = true;
		head.replaceChildren();
		body.replaceChildren();
		table.hidden = true;
		previewStatus.textContent =
			'Current saved View changed. Choose Preview rows to load the active saved revision.';
	};
	root.addEventListener(
		'wpessential:columns-session-changed',
		resetPreview
	);

	const render = ( result: PreviewResult ): void => {
		head.replaceChildren();
		body.replaceChildren();
		const headerRow = document.createElement( 'tr' );
		for ( const column of result.columns ) {
			const cell = document.createElement( 'th' );
			cell.scope = 'col';
			cell.textContent = column.label;
			headerRow.append( cell );
		}
		head.append( headerRow );
		for ( const row of result.rows ) {
			const tableRow = document.createElement( 'tr' );
			for ( const column of result.columns ) {
				const cell = document.createElement( 'td' );
				cell.textContent = row[ column.key ] ?? '';
				tableRow.append( cell );
			}
			body.append( tableRow );
		}
		table.hidden = false;
	};

	const execute = async ( requestedOffset: number ): Promise< void > => {
		if ( inFlight ) {
			return;
		}
		if (
			session.definitionId === null ||
			session.revision === null ||
			session.status !== 'published' ||
			! session.viewEnabled
		) {
			previewStatus.textContent =
				'Preview requires a published, enabled saved Column Set. No Query request was sent.';
			return;
		}

		const size = Number.parseInt( pageSize.value, 10 );
		if ( ! [ 10, 20, 50, 100 ].includes( size ) ) {
			previewStatus.textContent = 'Preview page size is invalid.';
			return;
		}
		const boundedOffset = Math.max( 0, Math.min( 10000, requestedOffset ) );
		const request: JsonObject = {
			view_id: session.definitionId,
			page_size: size,
			offset: boundedOffset,
		};
		const term = search.value.trim();
		if ( term !== '' ) {
			request.search = term;
		}

		try {
			inFlight = true;
			preview.disabled = true;
			previous.disabled = true;
			next.disabled = true;
			previewStatus.textContent = 'Loading bounded row preview…';
			const data = await postRoute(
				readBootstrap.ajaxUrl,
				readBootstrap.ajaxAction,
				readBootstrap.routes.read,
				request
			);
			const result = parsePreviewResult(
				data,
				session.definitionId,
				session.revision,
				size
			);
			if ( result === null ) {
				throw new Error( 'Malformed or stale preview response.' );
			}
			render( result );
			offset = boundedOffset;
			lastReturned = result.returned;
			previous.disabled = offset === 0;
			next.disabled =
				lastReturned < size || offset >= 10000 || offset + size > 10000;
			previewStatus.textContent = `${ result.returned } row${
				result.returned === 1 ? '' : 's'
			} previewed from revision ${
				result.viewRevision
			}. Row data was not mutated.`;
		} catch {
			previewStatus.textContent =
				'Row preview is unavailable or stale. No unvalidated response replaced the current preview.';
		} finally {
			inFlight = false;
			preview.disabled = false;
		}
	};

	preview.addEventListener( 'click', () => {
		void execute( 0 );
	} );
	search.addEventListener( 'keydown', ( event ) => {
		if ( event.key === 'Enter' ) {
			event.preventDefault();
			void execute( 0 );
		}
	} );
	previous.addEventListener( 'click', () => {
		const size = Number.parseInt( pageSize.value, 10 );
		void execute( Math.max( 0, offset - size ) );
	} );
	next.addEventListener( 'click', () => {
		const size = Number.parseInt( pageSize.value, 10 );
		if ( lastReturned === size && offset < 10000 ) {
			void execute( Math.min( 10000, offset + size ) );
		}
	} );
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
		const session: EditorSession = {
			definitionId: null,
			revision: null,
			viewKey: null,
			status: 'draft',
			viewEnabled: true,
			assignment: undefined,
			layout: undefined,
			visibility: undefined,
			columnIdentities: new Map(),
		};
		const saveBootstrap = parseSaveBootstrap( raw );
		if ( saveBootstrap !== null ) {
			wireSave( root, bootstrap, saveBootstrap, session );
		}
		const browseBootstrap = parseBrowseBootstrap( raw );
		if ( browseBootstrap !== null ) {
			wireBrowse( root, bootstrap, browseBootstrap, session );
		}
		const statusBootstrap = parseStatusBootstrap( raw );
		if ( statusBootstrap !== null ) {
			wireLifecycle( root, statusBootstrap, session );
		}
		const readBootstrap = parseReadBootstrap( raw );
		if ( readBootstrap !== null ) {
			wirePreview( root, readBootstrap, session );
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
