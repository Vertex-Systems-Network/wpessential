type RecordValue = Record< string, unknown >;
type TaxonomyTier = 'essential' | 'advanced' | 'expert';
type TaxonomySettingEntry = {
	control: HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement;
	id: string;
	label: string;
	searchText: string;
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

const TIER_RANK: Record< TaxonomyTier, number > = {
	essential: 0,
	advanced: 1,
	expert: 2,
};

const SETTING_SEARCH_ID = 'wpessential-taxonomy-find-setting';
const SETTING_SEARCH_RESULTS_ID = 'wpessential-taxonomy-setting-results';
const SETTING_SEARCH_STATUS_ID = 'wpessential-taxonomy-setting-search-status';

function selectInput( id: string ): HTMLSelectElement | null {
	const element = document.getElementById( id );
	return element instanceof HTMLSelectElement ? element : null;
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
	return 'Essential, Advanced and currently promoted Expert information are shown.';
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
		const labelText =
			label.textContent?.replace( /\s+/g, ' ' ).trim() ?? '';
		if ( labelText === '' ) {
			continue;
		}
		seen.add( id );
		entries.push( {
			control,
			id,
			label: labelText,
			searchText: normalizeSearchText( `${ labelText } ${ id }` ),
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
	updateInheritanceStates();
}

export function resetTaxonomyVisibility(): void {
	for ( const option of OPTIONAL_VISIBILITY_FIELDS ) {
		const input = selectInput( option.id );
		if ( input ) {
			input.value = 'inherit';
		}
	}
	updateInheritanceStates();
	setTaxonomyTier( 'essential' );
	resetTaxonomySettingSearch();
}

export function bindTaxonomyVisibility(): void {
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
