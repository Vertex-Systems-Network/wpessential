import './query.scss';

type JsonObject = Record< string, unknown >;
type QueryIdentity = {
	uuid: string;
	key: string;
	name: string;
	revision: number;
	lifecycle: string;
};
type QueryField = {
	ref: string;
	label: string;
	logicalType: string;
};
type QuerySource = {
	sourceRef: string;
	sourceType: string;
	capabilityVersion: number;
	label: string;
	fields: QueryField[];
	predicates: string[];
	maxPageSize: number;
	supportsRelations: boolean;
};
type QueryBootstrap = {
	surface: 'query';
	identity: QueryIdentity;
	sources: QuerySource[];
};
type QueryDraftState = {
	sourceRef: string;
	projection: string[];
	filterField: string;
	filterOperator: string;
	filterValue: string;
	orderField: string;
	orderDirection: 'asc' | 'desc';
	pageSize: number;
	offset: number;
};
type BuildResult = {
	ast: JsonObject | null;
	issues: string[];
};

const SUPPORTED_FILTER_OPERATORS = [ 'eq', 'neq', 'in', 'not_in' ] as const;

function isObject( value: unknown ): value is JsonObject {
	return (
		typeof value === 'object' && value !== null && ! Array.isArray( value )
	);
}

function semanticIdentifier( value: unknown ): value is string {
	return (
		typeof value === 'string' && /^[a-z][a-z0-9._-]{0,127}$/.test( value )
	);
}

function parseField( value: unknown ): QueryField | null {
	if (
		! isObject( value ) ||
		! semanticIdentifier( value.ref ) ||
		typeof value.label !== 'string' ||
		value.label.trim() === '' ||
		! semanticIdentifier( value.logicalType )
	) {
		return null;
	}
	return value as QueryField;
}

function parseSource( value: unknown ): QuerySource | null {
	if (
		! isObject( value ) ||
		! semanticIdentifier( value.sourceRef ) ||
		! semanticIdentifier( value.sourceType ) ||
		typeof value.capabilityVersion !== 'number' ||
		! Number.isInteger( value.capabilityVersion ) ||
		value.capabilityVersion < 1 ||
		typeof value.label !== 'string' ||
		value.label.trim() === '' ||
		! Array.isArray( value.fields ) ||
		! Array.isArray( value.predicates ) ||
		! value.predicates.every( semanticIdentifier ) ||
		typeof value.maxPageSize !== 'number' ||
		! Number.isInteger( value.maxPageSize ) ||
		value.maxPageSize < 1 ||
		typeof value.supportsRelations !== 'boolean'
	) {
		return null;
	}

	const fields = value.fields.map( parseField );
	if ( fields.some( ( field ) => field === null ) ) {
		return null;
	}

	return {
		sourceRef: value.sourceRef,
		sourceType: value.sourceType,
		capabilityVersion: value.capabilityVersion,
		label: value.label,
		fields: fields as QueryField[],
		predicates: value.predicates as string[],
		maxPageSize: value.maxPageSize,
		supportsRelations: value.supportsRelations,
	};
}

function parseIdentity( value: unknown ): QueryIdentity | null {
	if (
		! isObject( value ) ||
		typeof value.uuid !== 'string' ||
		value.uuid.trim() === '' ||
		! semanticIdentifier( value.key ) ||
		typeof value.name !== 'string' ||
		value.name.trim() === '' ||
		typeof value.revision !== 'number' ||
		! Number.isInteger( value.revision ) ||
		value.revision < 1 ||
		! semanticIdentifier( value.lifecycle )
	) {
		return null;
	}
	return value as QueryIdentity;
}

export function parseQueryBootstrap( value: unknown ): QueryBootstrap | null {
	if (
		! isObject( value ) ||
		value.surface !== 'query' ||
		! Array.isArray( value.sources )
	) {
		return null;
	}
	const identity = parseIdentity( value.identity );
	const sources = value.sources.map( parseSource );
	if ( identity === null || sources.some( ( source ) => source === null ) ) {
		return null;
	}
	return { surface: 'query', identity, sources: sources as QuerySource[] };
}

