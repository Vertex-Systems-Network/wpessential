// Field Group settings and per-field settings (Penpot page 10 · Fields, boards F5 and 14b).
// Only keys that FieldGroupDefinitionNormalizer, FieldDefinitionNormalizer or FieldValueNormalizer read are offered.
type JsonObject = Record< string, unknown >;

// Runtime post-meta binding V1 (FieldGroupPostTypeTargetCompiler) resolves only these sources.
const LOCATION_SOURCES: [ string, string ][] = [
	[ 'post_type', 'Post type' ],
	[ 'post_status', 'Post status' ],
	[ 'entity_id', 'Specific post ID' ],
];
const LOCATION_OPERATORS: [ string, string ][] = [
	[ 'equals', 'is' ],
	[ 'not_equals', 'is not' ],
	[ 'in', 'is one of' ],
	[ 'not_in', 'is none of' ],
];
// FieldGroupRuntimeStorageProjection binds native post meta only; other stored modes are shown but flagged.
const STORAGE_MODES: [ string, string ][] = [ [ 'native_post_meta', 'Post meta' ] ];
const STRING_TYPES = [ 'text', 'textarea' ];
// FieldValueNormalizer trims these by default; other string types keep spaces unless trim is set.
const TRIM_BY_DEFAULT = [ 'text', 'email', 'url' ];

