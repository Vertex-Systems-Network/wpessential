import './admin.scss';

type BootstrapRecord = Record< string, unknown >;
type CptPayload = Record< string, unknown >;

type CptDefinition = {
	id: string;
	status: string;
	revision: number;
	payload: CptPayload;
};

type CptRoute = {
	type: string;
	nonce: string;
};

type CptBootstrap = {
	surface: 'custom-post-types';
	ajaxUrl: string;
	ajaxAction: string;
	routes: {
		list: CptRoute;
		save: CptRoute;
		status: CptRoute;
	};
	definitions: CptDefinition[];
};

type AjaxEnvelope = {
	success: boolean;
	data?: unknown;
	error?: {
		code?: string;
		message?: string;
	};
};

function isBootstrapRecord( value: unknown ): value is BootstrapRecord {
	return (
		typeof value === 'object' && value !== null && ! Array.isArray( value )
	);
}

function isStringValue( value: unknown ): value is string {
	return typeof value === 'string';
}

function isCptDefinition( value: unknown ): value is CptDefinition {
	if ( ! isBootstrapRecord( value ) ) {
		return false;
	}

	return (
		typeof value.id === 'string' &&
		typeof value.status === 'string' &&
		typeof value.revision === 'number' &&
		Number.isInteger( value.revision ) &&
		isBootstrapRecord( value.payload )
	);
}

function isCptRoute( value: unknown ): value is CptRoute {
	return (
		isBootstrapRecord( value ) &&
		typeof value.type === 'string' &&
		typeof value.nonce === 'string'
	);
}

function parseCptBootstrap( value: unknown ): CptBootstrap | null {
	if (
		! isBootstrapRecord( value ) ||
		value.surface !== 'custom-post-types'
	) {
		return null;
	}
	if (
		typeof value.ajaxUrl !== 'string' ||
		typeof value.ajaxAction !== 'string' ||
		! isBootstrapRecord( value.routes ) ||
		! isCptRoute( value.routes.list ) ||
		! isCptRoute( value.routes.save ) ||
		! isCptRoute( value.routes.status ) ||
		! Array.isArray( value.definitions ) ||
		! value.definitions.every( isCptDefinition )
	) {
		return null;
	}

	return {
		surface: 'custom-post-types',
		ajaxUrl: value.ajaxUrl,
		ajaxAction: value.ajaxAction,
		routes: {
			list: value.routes.list,
			save: value.routes.save,
			status: value.routes.status,
		},
		definitions: value.definitions,
	};
}

function bootRuntimeObservatory(
	root: HTMLElement,
	payload: BootstrapRecord
): void {
	root.dataset.wpessentialEnhanced = 'ready';
	window.dispatchEvent(
		new CustomEvent( 'wpessential:admin-ready', {
			detail: {
				surface:
					root.dataset.wpessentialSurface ?? 'runtime-observatory',
				payload,
			},
		} )
	);
}

function textInput( id: string ): HTMLInputElement | null {
	const element = document.getElementById( id );
	return element instanceof HTMLInputElement ? element : null;
}

function selectInput( id: string ): HTMLSelectElement | null {
	const element = document.getElementById( id );
	return element instanceof HTMLSelectElement ? element : null;
}

function buttonInput( id: string ): HTMLButtonElement | null {
	const element = document.getElementById( id );
	return element instanceof HTMLButtonElement ? element : null;
}

function cptFieldValue( field: string ): string {
	const input = document.querySelector< HTMLInputElement >(
		`[data-wpessential-cpt-field="${ field }"]`
	);
	return input?.value.trim() ?? '';
}

function setCptFieldValue( field: string, value: unknown ): void {
	const input = document.querySelector< HTMLInputElement >(
		`[data-wpessential-cpt-field="${ field }"]`
	);
	if ( input ) {
		input.value = typeof value === 'string' ? value : '';
	}
}

function boolInput( id: string ): boolean {
	return textInput( id )?.checked ?? false;
}

function setBoolInput( id: string, value: unknown, fallback = false ): void {
	const input = textInput( id );
	if ( input ) {
		input.checked = typeof value === 'boolean' ? value : fallback;
	}
}

function supportInputs(): HTMLInputElement[] {
	return Array.from(
		document.querySelectorAll< HTMLInputElement >(
			'[data-wpessential-cpt-support]'
		)
	);
}

