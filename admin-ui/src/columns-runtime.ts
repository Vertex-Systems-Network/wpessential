import {
	mountAdminColumnsScaffold,
	parseAdminColumnsBootstrap,
} from './columns';

type JsonObject = Record< string, unknown >;
type SaveRoute = { type: string; nonce: string };
type SaveBootstrap = {
	ajaxUrl: string;
	ajaxAction: string;
	routes: { save: SaveRoute };
};
type SavedDefinition = {
	id: string;
	revision: number;
	payload: JsonObject;
};
type AjaxEnvelope = {
	success: boolean;
	data?: unknown;
};

const UUID_PATTERN =
	/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/;

function isObject( value: unknown ): value is JsonObject {
	return (
		typeof value === 'object' && value !== null && ! Array.isArray( value )
	);
}

function isSaveRoute( value: unknown ): value is SaveRoute {
	return (
		isObject( value ) &&
		typeof value.type === 'string' &&
		value.type === 'admin-columns.save.view' &&
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
		! isSaveRoute( value.routes.save )
	) {
		return null;
	}
	return {
		ajaxUrl: value.ajaxUrl,
		ajaxAction: value.ajaxAction,
		routes: { save: value.routes.save },
	};
}

function parseDefinition( value: unknown ): SavedDefinition | null {
	if (
		! isObject( value ) ||
		typeof value.id !== 'string' ||
		! UUID_PATTERN.test( value.id ) ||
		typeof value.revision !== 'number' ||
		! Number.isInteger( value.revision ) ||
		value.revision < 1 ||
		! isObject( value.payload )
	) {
		return null;
	}
	return {
		id: value.id,
		revision: value.revision,
		payload: value.payload,
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

async function postSave(
	bootstrap: SaveBootstrap,
	payload: JsonObject
): Promise< unknown > {
	const body = new URLSearchParams();
	body.set( 'action', bootstrap.ajaxAction );
	body.set( 'type', bootstrap.routes.save.type );
	body.set( 'nonce', bootstrap.routes.save.nonce );
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
		typeof decoded.success !== 'boolean' ||
		decoded.success !== true
	) {
		throw new Error( 'Admin Columns save request failed.' );
	}
	return ( decoded as AjaxEnvelope ).data;
}

function failBootstrap( root: HTMLElement ): void {
	root.dataset.wpessentialEnhanced = 'invalid-bootstrap';
	root.textContent =
		'Admin Columns editor is unavailable because its bootstrap contract is invalid.';
}

function wireSave(
	root: HTMLElement,
	bootstrap: NonNullable< ReturnType< typeof parseAdminColumnsBootstrap > >,
	saveBootstrap: SaveBootstrap
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

	const columnUuids = new Map< number, string >();
	let definitionId: string | null = null;
	let revision: number | null = null;
	let viewKey: string | null = null;
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
			if (
				! ( labelInput instanceof HTMLInputElement ) ||
				! ( sourceSelect instanceof HTMLSelectElement ) ||
				! ( formatSelect instanceof HTMLSelectElement ) ||
				! ( enabled instanceof HTMLInputElement ) ||
				labelInput.value.trim() === ''
			) {
				throw new Error(
					'Every Column requires a valid authored state.'
				);
			}
			const match = labelInput.id.match( /-(\d+)$/ );
			const draftId = match ? Number.parseInt( match[ 1 ]!, 10 ) : 0;
			const source = bootstrap.sources.find(
				( candidate ) => candidate.reference === sourceSelect.value
			);
			if (
				! Number.isInteger( draftId ) ||
				draftId < 1 ||
				! source ||
				! source.formats.includes( formatSelect.value )
			) {
				throw new Error( 'Column source or format is unavailable.' );
			}
			columns.push( {
				uuid: draftUuid( draftId ),
				key: `column_${ draftId }`,
				label: labelInput.value.trim(),
				enabled: enabled.checked,
				source: {
					owner: source.owner,
					reference: source.reference,
				},
				format: formatSelect.value,
				primary: false,
			} );
		}
		if ( columns.length === 0 ) {
			throw new Error( 'At least one Column is required.' );
		}
		viewKey ??= newViewKey();
		return {
			view_key: viewKey,
			name: name.value.trim(),
			enabled: true,
			target: { type: target.type, key: target.key },
			columns,
		};
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
				status: 'draft',
			};
			if ( definitionId !== null && revision !== null ) {
				request.id = definitionId;
				request.expected_revision = revision;
			}

			inFlight = true;
			save.disabled = true;
			save.textContent = 'Saving…';
			status.textContent = 'Saving revisioned Column Set definition…';
			const data = await postSave( saveBootstrap, request );
			if ( ! isObject( data ) ) {
				throw new Error( 'Invalid save response.' );
			}
			const definition = parseDefinition( data.definition );
			const returnedKey = definition?.payload.view_key;
			if (
				definition === null ||
				typeof returnedKey !== 'string' ||
				returnedKey !== viewKey ||
				( definitionId !== null && definition.id !== definitionId )
			) {
				throw new Error( 'Invalid revisioned definition response.' );
			}
			definitionId = definition.id;
			revision = definition.revision;
			status.textContent = `Column Set saved as revision ${ revision }.`;
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
		const saveBootstrap = parseSaveBootstrap( raw );
		if ( bootstrap === null || saveBootstrap === null ) {
			failBootstrap( root );
			return;
		}
		mountAdminColumnsScaffold( root, bootstrap );
		wireSave( root, bootstrap, saveBootstrap );
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
