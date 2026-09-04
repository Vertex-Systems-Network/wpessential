import './columns.scss';

type JsonObject = Record< string, unknown >;

type CapabilitySet = {
	sort: boolean;
	filter: boolean;
	edit: boolean;
	export: boolean;
};

type ColumnTarget = {
	type: string;
	key: string;
	label: string;
	capabilities: CapabilitySet;
};

type ColumnSource = {
	owner: string;
	reference: string;
	label: string;
	formats: string[];
	capabilities: CapabilitySet;
};

type AdminColumnsBootstrap = {
	surface: 'columns';
	contractVersion: 1;
	targets: ColumnTarget[];
	sources: ColumnSource[];
};

type DraftColumn = {
	id: number;
	key: string;
	label: string;
	sourceReference: string;
	format: string;
	enabled: boolean;
};

type DraftView = {
	name: string;
	targetKey: string;
	columns: DraftColumn[];
};

const SAFE_FORMATS = [
	'text',
	'number',
	'date',
	'boolean',
	'image',
	'badge',
	'link',
];
const SAFE_SOURCE_OWNERS = [
	'native',
	'fields',
	'taxonomy',
	'relations',
	'media',
	'status',
	'query',
	'provider',
	'renderer',
];

function isObject( value: unknown ): value is JsonObject {
	return (
		typeof value === 'object' && value !== null && ! Array.isArray( value )
	);
}

function identifier( value: unknown ): value is string {
	return (
		typeof value === 'string' &&
		/^[a-z0-9][a-z0-9._:-]{0,191}$/.test( value )
	);
}

function label( value: unknown ): value is string {
	return (
		typeof value === 'string' &&
		value.trim() !== '' &&
		value.trim().length <= 191
	);
}

function parseCapabilities( value: unknown ): CapabilitySet | null {
	if ( ! isObject( value ) ) {
		return null;
	}
	for ( const key of [ 'sort', 'filter', 'edit', 'export' ] ) {
		if ( typeof value[ key ] !== 'boolean' ) {
			return null;
		}
	}
	return value as CapabilitySet;
}

function parseTarget( value: unknown ): ColumnTarget | null {
	if (
		! isObject( value ) ||
		! identifier( value.type ) ||
		! identifier( value.key ) ||
		! label( value.label )
	) {
		return null;
	}
	const capabilities = parseCapabilities( value.capabilities );
	if ( capabilities === null ) {
		return null;
	}
	return {
		type: value.type,
		key: value.key,
		label: value.label.trim(),
		capabilities,
	};
}

function parseSource( value: unknown ): ColumnSource | null {
	if (
		! isObject( value ) ||
		! identifier( value.owner ) ||
		! SAFE_SOURCE_OWNERS.includes( value.owner ) ||
		! identifier( value.reference ) ||
		! label( value.label ) ||
		! Array.isArray( value.formats ) ||
		! value.formats.every(
			( format ) =>
				typeof format === 'string' && SAFE_FORMATS.includes( format )
		) ||
		value.formats.length === 0
	) {
		return null;
	}
	const capabilities = parseCapabilities( value.capabilities );
	if ( capabilities === null ) {
		return null;
	}
	return {
		owner: value.owner,
		reference: value.reference,
		label: value.label.trim(),
		formats: Array.from( new Set( value.formats as string[] ) ),
		capabilities,
	};
}