function normalizeScalar( raw: string, logicalType: string ): unknown | null {
	const value = raw.trim();
	if ( value === '' ) {
		return null;
	}
	if ( logicalType === 'integer' ) {
		if ( ! /^-?\d+$/.test( value ) ) {
			return null;
		}
		const parsed = Number( value );
		return Number.isSafeInteger( parsed ) ? parsed : null;
	}
	return value;
}

function sourceByRef(
	bootstrap: QueryBootstrap,
	sourceRef: string
): QuerySource | null {
	return (
		bootstrap.sources.find(
			( source ) => source.sourceRef === sourceRef
		) ?? null
	);
}

function fieldByRef(
	source: QuerySource,
	fieldRef: string
): QueryField | null {
	return source.fields.find( ( field ) => field.ref === fieldRef ) ?? null;
}

export function buildQueryAstPreview(
	bootstrap: QueryBootstrap,
	state: QueryDraftState
): BuildResult {
	const issues: string[] = [];
	const source = sourceByRef( bootstrap, state.sourceRef );
	if ( source === null ) {
		return {
			ast: null,
			issues: [ 'Select a registered Query data source.' ],
		};
	}

	const projection = state.projection.filter(
		( ref, index, values ) =>
			fieldByRef( source, ref ) !== null &&
			values.indexOf( ref ) === index
	);
	if ( projection.length === 0 ) {
		issues.push( 'Select at least one projection field.' );
	}

	if (
		! Number.isInteger( state.pageSize ) ||
		state.pageSize < 1 ||
		state.pageSize > source.maxPageSize
	) {
		issues.push(
			`Page size must be between 1 and ${ source.maxPageSize }.`
		);
	}
	if ( ! Number.isInteger( state.offset ) || state.offset < 0 ) {
		issues.push( 'Offset must be a non-negative integer.' );
	}

	let filter: JsonObject | null = null;
	if (
		state.filterField !== '' ||
		state.filterOperator !== '' ||
		state.filterValue !== ''
	) {
		const field = fieldByRef( source, state.filterField );
		const operatorAllowed =
			SUPPORTED_FILTER_OPERATORS.includes(
				state.filterOperator as ( typeof SUPPORTED_FILTER_OPERATORS )[ number ]
			) && source.predicates.includes( state.filterOperator );
		if ( field === null ) {
			issues.push(
				'Choose a filter field declared by the selected data source.'
			);
		} else if ( ! operatorAllowed ) {
			issues.push(
				'This filter operator is not advertised by the selected data source.'
			);
		} else if (
			state.filterOperator === 'in' ||
			state.filterOperator === 'not_in'
		) {
			const values = state.filterValue
				.split( ',' )
				.map( ( value ) =>
					normalizeScalar( value, field.logicalType )
				);
			if (
				values.length === 0 ||
				values.some( ( value ) => value === null )
			) {
				issues.push(
					'Set filters require a comma-separated list of valid values.'
				);
			} else {
				filter = {
					type: 'set_membership',
					field_ref: field.ref,
					operator: state.filterOperator,
					values,
				};
			}
		} else {
			const value = normalizeScalar(
				state.filterValue,
				field.logicalType
			);
			if ( value === null ) {
				issues.push( 'Comparison filter requires a valid value.' );
			} else {
				filter = {
					type: 'comparison',
					field_ref: field.ref,
					operator: state.filterOperator,
					value,
				};
			}
		}
	}

	const orderBy: JsonObject[] = [];
	if ( state.orderField !== '' ) {
		if ( fieldByRef( source, state.orderField ) === null ) {
			issues.push(
				'Choose an ordering field declared by the selected data source.'
			);
		} else {
			orderBy.push( {
				field_ref: state.orderField,
				direction: state.orderDirection,
			} );
		}
	}

	if ( issues.length > 0 ) {
		return { ast: null, issues };
	}

	return {
		ast: {
			identity: bootstrap.identity,
			ast_version: 1,
			source: {
				source_ref: source.sourceRef,
				source_type: source.sourceType,
				capability_version: source.capabilityVersion,
			},
			operation: 'select',
			projection,
			parameters: {},
			filter,
			order_by: orderBy,
			pagination: {
				mode: 'offset',
				page_size: state.pageSize,
				offset: state.offset,
			},
			distinct: false,
			execution_policy: {},
			cache_policy: {},
		},
		issues: [],
	};
}

