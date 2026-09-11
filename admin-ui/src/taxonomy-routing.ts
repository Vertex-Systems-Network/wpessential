type RecordValue = Record< string, unknown >;

const ROUTING_POLICY_ID = 'wpessential-taxonomy-routing-policy';
const REWRITE_MODE_ID = 'wpessential-taxonomy-rewrite-mode';
const REWRITE_SLUG_ID = 'wpessential-taxonomy-rewrite-slug';
const REWRITE_WITH_FRONT_ID = 'wpessential-taxonomy-rewrite-with-front';
const REWRITE_HIERARCHICAL_ID = 'wpessential-taxonomy-rewrite-hierarchical';
const REWRITE_EP_MASK_ID = 'wpessential-taxonomy-rewrite-ep-mask';
const QUERY_VAR_MODE_ID = 'wpessential-taxonomy-query-var-mode';
const QUERY_VAR_NAME_ID = 'wpessential-taxonomy-query-var-name';
const ROUTING_STATE_ID = 'wpessential-taxonomy-routing-policy-state';

function isRecord( value: unknown ): value is RecordValue {
	return (
		typeof value === 'object' && value !== null && ! Array.isArray( value )
	);
}

function selectInput( id: string ): HTMLSelectElement | null {
	const element = document.getElementById( id );
	return element instanceof HTMLSelectElement ? element : null;
}

function textInput( id: string ): HTMLInputElement | null {
	const element = document.getElementById( id );
	return element instanceof HTMLInputElement ? element : null;
}

function appendLabel(
	paragraph: HTMLParagraphElement,
	id: string,
	labelText: string
): void {
	const label = document.createElement( 'label' );
	label.htmlFor = id;
	const strong = document.createElement( 'strong' );
	strong.textContent = labelText;
	label.append( strong );
	paragraph.append( label, document.createElement( 'br' ) );
}

function appendHelp(
	paragraph: HTMLParagraphElement,
	help: string
): void {
	paragraph.append( document.createElement( 'br' ) );
	const description = document.createElement( 'span' );
	description.className = 'description';
	description.textContent = help;
	paragraph.append( description );
}

function appendSelectField(
	container: HTMLElement,
	id: string,
	labelText: string,
	help: string,
	options: Array< { value: string; label: string } >
): void {
	const paragraph = document.createElement( 'p' );
	appendLabel( paragraph, id, labelText );
	const select = document.createElement( 'select' );
	select.id = id;
	for ( const option of options ) {
		const element = document.createElement( 'option' );
		element.value = option.value;
		element.textContent = option.label;
		select.append( element );
	}
	paragraph.append( select );
	appendHelp( paragraph, help );
	container.append( paragraph );
}

function appendTextField(
	container: HTMLElement,
	id: string,
	labelText: string,
	help: string,
	placeholder = ''
): void {
	const paragraph = document.createElement( 'p' );
	appendLabel( paragraph, id, labelText );
	const input = document.createElement( 'input' );
	input.type = 'text';
	input.id = id;
	input.className = 'regular-text';
	input.placeholder = placeholder;
	paragraph.append( input );
	appendHelp( paragraph, help );
	container.append( paragraph );
}

function appendNumberField(
	container: HTMLElement,
	id: string,
	labelText: string,
	help: string
): void {
	const paragraph = document.createElement( 'p' );
	appendLabel( paragraph, id, labelText );
	const input = document.createElement( 'input' );
	input.type = 'number';
	input.id = id;
	input.className = 'small-text';
	input.min = '0';
	input.step = '1';
	input.inputMode = 'numeric';
	paragraph.append( input );
	appendHelp( paragraph, help );
	container.append( paragraph );
}

