import './admin.scss';

type RecordValue = Record< string, unknown >;
type TaxonomyPayload = RecordValue;

type TaxonomyDefinition = {
	id: string;
	status: string;
	revision: number;
	payload: TaxonomyPayload;
};

type ObjectTypeOption = {
	key: string;
	label: string;
	source: string;
	status: string;
	runtime_registered: boolean;
};

type Route = { type: string; nonce: string };
type ValidationIssue = {
	id: string;
	severity: string;
	field: string;
	message: string;
};
type ValidationReport = {
	valid: boolean;
	issues: ValidationIssue[];
	candidate: { taxonomy_key: string | null };
};
type Bootstrap = {
	surface: 'taxonomies';
	ajaxUrl: string;
	ajaxAction: string;
	routes: { list: Route; validate: Route; save: Route; status: Route };
	definitions: TaxonomyDefinition[];
	objectTypes: ObjectTypeOption[];
};
type EditorRequest = {
	id: string;
	revision: number;
	status: string;
	payload: TaxonomyPayload;
};
type AjaxEnvelope = {
	success: boolean;
	data?: unknown;
	error?: { code?: string; message?: string };
};

function isRecord( value: unknown ): value is RecordValue {
	return (
		typeof value === 'object' && value !== null && ! Array.isArray( value )
	);
}

function isDefinition( value: unknown ): value is TaxonomyDefinition {
	return (
		isRecord( value ) &&
		typeof value.id === 'string' &&
		typeof value.status === 'string' &&
		typeof value.revision === 'number' &&
		Number.isInteger( value.revision ) &&
		isRecord( value.payload )
	);
}

function isObjectTypeOption( value: unknown ): value is ObjectTypeOption {
	return (
		isRecord( value ) &&
		typeof value.key === 'string' &&
		typeof value.label === 'string' &&
		typeof value.source === 'string' &&
		typeof value.status === 'string' &&
		typeof value.runtime_registered === 'boolean'
	);
}

function isRoute( value: unknown ): value is Route {
	return (
		isRecord( value ) &&
		typeof value.type === 'string' &&
		typeof value.nonce === 'string'
	);
}

function parseBootstrap( value: unknown ): Bootstrap | null {
	if (
		! isRecord( value ) ||
		value.surface !== 'taxonomies' ||
		typeof value.ajaxUrl !== 'string' ||
		typeof value.ajaxAction !== 'string' ||
		! isRecord( value.routes ) ||
		! isRoute( value.routes.list ) ||
		! isRoute( value.routes.validate ) ||
		! isRoute( value.routes.save ) ||
		! isRoute( value.routes.status ) ||
		! Array.isArray( value.definitions ) ||
		! value.definitions.every( isDefinition ) ||
		! Array.isArray( value.objectTypes ) ||
		! value.objectTypes.every( isObjectTypeOption )
	) {
		return null;
	}

	return {
		surface: 'taxonomies',
		ajaxUrl: value.ajaxUrl,
		ajaxAction: value.ajaxAction,
		routes: {
			list: value.routes.list,
			validate: value.routes.validate,
			save: value.routes.save,
			status: value.routes.status,
		},
		definitions: value.definitions,
		objectTypes: value.objectTypes,
	};
}

function isIssue( value: unknown ): value is ValidationIssue {
	return (
		isRecord( value ) &&
		typeof value.id === 'string' &&
		typeof value.severity === 'string' &&
		typeof value.field === 'string' &&
		typeof value.message === 'string'
	);
}

function parseReport( value: unknown ): ValidationReport | null {
	if (
		! isRecord( value ) ||
		typeof value.valid !== 'boolean' ||
		! Array.isArray( value.issues ) ||
		! value.issues.every( isIssue ) ||
		! isRecord( value.candidate )
	) {
		return null;
	}

	const key = value.candidate.taxonomy_key;
	if ( key !== null && typeof key !== 'string' ) {
		return null;
	}

	return {
		valid: value.valid,
		issues: value.issues,
		candidate: { taxonomy_key: key },
	};
}

