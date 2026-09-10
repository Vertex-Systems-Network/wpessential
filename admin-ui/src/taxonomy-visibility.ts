type RecordValue = Record< string, unknown >;
type TaxonomyTier = 'essential' | 'advanced' | 'expert';

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

function selectInput( id: string ): HTMLSelectElement | null {
	const element = document.getElementById( id );
	return element instanceof HTMLSelectElement ? element : null;
}

function isTier( value: string | undefined ): value is TaxonomyTier {
	return value === 'essential' || value === 'advanced' || value === 'expert';
}

function updateInheritanceState( field: string, value: string ): void {
	const state = document.querySelector(
		`[data-wpessential-taxonomy-inheritance-state="${ field }"]`
	);
	if ( ! ( state instanceof HTMLElement ) ) {
		return;
	}

	state.textContent =
		value === 'true'
			? 'Explicit: enabled'
			: value === 'false'
				? 'Explicit: disabled'
				: 'Default / inherited';
}

function updateInheritanceStates(): void {
	for ( const option of OPTIONAL_VISIBILITY_FIELDS ) {
		const value = selectInput( option.id )?.value ?? 'inherit';
		updateInheritanceState( option.field, value );
	}
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

	const status = document.getElementById( 'wpessential-taxonomy-tier-status' );
	if ( status instanceof HTMLElement ) {
		status.textContent =
			tier === 'essential'
				? 'Essential controls are shown. Stored Advanced and Expert values remain preserved.'
				: tier === 'advanced'
					? 'Essential and Advanced controls are shown. Stored Expert values remain preserved.'
					: 'Essential, Advanced and currently promoted Expert information are shown.';
	}
}

export function collectTaxonomyVisibility(): RecordValue {
	const result: RecordValue = {};
	for ( const option of OPTIONAL_VISIBILITY_FIELDS ) {
		const value = selectInput( option.id )?.value ?? 'inherit';
		result[ option.field ] =
			value === 'true' ? true : value === 'false' ? false : undefined;
	}
	return result;
}

export function setTaxonomyVisibility( payload: RecordValue ): void {
	for ( const option of OPTIONAL_VISIBILITY_FIELDS ) {
		const input = selectInput( option.id );
		if ( ! input ) {
			continue;
		}
		const value = payload[ option.field ];
		input.value =
			typeof value === 'boolean' ? ( value ? 'true' : 'false' ) : 'inherit';
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
}
