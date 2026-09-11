import {
	bindTaxonomyRouting,
	collectTaxonomyRouting,
	resetTaxonomyRouting,
	setTaxonomyRouting,
} from './taxonomy-routing';

type RecordValue = Record< string, unknown >;
type TaxonomyTier = 'essential' | 'advanced' | 'expert';
type TaxonomySettingEntry = {
	control: HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement;
	id: string;
	label: string;
	searchText: string;
};

type SelectOption = {
	value: string;
	label: string;
};

const OPTIONAL_VISIBILITY_FIELDS = [
	{
		field: 'show_ui',
		id: 'wpessential-taxonomy-show-ui',
	},
	{
		field: 'publicly_queryable',
		id: 'wpessential-taxonomy-publicly-queryable',
	},
	{
		field: 'show_in_menu',
		id: 'wpessential-taxonomy-show-in-menu',
	},
	{
		field: 'show_in_nav_menus',
		id: 'wpessential-taxonomy-show-in-nav-menus',
	},
	{
		field: 'show_tagcloud',
		id: 'wpessential-taxonomy-show-tagcloud',
	},
	{
		field: 'show_in_quick_edit',
		id: 'wpessential-taxonomy-show-in-quick-edit',
	},
] as const;

const RUNTIME_PROVIDER_SLOTS = [
	'rest_controller',
	'meta_box',
	'meta_box_sanitize',
	'term_count',
] as const;

const TIER_RANK: Record< TaxonomyTier, number > = {
	essential: 0,
	advanced: 1,
	expert: 2,
};

const SETTING_SEARCH_ID = 'wpessential-taxonomy-find-setting';
const SETTING_SEARCH_RESULTS_ID = 'wpessential-taxonomy-setting-results';
const SETTING_SEARCH_STATUS_ID = 'wpessential-taxonomy-setting-search-status';
const RUNTIME_DEFAULTS_ID = 'wpessential-taxonomy-runtime-defaults';
const DEFAULT_TERM_NAME_ID = 'wpessential-taxonomy-default-term-name';
const DEFAULT_TERM_SLUG_ID = 'wpessential-taxonomy-default-term-slug';
const DEFAULT_TERM_DESCRIPTION_ID =
	'wpessential-taxonomy-default-term-description';
const SORT_ID = 'wpessential-taxonomy-sort';
const ARGS_ORDERBY_ID = 'wpessential-taxonomy-args-orderby';
const ARGS_ORDER_ID = 'wpessential-taxonomy-args-order';
const ARGS_FIELDS_ID = 'wpessential-taxonomy-args-fields';

function selectInput( id: string ): HTMLSelectElement | null {
	const element = document.getElementById( id );
	return element instanceof HTMLSelectElement ? element : null;
}

function textInput( id: string ): HTMLInputElement | null {
	const element = document.getElementById( id );
	return element instanceof HTMLInputElement ? element : null;
}

function isTier( value: string | undefined ): value is TaxonomyTier {
	return value === 'essential' || value === 'advanced' || value === 'expert';
}

function inheritanceStateLabel( value: string ): string {
	if ( value === 'true' ) {
		return 'Explicit: enabled';
	}
	if ( value === 'false' ) {
		return 'Explicit: disabled';
	}
	return 'Default / inherited';
}

function tierStatusMessage( tier: TaxonomyTier ): string {
	if ( tier === 'essential' ) {
		return 'Essential controls are shown. Stored Advanced and Expert values remain preserved.';
	}
	if ( tier === 'advanced' ) {
		return 'Essential and Advanced controls are shown. Stored Expert values remain preserved.';
	}
	return 'Essential, Advanced and currently promoted Expert controls are shown.';
}

function visibilityValue( value: string ): boolean | undefined {
	if ( value === 'true' ) {
		return true;
	}
	if ( value === 'false' ) {
		return false;
	}
	return undefined;
}

function visibilitySelectValue( value: unknown ): 'true' | 'false' | 'inherit' {
	if ( value === true ) {
		return 'true';
	}
	if ( value === false ) {
		return 'false';
	}
	return 'inherit';
}

function updateInheritanceState( field: string, value: string ): void {
	const state = document.querySelector(
		`[data-wpessential-taxonomy-inheritance-state="${ field }"]`
	);
	if ( ! ( state instanceof HTMLElement ) ) {
		return;
	}

	state.textContent = inheritanceStateLabel( value );
}