function visibleSupportKeys(): string[] {
	return supportInputs()
		.map( ( input ) => input.dataset.wpessentialCptSupport ?? '' )
		.filter( ( value ) => value !== '' );
}

function supportValues(): string[] {
	return supportInputs()
		.filter( ( input ) => input.checked )
		.map( ( input ) => input.dataset.wpessentialCptSupport ?? '' )
		.filter( ( value ) => value !== '' );
}

function setSupportValues( value: unknown ): void {
	const supports = Array.isArray( value )
		? value.filter( ( item ): item is string => typeof item === 'string' )
		: [ 'title', 'editor' ];
	for ( const input of supportInputs() ) {
		input.checked = supports.includes(
			input.dataset.wpessentialCptSupport ?? ''
		);
	}
}

function setNotice( message: string, error = false ): void {
	const notice = document.getElementById( 'wpessential-cpt-notice' );
	if ( ! ( notice instanceof HTMLElement ) ) {
		return;
	}
	const paragraph = notice.querySelector( 'p' );
	if ( paragraph ) {
		paragraph.textContent = message;
	}
	notice.hidden = message === '';
	notice.classList.toggle( 'notice-error', error );
	notice.classList.toggle( 'notice-success', ! error && message !== '' );
}

async function postCptRoute(
	bootstrap: CptBootstrap,
	route: CptRoute,
	payload: CptPayload
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
	const value: unknown = await response.json();
	if ( ! isBootstrapRecord( value ) || typeof value.success !== 'boolean' ) {
		throw new Error( 'WPEssential returned an invalid AJAX response.' );
	}
	const envelope = value as AjaxEnvelope;
	if ( ! response.ok || ! envelope.success ) {
		throw new Error(
			envelope.error?.message ??
				'The requested change could not be completed.'
		);
	}
	return envelope.data;
}

function createCell( text: string ): HTMLTableCellElement {
	const cell = document.createElement( 'td' );
	cell.textContent = text;
	return cell;
}

function actionButton(
	label: string,
	attributes: Record< string, string >
): HTMLButtonElement {
	const button = document.createElement( 'button' );
	button.type = 'button';
	button.className = 'button button-small';
	button.textContent = label;
	for ( const [ key, value ] of Object.entries( attributes ) ) {
		button.dataset[ key ] = value;
	}
	return button;
}

function renderCptRows( definitions: CptDefinition[] ): void {
	const rows = document.getElementById( 'wpessential-cpt-rows' );
	if ( ! ( rows instanceof HTMLTableSectionElement ) ) {
		return;
	}
	rows.replaceChildren();

	if ( definitions.length === 0 ) {
		const row = document.createElement( 'tr' );
		row.dataset.wpessentialCptEmpty = '';
		const cell = createCell(
			'No custom post types have been created yet.'
		);
		cell.colSpan = 5;
		row.append( cell );
		rows.append( row );
		return;
	}

	for ( const definition of definitions ) {
		const row = document.createElement( 'tr' );
		row.dataset.wpessentialCptRow = definition.id;
		const name =
			typeof definition.payload.name === 'string'
				? definition.payload.name
				: '';
		const key =
			typeof definition.payload.post_type_key === 'string'
				? definition.payload.post_type_key
				: '';

		row.append(
			createCell( name ),
			createCell( key ),
			createCell(
				definition.status.charAt( 0 ).toUpperCase() +
					definition.status.slice( 1 )
			),
			createCell( String( definition.revision ) )
		);

		const actions = document.createElement( 'td' );
		actions.append(
			actionButton( 'Edit', { wpessentialCptEdit: definition.id } ),
			document.createTextNode( ' ' )
		);
		if ( definition.status === 'published' ) {
			actions.append(
				actionButton( 'Disable', {
					wpessentialCptStatus: 'disabled',
					wpessentialCptId: definition.id,
				} ),
				document.createTextNode( ' ' )
			);
		} else {
			actions.append(
				actionButton( 'Publish', {
					wpessentialCptStatus: 'published',
					wpessentialCptId: definition.id,
				} ),
				document.createTextNode( ' ' )
			);
		}
		if ( definition.status !== 'archived' ) {
			actions.append(
				actionButton( 'Archive', {
					wpessentialCptStatus: 'archived',
					wpessentialCptId: definition.id,
				} )
			);
		}
		row.append( actions );
		rows.append( row );
	}
}

