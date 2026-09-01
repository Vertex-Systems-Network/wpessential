import './fields.scss';

type RecordValue = Record< string, unknown >;
type Route = { type: string; nonce: string };
type FieldDefinition = RecordValue;
type GroupDefinition = {
	id: string;
	status: string;
	revision: number;
	payload: RecordValue;
};
type CatalogType = RecordValue & {
	key: string;
	label: string;
	admin_available: boolean;
	admin_unavailable_reason: string | null;
};
type FieldsBootstrap = {
	surface: 'custom-fields';
	ajaxUrl: string;
	ajaxAction: string;
	routes: {
		list: Route;
		catalog: Route;
		validate: Route;
		save: Route;
		status: Route;
	};
	definitions: GroupDefinition[];
	catalog: RecordValue & { types: CatalogType[] };
};
type ValidationIssue = {
	id: string;
	severity: string;
	field: string;
	message: string;
};
type ValidationReport = {
	valid: boolean;
	issues: ValidationIssue[];
};
type AjaxEnvelope = {
	success: boolean;
	data?: unknown;
	error?: { message?: string };
};

function isRecord( value: unknown ): value is RecordValue {
	return typeof value === 'object' && value !== null && ! Array.isArray( value );
}

function isRoute( value: unknown ): value is Route {
	return (
		isRecord( value ) &&
		typeof value.type === 'string' &&
		typeof value.nonce === 'string'
	);
}

function isDefinition( value: unknown ): value is GroupDefinition {
	return (
		isRecord( value ) &&
		typeof value.id === 'string' &&
		typeof value.status === 'string' &&
		typeof value.revision === 'number' &&
		Number.isInteger( value.revision ) &&
		isRecord( value.payload )
	);
}

function isCatalogType( value: unknown ): value is CatalogType {
	return (
		isRecord( value ) &&
		typeof value.key === 'string' &&
		typeof value.label === 'string' &&
		typeof value.admin_available === 'boolean' &&
		( value.admin_unavailable_reason === null ||
			typeof value.admin_unavailable_reason === 'string' )
	);
}

function parseBootstrap( value: unknown ): FieldsBootstrap | null {
	if (
		! isRecord( value ) ||
		value.surface !== 'custom-fields' ||
		typeof value.ajaxUrl !== 'string' ||
		typeof value.ajaxAction !== 'string' ||
		! isRecord( value.routes ) ||
		! isRoute( value.routes.list ) ||
		! isRoute( value.routes.catalog ) ||
		! isRoute( value.routes.validate ) ||
		! isRoute( value.routes.save ) ||
		! isRoute( value.routes.status ) ||
		! Array.isArray( value.definitions ) ||
		! value.definitions.every( isDefinition ) ||
		! isRecord( value.catalog ) ||
		! Array.isArray( value.catalog.types ) ||
		! value.catalog.types.every( isCatalogType )
	) {
		return null;
	}

	return value as FieldsBootstrap;
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

function cloneRecord( value: RecordValue ): RecordValue {
	return structuredClone( value );
}

function fieldString( field: FieldDefinition, key: string ): string {
	return typeof field[ key ] === 'string' ? field[ key ] : '';
}

function fieldUuid( field: FieldDefinition ): string | null {
	return typeof field.uuid === 'string' && field.uuid !== '' ? field.uuid : null;
}

function groupFields( definition: GroupDefinition ): FieldDefinition[] {
	const fields = definition.payload.fields;
	if ( ! Array.isArray( fields ) ) {
		return [];
	}
	return fields.filter( isRecord ).map( cloneRecord );
}

function setNotice( message: string, error = false ): void {
	const notice = document.getElementById( 'wpessential-fields-notice' );
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
	const container = document.getElementById( 'wpessential-fields-validation' );
	if ( container instanceof HTMLElement ) {
		container.hidden = true;
		container.className = '';
	}
}

function parseValidation( value: unknown ): ValidationReport | null {
	if (
		! isRecord( value ) ||
		typeof value.valid !== 'boolean' ||
		! Array.isArray( value.issues )
	) {
		return null;
	}
	const issues: ValidationIssue[] = [];
	for ( const issue of value.issues ) {
		if (
			! isRecord( issue ) ||
			typeof issue.id !== 'string' ||
			typeof issue.severity !== 'string' ||
			typeof issue.field !== 'string' ||
			typeof issue.message !== 'string'
		) {
			return null;
		}
		issues.push( {
			id: issue.id,
			severity: issue.severity,
			field: issue.field,
			message: issue.message,
		} );
	}
	return { valid: value.valid, issues };
}

function renderValidation( report: ValidationReport ): void {
	const container = document.getElementById( 'wpessential-fields-validation' );
	const summary = container?.querySelector(
		'[data-wpessential-fields-validation-summary]'
	);
	const list = container?.querySelector(
		'[data-wpessential-fields-validation-issues]'
	);
	if (
		! ( container instanceof HTMLElement ) ||
		! ( summary instanceof HTMLElement ) ||
		! ( list instanceof HTMLElement )
	) {
		return;
	}

	list.replaceChildren();
	for ( const issue of report.issues ) {
		const item = document.createElement( 'li' );
		item.textContent = `${ issue.severity.replaceAll( '_', ' ' ) }: ${ issue.message }`;
		list.append( item );
	}
	const blocked = report.issues.filter(
		( issue ) => issue.severity === 'blocked'
	).length;
	summary.textContent = report.valid
		? report.issues.length === 0
			? 'Validation passed.'
			: `Validation passed with ${ report.issues.length } warning or informational item(s).`
		: `Validation blocked by ${ blocked } issue(s).`;
	container.hidden = false;
	container.className = `notice inline ${ report.valid ? 'notice-success' : 'notice-error' }`;
}

async function postRoute(
	bootstrap: FieldsBootstrap,
	route: Route,
	payload: RecordValue
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
			envelope.error?.message ?? 'The requested Field Group change failed.'
		);
	}
	return envelope.data;
}