function updateInheritanceStates(): void {
	for ( const option of OPTIONAL_VISIBILITY_FIELDS ) {
		const value = selectInput( option.id )?.value ?? 'inherit';
		updateInheritanceState( option.field, value );
	}
}

function normalizeSearchText( value: string ): string {
	return value.replace( /\s+/g, ' ' ).trim().toLowerCase();
}

function settingEntries(): TaxonomySettingEntry[] {
	const form = document.getElementById( 'wpessential-taxonomy-form' );
	if ( ! ( form instanceof HTMLFormElement ) ) {
		return [];
	}

	const entries: TaxonomySettingEntry[] = [];
	const seen = new Set< string >();
	for ( const label of Array.from(
		form.querySelectorAll< HTMLLabelElement >( 'label[for]' )
	) ) {
		const id = label.htmlFor;
		if ( id === '' || id === SETTING_SEARCH_ID || seen.has( id ) ) {
			continue;
		}
		const control = document.getElementById( id );
		if (
			! (
				control instanceof HTMLInputElement ||
				control instanceof HTMLSelectElement ||
				control instanceof HTMLTextAreaElement
			) ||
			( control instanceof HTMLInputElement && control.type === 'hidden' )
		) {
			continue;
		}
		const fullLabelText =
			label.textContent?.replace( /\s+/g, ' ' ).trim() ?? '';
		const visibleLabel =
			label
				.querySelector( 'strong' )
				?.textContent?.replace( /\s+/g, ' ' )
				.trim() ?? fullLabelText;
		if ( visibleLabel === '' ) {
			continue;
		}
		seen.add( id );
		entries.push( {
			control,
			id,
			label: visibleLabel,
			searchText: normalizeSearchText( `${ fullLabelText } ${ id }` ),
		} );
	}
	return entries;
}

function settingSearchStatus(): HTMLElement | null {
	const status = document.getElementById( SETTING_SEARCH_STATUS_ID );
	return status instanceof HTMLElement ? status : null;
}

function settingSearchResults(): HTMLUListElement | null {
	const results = document.getElementById( SETTING_SEARCH_RESULTS_ID );
	return results instanceof HTMLUListElement ? results : null;
}

function settingSearchInput(): HTMLInputElement | null {
	const input = document.getElementById( SETTING_SEARCH_ID );
	return input instanceof HTMLInputElement ? input : null;
}

function resetTaxonomySettingSearch(): void {
	const input = settingSearchInput();
	if ( input ) {
		input.value = '';
	}
	const results = settingSearchResults();
	if ( results ) {
		results.replaceChildren();
		results.hidden = true;
	}
	const status = settingSearchStatus();
	if ( status ) {
		status.textContent = 'Search by setting name to jump to a control.';
	}
}

function revealSetting( entry: TaxonomySettingEntry ): void {
	const tierSection = entry.control.closest< HTMLElement >(
		'[data-wpessential-taxonomy-tier-min]'
	);
	const minimumTier = tierSection?.dataset.wpessentialTaxonomyTierMin;
	if ( isTier( minimumTier ) ) {
		setTaxonomyTier( minimumTier );
	}

	const details = entry.control.closest( 'details' );
	if ( details instanceof HTMLDetailsElement ) {
		details.open = true;
	}
	entry.control.focus();
	entry.control.scrollIntoView( { block: 'center' } );
}

function renderSettingSearchResults( entries: TaxonomySettingEntry[] ): void {
	const input = settingSearchInput();
	const results = settingSearchResults();
	const status = settingSearchStatus();
	if ( ! input || ! results || ! status ) {
		return;
	}

	const query = normalizeSearchText( input.value );
	results.replaceChildren();
	if ( query === '' ) {
		results.hidden = true;
		status.textContent = 'Search by setting name to jump to a control.';
		return;
	}

	const matches = entries.filter( ( entry ) =>
		entry.searchText.includes( query )
	);
	if ( matches.length === 0 ) {
		results.hidden = true;
		status.textContent = 'No taxonomy settings matched your search.';
		return;
	}

	for ( const entry of matches ) {
		const item = document.createElement( 'li' );
		const button = document.createElement( 'button' );
		button.type = 'button';
		button.className = 'button-link';
		button.dataset.wpessentialTaxonomySettingTarget = entry.id;
		button.textContent = entry.label;
		item.append( button );
		results.append( item );
	}
	results.hidden = false;
	status.textContent = `${ matches.length } taxonomy setting${
		matches.length === 1 ? '' : 's'
	} matched. Choose a result or press Enter to jump to the first match.`;
}