function resetCptForm(): void {
	const form = document.getElementById( 'wpessential-cpt-form' );
	if ( form instanceof HTMLFormElement ) {
		form.reset();
	}
	const id = textInput( 'wpessential-cpt-id' );
	const revision = textInput( 'wpessential-cpt-revision' );
	const key = textInput( 'wpessential-cpt-key' );
	if ( id ) {
		id.value = '';
	}
	if ( revision ) {
		revision.value = '';
	}
	if ( key ) {
		key.readOnly = false;
	}
	setSupportValues( [ 'title', 'editor' ] );
	const status = selectInput( 'wpessential-cpt-status' );
	if ( status ) {
		status.value = 'draft';
	}
	const title = document.getElementById( 'wpessential-cpt-editor-title' );
	if ( title ) {
		title.textContent = 'Add custom post type';
	}
	const cancel = buttonInput( 'wpessential-cpt-cancel' );
	if ( cancel ) {
		cancel.hidden = true;
	}
}

function editCptDefinition( definition: CptDefinition ): void {
	const id = textInput( 'wpessential-cpt-id' );
	const revision = textInput( 'wpessential-cpt-revision' );
	const key = textInput( 'wpessential-cpt-key' );
	if ( id ) {
		id.value = definition.id;
	}
	if ( revision ) {
		revision.value = String( definition.revision );
	}
	setCptFieldValue( 'post_type_key', definition.payload.post_type_key );
	setCptFieldValue( 'name', definition.payload.name );
	setCptFieldValue( 'singular_name', definition.payload.singular_name );
	setCptFieldValue( 'description', definition.payload.description );
	setBoolInput( 'wpessential-cpt-public', definition.payload.public, true );
	setBoolInput(
		'wpessential-cpt-rest',
		definition.payload.show_in_rest,
		true
	);
	setBoolInput(
		'wpessential-cpt-hierarchical',
		definition.payload.hierarchical
	);
	const archiveValue = definition.payload.has_archive;
	setBoolInput(
		'wpessential-cpt-archive',
		typeof archiveValue === 'string' ? true : archiveValue
	);
	setSupportValues( definition.payload.supports );
	const status = selectInput( 'wpessential-cpt-status' );
	if ( status ) {
		status.value = definition.status;
	}
	if ( key ) {
		key.readOnly = true;
	}
	const title = document.getElementById( 'wpessential-cpt-editor-title' );
	if ( title ) {
		title.textContent = 'Edit custom post type';
	}
	const cancel = buttonInput( 'wpessential-cpt-cancel' );
	if ( cancel ) {
		cancel.hidden = false;
	}
	textInput( 'wpessential-cpt-name' )?.focus();
}