function catalogMap( bootstrap: FieldsBootstrap ): Map< string, CatalogType > {
	return new Map( bootstrap.catalog.types.map( ( type ) => [ type.key, type ] ) );
}

function fieldTypeAvailable(
	field: FieldDefinition,
	types: Map< string, CatalogType >
): boolean {
	const descriptor = types.get( fieldString( field, 'type' ) );
	return descriptor?.admin_available === true;
}

function actionButton( label: string, action: string, index: number ): HTMLButtonElement {
	const button = document.createElement( 'button' );
	button.type = 'button';
	button.className = 'button button-small';
	button.textContent = label;
	button.dataset.wpessentialFieldsAction = action;
	button.dataset.wpessentialFieldsIndex = String( index );
	return button;
}

function renderFieldRows(
	fields: FieldDefinition[],
	types: Map< string, CatalogType >
): void {
	const container = document.getElementById( 'wpessential-fields-rows' );
	if ( ! ( container instanceof HTMLElement ) ) {
		return;
	}
	container.replaceChildren();
	if ( fields.length === 0 ) {
		const empty = document.createElement( 'p' );
		empty.className = 'description';
		empty.textContent = 'No fields yet. Select a certified V1 type and add it.';
		container.append( empty );
		return;
	}

	fields.forEach( ( field, index ) => {
		const typeKey = fieldString( field, 'type' );
		const descriptor = types.get( typeKey );
		const available = fieldTypeAvailable( field, types );
		const persisted = fieldUuid( field ) !== null;
		const row = document.createElement( 'article' );
		row.className = 'wpessential-fields-row';
		row.dataset.wpessentialFieldsRow = String( index );

		const heading = document.createElement( 'div' );
		heading.className = 'wpessential-fields-row-heading';
		const title = document.createElement( 'strong' );
		title.textContent = descriptor?.label ?? typeKey || 'Unknown field';
		const meta = document.createElement( 'code' );
		meta.textContent = persisted ? `UUID ${ fieldUuid( field ) }` : 'New field';
		heading.append( title, meta );
		row.append( heading );

		if ( ! available ) {
			const locked = document.createElement( 'p' );
			locked.className = 'wpessential-fields-locked';
			locked.textContent =
				descriptor?.admin_unavailable_reason ??
				'This field type is unavailable in the V1 admin builder and is preserved read-only.';
			const values = document.createElement( 'p' );
			values.textContent = `Label: ${ fieldString( field, 'label' ) } · Key: ${ fieldString( field, 'key' ) }`;
			row.append( locked, values );
			container.append( row );
			return;
		}

		const grid = document.createElement( 'div' );
		grid.className = 'wpessential-fields-row-grid';
		const label = document.createElement( 'label' );
		label.textContent = 'Label';
		const labelInput = document.createElement( 'input' );
		labelInput.type = 'text';
		labelInput.value = fieldString( field, 'label' );
		labelInput.dataset.wpessentialFieldsField = 'label';
		labelInput.dataset.wpessentialFieldsIndex = String( index );
		label.append( labelInput );
		const key = document.createElement( 'label' );
		key.textContent = 'Storage key';
		const keyInput = document.createElement( 'input' );
		keyInput.type = 'text';
		keyInput.value = fieldString( field, 'key' );
		keyInput.readOnly = persisted;
		keyInput.dataset.wpessentialFieldsField = 'key';
		keyInput.dataset.wpessentialFieldsIndex = String( index );
		key.append( keyInput );
		grid.append( label, key );
		row.append( grid );

		if ( persisted ) {
			const immutable = document.createElement( 'p' );
			immutable.className = 'description';
			immutable.textContent =
				'Storage key is immutable here. Use the explicit storage-key migration workflow to rename it.';
			row.append( immutable );
		}

		const actions = document.createElement( 'div' );
		actions.className = 'wpessential-fields-row-actions';
		const up = actionButton( 'Up', 'up', index );
		up.disabled = index === 0;
		const down = actionButton( 'Down', 'down', index );
		down.disabled = index === fields.length - 1;
		actions.append( up, down, actionButton( 'Remove', 'remove', index ) );
		row.append( actions );
		container.append( row );
	} );
}