function ensureTaxonomySettingSearch(): void {
	if ( settingSearchInput() ) {
		return;
	}
	const tierNavigation = document.querySelector(
		'.wpessential-taxonomy-tier-navigation'
	);
	if ( ! ( tierNavigation instanceof HTMLElement ) ) {
		return;
	}

	const container = document.createElement( 'div' );
	container.className = 'wpessential-taxonomy-setting-search';
	const label = document.createElement( 'label' );
	label.htmlFor = SETTING_SEARCH_ID;
	const strong = document.createElement( 'strong' );
	strong.textContent = 'Find setting';
	label.append( strong );
	const input = document.createElement( 'input' );
	input.type = 'search';
	input.id = SETTING_SEARCH_ID;
	input.className = 'regular-text';
	input.autocomplete = 'off';
	input.placeholder = 'Search taxonomy settings';
	input.setAttribute( 'aria-controls', SETTING_SEARCH_RESULTS_ID );
	input.setAttribute( 'aria-describedby', SETTING_SEARCH_STATUS_ID );
	const status = document.createElement( 'p' );
	status.id = SETTING_SEARCH_STATUS_ID;
	status.className = 'description';
	status.setAttribute( 'role', 'status' );
	status.setAttribute( 'aria-live', 'polite' );
	const results = document.createElement( 'ul' );
	results.id = SETTING_SEARCH_RESULTS_ID;
	results.hidden = true;
	container.append(
		label,
		document.createElement( 'br' ),
		input,
		status,
		results
	);
	tierNavigation.insertAdjacentElement( 'beforebegin', container );
}

function bindTaxonomySettingSearch(): void {
	ensureTaxonomySettingSearch();
	const input = settingSearchInput();
	const results = settingSearchResults();
	if ( ! input || ! results ) {
		return;
	}
	const entries = settingEntries();
	input.addEventListener( 'input', () => {
		renderSettingSearchResults( entries );
	} );
	input.addEventListener( 'keydown', ( event ) => {
		if ( event.key !== 'Enter' ) {
			return;
		}
		const first = results.querySelector< HTMLButtonElement >(
			'[data-wpessential-taxonomy-setting-target]'
		);
		if ( ! first ) {
			return;
		}
		event.preventDefault();
		first.click();
	} );
	results.addEventListener( 'click', ( event ) => {
		const target = event.target;
		if ( ! ( target instanceof HTMLButtonElement ) ) {
			return;
		}
		const id = target.dataset.wpessentialTaxonomySettingTarget;
		const entry = entries.find( ( candidate ) => candidate.id === id );
		if ( entry ) {
			revealSetting( entry );
		}
	} );
	resetTaxonomySettingSearch();
}