export function parseAdminColumnsBootstrap(
	value: unknown
): AdminColumnsBootstrap | null {
	if (
		! isObject( value ) ||
		value.surface !== 'columns' ||
		value.contractVersion !== 1 ||
		! Array.isArray( value.targets ) ||
		! Array.isArray( value.sources )
	) {
		return null;
	}
	const targets = value.targets.map( parseTarget );
	const sources = value.sources.map( parseSource );
	if (
		targets.length === 0 ||
		sources.length === 0 ||
		targets.some( ( target ) => target === null ) ||
		sources.some( ( source ) => source === null )
	) {
		return null;
	}
	return {
		surface: 'columns',
		contractVersion: 1,
		targets: targets as ColumnTarget[],
		sources: sources as ColumnSource[],
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

function option( value: string, text: string ): HTMLOptionElement {
	const item = document.createElement( 'option' );
	item.value = value;
	item.textContent = text;
	return item;
}

function controlLabel( control: HTMLElement, text: string ): HTMLLabelElement {
	const node = element( 'label', 'wpessential-columns__label', text );
	if ( control.id ) {
		node.htmlFor = control.id;
	}
	return node;
}

function sourceByReference(
	bootstrap: AdminColumnsBootstrap,
	reference: string
): ColumnSource | null {
	return (
		bootstrap.sources.find(
			( source ) => source.reference === reference
		) ?? null
	);
}

function targetByKey(
	bootstrap: AdminColumnsBootstrap,
	key: string
): ColumnTarget | null {
	return bootstrap.targets.find( ( target ) => target.key === key ) ?? null;
}

function capabilitySummary( capabilities: CapabilitySet ): string {
	return [
		`Sort ${ capabilities.sort ? 'available' : 'unavailable' }`,
		`Filter ${ capabilities.filter ? 'available' : 'unavailable' }`,
		`Edit ${ capabilities.edit ? 'available' : 'unavailable' }`,
		`Export ${ capabilities.export ? 'available' : 'unavailable' }`,
	].join( ' · ' );
}

function initialView( bootstrap: AdminColumnsBootstrap ): DraftView {
	const firstTarget = bootstrap.targets[ 0 ];
	const firstSource = bootstrap.sources[ 0 ];
	return {
		name: '',
		targetKey: firstTarget?.key ?? '',
		columns: firstSource
			? [
					{
						id: 1,
						key: 'column_1',
						label: firstSource.label,
						sourceReference: firstSource.reference,
						format: firstSource.formats[ 0 ] ?? 'text',
						enabled: true,
					},
			  ]
			: [],
	};
}

export function mountAdminColumnsScaffold(
	root: HTMLElement,
	bootstrap: AdminColumnsBootstrap
): void {
	if ( root.dataset.wpessentialEnhanced === 'ready' ) {
		return;
	}
	root.dataset.wpessentialEnhanced = 'ready';

	let nextColumnId = 2;
	const draft = initialView( bootstrap );
	const title = element(
		'h2',
		'wpessential-columns__title',
		'Admin Columns'
	);
	const intro = element(
		'p',
		'wpessential-columns__intro',
		'Author a shared Column Set presentation. Save, Query execution and source-owner mutation remain intentionally unavailable until their certified server integrations exist.'
	);

	const stateNotice = element(
		'div',
		'notice notice-info inline wpessential-columns__state-note'
	);
	stateNotice.setAttribute( 'role', 'note' );
	stateNotice.textContent =
		'This editor changes authored shared draft state only. Personal preferences, effective runtime capabilities and diagnostics are separate read-only/state-specific contracts.';

	const nav = element( 'nav', 'wpessential-columns__nav' );
	nav.setAttribute( 'aria-label', 'Admin Columns sections' );
	for ( const [ index, section ] of [
		'Column Sets',
		'Segments',
		'Adapters',
		'Diagnostics',
	].entries() ) {
		const button = element( 'button', 'button', section );
		button.type = 'button';
		button.disabled = index !== 0;
		if ( index !== 0 ) {
			button.title = `${ section } integration is read-only or deferred in this first scaffold.`;
		}
		nav.append( button );
	}

	const form = element( 'form', 'wpessential-columns__form' );
	form.noValidate = true;
	const general = element( 'fieldset', 'wpessential-columns__section' );
	general.append( element( 'legend', undefined, 'General' ) );

	const nameInput = element( 'input' );
	nameInput.id = 'wpessential-columns-view-name';
	nameInput.type = 'text';
	nameInput.maxLength = 191;
	nameInput.autocomplete = 'off';
	const nameField = element( 'div', 'wpessential-columns__field' );
	nameField.append( controlLabel( nameInput, 'Column Set name' ), nameInput );

	const targetSelect = element( 'select' );
	targetSelect.id = 'wpessential-columns-target';
	for ( const target of bootstrap.targets ) {
		targetSelect.append( option( target.key, target.label ) );
	}
	const targetField = element( 'div', 'wpessential-columns__field' );
	targetField.append(
		controlLabel( targetSelect, 'Target adapter' ),
		targetSelect
	);
	const targetStatus = element(
		'p',
		'description wpessential-columns__capabilities'
	);
	targetStatus.id = 'wpessential-columns-target-capabilities';
	targetStatus.setAttribute( 'aria-live', 'polite' );
	general.append( nameField, targetField, targetStatus );

	const columnsSection = element(
		'fieldset',
		'wpessential-columns__section'
	);
	columnsSection.append( element( 'legend', undefined, 'Columns' ) );
	const columnsList = element( 'div', 'wpessential-columns__list' );
	const addColumn = element( 'button', 'button', 'Add Column' );
	addColumn.type = 'button';
	columnsSection.append( columnsList, addColumn );

	const querySection = element( 'fieldset', 'wpessential-columns__section' );
	querySection.append(
		element( 'legend', undefined, 'Query & Segments' ),
		element(
			'p',
			'description',
			'Sorting, filtering and search compile through Surface 6 Query. This scaffold does not invent or execute a local query engine.'
		)
	);

	const editingSection = element(
		'fieldset',
		'wpessential-columns__section'
	);
	editingSection.append(
		element( 'legend', undefined, 'Editing & Export' ),
		element(
			'p',
			'description',
			'Inline/bulk mutation must use the source owner plus Policy. Export requires a separately certified scope/redaction/formula-safety pipeline.'
		)
	);

	const diagnosticSection = element(
		'fieldset',
		'wpessential-columns__section'
	);
	diagnosticSection.append(
		element( 'legend', undefined, 'Effective state & Diagnostics' ),
		element(
			'p',
			'description',
			'Provider availability, effective capabilities, DB/remote call counts and performance evidence are read-only runtime state and are not stored as authored settings.'
		)
	);

	const status = element( 'div', 'wpessential-columns__status' );
	status.setAttribute( 'role', 'status' );
	status.setAttribute( 'aria-live', 'polite' );
	const saveHelp = element(
		'p',
		'description',
		'Save is disabled until the canonical server route, bootstrap, Policy and revisioned Definition integration are certified together.'
	);
	saveHelp.id = 'wpessential-columns-save-help';
	const save = element(
		'button',
		'button button-primary',
		'Save unavailable'
	);
	save.type = 'button';
	save.disabled = true;
	save.setAttribute( 'aria-describedby', saveHelp.id );

	form.append(
		general,
		columnsSection,
		querySection,
		editingSection,
		diagnosticSection,
		status,
		save,
		saveHelp
	);
	root.replaceChildren( title, intro, stateNotice, nav, form );

	const renderTarget = (): void => {
		const target = targetByKey( bootstrap, draft.targetKey );
		targetStatus.textContent = target
			? capabilitySummary( target.capabilities )
			: 'Target adapter unavailable. Target-specific controls remain disabled.';
	};

	const renderColumns = (): void => {
		columnsList.replaceChildren();
		if ( draft.columns.length === 0 ) {
			const empty = element(
				'p',
				'wpessential-columns__empty',
				'No Columns in this draft.'
			);
			columnsList.append( empty );
			return;
		}

		for ( const [ index, column ] of draft.columns.entries() ) {
			const card = element( 'section', 'wpessential-columns__column' );
			card.setAttribute(
				'aria-label',
				`Column ${ index + 1 }: ${ column.label || column.key }`
			);
			const heading = element(
				'h3',
				'wpessential-columns__column-title',
				`Column ${ index + 1 }`
			);

			const labelInput = element( 'input' );
			labelInput.id = `wpessential-columns-label-${ column.id }`;
			labelInput.type = 'text';
			labelInput.maxLength = 191;
			labelInput.value = column.label;
			const labelField = element( 'div', 'wpessential-columns__field' );
			labelField.append(
				controlLabel( labelInput, 'Label' ),
				labelInput
			);

			const sourceSelect = element( 'select' );
			sourceSelect.id = `wpessential-columns-source-${ column.id }`;
			for ( const source of bootstrap.sources ) {
				sourceSelect.append(
					option(
						source.reference,
						`${ source.label } — ${ source.owner }`
					)
				);
			}
			sourceSelect.value = column.sourceReference;
			const sourceField = element( 'div', 'wpessential-columns__field' );
			sourceField.append(
				controlLabel( sourceSelect, 'Source' ),
				sourceSelect
			);

			const formatSelect = element( 'select' );
			formatSelect.id = `wpessential-columns-format-${ column.id }`;
			const renderFormats = (): void => {
				const source = sourceByReference(
					bootstrap,
					column.sourceReference
				);
				formatSelect.replaceChildren();
				for ( const format of source?.formats ?? [ 'text' ] ) {
					formatSelect.append( option( format, format ) );
				}
				if ( ! ( source?.formats ?? [] ).includes( column.format ) ) {
					column.format = source?.formats[ 0 ] ?? 'text';
				}
				formatSelect.value = column.format;
			};
			renderFormats();
			const formatField = element( 'div', 'wpessential-columns__field' );
			formatField.append(
				controlLabel( formatSelect, 'Display format' ),
				formatSelect
			);

			const capabilityNote = element(
				'p',
				'description wpessential-columns__capabilities'
			);
			const renderCapabilities = (): void => {
				const source = sourceByReference(
					bootstrap,
					column.sourceReference
				);
				capabilityNote.textContent = source
					? `${ capabilitySummary(
							source.capabilities
					  ) }. Capability badges are effective state, not authorization.`
					: 'Source unavailable; no capability is inferred.';
			};
			renderCapabilities();

			const enabled = element( 'input' );
			enabled.type = 'checkbox';
			enabled.id = `wpessential-columns-enabled-${ column.id }`;
			enabled.checked = column.enabled;
			const enabledLabel = element(
				'label',
				'wpessential-columns__check',
				' Visible in shared presentation'
			);
			enabledLabel.htmlFor = enabled.id;
			enabledLabel.prepend( enabled );
			const authNote = element(
				'p',
				'description',
				'Visibility changes presentation only. It never grants or removes authorization.'
			);

			const actions = element( 'div', 'wpessential-columns__actions' );
			const up = element( 'button', 'button button-small', 'Move up' );
			up.type = 'button';
			up.disabled = index === 0;
			const down = element(
				'button',
				'button button-small',
				'Move down'
			);
			down.type = 'button';
			down.disabled = index === draft.columns.length - 1;
			const remove = element( 'button', 'button button-small', 'Remove' );
			remove.type = 'button';
			actions.append( up, down, remove );

			labelInput.addEventListener( 'input', () => {
				column.label = labelInput.value;
				status.textContent =
					'Local authored draft changed. No server mutation occurred.';
			} );
			sourceSelect.addEventListener( 'change', () => {
				column.sourceReference = sourceSelect.value;
				renderFormats();
				renderCapabilities();
				status.textContent =
					'Source selection changed locally; owner validation remains server-authoritative.';
			} );
			formatSelect.addEventListener( 'change', () => {
				column.format = formatSelect.value;
				status.textContent = 'Display format changed locally.';
			} );
			enabled.addEventListener( 'change', () => {
				column.enabled = enabled.checked;
				status.textContent =
					'Presentation visibility changed locally; authorization was not changed.';
			} );
			up.addEventListener( 'click', () => {
				if ( index > 0 ) {
					[ draft.columns[ index - 1 ], draft.columns[ index ] ] = [
						draft.columns[ index ],
						draft.columns[ index - 1 ],
					];
					renderColumns();
				}
			} );
			down.addEventListener( 'click', () => {
				if ( index < draft.columns.length - 1 ) {
					[ draft.columns[ index ], draft.columns[ index + 1 ] ] = [
						draft.columns[ index + 1 ],
						draft.columns[ index ],
					];
					renderColumns();
				}
			} );
			remove.addEventListener( 'click', () => {
				draft.columns = draft.columns.filter(
					( candidate ) => candidate.id !== column.id
				);
				renderColumns();
				status.textContent =
					'Column removed from the local authored draft only.';
			} );

			card.append(
				heading,
				labelField,
				sourceField,
				formatField,
				capabilityNote,
				enabledLabel,
				authNote,
				actions
			);
			columnsList.append( card );
		}
	};

	nameInput.addEventListener( 'input', () => {
		draft.name = nameInput.value;
		status.textContent =
			'Local authored draft changed. No server mutation occurred.';
	} );
	targetSelect.addEventListener( 'change', () => {
		draft.targetKey = targetSelect.value;
		renderTarget();
		status.textContent =
			'Target changed locally. Server capability re-evaluation is still required before persistence.';
	} );
	addColumn.addEventListener( 'click', () => {
		const source = bootstrap.sources[ 0 ];
		if ( ! source || draft.columns.length >= 100 ) {
			status.textContent =
				'No certified source is available or the bounded 100-Column draft limit was reached.';
			return;
		}
		draft.columns.push( {
			id: nextColumnId++,
			key: `column_${ nextColumnId }`,
			label: source.label,
			sourceReference: source.reference,
			format: source.formats[ 0 ] ?? 'text',
			enabled: true,
		} );
		renderColumns();
		status.textContent = 'Column added to the local authored draft only.';
	} );
	form.addEventListener( 'submit', ( event ) => event.preventDefault() );

	renderTarget();
	renderColumns();
	status.textContent =
		'Non-runtime scaffold ready. No data has been saved or executed.';
}
