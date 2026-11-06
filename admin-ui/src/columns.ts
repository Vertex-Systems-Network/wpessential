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

type ColumnLayoutDraft = {
	width: string;
	min_width: string;
	max_width: string;
	alignment: string;
	responsive_priority: string;
	sticky: boolean;
};

type DraftColumn = {
	id: number;
	key: string;
	label: string;
	sourceReference: string;
	format: string;
	enabled: boolean;
	layout: ColumnLayoutDraft;
};

type JsonValue = Record< string, unknown >;

/** View-level authored state the settings panel edits (matches the runtime editor session). */
type ViewSettingsState = {
	assignment: JsonValue | undefined;
	layout: JsonValue | undefined;
	visibility: JsonValue | undefined;
};

function defaultColumnLayout(): ColumnLayoutDraft {
	return {
		width: '',
		min_width: '',
		max_width: '',
		alignment: 'left',
		responsive_priority: '50',
		sticky: false,
	};
}

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
						layout: defaultColumnLayout(),
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
					const current = draft.columns[ index ];
					const previous = draft.columns[ index - 1 ];
					if ( current === undefined || previous === undefined ) {
						return;
					}
					draft.columns[ index - 1 ] = current;
					draft.columns[ index ] = previous;
					renderColumns();
				}
			} );
			down.addEventListener( 'click', () => {
				if ( index < draft.columns.length - 1 ) {
					const current = draft.columns[ index ];
					const next = draft.columns[ index + 1 ];
					if ( current === undefined || next === undefined ) {
						return;
					}
					draft.columns[ index ] = next;
					draft.columns[ index + 1 ] = current;
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
				columnLayoutControls( column ),
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
			layout: defaultColumnLayout(),
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

function numberControl(
	id: string,
	text: string,
	value: string,
	min: number,
	max: number
): HTMLDivElement {
	const input = element( 'input' );
	input.id = id;
	input.type = 'number';
	input.min = String( min );
	input.max = String( max );
	input.value = value;
	const field = element( 'div', 'wpessential-columns__field' );
	field.append( controlLabel( input, text ), input );
	return field;
}

function selectControl(
	id: string,
	text: string,
	value: string,
	choices: [ string, string ][]
): HTMLDivElement {
	const select = element( 'select' );
	select.id = id;
	for ( const [ choice, choiceText ] of choices ) {
		select.append( option( choice, choiceText ) );
	}
	select.value = value;
	const field = element( 'div', 'wpessential-columns__field' );
	field.append( controlLabel( select, text ), select );
	return field;
}

function checkControl(
	id: string,
	text: string,
	checked: boolean
): HTMLLabelElement {
	const input = element( 'input' );
	input.type = 'checkbox';
	input.id = id;
	input.checked = checked;
	const label = element( 'label', 'wpessential-columns__check', ` ${ text }` );
	label.htmlFor = id;
	label.prepend( input );
	return label;
}

/** Width, alignment, responsive priority and sticky for one column (Penpot 13b · Column layout). */
function columnLayoutControls( column: DraftColumn ): HTMLDetailsElement {
	const box = element( 'details', 'wpessential-columns__layout' );
	box.append( element( 'summary', undefined, 'Width & alignment' ) );
	const id = ( key: string ): string =>
		`wpessential-columns-layout-${ key }-${ column.id }`;
	box.append(
		numberControl( id( 'width' ), 'Width (px)', column.layout.width, 40, 2000 ),
		numberControl( id( 'min_width' ), 'Minimum width (px)', column.layout.min_width, 40, 2000 ),
		numberControl( id( 'max_width' ), 'Maximum width (px)', column.layout.max_width, 40, 2000 ),
		selectControl( id( 'alignment' ), 'Alignment', column.layout.alignment, [
			[ 'left', 'Left' ],
			[ 'center', 'Center' ],
			[ 'right', 'Right' ],
		] ),
		numberControl(
			id( 'responsive_priority' ),
			'Responsive priority (0–100, higher stays visible longer)',
			column.layout.responsive_priority,
			0,
			100
		),
		checkControl( id( 'sticky' ), 'Keep this column visible while scrolling sideways', column.layout.sticky )
	);
	for ( const control of box.querySelectorAll< HTMLInputElement | HTMLSelectElement >( 'input, select' ) ) {
		const key = control.id.replace( 'wpessential-columns-layout-', '' ).replace( `-${ column.id }`, '' ) as keyof ColumnLayoutDraft;
		control.dataset.wpessentialColumnsLayout = key;
		control.addEventListener( 'input', () => {
			if ( key === 'sticky' ) {
				column.layout.sticky = ( control as HTMLInputElement ).checked;
			} else {
				column.layout[ key ] = control.value;
			}
		} );
		control.addEventListener( 'change', () => control.dispatchEvent( new Event( 'input' ) ) );
	}
	return box;
}

function layoutControl( card: HTMLElement, key: string ): HTMLInputElement | HTMLSelectElement | null {
	return card.querySelector< HTMLInputElement | HTMLSelectElement >(
		`[data-wpessential-columns-layout="${ key }"]`
	);
}

/** The column layout map for the save payload, or undefined when every value is the default. */
export function readColumnLayout( card: HTMLElement ): JsonValue | undefined {
	// Whole numbers only: a typed 120.5 or 12px must stop the save, never round silently.
	const whole = ( key: string, label: string ): number | null => {
		const control = layoutControl( card, key );
		// A number input reports unparseable text (for example "1e") as an empty value.
		if ( control instanceof HTMLInputElement && control.validity.badInput ) {
			throw new Error( `${ label } must be a whole number.` );
		}
		const raw = ( control?.value ?? '' ).trim();
		if ( raw === '' ) {
			return null;
		}
		if ( ! /^\d+$/.test( raw ) ) {
			throw new Error( `${ label } must be a whole number.` );
		}
		return Number.parseInt( raw, 10 );
	};
	const width = ( key: string ): number | null =>
		whole( key, key.replace( '_', ' ' ).replace( /^./, ( c ) => c.toUpperCase() ) );
	const priority = whole( 'responsive_priority', 'Responsive priority' );
	const layout = {
		width: width( 'width' ),
		min_width: width( 'min_width' ),
		max_width: width( 'max_width' ),
		alignment: layoutControl( card, 'alignment' )?.value || 'left',
		responsive_priority: priority ?? 50,
		sticky: ( layoutControl( card, 'sticky' ) as HTMLInputElement | null )?.checked ?? false,
	};
	const isDefault =
		layout.width === null &&
		layout.min_width === null &&
		layout.max_width === null &&
		layout.alignment === 'left' &&
		layout.responsive_priority === 50 &&
		! layout.sticky;
	return isDefault ? undefined : layout;
}

/** Puts a saved column layout into the card controls (and its draft, through the input events). */
export function writeColumnLayout( card: HTMLElement, layout: JsonValue | undefined ): void {
	const values = layout ?? {};
	for ( const key of [ 'width', 'min_width', 'max_width', 'alignment', 'responsive_priority' ] ) {
		const control = layoutControl( card, key );
		const value = values[ key ];
		if ( control ) {
			control.value =
				typeof value === 'number' || typeof value === 'string'
					? String( value )
					: key === 'alignment'
					? 'left'
					: key === 'responsive_priority'
					? '50'
					: '';
			control.dispatchEvent( new Event( 'input' ) );
		}
	}
	const sticky = layoutControl( card, 'sticky' );
	if ( sticky instanceof HTMLInputElement ) {
		sticky.checked = values.sticky === true;
		sticky.dispatchEvent( new Event( 'input' ) );
	}
}

function listParts( raw: string ): string[] {
	return raw
		.split( ',' )
		.map( ( part ) => part.trim() )
		.filter( ( part ) => part !== '' );
}

function idList( raw: string, numeric: boolean ): ( string | number )[] {
	const parts = listParts( raw );
	// Invalid user IDs are kept out here and reported by assertViewSettings() before saving.
	return numeric
		? parts.filter( ( part ) => /^[1-9]\d*$/.test( part ) ).map( ( part ) => Number.parseInt( part, 10 ) )
		: parts;
}

/** Stops a save when the assigned user IDs contain anything but whole positive numbers. */
export function assertViewSettings(): void {
	const users = document.getElementById( 'wpessential-columns-assign-users' );
	const raw = users instanceof HTMLInputElement ? users.value : '';
	const invalid = listParts( raw ).filter( ( part ) => ! /^[1-9]\d*$/.test( part ) );
	if ( invalid.length > 0 ) {
		throw new Error( `Assigned user IDs must be whole numbers: ${ invalid.join( ', ' ) }.` );
	}
}

/**
 * Table layout, who the Column Set is assigned to and its visibility (Penpot 13a · View settings).
 * Values are written to state only after the user changes a control, so untouched views keep the server defaults.
 */
export function mountViewSettings( root: HTMLElement, state: ViewSettingsState ): void {
	const general = root.querySelector( '.wpessential-columns__section' );
	if ( ! general || document.getElementById( 'wpessential-columns-view-settings' ) ) {
		return;
	}
	const section = element( 'fieldset', 'wpessential-columns__section wpessential-columns__view-settings' );
	section.id = 'wpessential-columns-view-settings';
	section.append( element( 'legend', undefined, 'Table layout & assignment' ) );
	section.append(
		selectControl( 'wpessential-columns-density', 'Row density', 'comfortable', [
			[ 'comfortable', 'Comfortable' ],
			[ 'compact', 'Compact' ],
		] ),
		checkControl( 'wpessential-columns-sticky-header', 'Keep the header visible while scrolling', false ),
		checkControl( 'wpessential-columns-horizontal-scroll', 'Scroll sideways when the columns do not fit', true ),
		textField( 'wpessential-columns-assign-roles', 'Assigned roles', 'Comma-separated role keys, for example editor, author. Empty means everyone.' ),
		textField( 'wpessential-columns-assign-users', 'Assigned user IDs', 'Comma-separated user IDs. Empty means no single-user assignment.' ),
		selectControl( 'wpessential-columns-visibility-mode', 'Column Set visibility', 'visible', [
			[ 'visible', 'Visible' ],
			[ 'hidden', 'Hidden' ],
		] ),
		textField( 'wpessential-columns-visibility-reason', 'Reason when hidden', 'Shown to admins so they know why this Column Set is hidden.' ),
		element( 'p', 'description', 'Assignment and visibility change who sees this presentation. They never grant or remove permission to the content.' )
	);
	general.after( section );

	const value = ( id: string ): string =>
		( document.getElementById( id ) as HTMLInputElement | HTMLSelectElement | null )?.value ?? '';
	const checked = ( id: string ): boolean =>
		( document.getElementById( id ) as HTMLInputElement | null )?.checked ?? false;
	const update = (): void => {
		state.layout = {
			density: value( 'wpessential-columns-density' ) || 'comfortable',
			sticky_header: checked( 'wpessential-columns-sticky-header' ),
			horizontal_scroll: checked( 'wpessential-columns-horizontal-scroll' ),
		};
		const capabilities = Array.isArray( state.assignment?.capabilities ) ? state.assignment.capabilities : [];
		state.assignment = {
			roles: idList( value( 'wpessential-columns-assign-roles' ), false ),
			users: idList( value( 'wpessential-columns-assign-users' ), true ),
			capabilities,
		};
		const reason = value( 'wpessential-columns-visibility-reason' ).trim();
		state.visibility = {
			mode: value( 'wpessential-columns-visibility-mode' ) || 'visible',
			reason: reason === '' ? null : reason,
		};
	};
	section.addEventListener( 'input', update );
	section.addEventListener( 'change', update );
}

function textField( id: string, text: string, help: string ): HTMLDivElement {
	const input = element( 'input' );
	input.id = id;
	input.type = 'text';
	input.autocomplete = 'off';
	const field = element( 'div', 'wpessential-columns__field' );
	field.append( controlLabel( input, text ), input, element( 'p', 'description', help ) );
	return field;
}

/** Shows a loaded view's layout, assignment and visibility in the settings panel without marking it changed. */
export function syncViewSettings( state: ViewSettingsState ): void {
	const set = ( id: string, raw: string ): void => {
		const control = document.getElementById( id ) as HTMLInputElement | HTMLSelectElement | null;
		if ( control ) {
			control.value = raw;
		}
	};
	const tick = ( id: string, on: boolean ): void => {
		const control = document.getElementById( id ) as HTMLInputElement | null;
		if ( control ) {
			control.checked = on;
		}
	};
	const layout = state.layout ?? {};
	set( 'wpessential-columns-density', typeof layout.density === 'string' ? layout.density : 'comfortable' );
	tick( 'wpessential-columns-sticky-header', layout.sticky_header === true );
	tick( 'wpessential-columns-horizontal-scroll', layout.horizontal_scroll !== false );
	const assignment = state.assignment ?? {};
	set( 'wpessential-columns-assign-roles', Array.isArray( assignment.roles ) ? assignment.roles.join( ', ' ) : '' );
	set( 'wpessential-columns-assign-users', Array.isArray( assignment.users ) ? assignment.users.join( ', ' ) : '' );
	const visibility = state.visibility ?? {};
	set( 'wpessential-columns-visibility-mode', typeof visibility.mode === 'string' ? visibility.mode : 'visible' );
	set( 'wpessential-columns-visibility-reason', typeof visibility.reason === 'string' ? visibility.reason : '' );
}