function el< K extends keyof HTMLElementTagNameMap >(
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

function selectBox(
	id: string,
	choices: [ string, string ][],
	value: string
): HTMLSelectElement {
	const box = el( 'select' );
	box.id = id;
	for ( const [ choice, text ] of choices ) {
		const item = el( 'option', undefined, text );
		item.value = choice;
		box.append( item );
	}
	box.value = value;
	return box;
}

function labelled( text: string, control: HTMLElement, help?: string ): HTMLLabelElement {
	const label = el( 'label', 'wpessential-fields-setting' );
	label.append( el( 'span', undefined, text ), control );
	if ( help ) {
		label.append( el( 'span', 'description', help ) );
	}
	return label;
}

function tickBox( id: string, text: string, checked: boolean ): HTMLLabelElement {
	const box = el( 'input' );
	box.type = 'checkbox';
	box.id = id;
	box.checked = checked;
	const label = el( 'label', 'wpessential-fields-setting wpessential-fields-setting--check' );
	label.append( box, document.createTextNode( ` ${ text }` ) );
	return label;
}

function value( id: string ): string {
	const node = document.getElementById( id );
	return node instanceof HTMLInputElement || node instanceof HTMLSelectElement ? node.value : '';
}

function ticked( id: string ): boolean {
	const node = document.getElementById( id );
	return node instanceof HTMLInputElement && node.checked;
}

function setValue( id: string, next: string ): void {
	const node = document.getElementById( id );
	if ( node instanceof HTMLInputElement || node instanceof HTMLSelectElement ) {
		node.value = next;
	}
}

function setTicked( id: string, next: boolean ): void {
	const node = document.getElementById( id );
	if ( node instanceof HTMLInputElement ) {
		node.checked = next;
	}
}

function isObject( candidate: unknown ): candidate is JsonObject {
	return typeof candidate === 'object' && candidate !== null && ! Array.isArray( candidate );
}

function text( object: JsonObject, key: string, fallback: string ): string {
	const raw = object[ key ];
	return typeof raw === 'string' ? raw : fallback;
}

function locationRow( rule: JsonObject ): HTMLDivElement {
	const row = el( 'div', 'wpessential-fields-location-rule' );
	const source = selectBox( '', LOCATION_SOURCES, text( rule, 'source', 'post_type' ) );
	source.dataset.wpessentialFieldsRule = 'source';
	source.setAttribute( 'aria-label', 'Rule source' );
	const operator = selectBox( '', LOCATION_OPERATORS, text( rule, 'operator', 'equals' ) );
	operator.dataset.wpessentialFieldsRule = 'operator';
	operator.setAttribute( 'aria-label', 'Rule operator' );
	const input = el( 'input' );
	input.type = 'text';
	input.dataset.wpessentialFieldsRule = 'value';
	input.setAttribute( 'aria-label', 'Rule value' );
	input.placeholder = 'book (comma-separated for "is one of")';
	const raw = rule.value;
	input.value = Array.isArray( raw ) ? raw.join( ', ' ) : raw === undefined || raw === null ? '' : String( raw );
	const negate = el( 'input' );
	negate.type = 'checkbox';
	negate.dataset.wpessentialFieldsRule = 'negate';
	negate.checked = rule.negate === true;
	const negateLabel = el( 'label', undefined, ' Negate' );
	negateLabel.prepend( negate );
	const and = el( 'button', 'button button-small', 'AND' );
	and.type = 'button';
	and.dataset.wpessentialFieldsRuleAction = 'and';
	const remove = el( 'button', 'button-link', 'Remove' );
	remove.type = 'button';
	remove.dataset.wpessentialFieldsRuleAction = 'remove';
	row.append( source, operator, input, negateLabel, and, remove );
	return row;
}

function locationGroup( rules: JsonObject[] ): HTMLDivElement {
	const group = el( 'div', 'wpessential-fields-location-group' );
	for ( const rule of rules.length > 0 ? rules : [ {} ] ) {
		group.append( locationRow( rule ) );
	}
	return group;
}

function renderLocations( host: HTMLElement, locations: unknown ): void {
	host.replaceChildren();
	const groups = Array.isArray( locations ) ? locations : [];
	for ( const group of groups ) {
		const rules = Array.isArray( group ) ? group.filter( isObject ) : [];
		host.append( locationGroup( rules ) );
	}
}

function ruleValue( operator: string, raw: string ): unknown {
	const parts = raw
		.split( ',' )
		.map( ( part ) => part.trim() )
		.filter( ( part ) => part !== '' );
	if ( operator === 'in' || operator === 'not_in' ) {
		return parts;
	}
	return parts[ 0 ] ?? '';
}

/** True when the rule selects post types (is / is one of, or a negated is not / is none of). */
function isPostTypeAnchor( rule: JsonObject ): boolean {
	if ( rule.source !== 'post_type' ) {
		return false;
	}
	const positive = rule.operator === 'equals' || rule.operator === 'in';
	return rule.negate === true ? ! positive : positive;
}

function isEmptyValue( raw: unknown ): boolean {
	return Array.isArray( raw ) ? raw.length === 0 : raw === '';
}

function collectLocations( host: HTMLElement ): JsonObject[][] {
	const groups: JsonObject[][] = [];
	for ( const group of host.querySelectorAll< HTMLElement >( '.wpessential-fields-location-group' ) ) {
		const rules: JsonObject[] = [];
		for ( const row of group.querySelectorAll< HTMLElement >( '.wpessential-fields-location-rule' ) ) {
			const part = ( key: string ): HTMLInputElement | HTMLSelectElement | null =>
				row.querySelector( `[data-wpessential-fields-rule="${ key }"]` );
			const operator = part( 'operator' )?.value ?? 'equals';
			rules.push( {
				source: part( 'source' )?.value ?? 'post_type',
				operator,
				value: ruleValue( operator, part( 'value' )?.value ?? '' ),
				negate: ( part( 'negate' ) as HTMLInputElement | null )?.checked ?? false,
			} );
		}
		if ( rules.length > 0 ) {
			// Checked here because the server accepts these but runtime binding rejects them.
			if ( rules.some( ( rule ) => isEmptyValue( rule.value ) ) ) {
				throw new Error( 'Every location rule needs a value. Fill it in or remove the rule.' );
			}
			if ( ! rules.some( isPostTypeAnchor ) ) {
				throw new Error(
					'Each rule group needs a "Post type is" or "Post type is one of" rule so the fields know where to appear.'
				);
			}
			groups.push( rules );
		}
	}
	return groups;
}

/** Adds the group settings panel (presentation, storage, revisions, location rules) before the field list. */
export function mountGroupSettings( form: HTMLElement ): void {
	if ( document.getElementById( 'wpessential-fields-group-settings' ) ) {
		return;
	}
	const anchor = form.querySelector( '.wpessential-fields-list-heading' );
	const panel = el( 'fieldset', 'wpessential-fields-group-settings' );
	panel.id = 'wpessential-fields-group-settings';
	panel.append( el( 'legend', undefined, 'Group settings' ) );

	const presentation = el( 'div', 'wpessential-fields-settings-grid' );
	presentation.append(
		labelled(
			'Panel style',
			selectBox( 'wpessential-fields-panel-style', [
				[ 'standard', 'Standard box' ],
				[ 'seamless', 'Seamless (no box)' ],
				[ 'sectioned', 'Sectioned' ],
			], 'standard' )
		),
		labelled(
			'Label placement',
			selectBox( 'wpessential-fields-label-placement', [
				[ 'top', 'Above the input' ],
				[ 'left', 'Left of the input' ],
			], 'top' )
		),
		labelled(
			'Instruction placement',
			selectBox( 'wpessential-fields-instruction-placement', [
				[ 'below_label', 'Below the label' ],
				[ 'below_input', 'Below the input' ],
			], 'below_label' )
		),
		labelled(
			'Storage',
			selectBox( 'wpessential-fields-storage-mode', STORAGE_MODES, 'native_post_meta' ),
			'Where values are saved. Post meta is the storage the runtime binds today.'
		),
		tickBox( 'wpessential-fields-collapsible', 'Editors can collapse this group', false ),
		tickBox( 'wpessential-fields-collapsed', 'Start collapsed', false ),
		tickBox( 'wpessential-fields-revisions', 'Keep values in post revisions', false )
	);

	const locations = el( 'div', 'wpessential-fields-locations' );
	locations.id = 'wpessential-fields-locations';
	const addGroup = el( 'button', 'button', 'Add rule group (OR)' );
	addGroup.type = 'button';
	addGroup.addEventListener( 'click', () => locations.append( locationGroup( [] ) ) );
	locations.addEventListener( 'click', ( event ) => {
		const target = event.target;
		if ( ! ( target instanceof HTMLButtonElement ) ) {
			return;
		}
		const row = target.closest( '.wpessential-fields-location-rule' );
		const group = target.closest( '.wpessential-fields-location-group' );
		if ( target.dataset.wpessentialFieldsRuleAction === 'and' && row ) {
			row.after( locationRow( {} ) );
		}
		if ( target.dataset.wpessentialFieldsRuleAction === 'remove' && row ) {
			row.remove();
			if ( group && group.children.length === 0 ) {
				group.remove();
			}
		}
	} );

	panel.append(
		presentation,
		el( 'h4', undefined, 'Show this group when' ),
		el(
			'p',
			'description',
			'Rules in one group must all match (AND). Any matching group shows the fields (OR). No rules means the group is not placed anywhere yet.'
		),
		locations,
		addGroup
	);
	if ( anchor ) {
		anchor.before( panel );
	} else {
		form.prepend( panel );
	}

	const collapsible = document.getElementById( 'wpessential-fields-collapsible' );
	collapsible?.addEventListener( 'change', () => {
		const collapsed = document.getElementById( 'wpessential-fields-collapsed' );
		if ( collapsed instanceof HTMLInputElement ) {
			collapsed.disabled = ! ticked( 'wpessential-fields-collapsible' );
			if ( collapsed.disabled ) {
				collapsed.checked = false;
			}
		}
	} );
}

/** Fills the group settings panel from a saved payload (or the defaults for a new group). */
export function setGroupSettings( payload: JsonObject ): void {
	const presentation = isObject( payload.presentation ) ? payload.presentation : {};
	setValue( 'wpessential-fields-panel-style', text( presentation, 'panel_style', 'standard' ) );
	setValue( 'wpessential-fields-label-placement', text( presentation, 'label_placement', 'top' ) );
	setValue( 'wpessential-fields-instruction-placement', text( presentation, 'instruction_placement', 'below_label' ) );
	setTicked( 'wpessential-fields-collapsible', presentation.collapsible === true );
	setTicked( 'wpessential-fields-collapsed', presentation.collapsed === true );
	const collapsed = document.getElementById( 'wpessential-fields-collapsed' );
	if ( collapsed instanceof HTMLInputElement ) {
		collapsed.disabled = presentation.collapsible !== true;
	}
	const storage = isObject( payload.storage ) ? payload.storage : {};
	const mode = text( storage, 'mode', 'native_post_meta' );
	const storageBox = document.getElementById( 'wpessential-fields-storage-mode' );
	if ( storageBox instanceof HTMLSelectElement ) {
		storageBox.querySelector( '[data-wpessential-fields-stored-mode]' )?.remove();
		// Keep a stored mode visible so editing never changes it silently.
		if ( ! STORAGE_MODES.some( ( [ choice ] ) => choice === mode ) ) {
			const stored = el( 'option', undefined, `${ mode } (not bound at runtime yet)` );
			stored.value = mode;
			stored.dataset.wpessentialFieldsStoredMode = '';
			storageBox.append( stored );
		}
		storageBox.value = mode;
	}
	setTicked( 'wpessential-fields-revisions', payload.revision_policy === 'enabled' );
	const host = document.getElementById( 'wpessential-fields-locations' );
	if ( host ) {
		renderLocations( host, payload.locations );
	}
}

/** The group settings as payload keys. Throws with a message the editor shows when a value cannot work. */
export function collectGroupSettings(): JsonObject {
	const host = document.getElementById( 'wpessential-fields-locations' );
	const invalid = document.querySelector( '.wpessential-fields-row-settings [aria-invalid="true"]' );
	if ( invalid instanceof HTMLInputElement ) {
		throw new Error( `${ invalid.dataset.wpessentialFieldsLabel ?? 'A field setting' } must be a whole number.` );
	}
	const locations = host ? collectLocations( host ) : [];
	if ( locations.length === 0 && value( 'wpessential-fields-status' ) === 'published' ) {
		throw new Error( 'Add at least one location rule before publishing, so the fields have somewhere to appear.' );
	}
	return {
		presentation: {
			panel_style: value( 'wpessential-fields-panel-style' ) || 'standard',
			label_placement: value( 'wpessential-fields-label-placement' ) || 'top',
			instruction_placement: value( 'wpessential-fields-instruction-placement' ) || 'below_label',
			collapsible: ticked( 'wpessential-fields-collapsible' ),
			collapsed: ticked( 'wpessential-fields-collapsible' ) && ticked( 'wpessential-fields-collapsed' ),
		},
		storage: { mode: value( 'wpessential-fields-storage-mode' ) || 'unconfigured' },
		revision_policy: ticked( 'wpessential-fields-revisions' ) ? 'enabled' : 'disabled',
		locations,
	};
}

function settingsOf( field: JsonObject ): JsonObject {
	if ( ! isObject( field.settings ) ) {
		field.settings = {};
	}
	return field.settings as JsonObject;
}

/** Parses a number input; null means the text is not a valid number of that kind. */
function optionalNumber( raw: string, integer: boolean ): number | undefined | null {
	const trimmed = raw.trim();
	if ( trimmed === '' ) {
		return undefined;
	}
	if ( integer ? ! /^\d+$/.test( trimmed ) : ! Number.isFinite( Number( trimmed ) ) ) {
		return null;
	}
	return integer ? Number.parseInt( trimmed, 10 ) : Number( trimmed );
}

/**
 * Settings for one field: required, REST, the type's value rules and cloning.
 * Changes are written straight into the field object the editor saves.
 */
export function fieldSettingsPanel( field: JsonObject, index: number ): HTMLDetailsElement {
	const type = typeof field.type === 'string' ? field.type : '';
	const settings = settingsOf( field );
	const repeat = isObject( field.repeatability ) ? field.repeatability : {};
	const id = ( key: string ): string => `wpessential-fields-${ index }-${ key }`;
	const box = el( 'details', 'wpessential-fields-row-settings' );
	box.append( el( 'summary', undefined, 'Field settings' ) );
	const grid = el( 'div', 'wpessential-fields-settings-grid' );
	box.append( grid );

	const bindTick = ( key: string, label: string, checked: boolean, write: ( on: boolean ) => void ): void => {
		const control = tickBox( id( key ), label, checked );
		control.querySelector( 'input' )?.addEventListener( 'change', ( event ) => {
			write( ( event.target as HTMLInputElement ).checked );
		} );
		grid.append( control );
	};
	const bindNumber = (
		key: string,
		label: string,
		current: unknown,
		integer: boolean,
		write: ( next: number | undefined ) => void
	): void => {
		const input = el( 'input' );
		input.type = 'number';
		input.id = id( key );
		input.step = integer ? '1' : 'any';
		input.value = typeof current === 'number' ? String( current ) : '';
		input.dataset.wpessentialFieldsLabel = label;
		input.addEventListener( 'input', () => {
			// Unparseable text such as "1e" reads as an empty value, so badInput is checked too.
			const parsed = input.validity.badInput ? null : optionalNumber( input.value, integer );
			// A bad value is flagged and blocks the save; the last good value is never replaced by a guess.
			input.setAttribute( 'aria-invalid', parsed === null ? 'true' : 'false' );
			if ( parsed !== null ) {
				write( parsed );
			}
		} );
		grid.append( labelled( label, input ) );
	};
	const setting = ( key: string ) => ( next: unknown ): void => {
		if ( next === undefined || next === false ) {
			delete settings[ key ];
		} else {
			settings[ key ] = next;
		}
	};

	bindTick( 'required', 'Required', settings.required === true, setting( 'required' ) );
	bindTick( 'rest', 'Show in the REST API', field.show_in_rest === true, ( on ) => {
		field.show_in_rest = on;
	} );

	if ( STRING_TYPES.includes( type ) ) {
		const trimDefault = TRIM_BY_DEFAULT.includes( type );
		bindTick( 'trim', 'Trim spaces from the value', typeof settings.trim === 'boolean' ? settings.trim : trimDefault, ( on ) => {
			settings.trim = on;
		} );
		bindNumber( 'min_length', 'Minimum length', settings.min_length, true, setting( 'min_length' ) );
		bindNumber( 'max_length', 'Maximum length', settings.max_length ?? settings.maxlength, true, ( next ) => {
			delete settings.maxlength;
			setting( 'max_length' )( next );
		} );
	}
	if ( type === 'number' ) {
		bindTick( 'integer', 'Whole numbers only', settings.integer === true, setting( 'integer' ) );
		bindNumber( 'min', 'Minimum value', settings.min, false, setting( 'min' ) );
		bindNumber( 'max', 'Maximum value', settings.max, false, setting( 'max' ) );
	}
	if ( type === 'date' ) {
		const precision = selectBox( id( 'precision' ), [
			[ 'day', 'Day (YYYY-MM-DD)' ],
			[ 'week', 'Week (YYYY-Www)' ],
			[ 'month', 'Month (YYYY-MM)' ],
		], text( settings, 'precision', text( settings, 'control_mode', 'day' ) ) );
		precision.addEventListener( 'change', () => {
			delete settings.control_mode;
			setting( 'precision' )( precision.value === 'day' ? undefined : precision.value );
		} );
		grid.append( labelled( 'Date precision', precision ) );
	}

	// Cloning: the normalizer reads these top-level keys and folds them into `repeatability`.
	const clone = ( key: string, fallback: unknown ): unknown => ( key in field ? field[ key ] : fallback );
	const cloning = clone( 'cloneable', repeat.enabled ) === true;
	// Reordering needs cloning, so the sortable box follows the cloneable box.
	const syncSortable = ( on: boolean ): void => {
		const sortable = grid.querySelector< HTMLInputElement >( `#${ id( 'sortable' ) }` );
		if ( sortable ) {
			sortable.disabled = ! on;
			if ( ! on ) {
				sortable.checked = false;
			}
		}
	};
	bindTick( 'cloneable', 'Allow several values (clone)', cloning, ( on ) => {
		field.cloneable = on;
		if ( ! on ) {
			field.sortable = false;
		}
		syncSortable( on );
	} );
	bindTick( 'sortable', 'Editors can reorder clones', cloning && clone( 'sortable', repeat.sortable ) === true, ( on ) => {
		field.sortable = on;
	} );
	syncSortable( cloning );
	bindNumber( 'min_clones', 'Minimum clones', clone( 'min_clones', repeat.min ), true, ( next ) => {
		field.min_clones = next ?? 0;
	} );
	bindNumber( 'max_clones', 'Maximum clones', clone( 'max_clones', repeat.max ), true, ( next ) => {
		field.max_clones = next ?? null;
	} );
	bindTick( 'clone_default', 'New clones start with the default value', clone( 'clone_default', repeat.clone_default ) === true, ( on ) => {
		field.clone_default = on;
	} );
	bindTick( 'clone_as_multiple', 'Store each clone as its own meta row', clone( 'clone_as_multiple', repeat.store_as_multiple ) === true, ( on ) => {
		field.clone_as_multiple = on;
	} );
	bindTick( 'clone_empty_start', 'Start with no clones', clone( 'clone_empty_start', repeat.empty_start ) === true, ( on ) => {
		field.clone_empty_start = on;
	} );
	const addLabel = el( 'input' );
	addLabel.type = 'text';
	addLabel.id = id( 'add_button_label' );
	const currentAdd = clone( 'add_button_label', repeat.add_button_label );
	addLabel.value = typeof currentAdd === 'string' ? currentAdd : '';
	addLabel.placeholder = '+ Add more';
	addLabel.addEventListener( 'input', () => {
		field.add_button_label = addLabel.value.trim() === '' ? null : addLabel.value.trim();
	} );
	grid.append( labelled( 'Add button label', addLabel ) );

	return box;
}
