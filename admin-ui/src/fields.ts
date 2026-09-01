import './fields.scss';

type JsonObject = Record< string, unknown >;
type Route = { type: string; nonce: string };
type GroupDefinition = {
	id: string;
	status: string;
	revision: number;
	payload: JsonObject;
};
type CatalogType = JsonObject & {
	key: string;
	label: string;
	admin_available: boolean;
	admin_unavailable_reason: string | null;
};
type Bootstrap = {
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
	catalog: JsonObject & { types: CatalogType[] };
};
type ValidationIssue = {
	severity: string;
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

function isObject( value: unknown ): value is JsonObject {
	return (
		typeof value === 'object' && value !== null && ! Array.isArray( value )
	);
}

function isRoute( value: unknown ): value is Route {
	return (
		isObject( value ) &&
		typeof value.type === 'string' &&
		typeof value.nonce === 'string'
	);
}

function isDefinition( value: unknown ): value is GroupDefinition {
	return (
		isObject( value ) &&
		typeof value.id === 'string' &&
		typeof value.status === 'string' &&
		typeof value.revision === 'number' &&
		Number.isInteger( value.revision ) &&
		isObject( value.payload )
	);
}

function isCatalogType( value: unknown ): value is CatalogType {
	return (
		isObject( value ) &&
		typeof value.key === 'string' &&
		typeof value.label === 'string' &&
		typeof value.admin_available === 'boolean' &&
		( value.admin_unavailable_reason === null ||
			typeof value.admin_unavailable_reason === 'string' )
	);
}

function parseBootstrap( value: unknown ): Bootstrap | null {
	if (
		! isObject( value ) ||
		value.surface !== 'custom-fields' ||
		typeof value.ajaxUrl !== 'string' ||
		typeof value.ajaxAction !== 'string' ||
		! isObject( value.routes ) ||
		! isRoute( value.routes.list ) ||
		! isRoute( value.routes.catalog ) ||
		! isRoute( value.routes.validate ) ||
		! isRoute( value.routes.save ) ||
		! isRoute( value.routes.status ) ||
		! Array.isArray( value.definitions ) ||
		! value.definitions.every( isDefinition ) ||
		! isObject( value.catalog ) ||
		! Array.isArray( value.catalog.types ) ||
		! value.catalog.types.every( isCatalogType )
	) {
		return null;
	}

	return value as Bootstrap;
}

function input( id: string ): HTMLInputElement | null {
	const element = document.getElementById( id );
	return element instanceof HTMLInputElement ? element : null;
}

function select( id: string ): HTMLSelectElement | null {
	const element = document.getElementById( id );
	return element instanceof HTMLSelectElement ? element : null;
}

function button( id: string ): HTMLButtonElement | null {
	const element = document.getElementById( id );
	return element instanceof HTMLButtonElement ? element : null;
}

function copyObject( value: JsonObject ): JsonObject {
	const copy: unknown = JSON.parse( JSON.stringify( value ) );
	return isObject( copy ) ? copy : {};
}

function stringValue( object: JsonObject, key: string ): string {
	const value = object[ key ];
	return typeof value === 'string' ? value : '';
}

function uuid( field: JsonObject ): string | null {
	const value = field.uuid;
	return typeof value === 'string' && value !== '' ? value : null;
}

function fieldsFrom( definition: GroupDefinition ): JsonObject[] {
	const value = definition.payload.fields;
	if ( ! Array.isArray( value ) ) {
		return [];
	}
	return value.filter( isObject ).map( copyObject );
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
	const element = document.getElementById( 'wpessential-fields-validation' );
	if ( element instanceof HTMLElement ) {
		element.hidden = true;
		element.className = '';
	}
}

function parseValidation( value: unknown ): ValidationReport | null {
	if (
		! isObject( value ) ||
		typeof value.valid !== 'boolean' ||
		! Array.isArray( value.issues )
	) {
		return null;
	}

	const issues: ValidationIssue[] = [];
	for ( const issue of value.issues ) {
		if (
			! isObject( issue ) ||
			typeof issue.severity !== 'string' ||
			typeof issue.message !== 'string'
		) {
			return null;
		}
		issues.push( { severity: issue.severity, message: issue.message } );
	}
	return { valid: value.valid, issues };
}

function showValidation( report: ValidationReport ): void {
	const element = document.getElementById( 'wpessential-fields-validation' );
	const summary = element?.querySelector(
		'[data-wpessential-fields-validation-summary]'
	);
	const list = element?.querySelector(
		'[data-wpessential-fields-validation-issues]'
	);
	if (
		! ( element instanceof HTMLElement ) ||
		! ( summary instanceof HTMLElement ) ||
		! ( list instanceof HTMLElement )
	) {
		return;
	}

	list.replaceChildren();
	for ( const issue of report.issues ) {
		const item = document.createElement( 'li' );
		item.textContent = `${ issue.severity.replaceAll( '_', ' ' ) }: ${
			issue.message
		}`;
		list.append( item );
	}
	const blocked = report.issues.filter(
		( issue ) => issue.severity === 'blocked'
	).length;
	if ( report.valid ) {
		summary.textContent =
			report.issues.length === 0
				? 'Validation passed.'
				: `Validation passed with ${ report.issues.length } warning or informational item(s).`;
	} else {
		summary.textContent = `Validation blocked by ${ blocked } issue(s).`;
	}
	element.hidden = false;
	element.className = `notice inline ${
		report.valid ? 'notice-success' : 'notice-error'
	}`;
}

async function post(
	bootstrap: Bootstrap,
	route: Route,
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
	if ( ! isObject( decoded ) || typeof decoded.success !== 'boolean' ) {
		throw new Error( 'WPEssential returned an invalid AJAX response.' );
	}
	const envelope = decoded as AjaxEnvelope;
	if ( ! response.ok || ! envelope.success ) {
		throw new Error(
			envelope.error?.message ??
				'The requested Field Group change failed.'
		);
	}
	return envelope.data;
}

function makeActionButton(
	label: string,
	action: string,
	index: number | null = null
): HTMLButtonElement {
	const element = document.createElement( 'button' );
	element.type = 'button';
	element.className = 'button button-small';
	element.textContent = label;
	element.dataset.wpessentialFieldsAction = action;
	if ( index !== null ) {
		element.dataset.wpessentialFieldsIndex = String( index );
	}
	return element;
}

function typeMap( bootstrap: Bootstrap ): Map< string, CatalogType > {
	return new Map(
		bootstrap.catalog.types.map( ( type ) => [ type.key, type ] )
	);
}

function isEditable(
	field: JsonObject,
	types: Map< string, CatalogType >
): boolean {
	return types.get( stringValue( field, 'type' ) )?.admin_available === true;
}

function renderFields(
	fields: JsonObject[],
	types: Map< string, CatalogType >
): void {
	const host = document.getElementById( 'wpessential-fields-rows' );
	if ( ! ( host instanceof HTMLElement ) ) {
		return;
	}
	host.replaceChildren();
	if ( fields.length === 0 ) {
		const empty = document.createElement( 'p' );
		empty.className = 'description';
		empty.textContent =
			'No fields yet. Select a certified V1 type and add it.';
		host.append( empty );
		return;
	}

	fields.forEach( ( field, index ) => {
		const typeKey = stringValue( field, 'type' );
		const descriptor = types.get( typeKey );
		const persistedUuid = uuid( field );
		const editable = isEditable( field, types );
		const row = document.createElement( 'article' );
		row.className = 'wpessential-fields-row';

		const heading = document.createElement( 'div' );
		heading.className = 'wpessential-fields-row-heading';
		const title = document.createElement( 'strong' );
		title.textContent = descriptor?.label ?? ( typeKey || 'Unknown field' );
		const identity = document.createElement( 'code' );
		identity.textContent = persistedUuid
			? `UUID ${ persistedUuid }`
			: 'New field';
		heading.append( title, identity );
		row.append( heading );

		if ( ! editable ) {
			const warning = document.createElement( 'p' );
			warning.className = 'wpessential-fields-locked';
			warning.textContent =
				descriptor?.admin_unavailable_reason ??
				'This field type is unavailable in the V1 admin builder and is preserved read-only.';
			const values = document.createElement( 'p' );
			values.textContent = `Label: ${ stringValue(
				field,
				'label'
			) } · Key: ${ stringValue( field, 'key' ) }`;
			row.append( warning, values );
			host.append( row );
			return;
		}

		const grid = document.createElement( 'div' );
		grid.className = 'wpessential-fields-row-grid';
		const label = document.createElement( 'label' );
		label.textContent = 'Label';
		const labelInput = document.createElement( 'input' );
		labelInput.type = 'text';
		labelInput.value = stringValue( field, 'label' );
		labelInput.dataset.wpessentialFieldsField = 'label';
		labelInput.dataset.wpessentialFieldsIndex = String( index );
		label.append( labelInput );
		const key = document.createElement( 'label' );
		key.textContent = 'Storage key';
		const keyInput = document.createElement( 'input' );
		keyInput.type = 'text';
		keyInput.value = stringValue( field, 'key' );
		keyInput.readOnly = persistedUuid !== null;
		keyInput.dataset.wpessentialFieldsField = 'key';
		keyInput.dataset.wpessentialFieldsIndex = String( index );
		key.append( keyInput );
		grid.append( label, key );
		row.append( grid );

		if ( persistedUuid ) {
			const immutable = document.createElement( 'p' );
			immutable.className = 'description';
			immutable.textContent =
				'Storage key is immutable here. Use the explicit storage-key migration workflow to rename it.';
			row.append( immutable );
		}

		const actions = document.createElement( 'div' );
		actions.className = 'wpessential-fields-row-actions';
		const up = makeActionButton( 'Up', 'up', index );
		up.disabled = index === 0;
		const down = makeActionButton( 'Down', 'down', index );
		down.disabled = index === fields.length - 1;
		actions.append(
			up,
			down,
			makeActionButton( 'Remove', 'remove', index )
		);
		row.append( actions );
		host.append( row );
	} );
}

function renderDefinitions( definitions: GroupDefinition[] ): void {
	const host = document.getElementById( 'wpessential-fields-definitions' );
	if ( ! ( host instanceof HTMLTableSectionElement ) ) {
		return;
	}
	host.replaceChildren();
	if ( definitions.length === 0 ) {
		const row = document.createElement( 'tr' );
		const cell = document.createElement( 'td' );
		cell.colSpan = 6;
		cell.textContent = 'No Field Groups have been created yet.';
		row.append( cell );
		host.append( row );
		return;
	}

	for ( const definition of definitions ) {
		const row = document.createElement( 'tr' );
		const rawFields = definition.payload.fields;
		const values = [
			stringValue( definition.payload, 'title' ),
			stringValue( definition.payload, 'group_key' ),
			String( Array.isArray( rawFields ) ? rawFields.length : 0 ),
			definition.status,
			String( definition.revision ),
		];
		for ( const value of values ) {
			const cell = document.createElement( 'td' );
			cell.textContent = value;
			row.append( cell );
		}

		const actions = document.createElement( 'td' );
		const edit = makeActionButton( 'Edit', 'edit-definition' );
		edit.dataset.wpessentialFieldsId = definition.id;
		actions.append( edit, document.createTextNode( ' ' ) );
		const status = makeActionButton(
			definition.status === 'published' ? 'Disable' : 'Publish',
			'status-definition'
		);
		status.dataset.wpessentialFieldsId = definition.id;
		status.dataset.wpessentialFieldsStatus =
			definition.status === 'published' ? 'disabled' : 'published';
		actions.append( status );
		if ( definition.status !== 'archived' ) {
			actions.append( document.createTextNode( ' ' ) );
			const archive = makeActionButton( 'Archive', 'status-definition' );
			archive.dataset.wpessentialFieldsId = definition.id;
			archive.dataset.wpessentialFieldsStatus = 'archived';
			actions.append( archive );
		}
		row.append( actions );
		host.append( row );
	}
}

function bootBuilder( root: HTMLElement, bootstrap: Bootstrap ): void {
	let definitions = [ ...bootstrap.definitions ];
	let fields: JsonObject[] = [];
	let busy = false;
	const types = typeMap( bootstrap );

	const current = (): GroupDefinition | undefined => {
		const id = input( 'wpessential-fields-id' )?.value ?? '';
		return definitions.find( ( definition ) => definition.id === id );
	};

	const reset = (): void => {
		const form = document.getElementById( 'wpessential-fields-form' );
		if ( form instanceof HTMLFormElement ) {
			form.reset();
		}
		fields = [];
		const id = input( 'wpessential-fields-id' );
		const revision = input( 'wpessential-fields-revision' );
		const groupKey = input( 'wpessential-fields-group-key' );
		if ( id ) {
			id.value = '';
		}
		if ( revision ) {
			revision.value = '';
		}
		if ( groupKey ) {
			groupKey.readOnly = false;
		}
		const status = select( 'wpessential-fields-status' );
		if ( status ) {
			status.value = 'draft';
		}
		const heading = document.getElementById(
			'wpessential-fields-editor-title'
		);
		if ( heading ) {
			heading.textContent = 'Add field group';
		}
		const cancel = button( 'wpessential-fields-cancel' );
		if ( cancel ) {
			cancel.hidden = true;
		}
		renderFields( fields, types );
		clearValidation();
	};

	const edit = ( definition: GroupDefinition ): void => {
		const id = input( 'wpessential-fields-id' );
		const revision = input( 'wpessential-fields-revision' );
		const groupKey = input( 'wpessential-fields-group-key' );
		const title = input( 'wpessential-fields-group-title' );
		const description = input( 'wpessential-fields-group-description' );
		if ( id ) {
			id.value = definition.id;
		}
		if ( revision ) {
			revision.value = String( definition.revision );
		}
		if ( groupKey ) {
			groupKey.value = stringValue( definition.payload, 'group_key' );
			groupKey.readOnly = true;
		}
		if ( title ) {
			title.value = stringValue( definition.payload, 'title' );
		}
		if ( description ) {
			description.value = stringValue(
				definition.payload,
				'description'
			);
		}
		const rest = input( 'wpessential-fields-show-rest' );
		if ( rest ) {
			rest.checked = definition.payload.show_in_rest === true;
		}
		const status = select( 'wpessential-fields-status' );
		if ( status ) {
			status.value = definition.status;
		}
		fields = fieldsFrom( definition );
		renderFields( fields, types );
		const heading = document.getElementById(
			'wpessential-fields-editor-title'
		);
		if ( heading ) {
			heading.textContent = 'Edit field group';
		}
		const cancel = button( 'wpessential-fields-cancel' );
		if ( cancel ) {
			cancel.hidden = false;
		}
		clearValidation();
	};

	const payload = (): JsonObject => {
		const existing = current();
		return {
			...( existing?.payload ?? {} ),
			group_key:
				input( 'wpessential-fields-group-key' )?.value.trim() ?? '',
			title:
				input( 'wpessential-fields-group-title' )?.value.trim() ?? '',
			description:
				input( 'wpessential-fields-group-description' )?.value.trim() ??
				'',
			show_in_rest:
				input( 'wpessential-fields-show-rest' )?.checked ?? false,
			fields: fields.map( copyObject ),
		};
	};

	const request = (): JsonObject => {
		const existing = current();
		const result: JsonObject = {
			payload: payload(),
			status: select( 'wpessential-fields-status' )?.value ?? 'draft',
		};
		if ( existing ) {
			result.id = existing.id;
			result.expected_revision = existing.revision;
		}
		return result;
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
					: 'The Field Group request failed.',
				true
			);
		} finally {
			busy = false;
			root.removeAttribute( 'aria-busy' );
		}
	};

	const refresh = async (): Promise< void > => {
		const result = await post( bootstrap, bootstrap.routes.list, {} );
		if ( ! isObject( result ) || ! Array.isArray( result.definitions ) ) {
			throw new Error( 'Field Group list response was invalid.' );
		}
		const next = result.definitions.filter( isDefinition );
		if ( next.length !== result.definitions.length ) {
			throw new Error( 'Field Group list contained invalid records.' );
		}
		definitions = next;
		renderDefinitions( definitions );
	};

	const validate = async (): Promise< ValidationReport > => {
		const result = await post(
			bootstrap,
			bootstrap.routes.validate,
			request()
		);
		const report = parseValidation( result );
		if ( ! report ) {
			throw new Error( 'Field Group validation response was invalid.' );
		}
		showValidation( report );
		return report;
	};

	button( 'wpessential-fields-add' )?.addEventListener( 'click', () => {
		const chooser = select( 'wpessential-fields-add-type' );
		const descriptor = types.get( chooser?.value ?? '' );
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
		if ( chooser ) {
			chooser.value = '';
		}
		renderFields( fields, types );
		clearValidation();
	} );

	root.addEventListener( 'input', ( event ) => {
		const target = event.target;
		if ( ! ( target instanceof HTMLInputElement ) ) {
			return;
		}
		const key = target.dataset.wpessentialFieldsField;
		const index = Number( target.dataset.wpessentialFieldsIndex ?? -1 );
		if ( ! key || ! Number.isInteger( index ) || ! fields[ index ] ) {
			return;
		}
		if ( key === 'key' && uuid( fields[ index ] ) !== null ) {
			return;
		}
		fields[ index ][ key ] = target.value;
		clearValidation();
	} );

	root.addEventListener( 'click', ( event ) => {
		const target = event.target;
		if ( ! ( target instanceof HTMLButtonElement ) ) {
			return;
		}
		const action = target.dataset.wpessentialFieldsAction;
		if ( ! action ) {
			return;
		}

		if ( action === 'edit-definition' ) {
			const definition = definitions.find(
				( item ) => item.id === target.dataset.wpessentialFieldsId
			);
			if ( definition ) {
				edit( definition );
			}
			return;
		}

		if ( action === 'status-definition' ) {
			const definition = definitions.find(
				( item ) => item.id === target.dataset.wpessentialFieldsId
			);
			const status = target.dataset.wpessentialFieldsStatus;
			if ( ! definition || ! status ) {
				return;
			}
			void run( async () => {
				await post( bootstrap, bootstrap.routes.status, {
					id: definition.id,
					expected_revision: definition.revision,
					status,
				} );
				await refresh();
				reset();
				setNotice( `Field Group status changed to ${ status }.` );
			} );
			return;
		}

		const index = Number( target.dataset.wpessentialFieldsIndex ?? -1 );
		if (
			! Number.isInteger( index ) ||
			! fields[ index ] ||
			! isEditable( fields[ index ], types )
		) {
			return;
		}
		if ( action === 'remove' ) {
			fields.splice( index, 1 );
		} else if ( action === 'up' && index > 0 ) {
			const selected = fields[ index ];
			if ( ! selected ) {
				return;
			}
			fields.splice( index, 1 );
			fields.splice( index - 1, 0, selected );
		} else if ( action === 'down' && index < fields.length - 1 ) {
			const selected = fields[ index ];
			if ( ! selected ) {
				return;
			}
			fields.splice( index, 1 );
			fields.splice( index + 1, 0, selected );
		} else {
			return;
		}
		renderFields( fields, types );
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
					setNotice(
						'Field Group was not saved because validation found blocking issues.',
						true
					);
					return;
				}
				const editing = current() !== undefined;
				await post( bootstrap, bootstrap.routes.save, request() );
				await refresh();
				reset();
				setNotice(
					editing ? 'Field Group updated.' : 'Field Group created.'
				);
			} );
		} );
	}

	button( 'wpessential-fields-validate' )?.addEventListener( 'click', () => {
		void run( async () => {
			await validate();
		} );
	} );
	button( 'wpessential-fields-cancel' )?.addEventListener( 'click', reset );
	button( 'wpessential-fields-refresh' )?.addEventListener( 'click', () => {
		void run( async () => {
			await refresh();
			setNotice( 'Field Groups refreshed.' );
		} );
	} );

	renderFields( fields, types );
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
	if (
		! ( root instanceof HTMLElement ) ||
		! ( script instanceof HTMLScriptElement )
	) {
		return;
	}
	try {
		const bootstrap = parseBootstrap(
			JSON.parse( script.textContent ?? '{}' )
		);
		if ( ! bootstrap ) {
			root.dataset.wpessentialEnhanced = 'invalid-bootstrap';
			return;
		}
		bootBuilder( root, bootstrap );
	} catch {
		root.dataset.wpessentialEnhanced = 'invalid-bootstrap';
	}
}

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', boot, { once: true } );
} else {
	boot();
}