function ensureRoutingPolicy(): void {
	if ( document.getElementById( ROUTING_POLICY_ID ) ) {
		return;
	}
	const expert = document.getElementById(
		'wpessential-taxonomy-tier-expert'
	);
	if ( ! ( expert instanceof HTMLElement ) ) {
		return;
	}

	const section = document.createElement( 'section' );
	section.id = ROUTING_POLICY_ID;
	section.setAttribute(
		'aria-labelledby',
		'wpessential-taxonomy-routing-policy-title'
	);
	const heading = document.createElement( 'h4' );
	heading.id = 'wpessential-taxonomy-routing-policy-title';
	heading.textContent = 'Rewrite & query variable policy';
	const intro = document.createElement( 'p' );
	intro.className = 'description';
	intro.textContent =
		'These controls author only the canonical WordPress rewrite and query_var fields. Validate to preview the effective path and collision warnings. Saving does not flush rewrite rules.';
	section.append( heading, intro );

	appendSelectField(
		section,
		REWRITE_MODE_ID,
		'Rewrite mode',
		'Default removes the authored override and uses WordPress taxonomy defaults. Custom enables only the typed options below.',
		[
			{ value: '', label: 'Default / WordPress' },
			{ value: 'true', label: 'Enabled' },
			{ value: 'false', label: 'Disabled' },
			{ value: 'custom', label: 'Custom options' },
		]
	);
	appendTextField(
		section,
		REWRITE_SLUG_ID,
		'Rewrite slug',
		'Used only in Custom mode. Safe lowercase path segments are allowed, including nested paths.',
		'library/genre'
	);
	appendSelectField(
		section,
		REWRITE_WITH_FRONT_ID,
		'Use front base',
		'Used only in Custom mode. Default leaves with_front absent from the typed rewrite map.',
		[
			{ value: '', label: 'Default' },
			{ value: 'true', label: 'Enabled' },
			{ value: 'false', label: 'Disabled' },
		]
	);
	appendSelectField(
		section,
		REWRITE_HIERARCHICAL_ID,
		'Hierarchical rewrite path',
		'Used only in Custom mode. This controls hierarchical URL paths, not whether the taxonomy itself is hierarchical.',
		[
			{ value: '', label: 'Default' },
			{ value: 'true', label: 'Enabled' },
			{ value: 'false', label: 'Disabled' },
		]
	);
	appendNumberField(
		section,
		REWRITE_EP_MASK_ID,
		'Endpoint mask',
		'Used only in Custom mode. Optional non-negative WordPress endpoint mask integer.'
	);

	appendSelectField(
		section,
		QUERY_VAR_MODE_ID,
		'Query variable mode',
		'Default removes the authored override. Custom uses the safe machine name below.',
		[
			{ value: '', label: 'Default / WordPress' },
			{ value: 'true', label: 'Enabled with taxonomy key' },
			{ value: 'false', label: 'Disabled' },
			{ value: 'custom', label: 'Custom name' },
		]
	);
	appendTextField(
		section,
		QUERY_VAR_NAME_ID,
		'Custom query variable name',
		'Used only in Custom mode. Lowercase letters, numbers, dashes and underscores; maximum 64 characters.',
		'library_genre'
	);

	const state = document.createElement( 'p' );
	state.id = ROUTING_STATE_ID;
	state.className = 'description';
	state.setAttribute( 'role', 'status' );
	state.setAttribute( 'aria-live', 'polite' );
	section.append( state );
	expert.prepend( section );
}

function optionalBoolean( value: string ): boolean | undefined {
	if ( value === 'true' ) {
		return true;
	}
	if ( value === 'false' ) {
		return false;
	}
	return undefined;
}

function rewriteFromInputs(): boolean | RecordValue | undefined {
	const mode = selectInput( REWRITE_MODE_ID )?.value ?? '';
	if ( mode === '' ) {
		return undefined;
	}
	if ( mode === 'true' ) {
		return true;
	}
	if ( mode === 'false' ) {
		return false;
	}

	const rewrite: RecordValue = {};
	const slug = textInput( REWRITE_SLUG_ID )?.value.trim() ?? '';
	if ( slug !== '' ) {
		rewrite.slug = slug;
	}
	const withFront = optionalBoolean(
		selectInput( REWRITE_WITH_FRONT_ID )?.value ?? ''
	);
	if ( withFront !== undefined ) {
		rewrite.with_front = withFront;
	}
	const hierarchical = optionalBoolean(
		selectInput( REWRITE_HIERARCHICAL_ID )?.value ?? ''
	);
	if ( hierarchical !== undefined ) {
		rewrite.hierarchical = hierarchical;
	}
	const epMask = textInput( REWRITE_EP_MASK_ID )?.value.trim() ?? '';
	if ( epMask !== '' ) {
		rewrite.ep_mask = Number( epMask );
	}
	return rewrite;
}

function queryVarFromInputs(): boolean | string | undefined {
	const mode = selectInput( QUERY_VAR_MODE_ID )?.value ?? '';
	if ( mode === '' ) {
		return undefined;
	}
	if ( mode === 'true' ) {
		return true;
	}
	if ( mode === 'false' ) {
		return false;
	}
	return textInput( QUERY_VAR_NAME_ID )?.value.trim() ?? '';
}

function setBooleanSelect( id: string, value: unknown ): void {
	const select = selectInput( id );
	if ( ! select ) {
		return;
	}
	select.value = value === true ? 'true' : value === false ? 'false' : '';
}