function appendTextField(
	container: HTMLElement,
	id: string,
	labelText: string,
	help: string,
	placeholder = ''
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

function appendSelectField(
	container: HTMLElement,
	id: string,
	labelText: string,
	help: string,
	options: SelectOption[]
): void {
	const paragraph = document.createElement( 'p' );
	const label = document.createElement( 'label' );
	label.htmlFor = id;
	const strong = document.createElement( 'strong' );
	strong.textContent = labelText;
	label.append( strong );
	const select = document.createElement( 'select' );
	select.id = id;
	for ( const option of options ) {
		const element = document.createElement( 'option' );
		element.value = option.value;
		element.textContent = option.label;
		select.append( element );
	}
	const description = document.createElement( 'span' );
	description.className = 'description';
	description.textContent = help;
	paragraph.append(
		label,
		document.createElement( 'br' ),
		select,
		document.createElement( 'br' ),
		description
	);
	container.append( paragraph );
}

function ensureRuntimeDefaults(): void {
	if ( document.getElementById( RUNTIME_DEFAULTS_ID ) ) {
		return;
	}
	const expert = document.getElementById(
		'wpessential-taxonomy-tier-expert'
	);
	if ( ! ( expert instanceof HTMLElement ) ) {
		return;
	}

	const section = document.createElement( 'section' );
	section.id = RUNTIME_DEFAULTS_ID;
	section.setAttribute(
		'aria-labelledby',
		'wpessential-taxonomy-runtime-defaults-title'
	);
	const heading = document.createElement( 'h4' );
	heading.id = 'wpessential-taxonomy-runtime-defaults-title';
	heading.textContent = 'Bounded runtime defaults';
	const intro = document.createElement( 'p' );
	intro.className = 'description';
	intro.textContent =
		'These controls author only existing canonical Taxonomy fields. Blank or Default values remove the authored override. Term ordering itself remains owned by Content Order.';
	section.append( heading, intro );

	appendTextField(
		section,
		DEFAULT_TERM_NAME_ID,
		'Default term name',
		'Optional. WordPress may create this term when the taxonomy is registered. A name is required whenever default-term details are authored.',
		'General'
	);
	appendTextField(
		section,
		DEFAULT_TERM_SLUG_ID,
		'Default term slug',
		'Optional lowercase machine slug for the default term.',
		'general'
	);
	appendTextField(
		section,
		DEFAULT_TERM_DESCRIPTION_ID,
		'Default term description',
		'Optional description stored with the default term.'
	);
	appendSelectField(
		section,
		SORT_ID,
		'Preserve object-term sort order',
		'Default removes the authored sort key. This does not create a persistent term-ordering engine.',
		[
			{ value: 'inherit', label: 'Default / disabled' },
			{ value: 'true', label: 'Enabled' },
			{ value: 'false', label: 'Explicitly disabled' },
		]
	);
	appendSelectField(
		section,
		ARGS_ORDERBY_ID,
		'Object-term order by',
		'Optional allowlisted wp_get_object_terms() ordering field.',
		[
			{ value: '', label: 'Default' },
			{ value: 'name', label: 'Name' },
			{ value: 'slug', label: 'Slug' },
			{ value: 'term_group', label: 'Term group' },
			{ value: 'term_id', label: 'Term ID' },
			{ value: 'id', label: 'ID' },
			{ value: 'description', label: 'Description' },
			{ value: 'parent', label: 'Parent' },
			{ value: 'term_order', label: 'Term order' },
			{ value: 'count', label: 'Count' },
			{ value: 'include', label: 'Include order' },
			{ value: 'none', label: 'None' },
		]
	);
	appendSelectField(
		section,
		ARGS_ORDER_ID,
		'Object-term order direction',
		'Optional direction used only with the bounded object-term argument map.',
		[
			{ value: '', label: 'Default' },
			{ value: 'ASC', label: 'Ascending' },
			{ value: 'DESC', label: 'Descending' },
		]
	);
	appendSelectField(
		section,
		ARGS_FIELDS_ID,
		'Object-term fields mode',
		'Optional allowlisted result shape for wp_get_object_terms().',
		[
			{ value: '', label: 'Default' },
			{ value: 'all', label: 'All term objects' },
			{ value: 'all_with_object_id', label: 'All with object ID' },
			{ value: 'ids', label: 'Term IDs' },
			{ value: 'tt_ids', label: 'Term taxonomy IDs' },
			{ value: 'names', label: 'Names' },
			{ value: 'slugs', label: 'Slugs' },
		]
	);

	expert.append( section );
}

function defaultTermFromInputs(): RecordValue | undefined {
	const name = textInput( DEFAULT_TERM_NAME_ID )?.value.trim() ?? '';
	const slug = textInput( DEFAULT_TERM_SLUG_ID )?.value.trim() ?? '';
	const description =
		textInput( DEFAULT_TERM_DESCRIPTION_ID )?.value.trim() ?? '';
	if ( name === '' && slug === '' && description === '' ) {
		return undefined;
	}

	const value: RecordValue = { name };
	if ( slug !== '' ) {
		value.slug = slug;
	}
	if ( description !== '' ) {
		value.description = description;
	}
	return value;
}

function objectTermArgsFromInputs(): RecordValue | undefined {
	const orderby = selectInput( ARGS_ORDERBY_ID )?.value ?? '';
	const order = selectInput( ARGS_ORDER_ID )?.value ?? '';
	const fields = selectInput( ARGS_FIELDS_ID )?.value ?? '';
	if ( orderby === '' && order === '' && fields === '' ) {
		return undefined;
	}

	const value: RecordValue = {};
	if ( orderby !== '' ) {
		value.orderby = orderby;
	}
	if ( order !== '' ) {
		value.order = order;
	}
	if ( fields !== '' ) {
		value.fields = fields;
	}
	return value;
}

function providerSelect( slot: string ): HTMLSelectElement | null {
	const select = document.querySelector< HTMLSelectElement >(
		`[data-wpessential-taxonomy-runtime-provider="${ slot }"]`
	);
	return select instanceof HTMLSelectElement ? select : null;
}

function providerState( slot: string ): HTMLElement | null {
	const state = document.querySelector(
		`[data-wpessential-taxonomy-provider-state="${ slot }"]`
	);
	return state instanceof HTMLElement ? state : null;
}

function selectedProviderOption(
	select: HTMLSelectElement
): HTMLOptionElement | null {
	const option = select.selectedOptions.item( 0 );
	return option instanceof HTMLOptionElement ? option : null;
}

function updateRuntimeProviderState( slot: string ): void {
	const select = providerSelect( slot );
	if ( ! select ) {
		return;
	}
	const state = providerState( slot );

	if ( select.value === '' ) {
		select.setCustomValidity( '' );
		if ( state ) {
			state.textContent = 'WordPress default';
		}
		return;
	}

	const option = selectedProviderOption( select );
	const available = option?.dataset.wpessentialTaxonomyProviderAvailable;
	if ( available === 'true' ) {
		select.setCustomValidity( '' );
		if ( state ) {
			state.textContent = 'Available registered provider.';
		}
		return;
	}

	select.setCustomValidity(
		'This stored runtime provider is unavailable. Choose WordPress default or an available registered provider.'
	);
	if ( state ) {
		state.textContent =
			'Unavailable. Choose WordPress default or an available registered provider before saving.';
	}
}

function runtimeProvidersFromInputs(): RecordValue | undefined {
	const providers: RecordValue = {};
	for ( const slot of RUNTIME_PROVIDER_SLOTS ) {
		const value = providerSelect( slot )?.value.trim() ?? '';
		if ( value !== '' ) {
			providers[ slot ] = value;
		}
	}
	return Object.keys( providers ).length === 0 ? undefined : providers;
}

function removeStaleProviderOption( select: HTMLSelectElement ): void {
	select
		.querySelectorAll< HTMLOptionElement >(
			'[data-wpessential-taxonomy-provider-stale]'
		)
		.forEach( ( option ) => option.remove() );
}

function setRuntimeProviders( payload: RecordValue ): void {
	const providers = isRecord( payload.runtime_providers )
		? payload.runtime_providers
		: {};

	for ( const slot of RUNTIME_PROVIDER_SLOTS ) {
		const select = providerSelect( slot );
		if ( ! select ) {
			continue;
		}
		removeStaleProviderOption( select );
		const stored = providers[ slot ];
		if ( typeof stored !== 'string' || stored.trim() === '' ) {
			select.value = '';
			updateRuntimeProviderState( slot );
			continue;
		}

		const providerId = stored.trim();
		const known = Array.from( select.options ).find(
			( option ) => option.value === providerId
		);
		if ( known ) {
			select.value = providerId;
			updateRuntimeProviderState( slot );
			continue;
		}

		const stale = document.createElement( 'option' );
		stale.value = providerId;
		stale.textContent = `${ providerId } — not registered`;
		stale.dataset.wpessentialTaxonomyProviderAvailable = 'false';
		stale.dataset.wpessentialTaxonomyProviderStale = '';
		select.append( stale );
		select.value = providerId;
		updateRuntimeProviderState( slot );
	}
}

function resetRuntimeProviders(): void {
	for ( const slot of RUNTIME_PROVIDER_SLOTS ) {
		const select = providerSelect( slot );
		if ( ! select ) {
			continue;
		}
		removeStaleProviderOption( select );
		select.value = '';
		updateRuntimeProviderState( slot );
	}
}

function bindRuntimeProviders(): void {
	for ( const slot of RUNTIME_PROVIDER_SLOTS ) {
		providerSelect( slot )?.addEventListener( 'change', () => {
			updateRuntimeProviderState( slot );
		} );
		updateRuntimeProviderState( slot );
	}
}

function setTextValue( id: string, value: unknown ): void {
	const input = textInput( id );
	if ( input ) {
		input.value = typeof value === 'string' ? value : '';
	}
}

function setSelectValue( id: string, value: unknown ): void {
	const input = selectInput( id );
	if ( input ) {
		input.value = typeof value === 'string' ? value : '';
	}
}

function setRuntimeDefaults( payload: RecordValue ): void {
	const defaultTerm = isRecord( payload.default_term )
		? payload.default_term
		: {};
	setTextValue( DEFAULT_TERM_NAME_ID, defaultTerm.name );
	setTextValue( DEFAULT_TERM_SLUG_ID, defaultTerm.slug );
	setTextValue( DEFAULT_TERM_DESCRIPTION_ID, defaultTerm.description );

	const args = isRecord( payload.args ) ? payload.args : {};
	setSelectValue( ARGS_ORDERBY_ID, args.orderby );
	setSelectValue( ARGS_ORDER_ID, args.order );
	setSelectValue( ARGS_FIELDS_ID, args.fields );

	const sort = selectInput( SORT_ID );
	if ( sort ) {
		sort.value = visibilitySelectValue( payload.sort );
	}
}

function resetRuntimeDefaults(): void {
	setTextValue( DEFAULT_TERM_NAME_ID, '' );
	setTextValue( DEFAULT_TERM_SLUG_ID, '' );
	setTextValue( DEFAULT_TERM_DESCRIPTION_ID, '' );
	setSelectValue( ARGS_ORDERBY_ID, '' );
	setSelectValue( ARGS_ORDER_ID, '' );
	setSelectValue( ARGS_FIELDS_ID, '' );
	const sort = selectInput( SORT_ID );
	if ( sort ) {
		sort.value = 'inherit';
	}
}

function isRecord( value: unknown ): value is RecordValue {
	return (
		typeof value === 'object' && value !== null && ! Array.isArray( value )
	);
}

export function setTaxonomyTier( tier: TaxonomyTier ): void {
	for ( const button of Array.from(
		document.querySelectorAll< HTMLButtonElement >(
			'[data-wpessential-taxonomy-tier]'
		)
	) ) {
		const buttonTier = button.dataset.wpessentialTaxonomyTier;
		const active = buttonTier === tier;
		button.setAttribute( 'aria-pressed', active ? 'true' : 'false' );
		button.classList.toggle( 'button-primary', active );
	}

	for ( const section of Array.from(
		document.querySelectorAll< HTMLElement >(
			'[data-wpessential-taxonomy-tier-min]'
		)
	) ) {
		const minimum = section.dataset.wpessentialTaxonomyTierMin;
		section.hidden =
			! isTier( minimum ) || TIER_RANK[ minimum ] > TIER_RANK[ tier ];
	}

	const status = document.getElementById(
		'wpessential-taxonomy-tier-status'
	);
	if ( status instanceof HTMLElement ) {
		status.textContent = tierStatusMessage( tier );
	}
}

export function collectTaxonomyVisibility(): RecordValue {
	const result: RecordValue = {};
	for ( const option of OPTIONAL_VISIBILITY_FIELDS ) {
		const value = selectInput( option.id )?.value ?? 'inherit';
		result[ option.field ] = visibilityValue( value );
	}
	result.default_term = defaultTermFromInputs();
	result.args = objectTermArgsFromInputs();
	result.sort = visibilityValue( selectInput( SORT_ID )?.value ?? 'inherit' );
	result.runtime_providers = runtimeProvidersFromInputs();
	Object.assign( result, collectTaxonomyRouting() );
	return result;
}

export function setTaxonomyVisibility( payload: RecordValue ): void {
	for ( const option of OPTIONAL_VISIBILITY_FIELDS ) {
		const input = selectInput( option.id );
		if ( ! input ) {
			continue;
		}
		input.value = visibilitySelectValue( payload[ option.field ] );
	}
	setRuntimeDefaults( payload );
	setRuntimeProviders( payload );
	setTaxonomyRouting( payload );
	updateInheritanceStates();
}

export function resetTaxonomyVisibility(): void {
	for ( const option of OPTIONAL_VISIBILITY_FIELDS ) {
		const input = selectInput( option.id );
		if ( input ) {
			input.value = 'inherit';
		}
	}
	resetRuntimeDefaults();
	resetRuntimeProviders();
	resetTaxonomyRouting();
	updateInheritanceStates();
	setTaxonomyTier( 'essential' );
	resetTaxonomySettingSearch();
}

export function bindTaxonomyVisibility(): void {
	bindTaxonomyRouting();
	ensureRuntimeDefaults();
	bindRuntimeProviders();
	for ( const button of Array.from(
		document.querySelectorAll< HTMLButtonElement >(
			'[data-wpessential-taxonomy-tier]'
		)
	) ) {
		button.addEventListener( 'click', () => {
			const tier = button.dataset.wpessentialTaxonomyTier;
			if ( isTier( tier ) ) {
				setTaxonomyTier( tier );
			}
		} );
	}

	for ( const option of OPTIONAL_VISIBILITY_FIELDS ) {
		selectInput( option.id )?.addEventListener( 'change', () => {
			updateInheritanceState(
				option.field,
				selectInput( option.id )?.value ?? 'inherit'
			);
		} );
	}

	updateInheritanceStates();
	setTaxonomyTier( 'essential' );
	bindTaxonomySettingSearch();
}
