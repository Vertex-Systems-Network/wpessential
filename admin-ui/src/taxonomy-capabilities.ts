type RecordValue = Record< string, unknown >;

const CAPABILITY_POLICY_ID = 'wpessential-taxonomy-capability-policy';
const CAPABILITY_STATE_ID = 'wpessential-taxonomy-capability-policy-state';

const CAPABILITY_FIELDS = [
	{
		key: 'manage_terms',
		id: 'wpessential-taxonomy-capability-manage-terms',
		label: 'Manage terms capability',
		placeholder: 'manage_categories',
		help: 'Leave blank for the WordPress default. This names the capability required to manage taxonomy terms; role grants remain owned by Roles & Capabilities.',
	},
	{
		key: 'edit_terms',
		id: 'wpessential-taxonomy-capability-edit-terms',
		label: 'Edit terms capability',
		placeholder: 'manage_categories',
		help: 'Leave blank for the WordPress default. This names the capability checked when editing terms; it does not grant that capability to any role.',
	},
	{
		key: 'delete_terms',
		id: 'wpessential-taxonomy-capability-delete-terms',
		label: 'Delete terms capability',
		placeholder: 'manage_categories',
		help: 'Leave blank for the WordPress default. This names the capability checked when deleting terms; destructive term operations are not performed here.',
	},
	{
		key: 'assign_terms',
		id: 'wpessential-taxonomy-capability-assign-terms',
		label: 'Assign terms capability',
		placeholder: 'edit_posts',
		help: 'Leave blank for the WordPress default. This names the capability checked when assigning terms to objects; role grants remain external to Taxonomy.',
	},
] as const;

function isRecord( value: unknown ): value is RecordValue {
	return (
		typeof value === 'object' && value !== null && ! Array.isArray( value )
	);
}

function textInput( id: string ): HTMLInputElement | null {
	const element = document.getElementById( id );
	return element instanceof HTMLInputElement ? element : null;
}

function appendCapabilityField(
	container: HTMLElement,
	id: string,
	labelText: string,
	help: string,
	placeholder: string
): void {
	const paragraph = document.createElement( 'p' );
	const label = document.createElement( 'label' );
	label.htmlFor = id;
	const strong = document.createElement( 'strong' );
	strong.textContent = labelText;
	label.append( strong );

	const input = document.createElement( 'input' );
	input.type = 'text';
	input.id = id;
	input.className = 'regular-text';
	input.placeholder = placeholder;
	input.maxLength = 64;
	input.autocomplete = 'off';

	const description = document.createElement( 'span' );
	description.className = 'description';
	description.textContent = help;
	paragraph.append(
		label,
		document.createElement( 'br' ),
		input,
		document.createElement( 'br' ),
		description
	);
	container.append( paragraph );
}

function ensureCapabilityPolicy(): void {
	if ( document.getElementById( CAPABILITY_POLICY_ID ) ) {
		return;
	}
	const expert = document.getElementById(
		'wpessential-taxonomy-tier-expert'
	);
	if ( ! ( expert instanceof HTMLElement ) ) {
		return;
	}

	const section = document.createElement( 'section' );
	section.id = CAPABILITY_POLICY_ID;
	section.setAttribute(
		'aria-labelledby',
		'wpessential-taxonomy-capability-policy-title'
	);
	const heading = document.createElement( 'h4' );
	heading.id = 'wpessential-taxonomy-capability-policy-title';
	heading.textContent = 'Taxonomy capability map';
	const intro = document.createElement( 'p' );
	intro.className = 'description';
	intro.textContent =
		'These controls author only the four native register_taxonomy() capability names. Blank values use WordPress defaults. Validate before saving: WPEssential reports the effective map and warns when the current WordPress user cannot satisfy authored capability checks. Role grants remain owned by Roles & Capabilities.';
	section.append( heading, intro );

	for ( const field of CAPABILITY_FIELDS ) {
		appendCapabilityField(
			section,
			field.id,
			field.label,
			field.help,
			field.placeholder
		);
	}

	const state = document.createElement( 'p' );
	state.id = CAPABILITY_STATE_ID;
	state.className = 'description';
	state.setAttribute( 'role', 'status' );
	state.setAttribute( 'aria-live', 'polite' );
	section.append( state );
	expert.prepend( section );
}

function updateCapabilityState(): void {
	const state = document.getElementById( CAPABILITY_STATE_ID );
	if ( ! ( state instanceof HTMLElement ) ) {
		return;
	}

	const authored = CAPABILITY_FIELDS.filter(
		( field ) => ( textInput( field.id )?.value.trim() ?? '' ) !== ''
	);
	if ( authored.length === 0 ) {
		state.textContent =
			'WordPress default capability map is active. Validate to inspect the effective server-side map.';
		return;
	}

	const entryState = authored.length === 1 ? 'entry is' : 'entries are';
	state.textContent = `${ authored.length } native capability map ${ entryState } explicitly authored. Validate for effective-map and current-user lockout diagnostics.`;
}

export function collectTaxonomyCapabilities(): RecordValue | undefined {
	const capabilities: RecordValue = {};
	for ( const field of CAPABILITY_FIELDS ) {
		const value = textInput( field.id )?.value.trim() ?? '';
		if ( value !== '' ) {
			capabilities[ field.key ] = value;
		}
	}
	return Object.keys( capabilities ).length === 0 ? undefined : capabilities;
}

export function setTaxonomyCapabilities( payload: RecordValue ): void {
	const capabilities = isRecord( payload.capabilities )
		? payload.capabilities
		: {};
	for ( const field of CAPABILITY_FIELDS ) {
		const input = textInput( field.id );
		if ( input ) {
			const value = capabilities[ field.key ];
			input.value = typeof value === 'string' ? value : '';
		}
	}
	updateCapabilityState();
}

export function resetTaxonomyCapabilities(): void {
	for ( const field of CAPABILITY_FIELDS ) {
		const input = textInput( field.id );
		if ( input ) {
			input.value = '';
		}
	}
	updateCapabilityState();
}

export function bindTaxonomyCapabilities(): void {
	ensureCapabilityPolicy();
	for ( const field of CAPABILITY_FIELDS ) {
		textInput( field.id )?.addEventListener(
			'input',
			updateCapabilityState
		);
	}
	updateCapabilityState();
}