function parseMutationDefinition( value: unknown ): TaxonomyDefinition | null {
	if ( ! isRecord( value ) || ! isDefinition( value.definition ) ) {
		return null;
	}

	return value.definition;
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

function fieldValue( field: string ): string {
	const input = document.querySelector< HTMLInputElement >(
		`[data-wpessential-taxonomy-field="${ field }"]`
	);
	return input?.value.trim() ?? '';
}

function setFieldValue( field: string, value: unknown ): void {
	const input = document.querySelector< HTMLInputElement >(
		`[data-wpessential-taxonomy-field="${ field }"]`
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

function objectTypeInputs(): HTMLInputElement[] {
	return Array.from(
		document.querySelectorAll< HTMLInputElement >(
			'[data-wpessential-taxonomy-object-type]'
		)
	);
}

function splitObjectTypes( value: string ): string[] {
	return value
		.split( ',' )
		.map( ( item ) => item.trim() )
		.filter( ( item ) => item !== '' );
}

function objectTypesFromInput(): string[] {
	const selected = objectTypeInputs()
		.filter( ( input ) => input.checked )
		.map( ( input ) => input.value.trim() )
		.filter( ( value ) => value !== '' );
	const additional = splitObjectTypes(
		textInput( 'wpessential-taxonomy-object-types-extra' )?.value ?? ''
	);

	return Array.from( new Set( [ ...selected, ...additional ] ) );
}

function setObjectTypes( value: unknown ): void {
	const values = Array.isArray( value )
		? value.filter( ( item ): item is string => typeof item === 'string' )
		: [];
	const remaining = new Set( values );

	for ( const input of objectTypeInputs() ) {
		input.checked = remaining.has( input.value );
		remaining.delete( input.value );
	}

	const extra = textInput( 'wpessential-taxonomy-object-types-extra' );
	if ( extra ) {
		extra.value = Array.from( remaining ).join( ', ' );
	}
}

function setNotice( message: string, error = false ): void {
	const notice = document.getElementById( 'wpessential-taxonomy-notice' );
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

function clearValidation(): void {
	const report = document.getElementById( 'wpessential-taxonomy-validation' );
	if ( report instanceof HTMLElement ) {
		report.hidden = true;
		report.classList.remove(
			'notice',
			'notice-error',
			'notice-warning',
			'notice-success'
		);
	}
}

function renderValidation( report: ValidationReport ): void {
	const container = document.getElementById(
		'wpessential-taxonomy-validation'
	);
	if ( ! ( container instanceof HTMLElement ) ) {
		return;
	}

	const summary = container.querySelector(
		'[data-wpessential-taxonomy-validation-summary]'
	);
	const issues = container.querySelector(
		'[data-wpessential-taxonomy-validation-issues]'
	);
	if (
		! ( summary instanceof HTMLElement ) ||
		! ( issues instanceof HTMLElement )
	) {
		return;
	}

	issues.replaceChildren();
	for ( const issue of report.issues ) {
		const item = document.createElement( 'li' );
		item.textContent = `${ issue.severity.replaceAll( '_', ' ' ) }: ${
			issue.message
		}`;
		item.dataset.wpessentialTaxonomyValidationSeverity = issue.severity;
		issues.append( item );
	}

	const warningCount = report.issues.filter(
		( issue ) => issue.severity !== 'blocked'
	).length;
	if ( report.valid ) {
		summary.textContent =
			warningCount === 0
				? 'Validation passed. No blocking issues found.'
				: `Validation passed with ${ warningCount } warning or informational item(s).`;
	} else {
		const blockedCount = report.issues.filter(
			( issue ) => issue.severity === 'blocked'
		).length;
		summary.textContent = `Validation blocked by ${ blockedCount } issue(s). Resolve them before saving.`;
	}

	container.hidden = false;
	container.classList.add( 'notice' );
	container.classList.toggle( 'notice-error', ! report.valid );
	container.classList.toggle(
		'notice-warning',
		report.valid && report.issues.length > 0
	);
	container.classList.toggle(
		'notice-success',
		report.valid && report.issues.length === 0
	);
}

async function postRoute(
	bootstrap: Bootstrap,
	route: Route,
	payload: TaxonomyPayload
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
	if ( ! isRecord( value ) || typeof value.success !== 'boolean' ) {
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

function cell( text: string ): HTMLTableCellElement {
	const element = document.createElement( 'td' );
	element.textContent = text;
	return element;
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

function sortedDefinitions(
	definitions: TaxonomyDefinition[]
): TaxonomyDefinition[] {
	return [ ...definitions ].sort( ( left, right ) => {
		const leftKey =
			typeof left.payload.taxonomy_key === 'string'
				? left.payload.taxonomy_key
				: left.id;
		const rightKey =
			typeof right.payload.taxonomy_key === 'string'
				? right.payload.taxonomy_key
				: right.id;
		return leftKey.localeCompare( rightKey );
	} );
}

function upsertDefinition(
	definitions: TaxonomyDefinition[],
	definition: TaxonomyDefinition
): TaxonomyDefinition[] {
	return sortedDefinitions( [
		...definitions.filter(
			( candidate ) => candidate.id !== definition.id
		),
		definition,
	] );
}

function renderRows( definitions: TaxonomyDefinition[] ): void {
	const rows = document.getElementById( 'wpessential-taxonomy-rows' );
	if ( ! ( rows instanceof HTMLTableSectionElement ) ) {
		return;
	}
	rows.replaceChildren();

	if ( definitions.length === 0 ) {
		const row = document.createElement( 'tr' );
		row.dataset.wpessentialTaxonomyEmpty = '';
		const empty = cell( 'No taxonomies have been created yet.' );
		empty.colSpan = 6;
		row.append( empty );
		rows.append( row );
		return;
	}

	for ( const definition of definitions ) {
		const row = document.createElement( 'tr' );
		row.dataset.wpessentialTaxonomyRow = definition.id;
		const name =
			typeof definition.payload.name === 'string'
				? definition.payload.name
				: '';
		const key =
			typeof definition.payload.taxonomy_key === 'string'
				? definition.payload.taxonomy_key
				: '';
		const objectTypes = Array.isArray( definition.payload.object_types )
			? definition.payload.object_types
					.filter(
						( value ): value is string => typeof value === 'string'
					)
					.join( ', ' )
			: '';

		row.append(
			cell( name ),
			cell( key ),
			cell( objectTypes ),
			cell(
				definition.status.charAt( 0 ).toUpperCase() +
					definition.status.slice( 1 )
			),
			cell( String( definition.revision ) )
		);

		const actions = document.createElement( 'td' );
		actions.append(
			actionButton( 'Edit', { wpessentialTaxonomyEdit: definition.id } ),
			document.createTextNode( ' ' )
		);
		if ( definition.status === 'published' ) {
			actions.append(
				actionButton( 'Disable', {
					wpessentialTaxonomyStatus: 'disabled',
					wpessentialTaxonomyId: definition.id,
				} ),
				document.createTextNode( ' ' )
			);
		} else {
			actions.append(
				actionButton( 'Publish', {
					wpessentialTaxonomyStatus: 'published',
					wpessentialTaxonomyId: definition.id,
				} ),
				document.createTextNode( ' ' )
			);
		}
		if ( definition.status !== 'archived' ) {
			actions.append(
				actionButton( 'Archive', {
					wpessentialTaxonomyStatus: 'archived',
					wpessentialTaxonomyId: definition.id,
				} )
			);
		}
		row.append( actions );
		rows.append( row );
	}
}

function resetForm(): void {
	const form = document.getElementById( 'wpessential-taxonomy-form' );
	if ( form instanceof HTMLFormElement ) {
		form.reset();
	}
	const id = textInput( 'wpessential-taxonomy-id' );
	const revision = textInput( 'wpessential-taxonomy-revision' );
	const key = textInput( 'wpessential-taxonomy-key' );
	if ( id ) {
		id.value = '';
	}
	if ( revision ) {
		revision.value = '';
	}
	if ( key ) {
		key.readOnly = false;
	}
	setObjectTypes( [ 'post' ] );
	const status = selectInput( 'wpessential-taxonomy-status' );
	if ( status ) {
		status.value = 'draft';
	}
	const title = document.getElementById(
		'wpessential-taxonomy-editor-title'
	);
	if ( title ) {
		title.textContent = 'Add taxonomy';
	}
	const cancel = buttonInput( 'wpessential-taxonomy-cancel' );
	if ( cancel ) {
		cancel.hidden = true;
	}
	clearValidation();
}

function editDefinition( definition: TaxonomyDefinition ): void {
	const id = textInput( 'wpessential-taxonomy-id' );
	const revision = textInput( 'wpessential-taxonomy-revision' );
	const key = textInput( 'wpessential-taxonomy-key' );
	if ( id ) {
		id.value = definition.id;
	}
	if ( revision ) {
		revision.value = String( definition.revision );
	}
	setFieldValue( 'taxonomy_key', definition.payload.taxonomy_key );
	setFieldValue( 'name', definition.payload.name );
	setFieldValue( 'singular_name', definition.payload.singular_name );
	setFieldValue( 'description', definition.payload.description );
	setObjectTypes( definition.payload.object_types );
	setBoolInput(
		'wpessential-taxonomy-public',
		definition.payload.public,
		true
	);
	setBoolInput(
		'wpessential-taxonomy-rest',
		definition.payload.show_in_rest,
		true
	);
	setBoolInput(
		'wpessential-taxonomy-hierarchical',
		definition.payload.hierarchical
	);
	setBoolInput(
		'wpessential-taxonomy-admin-column',
		definition.payload.show_admin_column
	);
	const status = selectInput( 'wpessential-taxonomy-status' );
	if ( status ) {
		status.value = definition.status;
	}
	if ( key ) {
		key.readOnly = true;
	}
	const title = document.getElementById(
		'wpessential-taxonomy-editor-title'
	);
	if ( title ) {
		title.textContent = 'Edit taxonomy';
	}
	const cancel = buttonInput( 'wpessential-taxonomy-cancel' );
	if ( cancel ) {
		cancel.hidden = false;
	}
	clearValidation();
	textInput( 'wpessential-taxonomy-name' )?.focus();
}

function collectEditor( definitions: TaxonomyDefinition[] ): EditorRequest {
	const id = textInput( 'wpessential-taxonomy-id' )?.value ?? '';
	const revision = Number(
		textInput( 'wpessential-taxonomy-revision' )?.value ?? 0
	);
	const existing = definitions.find( ( definition ) => definition.id === id );

	return {
		id,
		revision,
		status: selectInput( 'wpessential-taxonomy-status' )?.value ?? 'draft',
		payload: {
			...( existing?.payload ?? {} ),
			taxonomy_key: fieldValue( 'taxonomy_key' ),
			object_types: objectTypesFromInput(),
			name: fieldValue( 'name' ),
			singular_name: fieldValue( 'singular_name' ),
			description: fieldValue( 'description' ),
			public: boolInput( 'wpessential-taxonomy-public' ),
			show_in_rest: boolInput( 'wpessential-taxonomy-rest' ),
			hierarchical: boolInput( 'wpessential-taxonomy-hierarchical' ),
			show_admin_column: boolInput( 'wpessential-taxonomy-admin-column' ),
		},
	};
}

function mutationRequest(
	editor: EditorRequest,
	validationOnly = false
): TaxonomyPayload {
	const request: TaxonomyPayload = { payload: editor.payload };
	if ( ! validationOnly ) {
		request.status = editor.status;
	}
	if ( editor.id !== '' ) {
		request.id = editor.id;
		if ( ! validationOnly ) {
			request.expected_revision = editor.revision;
		}
	}
	return request;
}

function boot(): void {
	const root = document.getElementById( 'wpessential-taxonomy-root' );
	const script = document.getElementById( 'wpessential-taxonomy-bootstrap' );
	if (
		! ( root instanceof HTMLElement ) ||
		! ( script instanceof HTMLScriptElement )
	) {
		return;
	}

	let raw: unknown;
	try {
		raw = JSON.parse( script.textContent ?? '{}' );
	} catch {
		root.dataset.wpessentialEnhanced = 'invalid-bootstrap';
		return;
	}

	const bootstrap = parseBootstrap( raw );
	if ( ! bootstrap ) {
		root.dataset.wpessentialEnhanced = 'invalid-bootstrap';
		return;
	}

	let definitions = sortedDefinitions( bootstrap.definitions );
	let busy = false;

	const refresh = async (): Promise< void > => {
		const data = await postRoute( bootstrap, bootstrap.routes.list, {} );
		if ( ! isRecord( data ) || ! Array.isArray( data.definitions ) ) {
			throw new Error( 'The Taxonomy list response was invalid.' );
		}
		const next = data.definitions.filter( isDefinition );
		if ( next.length !== data.definitions.length ) {
			throw new Error( 'The Taxonomy list contained invalid records.' );
		}
		definitions = sortedDefinitions( next );
		renderRows( definitions );
	};

	const validate = async (
		editor: EditorRequest
	): Promise< ValidationReport > => {
		const data = await postRoute(
			bootstrap,
			bootstrap.routes.validate,
			mutationRequest( editor, true )
		);
		const report = parseReport( data );
		if ( ! report ) {
			throw new Error( 'The Taxonomy validation response was invalid.' );
		}
		renderValidation( report );
		return report;
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

	const form = document.getElementById( 'wpessential-taxonomy-form' );
	if ( form instanceof HTMLFormElement ) {
		form.addEventListener( 'input', clearValidation );
		form.addEventListener( 'change', clearValidation );
		form.addEventListener( 'submit', ( event ) => {
			event.preventDefault();
			void run( async () => {
				const editor = collectEditor( definitions );
				const report = await validate( editor );
				if ( ! report.valid ) {
					setNotice(
						'Taxonomy was not saved because validation found blocking issues.',
						true
					);
					return;
				}

				const data = await postRoute(
					bootstrap,
					bootstrap.routes.save,
					mutationRequest( editor )
				);
				const saved = parseMutationDefinition( data );
				if ( ! saved ) {
					throw new Error(
						'The Taxonomy save response was invalid.'
					);
				}
				definitions = upsertDefinition( definitions, saved );
				renderRows( definitions );
				resetForm();
				setNotice(
					editor.id === '' ? 'Taxonomy created.' : 'Taxonomy updated.'
				);
			} );
		} );
	}

	buttonInput( 'wpessential-taxonomy-validate' )?.addEventListener(
		'click',
		() => {
			void run( async () => {
				await validate( collectEditor( definitions ) );
			} );
		}
	);
	buttonInput( 'wpessential-taxonomy-cancel' )?.addEventListener(
		'click',
		() => {
			resetForm();
			setNotice( '' );
		}
	);
	buttonInput( 'wpessential-taxonomy-refresh' )?.addEventListener(
		'click',
		() => {
			void run( async () => {
				await refresh();
				setNotice( 'Taxonomies refreshed.' );
			} );
		}
	);

	root.addEventListener( 'click', ( event ) => {
		const target = event.target;
		if ( ! ( target instanceof HTMLButtonElement ) ) {
			return;
		}
		const editId = target.dataset.wpessentialTaxonomyEdit;
		if ( editId ) {
			const definition = definitions.find(
				( candidate ) => candidate.id === editId
			);
			if ( definition ) {
				editDefinition( definition );
			}
			return;
		}

		const status = target.dataset.wpessentialTaxonomyStatus;
		const id = target.dataset.wpessentialTaxonomyId;
		if ( ! status || ! id ) {
			return;
		}
		const definition = definitions.find(
			( candidate ) => candidate.id === id
		);
		if ( ! definition ) {
			setNotice( 'The selected taxonomy is no longer available.', true );
			return;
		}

		void run( async () => {
			const data = await postRoute( bootstrap, bootstrap.routes.status, {
				id,
				expected_revision: definition.revision,
				status,
			} );
			const changed = parseMutationDefinition( data );
			if ( ! changed ) {
				throw new Error( 'The Taxonomy status response was invalid.' );
			}
			definitions = upsertDefinition( definitions, changed );
			renderRows( definitions );
			if ( textInput( 'wpessential-taxonomy-id' )?.value === id ) {
				resetForm();
			}
			setNotice( `Taxonomy status changed to ${ status }.` );
		} );
	} );

	setObjectTypes( [ 'post' ] );
	renderRows( definitions );
	root.dataset.wpessentialEnhanced = 'ready';
	window.dispatchEvent(
		new CustomEvent( 'wpessential:admin-ready', {
			detail: { surface: 'taxonomies', payload: bootstrap },
		} )
	);
}

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', boot, { once: true } );
} else {
	boot();
}