function renderDefinitions( definitions: GroupDefinition[] ): void {
	const body = document.getElementById( 'wpessential-fields-definitions' );
	if ( ! ( body instanceof HTMLTableSectionElement ) ) {
		return;
	}
	body.replaceChildren();
	if ( definitions.length === 0 ) {
		const row = document.createElement( 'tr' );
		const cell = document.createElement( 'td' );
		cell.colSpan = 6;
		cell.textContent = 'No Field Groups have been created yet.';
		row.append( cell );
		body.append( row );
		return;
	}

	for ( const definition of definitions ) {
		const row = document.createElement( 'tr' );
		const payload = definition.payload;
		const fields = Array.isArray( payload.fields ) ? payload.fields.length : 0;
		for ( const text of [
			typeof payload.title === 'string' ? payload.title : '',
			typeof payload.group_key === 'string' ? payload.group_key : '',
			String( fields ),
			definition.status,
			String( definition.revision ),
		] ) {
			const cell = document.createElement( 'td' );
			cell.textContent = text;
			row.append( cell );
		}
		const actions = document.createElement( 'td' );
		const edit = actionButton( 'Edit', 'edit-definition', 0 );
		edit.dataset.wpessentialFieldsId = definition.id;
		actions.append( edit, document.createTextNode( ' ' ) );
		const toggle = actionButton(
			definition.status === 'published' ? 'Disable' : 'Publish',
			'status-definition',
			0
		);
		toggle.dataset.wpessentialFieldsId = definition.id;
		toggle.dataset.wpessentialFieldsStatus =
			definition.status === 'published' ? 'disabled' : 'published';
		actions.append( toggle, document.createTextNode( ' ' ) );
		if ( definition.status !== 'archived' ) {
			const archive = actionButton( 'Archive', 'status-definition', 0 );
			archive.dataset.wpessentialFieldsId = definition.id;
			archive.dataset.wpessentialFieldsStatus = 'archived';
			actions.append( archive );
		}
		row.append( actions );
		body.append( row );
	}
}