function setRoutingPolicy( payload: RecordValue ): void {
	const rewriteMode = selectInput( REWRITE_MODE_ID );
	if ( rewriteMode ) {
		if ( payload.rewrite === true ) {
			rewriteMode.value = 'true';
		} else if ( payload.rewrite === false ) {
			rewriteMode.value = 'false';
		} else if ( isRecord( payload.rewrite ) ) {
			rewriteMode.value = 'custom';
		} else {
			rewriteMode.value = '';
		}
	}
	const rewrite = isRecord( payload.rewrite ) ? payload.rewrite : {};
	const slug = textInput( REWRITE_SLUG_ID );
	if ( slug ) {
		slug.value = typeof rewrite.slug === 'string' ? rewrite.slug : '';
	}
	setBooleanSelect( REWRITE_WITH_FRONT_ID, rewrite.with_front );
	setBooleanSelect( REWRITE_HIERARCHICAL_ID, rewrite.hierarchical );
	const epMask = textInput( REWRITE_EP_MASK_ID );
	if ( epMask ) {
		epMask.value =
			typeof rewrite.ep_mask === 'number' &&
			Number.isInteger( rewrite.ep_mask ) &&
			rewrite.ep_mask >= 0
				? String( rewrite.ep_mask )
				: '';
	}

	const queryMode = selectInput( QUERY_VAR_MODE_ID );
	if ( queryMode ) {
		if ( payload.query_var === true ) {
			queryMode.value = 'true';
		} else if ( payload.query_var === false ) {
			queryMode.value = 'false';
		} else if ( typeof payload.query_var === 'string' ) {
			queryMode.value = 'custom';
		} else {
			queryMode.value = '';
		}
	}
	const queryName = textInput( QUERY_VAR_NAME_ID );
	if ( queryName ) {
		queryName.value =
			typeof payload.query_var === 'string' ? payload.query_var : '';
	}
	updateRoutingState();
}

function resetRoutingPolicy(): void {
	const rewriteMode = selectInput( REWRITE_MODE_ID );
	const queryMode = selectInput( QUERY_VAR_MODE_ID );
	if ( rewriteMode ) {
		rewriteMode.value = '';
	}
	if ( queryMode ) {
		queryMode.value = '';
	}
	const slug = textInput( REWRITE_SLUG_ID );
	const epMask = textInput( REWRITE_EP_MASK_ID );
	const queryName = textInput( QUERY_VAR_NAME_ID );
	if ( slug ) {
		slug.value = '';
	}
	if ( epMask ) {
		epMask.value = '';
	}
	if ( queryName ) {
		queryName.value = '';
	}
	setBooleanSelect( REWRITE_WITH_FRONT_ID, undefined );
	setBooleanSelect( REWRITE_HIERARCHICAL_ID, undefined );
	updateRoutingState();
}

function updateRoutingState(): void {
	const state = document.getElementById( ROUTING_STATE_ID );
	if ( ! ( state instanceof HTMLElement ) ) {
		return;
	}
	const rewriteMode = selectInput( REWRITE_MODE_ID )?.value ?? '';
	const queryMode = selectInput( QUERY_VAR_MODE_ID )?.value ?? '';
	const rewriteLabel = rewriteMode === '' ? 'WordPress default' : rewriteMode;
	const queryLabel = queryMode === '' ? 'WordPress default' : queryMode;
	state.textContent = `Rewrite: ${ rewriteLabel }; query variable: ${ queryLabel }. Validate for server-authoritative effective routing and collision diagnostics.`;
}

export function collectTaxonomyRouting(): RecordValue {
	return {
		rewrite: rewriteFromInputs(),
		query_var: queryVarFromInputs(),
	};
}

export function setTaxonomyRouting( payload: RecordValue ): void {
	setRoutingPolicy( payload );
}

export function resetTaxonomyRouting(): void {
	resetRoutingPolicy();
}

export function bindTaxonomyRouting(): void {
	ensureRoutingPolicy();
	for ( const id of [
		REWRITE_MODE_ID,
		REWRITE_WITH_FRONT_ID,
		REWRITE_HIERARCHICAL_ID,
		QUERY_VAR_MODE_ID,
	] ) {
		selectInput( id )?.addEventListener( 'change', updateRoutingState );
	}
	for ( const id of [
		REWRITE_SLUG_ID,
		REWRITE_EP_MASK_ID,
		QUERY_VAR_NAME_ID,
	] ) {
		textInput( id )?.addEventListener( 'input', updateRoutingState );
	}
	updateRoutingState();
}