function bootCptAdmin( root: HTMLElement, bootstrap: CptBootstrap ): void {
	let definitions = [ ...bootstrap.definitions ];
	let busy = false;

	const refresh = async (): Promise< void > => {
		const data = await postCptRoute( bootstrap, bootstrap.routes.list, {} );
		if (
			! isBootstrapRecord( data ) ||
			! Array.isArray( data.definitions )
		) {
			throw new Error(
				'The Custom Post Type list response was invalid.'
			);
		}
		const next = data.definitions.filter( isCptDefinition );
		if ( next.length !== data.definitions.length ) {
			throw new Error(
				'The Custom Post Type list contained invalid records.'
			);
		}
		definitions = next;
		renderCptRows( definitions );
	};

	const run = async ( operation: () => Promise< void > ): Promise< void > => {
		if ( busy ) {
			return;
		}
		busy = true;
		root.setAttribute( 'aria-busy', 'true' );
		setNotice( '' );
		try {
			await operation();
		} catch ( error ) {
			setNotice(
				error instanceof Error
					? error.message
					: 'The requested change could not be completed.',
				true
			);
		} finally {
			busy = false;
			root.removeAttribute( 'aria-busy' );
		}
	};

	const form = document.getElementById( 'wpessential-cpt-form' );
	if ( form instanceof HTMLFormElement ) {
		form.addEventListener( 'submit', ( event ) => {
			event.preventDefault();
			void run( async () => {
				const id = textInput( 'wpessential-cpt-id' )?.value ?? '';
				const revision = Number(
					textInput( 'wpessential-cpt-revision' )?.value ?? 0
				);
				const existing = definitions.find(
					( definition ) => definition.id === id
				);
				const selectedSupports = supportValues();
				const editableSupports = visibleSupportKeys();
				const existingSupports = Array.isArray(
					existing?.payload.supports
				)
					? existing.payload.supports.filter( isStringValue )
					: [];
				const preservedSupports = existingSupports.filter(
					( support ) => ! editableSupports.includes( support )
				);
				const archiveEnabled = boolInput( 'wpessential-cpt-archive' );
				const existingArchive = existing?.payload.has_archive;
				const archiveValue =
					archiveEnabled && typeof existingArchive === 'string'
						? existingArchive
						: archiveEnabled;
				const payload: CptPayload = {
					...( existing?.payload ?? {} ),
					post_type_key: cptFieldValue( 'post_type_key' ),
					name: cptFieldValue( 'name' ),
					singular_name: cptFieldValue( 'singular_name' ),
					description: cptFieldValue( 'description' ),
					public: boolInput( 'wpessential-cpt-public' ),
					show_in_rest: boolInput( 'wpessential-cpt-rest' ),
					hierarchical: boolInput( 'wpessential-cpt-hierarchical' ),
					has_archive: archiveValue,
					supports: [ ...preservedSupports, ...selectedSupports ],
				};
				const request: CptPayload = {
					payload,
					status:
						selectInput( 'wpessential-cpt-status' )?.value ??
						'draft',
				};
				if ( id !== '' ) {
					request.id = id;
					request.expected_revision = revision;
				}

				await postCptRoute( bootstrap, bootstrap.routes.save, request );
				await refresh();
				resetCptForm();
				setNotice(
					id === ''
						? 'Custom post type created.'
						: 'Custom post type updated.'
				);
			} );
		} );
	}

	buttonInput( 'wpessential-cpt-cancel' )?.addEventListener( 'click', () => {
		resetCptForm();
		setNotice( '' );
	} );
	buttonInput( 'wpessential-cpt-refresh' )?.addEventListener( 'click', () => {
		void run( async () => {
			await refresh();
			setNotice( 'Custom post types refreshed.' );
		} );
	} );

	root.addEventListener( 'click', ( event ) => {
		const target = event.target;
		if ( ! ( target instanceof HTMLButtonElement ) ) {
			return;
		}

		const editId = target.dataset.wpessentialCptEdit;
		if ( editId ) {
			const definition = definitions.find(
				( candidate ) => candidate.id === editId
			);
			if ( definition ) {
				editCptDefinition( definition );
			}
			return;
		}

		const status = target.dataset.wpessentialCptStatus;
		const id = target.dataset.wpessentialCptId;
		if ( ! status || ! id ) {
			return;
		}
		const definition = definitions.find(
			( candidate ) => candidate.id === id
		);
		if ( ! definition ) {
			setNotice(
				'The selected custom post type is no longer available.',
				true
			);
			return;
		}

		void run( async () => {
			await postCptRoute( bootstrap, bootstrap.routes.status, {
				id,
				expected_revision: definition.revision,
				status,
			} );
			await refresh();
			if ( textInput( 'wpessential-cpt-id' )?.value === id ) {
				resetCptForm();
			}
			setNotice( `Custom post type status changed to ${ status }.` );
		} );
	} );

	renderCptRows( definitions );
	root.dataset.wpessentialEnhanced = 'ready';
	window.dispatchEvent(
		new CustomEvent( 'wpessential:admin-ready', {
			detail: { surface: 'custom-post-types', payload: bootstrap },
		} )
	);
}

function bootAdmin(): void {
	const root = document.getElementById( 'wpessential-admin-root' );
	const bootstrap = document.getElementById( 'wpessential-admin-bootstrap' );
	if (
		! ( root instanceof HTMLElement ) ||
		! ( bootstrap instanceof HTMLScriptElement )
	) {
		return;
	}

	try {
		const payload: unknown = JSON.parse( bootstrap.textContent ?? '{}' );
		if ( ! isBootstrapRecord( payload ) ) {
			root.dataset.wpessentialEnhanced = 'invalid-bootstrap';
			return;
		}

		if ( root.dataset.wpessentialSurface === 'custom-post-types' ) {
			const cptBootstrap = parseCptBootstrap( payload );
			if ( ! cptBootstrap ) {
				root.dataset.wpessentialEnhanced = 'invalid-bootstrap';
				return;
			}
			bootCptAdmin( root, cptBootstrap );
			return;
		}

		bootRuntimeObservatory( root, payload );
	} catch {
		root.dataset.wpessentialEnhanced = 'invalid-bootstrap';
	}
}

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', bootAdmin, { once: true } );
} else {
	bootAdmin();
}