function element< K extends keyof HTMLElementTagNameMap >(
	tag: K,
	className?: string,
	text?: string
): HTMLElementTagNameMap[ K ] {
	const node = document.createElement( tag );
	if ( className ) {
		node.className = className;
	}
	if ( text !== undefined ) {
		node.textContent = text;
	}
	return node;
}

function labelFor( control: HTMLElement, text: string ): HTMLLabelElement {
	const label = element( 'label', 'wpessential-query__label', text );
	if ( control.id ) {
		label.htmlFor = control.id;
	}
	return label;
}

function option( value: string, text: string ): HTMLOptionElement {
	const item = document.createElement( 'option' );
	item.value = value;
	item.textContent = text;
	return item;
}

function initialState( bootstrap: QueryBootstrap ): QueryDraftState {
	const source = bootstrap.sources[ 0 ];
	return {
		sourceRef: source?.sourceRef ?? '',
		projection: source?.fields[ 0 ] ? [ source.fields[ 0 ].ref ] : [],
		filterField: '',
		filterOperator: '',
		filterValue: '',
		orderField: '',
		orderDirection: 'asc',
		pageSize: Math.min( 20, source?.maxPageSize ?? 20 ),
		offset: 0,
	};
}

export function mountQueryScaffold(
	root: HTMLElement,
	bootstrap: QueryBootstrap
): void {
	if ( root.dataset.wpessentialEnhanced === 'ready' ) {
		return;
	}

	const state = initialState( bootstrap );
	const title = element(
		'h2',
		'wpessential-query__title',
		'Query definition'
	);
	const description = element(
		'p',
		'wpessential-query__description',
		'Author a bounded Query AST. Server-side validation remains authoritative; execution preview is intentionally unavailable in this tranche.'
	);
	const form = element( 'form', 'wpessential-query__form' );
	form.noValidate = true;

	const sourceSelect = element( 'select' );
	sourceSelect.id = 'wpessential-query-source';
	for ( const source of bootstrap.sources ) {
		sourceSelect.append( option( source.sourceRef, source.label ) );
	}
	const sourceField = element( 'div', 'wpessential-query__field' );
	sourceField.append( labelFor( sourceSelect, 'Data source' ), sourceSelect );

	const projection = element( 'fieldset', 'wpessential-query__fieldset' );
	const projectionLegend = element(
		'legend',
		undefined,
		'Projection fields'
	);
	const projectionOptions = element( 'div', 'wpessential-query__checks' );
	projection.append( projectionLegend, projectionOptions );

	const filterField = element( 'select' );
	filterField.id = 'wpessential-query-filter-field';
	const filterOperator = element( 'select' );
	filterOperator.id = 'wpessential-query-filter-operator';
	const filterValue = element( 'input' );
	filterValue.id = 'wpessential-query-filter-value';
	filterValue.type = 'text';
	filterValue.autocomplete = 'off';
	const filterGroup = element( 'fieldset', 'wpessential-query__fieldset' );
	filterGroup.append(
		element( 'legend', undefined, 'Optional bounded filter' )
	);
	for ( const [ control, text ] of [
		[ filterField, 'Field' ],
		[ filterOperator, 'Operator' ],
		[ filterValue, 'Value' ],
	] as const ) {
		const wrapper = element( 'div', 'wpessential-query__field' );
		wrapper.append( labelFor( control, text ), control );
		filterGroup.append( wrapper );
	}

	const orderField = element( 'select' );
	orderField.id = 'wpessential-query-order-field';
	const orderDirection = element( 'select' );
	orderDirection.id = 'wpessential-query-order-direction';
	orderDirection.append(
		option( 'asc', 'Ascending' ),
		option( 'desc', 'Descending' )
	);
	const orderGroup = element( 'fieldset', 'wpessential-query__fieldset' );
	orderGroup.append( element( 'legend', undefined, 'Optional ordering' ) );
	for ( const [ control, text ] of [
		[ orderField, 'Field' ],
		[ orderDirection, 'Direction' ],
	] as const ) {
		const wrapper = element( 'div', 'wpessential-query__field' );
		wrapper.append( labelFor( control, text ), control );
		orderGroup.append( wrapper );
	}

	const pageSize = element( 'input' );
	pageSize.id = 'wpessential-query-page-size';
	pageSize.type = 'number';
	pageSize.min = '1';
	pageSize.step = '1';
	const offsetInput = element( 'input' );
	offsetInput.id = 'wpessential-query-offset';
	offsetInput.type = 'number';
	offsetInput.min = '0';
	offsetInput.step = '1';
	const pagination = element( 'fieldset', 'wpessential-query__fieldset' );
	pagination.append( element( 'legend', undefined, 'Offset pagination' ) );
	for ( const [ control, text ] of [
		[ pageSize, 'Page size' ],
		[ offsetInput, 'Offset' ],
	] as const ) {
		const wrapper = element( 'div', 'wpessential-query__field' );
		wrapper.append( labelFor( control, text ), control );
		pagination.append( wrapper );
	}

	const status = element( 'div', 'wpessential-query__status' );
	status.id = 'wpessential-query-status';
	status.setAttribute( 'role', 'status' );
	status.setAttribute( 'aria-live', 'polite' );

	const unsupported = element( 'div', 'notice notice-warning inline' );
	unsupported.id = 'wpessential-query-unsupported';
	unsupported.hidden = true;
	unsupported.setAttribute( 'role', 'note' );

	const preview = element( 'pre', 'wpessential-query__preview' );
	preview.id = 'wpessential-query-ast-preview';
	preview.tabIndex = 0;
	preview.setAttribute( 'aria-label', 'Read-only Query AST preview' );

	const executeHelp = element(
		'p',
		'description',
		'Execution preview is disabled until a separately certified admin execution boundary exists.'
	);
	executeHelp.id = 'wpessential-query-execute-help';
	const execute = element(
		'button',
		'button button-primary',
		'Preview execution unavailable'
	);
	execute.type = 'button';
	execute.disabled = true;
	execute.setAttribute( 'aria-describedby', executeHelp.id );

	form.append(
		sourceField,
		projection,
		filterGroup,
		orderGroup,
		pagination,
		unsupported,
		status,
		preview,
		execute,
		executeHelp
	);
	root.replaceChildren( title, description, form );

	const renderSourceControls = (): void => {
		const source = sourceByRef( bootstrap, state.sourceRef );
		projectionOptions.replaceChildren();
		filterField.replaceChildren( option( '', 'No filter' ) );
		orderField.replaceChildren( option( '', 'Default ordering' ) );
		filterOperator.replaceChildren( option( '', 'No operator' ) );

		if ( source === null ) {
			return;
		}

		pageSize.max = String( source.maxPageSize );
		if ( state.pageSize > source.maxPageSize ) {
			state.pageSize = source.maxPageSize;
		}
		for ( const field of source.fields ) {
			const wrapper = element( 'label', 'wpessential-query__check' );
			const checkbox = element( 'input' );
			checkbox.type = 'checkbox';
			checkbox.value = field.ref;
			checkbox.checked = state.projection.includes( field.ref );
			checkbox.dataset.wpessentialQueryProjection = field.ref;
			wrapper.append(
				checkbox,
				document.createTextNode( ` ${ field.label }` )
			);
			projectionOptions.append( wrapper );
			filterField.append( option( field.ref, field.label ) );
			orderField.append( option( field.ref, field.label ) );
		}
		for ( const predicate of SUPPORTED_FILTER_OPERATORS ) {
			if ( source.predicates.includes( predicate ) ) {
				filterOperator.append(
					option( predicate, predicate.replaceAll( '_', ' ' ) )
				);
			}
		}
		filterField.value = state.filterField;
		filterOperator.value = state.filterOperator;
		orderField.value = state.orderField;
		orderDirection.value = state.orderDirection;
		pageSize.value = String( state.pageSize );
		offsetInput.value = String( state.offset );

		const unavailable: string[] = [];
		if ( source.supportsRelations ) {
			unavailable.push(
				'Relation authoring is not exposed by this first admin scaffold.'
			);
		}
		if (
			source.predicates.some(
				( predicate ) =>
					! SUPPORTED_FILTER_OPERATORS.includes(
						predicate as ( typeof SUPPORTED_FILTER_OPERATORS )[ number ]
					)
			)
		) {
			unavailable.push(
				'Some source predicates require a later dedicated control and remain intentionally unavailable.'
			);
		}
		unsupported.textContent = unavailable.join( ' ' );
		unsupported.hidden = unavailable.length === 0;
	};

	const renderPreview = (): void => {
		const result = buildQueryAstPreview( bootstrap, state );
		if ( result.ast === null ) {
			preview.textContent = '';
			status.textContent = result.issues.join( ' ' );
			status.classList.add( 'wpessential-query__status--error' );
			return;
		}
		preview.textContent = JSON.stringify( result.ast, null, 2 );
		status.textContent =
			'Draft structure is locally well formed. Server-side Query validation is still required before save or execution.';
		status.classList.remove( 'wpessential-query__status--error' );
	};

	sourceSelect.addEventListener( 'change', () => {
		state.sourceRef = sourceSelect.value;
		const source = sourceByRef( bootstrap, state.sourceRef );
		state.projection = source?.fields[ 0 ]
			? [ source.fields[ 0 ].ref ]
			: [];
		state.filterField = '';
		state.filterOperator = '';
		state.filterValue = '';
		state.orderField = '';
		state.pageSize = Math.min( 20, source?.maxPageSize ?? 20 );
		state.offset = 0;
		renderSourceControls();
		renderPreview();
	} );

	projectionOptions.addEventListener( 'change', ( event ) => {
		const target = event.target;
		if ( ! ( target instanceof HTMLInputElement ) ) {
			return;
		}
		const ref = target.dataset.wpessentialQueryProjection;
		if ( ! ref ) {
			return;
		}
		state.projection = target.checked
			? [ ...state.projection, ref ]
			: state.projection.filter( ( value ) => value !== ref );
		renderPreview();
	} );
	filterField.addEventListener( 'change', () => {
		state.filterField = filterField.value;
		renderPreview();
	} );
	filterOperator.addEventListener( 'change', () => {
		state.filterOperator = filterOperator.value;
		renderPreview();
	} );
	filterValue.addEventListener( 'input', () => {
		state.filterValue = filterValue.value;
		renderPreview();
	} );
	orderField.addEventListener( 'change', () => {
		state.orderField = orderField.value;
		renderPreview();
	} );
	orderDirection.addEventListener( 'change', () => {
		state.orderDirection = orderDirection.value === 'desc' ? 'desc' : 'asc';
		renderPreview();
	} );
	pageSize.addEventListener( 'input', () => {
		state.pageSize = Number( pageSize.value );
		renderPreview();
	} );
	offsetInput.addEventListener( 'input', () => {
		state.offset = Number( offsetInput.value );
		renderPreview();
	} );

	renderSourceControls();
	renderPreview();
	root.dataset.wpessentialEnhanced = 'ready';
	window.dispatchEvent(
		new CustomEvent( 'wpessential:admin-ready', {
			detail: { surface: 'query', payload: bootstrap },
		} )
	);
}

function boot(): void {
	const root = document.getElementById( 'wpessential-query-root' );
	const script = document.getElementById( 'wpessential-query-bootstrap' );
	if (
		! ( root instanceof HTMLElement ) ||
		! ( script instanceof HTMLScriptElement )
	) {
		return;
	}
	try {
		const bootstrap = parseQueryBootstrap(
			JSON.parse( script.textContent ?? '{}' )
		);
		if ( bootstrap === null ) {
			root.dataset.wpessentialEnhanced = 'invalid-bootstrap';
			root.textContent =
				'Query editor is unavailable because its bootstrap contract is invalid.';
			return;
		}
		mountQueryScaffold( root, bootstrap );
	} catch {
		root.dataset.wpessentialEnhanced = 'invalid-bootstrap';
		root.textContent =
			'Query editor is unavailable because its bootstrap contract is invalid.';
	}
}

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', boot, { once: true } );
} else {
	boot();
}