function bootFieldsAdmin( root: HTMLElement, bootstrap: FieldsBootstrap ): void {
	let definitions = [ ...bootstrap.definitions ];
	let fields: FieldDefinition[] = [];
	let busy = false;
	const types = catalogMap( bootstrap );

	const currentDefinition = (): GroupDefinition | undefined => {
		const id = textInput( 'wpessential-fields-id' )?.value ?? '';
		return definitions.find( ( definition ) => definition.id === id );
	};

	const reset = (): void => {
		const form = document.getElementById( 'wpessential-fields-form' );
		if ( form instanceof HTMLFormElement ) {
			form.reset();
		}
		fields = [];
		const id = textInput( 'wpessential-fields-id' );
		const revision = textInput( 'wpessential-fields-revision' );
		const groupKey = textInput( 'wpessential-fields-group-key' );
		if ( id ) id.value = '';
		if ( revision ) revision.value = '';
		if ( groupKey ) groupKey.readOnly = false;
		const status = selectInput( 'wpessential-fields-status' );
		if ( status ) status.value = 'draft';
		const title = document.getElementById( 'wpessential-fields-editor-title' );
		if ( title ) title.textContent = 'Add field group';
		const cancel = buttonInput( 'wpessential-fields-cancel' );
		if ( cancel ) cancel.hidden = true;
		renderFieldRows( fields, types );
		clearValidation();
	};

	const edit = ( definition: GroupDefinition ): void => {
		const payload = definition.payload;
		const id = textInput( 'wpessential-fields-id' );
		const revision = textInput( 'wpessential-fields-revision' );
		const groupKey = textInput( 'wpessential-fields-group-key' );
		const titleInput = textInput( 'wpessential-fields-group-title' );
		const description = textInput( 'wpessential-fields-group-description' );
		if ( id ) id.value = definition.id;
		if ( revision ) revision.value = String( definition.revision );
		if ( groupKey ) {
			groupKey.value = typeof payload.group_key === 'string' ? payload.group_key : '';
			groupKey.readOnly = true;
		}
		if ( titleInput ) titleInput.value = typeof payload.title === 'string' ? payload.title : '';
		if ( description ) description.value = typeof payload.description === 'string' ? payload.description : '';
		const showRest = textInput( 'wpessential-fields-show-rest' );
		if ( showRest ) showRest.checked = payload.show_in_rest === true;
		const status = selectInput( 'wpessential-fields-status' );
		if ( status ) status.value = definition.status;
		fields = groupFields( definition );
		renderFieldRows( fields, types );
		const heading = document.getElementById( 'wpessential-fields-editor-title' );
		if ( heading ) heading.textContent = 'Edit field group';
		const cancel = buttonInput( 'wpessential-fields-cancel' );
		if ( cancel ) cancel.hidden = false;
		clearValidation();
	};

	const collectPayload = (): RecordValue => {
		const existing = currentDefinition();
		return {
			...( existing?.payload ?? {} ),
			group_key: textInput( 'wpessential-fields-group-key' )?.value.trim() ?? '',
			title: textInput( 'wpessential-fields-group-title' )?.value.trim() ?? '',
			description:
				textInput( 'wpessential-fields-group-description' )?.value.trim() ?? '',
			show_in_rest: textInput( 'wpessential-fields-show-rest' )?.checked ?? false,
			fields: fields.map( cloneRecord ),
		};
	};

	const request = (): RecordValue => {
		const existing = currentDefinition();
		const result: RecordValue = {
			payload: collectPayload(),
			status: selectInput( 'wpessential-fields-status' )?.value ?? 'draft',
		};
		if ( existing ) {
			result.id = existing.id;
			result.expected_revision = existing.revision;
		}
		return result;
	};

	const run = async ( operation: () => Promise< void > ): Promise< void > => {
		if ( busy ) return;
		busy = true;
		root.setAttribute( 'aria-busy', 'true' );
		setNotice( '' );
		try {
			await operation();
		} catch ( error ) {
			setNotice(
				error instanceof Error ? error.message : 'The Field Group request failed.',
				true
			);
		} finally {
			busy = false;
			root.removeAttribute( 'aria-busy' );
		}
	};

	const refresh = async (): Promise< void > => {
		const data = await postRoute( bootstrap, bootstrap.routes.list, {} );
		if ( ! isRecord( data ) || ! Array.isArray( data.definitions ) ) {
			throw new Error( 'Field Group list response was invalid.' );
		}
		const next = data.definitions.filter( isDefinition );
		if ( next.length !== data.definitions.length ) {
			throw new Error( 'Field Group list contained invalid records.' );
		}
		definitions = next;
		renderDefinitions( definitions );
	};

	const validate = async (): Promise< ValidationReport > => {
		const data = await postRoute( bootstrap, bootstrap.routes.validate, request() );
		const report = parseValidation( data );
		if ( ! report ) {
			throw new Error( 'Field Group validation response was invalid.' );
		}
		renderValidation( report );
		return report;
	};

	buttonInput( 'wpessential-fields-add' )?.addEventListener( 'click', () => {
		const select = selectInput( 'wpessential-fields-add-type' );
		const typeKey = select?.value ?? '';
		const descriptor = types.get( typeKey );
		if ( ! descriptor?.admin_available ) {
			setNotice( 'Select an available V1 field type.', true );
			return;
		}
		fields.push( {
			uuid: null,
			key: '',
			label: descriptor.label,
			type: descriptor.key,
			settings: {},
			show_in_rest: false,
			rest_schema: 'auto',
		} );
		if ( select ) select.value = '';
		renderFieldRows( fields, types );
		clearValidation();
	} );

	root.addEventListener( 'input', ( event ) => {
		const input = event.target;
		if ( ! ( input instanceof HTMLInputElement ) ) return;
		const key = input.dataset.wpessentialFieldsField;
		const index = Number( input.dataset.wpessentialFieldsIndex ?? -1 );
		if ( ! key || ! Number.isInteger( index ) || ! fields[ index ] ) return;
		if ( key === 'key' && fieldUuid( fields[ index ] ) !== null ) return;
		fields[ index ][ key ] = input.value;
		clearValidation();
	} );

	root.addEventListener( 'click', ( event ) => {
		const target = event.target;
		if ( ! ( target instanceof HTMLButtonElement ) ) return;
		const action = target.dataset.wpessentialFieldsAction;
		if ( ! action ) return;

		if ( action === 'edit-definition' ) {
			const definition = definitions.find(
				( item ) => item.id === target.dataset.wpessentialFieldsId
			);
			if ( definition ) edit( definition );
			return;
		}
		if ( action === 'status-definition' ) {
			const id = target.dataset.wpessentialFieldsId;
			const status = target.dataset.wpessentialFieldsStatus;
			const definition = definitions.find( ( item ) => item.id === id );
			if ( ! definition || ! status ) return;
			void run( async () => {
				await postRoute( bootstrap, bootstrap.routes.status, {
					id: definition.id,
					expected_revision: definition.revision,
					status,
				} );
				await refresh();
				if ( currentDefinition()?.id === definition.id ) reset();
				setNotice( `Field Group status changed to ${ status }.` );
			} );
			return;
		}

		const index = Number( target.dataset.wpessentialFieldsIndex ?? -1 );
		if ( ! Number.isInteger( index ) || ! fields[ index ] ) return;
		if ( ! fieldTypeAvailable( fields[ index ], types ) ) return;
		if ( action === 'remove' ) {
			fields.splice( index, 1 );
		} else if ( action === 'up' && index > 0 ) {
			[ fields[ index - 1 ], fields[ index ] ] = [ fields[ index ], fields[ index - 1 ] ];
		} else if ( action === 'down' && index < fields.length - 1 ) {
			[ fields[ index ], fields[ index + 1 ] ] = [ fields[ index + 1 ], fields[ index ] ];
		} else {
			return;
		}
		renderFieldRows( fields, types );
		clearValidation();
	} );

	const form = document.getElementById( 'wpessential-fields-form' );
	if ( form instanceof HTMLFormElement ) {
		form.addEventListener( 'change', clearValidation );
		form.addEventListener( 'submit', ( event ) => {
			event.preventDefault();
			void run( async () => {
				const report = await validate();
				if ( ! report.valid ) {
					setNotice( 'Field Group was not saved because validation found blocking issues.', true );
					return;
				}
				const editing = currentDefinition() !== undefined;
				await postRoute( bootstrap, bootstrap.routes.save, request() );
				await refresh();
				reset();
				setNotice( editing ? 'Field Group updated.' : 'Field Group created.' );
			} );
		} );
	}

	buttonInput( 'wpessential-fields-validate' )?.addEventListener( 'click', () => {
		void run( async () => {
			await validate();
		} );
	} );
	buttonInput( 'wpessential-fields-cancel' )?.addEventListener( 'click', reset );
	buttonInput( 'wpessential-fields-refresh' )?.addEventListener( 'click', () => {
		void run( async () => {
			await refresh();
			setNotice( 'Field Groups refreshed.' );
		} );
	} );

	renderFieldRows( fields, types );
	renderDefinitions( definitions );
	root.dataset.wpessentialEnhanced = 'ready';
	window.dispatchEvent(
		new CustomEvent( 'wpessential:admin-ready', {
			detail: { surface: 'custom-fields', payload: bootstrap },
		} )
	);
}

function boot(): void {
	const root = document.getElementById( 'wpessential-fields-root' );
	const script = document.getElementById( 'wpessential-fields-bootstrap' );
	if ( ! ( root instanceof HTMLElement ) || ! ( script instanceof HTMLScriptElement ) ) {
		return;
	}
	try {
		const bootstrap = parseBootstrap( JSON.parse( script.textContent ?? '{}' ) );
		if ( ! bootstrap ) {
			root.dataset.wpessentialEnhanced = 'invalid-bootstrap';
			return;
		}
		bootFieldsAdmin( root, bootstrap );
	} catch {
		root.dataset.wpessentialEnhanced = 'invalid-bootstrap';
	}
}

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', boot, { once: true } );
} else {
	boot();
}
