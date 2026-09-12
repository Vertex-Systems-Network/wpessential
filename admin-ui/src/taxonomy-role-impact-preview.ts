type RecordValue = Record< string, unknown >;

export type TaxonomyRoleImpactOperation = {
	operation: string;
	capability: string;
	impact: RecordValue;
};

export type TaxonomyRoleImpact = {
	state: 'healthy' | 'degraded' | 'unavailable';
	operations: TaxonomyRoleImpactOperation[];
	caveats: string[];
};

function isRecord( value: unknown ): value is RecordValue {
	return (
		typeof value === 'object' && value !== null && ! Array.isArray( value )
	);
}

function isStringArray( value: unknown ): value is string[] {
	return (
		Array.isArray( value ) &&
		value.every( ( item ) => typeof item === 'string' )
	);
}

function isOperation( value: unknown ): value is TaxonomyRoleImpactOperation {
	return (
		isRecord( value ) &&
		typeof value.operation === 'string' &&
		typeof value.capability === 'string' &&
		isRecord( value.impact )
	);
}

export function isTaxonomyRoleImpact(
	value: unknown
): value is TaxonomyRoleImpact {
	return (
		isRecord( value ) &&
		( value.state === 'healthy' ||
			value.state === 'degraded' ||
			value.state === 'unavailable' ) &&
		Array.isArray( value.operations ) &&
		value.operations.every( isOperation ) &&
		isStringArray( value.caveats )
	);
}

function listValue( value: unknown ): string {
	if ( ! isStringArray( value ) || value.length === 0 ) {
		return 'none';
	}
	return value.join( ', ' );
}

function humanize( value: string ): string {
	const label = value.replaceAll( '_', ' ' );
	return label.charAt( 0 ).toUpperCase() + label.slice( 1 );
}

export function ensureTaxonomyRoleImpactPreview(
	panel: HTMLElement,
	summary: HTMLDListElement
): void {
	if ( panel.querySelector( '[data-wpessential-taxonomy-role-impact]' ) ) {
		return;
	}

	const term = document.createElement( 'dt' );
	term.textContent = 'Role impact';
	const state = document.createElement( 'dd' );
	state.dataset.wpessentialTaxonomyDiagnostic = 'role-impact-state';
	summary.append( term, state );

	const details = document.createElement( 'details' );
	details.dataset.wpessentialTaxonomyRoleImpact = '';
	details.open = true;
	const detailsSummary = document.createElement( 'summary' );
	detailsSummary.textContent = 'Capability role-impact preview';
	const intro = document.createElement( 'p' );
	intro.className = 'description';
	intro.textContent =
		'Read-only role-entry diagnostics from Roles & Capabilities. These entries do not represent final user authorization.';
	const operations = document.createElement( 'ul' );
	operations.dataset.wpessentialTaxonomyRoleImpactOperations = '';
	const caveatHeading = document.createElement( 'h4' );
	caveatHeading.textContent = 'Role-impact caveats';
	const caveats = document.createElement( 'ul' );
	caveats.dataset.wpessentialTaxonomyRoleImpactCaveats = '';
	details.append( detailsSummary, intro, operations, caveatHeading, caveats );

	const dependency = panel
		.querySelector( '[data-wpessential-taxonomy-dependency-usage]' )
		?.closest( 'details' );
	if ( dependency instanceof HTMLDetailsElement ) {
		dependency.insertAdjacentElement( 'beforebegin', details );
	} else {
		panel.append( details );
	}
}

export function clearTaxonomyRoleImpactPreview( panel: HTMLElement ): void {
	const state = panel.querySelector(
		'[data-wpessential-taxonomy-diagnostic="role-impact-state"]'
	);
	if ( state instanceof HTMLElement ) {
		state.textContent = '';
		delete state.dataset.wpessentialTaxonomyRoleImpactState;
	}
	const details = panel.querySelector(
		'[data-wpessential-taxonomy-role-impact]'
	);
	if ( details instanceof HTMLElement ) {
		delete details.dataset.wpessentialTaxonomyRoleImpactState;
	}
	const operations = panel.querySelector(
		'[data-wpessential-taxonomy-role-impact-operations]'
	);
	if ( operations instanceof HTMLElement ) {
		operations.replaceChildren();
	}
	const caveats = panel.querySelector(
		'[data-wpessential-taxonomy-role-impact-caveats]'
	);
	if ( caveats instanceof HTMLElement ) {
		caveats.replaceChildren();
	}
}

export function renderTaxonomyRoleImpactPreview(
	panel: HTMLElement,
	roleImpact: TaxonomyRoleImpact
): void {
	const state = panel.querySelector(
		'[data-wpessential-taxonomy-diagnostic="role-impact-state"]'
	);
	if ( state instanceof HTMLElement ) {
		state.textContent = humanize( roleImpact.state );
		state.dataset.wpessentialTaxonomyRoleImpactState = roleImpact.state;
	}
	const details = panel.querySelector(
		'[data-wpessential-taxonomy-role-impact]'
	);
	if ( details instanceof HTMLElement ) {
		details.dataset.wpessentialTaxonomyRoleImpactState = roleImpact.state;
	}
	const operations = panel.querySelector(
		'[data-wpessential-taxonomy-role-impact-operations]'
	);
	if ( operations instanceof HTMLElement ) {
		operations.replaceChildren();
		for ( const operation of roleImpact.operations ) {
			const item = document.createElement( 'li' );
			item.dataset.wpessentialTaxonomyRoleImpactOperation =
				operation.operation;
			item.textContent = `${ operation.operation }: ${
				operation.capability
			}. Allow: ${ listValue(
				operation.impact.explicit_allow_roles
			) }; explicit deny: ${ listValue(
				operation.impact.explicit_deny_roles
			) }; absent: ${ listValue( operation.impact.absent_roles ) }.`;
			operations.append( item );
		}
	}
	const caveats = panel.querySelector(
		'[data-wpessential-taxonomy-role-impact-caveats]'
	);
	if ( caveats instanceof HTMLElement ) {
		caveats.replaceChildren();
		const values =
			roleImpact.caveats.length > 0
				? roleImpact.caveats
				: [ 'No additional role-impact caveats were reported.' ];
		for ( const caveat of values ) {
			const item = document.createElement( 'li' );
			item.textContent = caveat;
			caveats.append( item );
		}
	}
}
