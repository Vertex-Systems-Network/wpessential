import './admin.scss';
import {
	bindTaxonomyEditorSafety,
	type TaxonomyEditorSafety,
} from './taxonomy-editor-safety';
import {
	bindTaxonomyLabelEditor,
	collectTaxonomyLabels,
	resetTaxonomyLabels,
	setTaxonomyLabels,
} from './taxonomy-labels';
import {
	clearTaxonomyRoleImpactPreview,
	ensureTaxonomyRoleImpactPreview,
	isTaxonomyRoleImpact,
	renderTaxonomyRoleImpactPreview,
	type TaxonomyRoleImpact,
} from './taxonomy-role-impact-preview';
import {
	bindTaxonomyVisibility,
	collectTaxonomyVisibility,
	resetTaxonomyVisibility,
	setTaxonomyVisibility,
} from './taxonomy-visibility';

type RecordValue = Record< string, unknown >;
type TaxonomyPayload = RecordValue;

type DefinitionReference = {
	id: string;
	resolved: boolean;
	slug: string | null;
	type: string | null;
	owner_surface_id: number | null;
	status: string | null;
};
type AssociationHealth = {
	key: string;
	state: string;
	canonical: boolean;
	canonical_definition_id: string | null;
	canonical_status: string | null;
	runtime_registered: boolean | null;
};
type DependencyUsage = {
	count: number;
	declared: DefinitionReference[];
	dependents: DefinitionReference[];
	object_types: AssociationHealth[];
};
type RuntimeHealth = {
	state: string;
	registered: boolean | null;
	definition_status: string;
};
type TaxonomyReadModel = {
	runtime_health: RuntimeHealth;
	dependency_usage: DependencyUsage;
	role_impact: TaxonomyRoleImpact;
};
type TaxonomyDefinition = {
	id: string;
	status: string;
	revision: number;
	payload: TaxonomyPayload;
	read_model: TaxonomyReadModel;
};

type ObjectTypeOption = {
	key: string;
	label: string;
	source: string;
	status: string;
	runtime_registered: boolean;
};

type Route = { type: string; nonce: string };
type ValidationIssue = {
	id: string;
	severity: string;
	field: string;
	message: string;
};
type TaxonomyDiagnostics = {
	effective_args: RecordValue;
	overrides: RecordValue;
	provider_ids: Record< string, string >;
	association_health: AssociationHealth[];
	dependency_usage: DependencyUsage;
	role_impact: TaxonomyRoleImpact;
	runtime: { registered: boolean | null };
	previews: { rest: RecordValue; rewrite: RecordValue };
};
type ValidationReport = {
	valid: boolean;
	issues: ValidationIssue[];
	candidate: { taxonomy_key: string | null };
	diagnostics: TaxonomyDiagnostics | null;
};
type Bootstrap = {
	surface: 'taxonomies';
	ajaxUrl: string;
	ajaxAction: string;
	routes: { list: Route; validate: Route; save: Route; status: Route };
	definitions: TaxonomyDefinition[];
	objectTypes: ObjectTypeOption[];
};
type EditorRequest = {
	id: string;
	revision: number;
	status: string;
	payload: TaxonomyPayload;
};
type AjaxEnvelope = {
	success: boolean;
	data?: unknown;
	error?: { code?: string; message?: string };
};

function isRecord( value: unknown ): value is RecordValue {
	return (
		typeof value === 'object' && value !== null && ! Array.isArray( value )
	);
}

function isNullableString( value: unknown ): value is string | null {
	return value === null || typeof value === 'string';
}

function isNullableNumber( value: unknown ): value is number | null {
	return (
		value === null ||
		( typeof value === 'number' && Number.isInteger( value ) )
	);
}

function isNullableBoolean( value: unknown ): value is boolean | null {
	return value === null || typeof value === 'boolean';
}

function isDefinitionReference( value: unknown ): value is DefinitionReference {
	return (
		isRecord( value ) &&
		typeof value.id === 'string' &&
		typeof value.resolved === 'boolean' &&
		isNullableString( value.slug ) &&
		isNullableString( value.type ) &&
		isNullableNumber( value.owner_surface_id ) &&
		isNullableString( value.status )
	);
}

function isAssociationHealth( value: unknown ): value is AssociationHealth {
	return (
		isRecord( value ) &&
		typeof value.key === 'string' &&
		typeof value.state === 'string' &&
		typeof value.canonical === 'boolean' &&
		isNullableString( value.canonical_definition_id ) &&
		isNullableString( value.canonical_status ) &&
		isNullableBoolean( value.runtime_registered )
	);
}

function isDependencyUsage( value: unknown ): value is DependencyUsage {
	return (
		isRecord( value ) &&
		typeof value.count === 'number' &&
		Number.isInteger( value.count ) &&
		value.count >= 0 &&
		Array.isArray( value.declared ) &&
		value.declared.every( isDefinitionReference ) &&
		Array.isArray( value.dependents ) &&
		value.dependents.every( isDefinitionReference ) &&
		Array.isArray( value.object_types ) &&
		value.object_types.every( isAssociationHealth )
	);
}

function isRuntimeHealth( value: unknown ): value is RuntimeHealth {
	return (
		isRecord( value ) &&
		typeof value.state === 'string' &&
		isNullableBoolean( value.registered ) &&
		typeof value.definition_status === 'string'
	);
}

function isTaxonomyReadModel( value: unknown ): value is TaxonomyReadModel {
	return (
		isRecord( value ) &&
		isRuntimeHealth( value.runtime_health ) &&
		isDependencyUsage( value.dependency_usage ) &&
		isTaxonomyRoleImpact( value.role_impact )
	);
}

function isDefinition( value: unknown ): value is TaxonomyDefinition {
	return (
		isRecord( value ) &&
		typeof value.id === 'string' &&
		typeof value.status === 'string' &&
		typeof value.revision === 'number' &&
		Number.isInteger( value.revision ) &&
		isRecord( value.payload ) &&
		isTaxonomyReadModel( value.read_model )
	);
}

function isObjectTypeOption( value: unknown ): value is ObjectTypeOption {
	return (
		isRecord( value ) &&
		typeof value.key === 'string' &&
		typeof value.label === 'string' &&
		typeof value.source === 'string' &&
		typeof value.status === 'string' &&
		typeof value.runtime_registered === 'boolean'
	);
}

function isRoute( value: unknown ): value is Route {
	return (
		isRecord( value ) &&
		typeof value.type === 'string' &&
		typeof value.nonce === 'string'
	);
}

function parseBootstrap( value: unknown ): Bootstrap | null {
	if (
		! isRecord( value ) ||
		value.surface !== 'taxonomies' ||
		typeof value.ajaxUrl !== 'string' ||
		typeof value.ajaxAction !== 'string' ||
		! isRecord( value.routes ) ||
		! isRoute( value.routes.list ) ||
		! isRoute( value.routes.validate ) ||
		! isRoute( value.routes.save ) ||
		! isRoute( value.routes.status ) ||
		! Array.isArray( value.definitions ) ||
		! value.definitions.every( isDefinition ) ||
		! Array.isArray( value.objectTypes ) ||
		! value.objectTypes.every( isObjectTypeOption )
	) {
		return null;
	}

	return {
		surface: 'taxonomies',
		ajaxUrl: value.ajaxUrl,
		ajaxAction: value.ajaxAction,
		routes: {
			list: value.routes.list,
			validate: value.routes.validate,
			save: value.routes.save,
			status: value.routes.status,
		},
		definitions: value.definitions,
		objectTypes: value.objectTypes,
	};
}

function isIssue( value: unknown ): value is ValidationIssue {
	return (
		isRecord( value ) &&
		typeof value.id === 'string' &&
		typeof value.severity === 'string' &&
		typeof value.field === 'string' &&
		typeof value.message === 'string'
	);
}

function parseProviderIds( value: unknown ): Record< string, string > | null {
	if ( Array.isArray( value ) ) {
		return value.length === 0 ? {} : null;
	}
	if ( ! isRecord( value ) ) {
		return null;
	}

	const providers: Record< string, string > = {};
	for ( const [ slot, providerId ] of Object.entries( value ) ) {
		if ( typeof providerId !== 'string' ) {
			return null;
		}
		providers[ slot ] = providerId;
	}
	return providers;
}

function parseDiagnostics( value: unknown ): TaxonomyDiagnostics | null {
	if (
		! isRecord( value ) ||
		! isRecord( value.effective_args ) ||
		! isRecord( value.overrides ) ||
		! Array.isArray( value.association_health ) ||
		! value.association_health.every( isAssociationHealth ) ||
		! isDependencyUsage( value.dependency_usage ) ||
		! isTaxonomyRoleImpact( value.role_impact ) ||
		! isRecord( value.runtime ) ||
		! isNullableBoolean( value.runtime.registered ) ||
		! isRecord( value.previews ) ||
		! isRecord( value.previews.rest ) ||
		! isRecord( value.previews.rewrite )
	) {
		return null;
	}

	const providerIds = parseProviderIds( value.provider_ids );
	if ( providerIds === null ) {
		return null;
	}

	return {
		effective_args: value.effective_args,
		overrides: value.overrides,
		provider_ids: providerIds,
		association_health: value.association_health,
		dependency_usage: value.dependency_usage,
		role_impact: value.role_impact,
		runtime: { registered: value.runtime.registered },
		previews: {
			rest: value.previews.rest,
			rewrite: value.previews.rewrite,
		},
	};
}

function parseReport( value: unknown ): ValidationReport | null {
	if (
		! isRecord( value ) ||
		typeof value.valid !== 'boolean' ||
		! Array.isArray( value.issues ) ||
		! value.issues.every( isIssue ) ||
		! isRecord( value.candidate )
	) {
		return null;
	}

	const key = value.candidate.taxonomy_key;
	if ( key !== null && typeof key !== 'string' ) {
		return null;
	}

	let diagnostics: TaxonomyDiagnostics | null = null;
	if ( value.diagnostics !== null ) {
		diagnostics = parseDiagnostics( value.diagnostics );
		if ( diagnostics === null ) {
			return null;
		}
	}

	return {
		valid: value.valid,
		issues: value.issues,
		candidate: { taxonomy_key: key },
		diagnostics,
	};
}

function parseMutationDefinition( value: unknown ): TaxonomyDefinition | null {
	if ( ! isRecord( value ) || ! isDefinition( value.definition ) ) {
		return null;
	}

	return value.definition;
}

function textInput( id: string ): HTMLInputElement | null {
	const element = document.getElementById( id );
	return element instanceof HTMLInputElement ? element : null;
}

function selectInput( id: string ): HTMLSelectElement | null {
	const element = document.getElementById( id );
	return element instanceof HTMLSelectElement ? element : null;
}

function buttonInput( id: string ): HTMLButtonElement | null {
	const element = document.getElementById( id );
	return element instanceof HTMLButtonElement ? element : null;
}

function fieldValue( field: string ): string {
	const input = document.querySelector< HTMLInputElement >(
		`[data-wpessential-taxonomy-field="${ field }"]`
	);
	return input?.value.trim() ?? '';
}

function setFieldValue( field: string, value: unknown ): void {
	const input = document.querySelector< HTMLInputElement >(
		`[data-wpessential-taxonomy-field="${ field }"]`
	);
	if ( input ) {
		input.value = typeof value === 'string' ? value : '';
	}
}

function boolInput( id: string ): boolean {
	return textInput( id )?.checked ?? false;
}

function setBoolInput( id: string, value: unknown, fallback = false ): void {
	const input = textInput( id );
	if ( input ) {
		input.checked = typeof value === 'boolean' ? value : fallback;
	}
}

function objectTypeInputs(): HTMLInputElement[] {
	return Array.from(
		document.querySelectorAll< HTMLInputElement >(
			'[data-wpessential-taxonomy-object-type]'
		)
	);
}

function splitObjectTypes( value: string ): string[] {
	return value
		.split( ',' )
		.map( ( item ) => item.trim() )
		.filter( ( item ) => item !== '' );
}

function objectTypesFromInput(): string[] {
	const selected = objectTypeInputs()
		.filter( ( input ) => input.checked )
		.map( ( input ) => input.value.trim() )
		.filter( ( value ) => value !== '' );
	const additional = splitObjectTypes(
		textInput( 'wpessential-taxonomy-object-types-extra' )?.value ?? ''
	);

	return Array.from( new Set( [ ...selected, ...additional ] ) );
}

function setObjectTypes( value: unknown ): void {
	const values = Array.isArray( value )
		? value.filter( ( item ): item is string => typeof item === 'string' )
		: [];
	const remaining = new Set( values );

	for ( const input of objectTypeInputs() ) {
		input.checked = remaining.has( input.value );
		remaining.delete( input.value );
	}

	const extra = textInput( 'wpessential-taxonomy-object-types-extra' );
	if ( extra ) {
		extra.value = Array.from( remaining ).join( ', ' );
	}
}

function setNotice( message: string, error = false ): void {
	const notice = document.getElementById( 'wpessential-taxonomy-notice' );
	if ( ! ( notice instanceof HTMLElement ) ) {
		return;
	}

	const paragraph = notice.querySelector( 'p' );
	if ( paragraph ) {
		paragraph.textContent = message;
	}
	notice.hidden = message === '';
	notice.classList.toggle( 'notice-error', error );
	notice.classList.toggle( 'notice-success', ! error && message !== '' );
}

function diagnosticsValue(
	container: HTMLElement,
	name: string
): HTMLElement | null {
	const value = container.querySelector(
		`[data-wpessential-taxonomy-diagnostic="${ name }"]`
	);
	return value instanceof HTMLElement ? value : null;
}

function createDiagnosticsValue(
	list: HTMLDListElement,
	label: string,
	name: string
): void {
	const term = document.createElement( 'dt' );
	term.textContent = label;
	const value = document.createElement( 'dd' );
	value.dataset.wpessentialTaxonomyDiagnostic = name;
	list.append( term, value );
}

function ensureDiagnosticsPanel(): HTMLElement | null {
	const existing = document.getElementById(
		'wpessential-taxonomy-diagnostics'
	);
	if ( existing instanceof HTMLElement ) {
		return existing;
	}

	const validation = document.getElementById(
		'wpessential-taxonomy-validation'
	);
	if ( ! ( validation instanceof HTMLElement ) ) {
		return null;
	}

	const panel = document.createElement( 'section' );
	panel.id = 'wpessential-taxonomy-diagnostics';
	panel.className = 'wpessential-cpt-validation';
	panel.hidden = true;
	panel.setAttribute(
		'aria-labelledby',
		'wpessential-taxonomy-diagnostics-title'
	);
	panel.setAttribute( 'aria-live', 'polite' );

	const heading = document.createElement( 'h3' );
	heading.id = 'wpessential-taxonomy-diagnostics-title';
	heading.textContent = 'Diagnostics preview';
	const intro = document.createElement( 'p' );
	intro.textContent =
		'Read-only effective runtime state compiled by the canonical Taxonomy definition. Nothing in this panel changes WordPress.';

	const summary = document.createElement( 'dl' );
	summary.dataset.wpessentialTaxonomyDiagnosticsSummary = '';
	createDiagnosticsValue( summary, 'Runtime registration', 'runtime' );
	createDiagnosticsValue( summary, 'REST route', 'rest-route' );
	createDiagnosticsValue( summary, 'Rewrite preview', 'rewrite-path' );
	createDiagnosticsValue( summary, 'Runtime providers', 'providers' );
	createDiagnosticsValue(
		summary,
		'Dependency / usage references',
		'dependency-count'
	);

	const associationHeading = document.createElement( 'h4' );
	associationHeading.textContent = 'Association health';
	const associations = document.createElement( 'ul' );
	associations.dataset.wpessentialTaxonomyAssociationHealth = '';

	const dependencyDetails = document.createElement( 'details' );
	const dependencySummary = document.createElement( 'summary' );
	dependencySummary.textContent = 'Dependency / usage summary';
	const dependencyUsage = document.createElement( 'pre' );
	dependencyUsage.dataset.wpessentialTaxonomyDependencyUsage = '';
	dependencyDetails.append( dependencySummary, dependencyUsage );

	const effective = document.createElement( 'details' );
	effective.open = true;
	const effectiveSummary = document.createElement( 'summary' );
	effectiveSummary.textContent = 'Effective register_taxonomy() arguments';
	const effectiveArgs = document.createElement( 'pre' );
	effectiveArgs.dataset.wpessentialTaxonomyEffectiveArgs = '';
	effective.append( effectiveSummary, effectiveArgs );

	const overrides = document.createElement( 'details' );
	const overridesSummary = document.createElement( 'summary' );
	overridesSummary.textContent = 'Explicit overrides';
	const overrideArgs = document.createElement( 'pre' );
	overrideArgs.dataset.wpessentialTaxonomyOverrides = '';
	overrides.append( overridesSummary, overrideArgs );

	panel.append(
		heading,
		intro,
		summary,
		associationHeading,
		associations,
		dependencyDetails,
		effective,
		overrides
	);
	ensureTaxonomyRoleImpactPreview( panel, summary );
	validation.insertAdjacentElement( 'afterend', panel );
	return panel;
}

function clearDiagnostics(): void {
	const panel = document.getElementById( 'wpessential-taxonomy-diagnostics' );
	if ( ! ( panel instanceof HTMLElement ) ) {
		return;
	}

	panel.hidden = true;
	for ( const name of [
		'runtime',
		'rest-route',
		'rewrite-path',
		'providers',
		'dependency-count',
	] ) {
		const value = diagnosticsValue( panel, name );
		if ( value ) {
			value.textContent = '';
		}
	}
	const associations = panel.querySelector(
		'[data-wpessential-taxonomy-association-health]'
	);
	if ( associations instanceof HTMLElement ) {
		associations.replaceChildren();
	}
	const dependencyUsage = panel.querySelector(
		'[data-wpessential-taxonomy-dependency-usage]'
	);
	if ( dependencyUsage instanceof HTMLElement ) {
		dependencyUsage.textContent = '';
	}
	const effective = panel.querySelector(
		'[data-wpessential-taxonomy-effective-args]'
	);
	if ( effective instanceof HTMLElement ) {
		effective.textContent = '';
	}
	const overrides = panel.querySelector(
		'[data-wpessential-taxonomy-overrides]'
	);
	if ( overrides instanceof HTMLElement ) {
		overrides.textContent = '';
	}
	clearTaxonomyRoleImpactPreview( panel );
}

function diagnosticString(
	record: RecordValue,
	key: string,
	fallback: string
): string {
	const value = record[ key ];
	return typeof value === 'string' && value !== '' ? value : fallback;
}

function renderDiagnostics( diagnostics: TaxonomyDiagnostics | null ): void {
	if ( diagnostics === null ) {
		clearDiagnostics();
		return;
	}

	const panel = ensureDiagnosticsPanel();
	if ( ! panel ) {
		return;
	}

	const runtime = diagnosticsValue( panel, 'runtime' );
	if ( runtime ) {
		let runtimeState = 'Unavailable';
		if ( diagnostics.runtime.registered === true ) {
			runtimeState = 'Registered';
		} else if ( diagnostics.runtime.registered === false ) {
			runtimeState = 'Not registered';
		}
		runtime.textContent = runtimeState;
	}
	const restRoute = diagnosticsValue( panel, 'rest-route' );
	if ( restRoute ) {
		restRoute.textContent = diagnosticString(
			diagnostics.previews.rest,
			'route',
			'Disabled'
		);
	}
	const rewritePath = diagnosticsValue( panel, 'rewrite-path' );
	if ( rewritePath ) {
		rewritePath.textContent = diagnosticString(
			diagnostics.previews.rewrite,
			'path_pattern',
			'Disabled'
		);
	}
	const providers = diagnosticsValue( panel, 'providers' );
	if ( providers ) {
		const entries = Object.entries( diagnostics.provider_ids );
		providers.textContent =
			entries.length === 0
				? 'WordPress defaults'
				: entries
						.map(
							( [ slot, providerId ] ) =>
								`${ slot }: ${ providerId }`
						)
						.join( ', ' );
	}
	const dependencyCount = diagnosticsValue( panel, 'dependency-count' );
	if ( dependencyCount ) {
		dependencyCount.textContent = String(
			diagnostics.dependency_usage.count
		);
	}

	const associations = panel.querySelector(
		'[data-wpessential-taxonomy-association-health]'
	);
	if ( associations instanceof HTMLElement ) {
		associations.replaceChildren();
		if ( diagnostics.association_health.length === 0 ) {
			const empty = document.createElement( 'li' );
			empty.textContent = 'No object-type associations selected.';
			associations.append( empty );
		} else {
			for ( const association of diagnostics.association_health ) {
				const item = document.createElement( 'li' );
				item.dataset.wpessentialTaxonomyAssociationState =
					association.state;
				const canonical =
					association.canonical_status === null
						? ''
						: `; canonical ${ association.canonical_status }`;
				item.textContent = `${ association.key }: ${ association.state }${ canonical }`;
				associations.append( item );
			}
		}
	}

	const dependencyUsage = panel.querySelector(
		'[data-wpessential-taxonomy-dependency-usage]'
	);
	if ( dependencyUsage instanceof HTMLElement ) {
		dependencyUsage.textContent = JSON.stringify(
			diagnostics.dependency_usage,
			null,
			2
		);
	}
	const effective = panel.querySelector(
		'[data-wpessential-taxonomy-effective-args]'
	);
	if ( effective instanceof HTMLElement ) {
		effective.textContent = JSON.stringify(
			diagnostics.effective_args,
			null,
			2
		);
	}
	const overrides = panel.querySelector(
		'[data-wpessential-taxonomy-overrides]'
	);
	if ( overrides instanceof HTMLElement ) {
		overrides.textContent = JSON.stringify(
			diagnostics.overrides,
			null,
			2
		);
	}
	renderTaxonomyRoleImpactPreview( panel, diagnostics.role_impact );
	panel.hidden = false;
}

function clearValidation(): void {
	const report = document.getElementById( 'wpessential-taxonomy-validation' );
	if ( report instanceof HTMLElement ) {
		report.hidden = true;
		report.classList.remove(
			'notice',
			'notice-error',
			'notice-warning',
			'notice-success'
		);
	}
	clearDiagnostics();
}

function renderValidation( report: ValidationReport ): void {
	const container = document.getElementById(
		'wpessential-taxonomy-validation'
	);
	if ( ! ( container instanceof HTMLElement ) ) {
		return;
	}

	const summary = container.querySelector(
		'[data-wpessential-taxonomy-validation-summary]'
	);
	const issues = container.querySelector(
		'[data-wpessential-taxonomy-validation-issues]'
	);
	if (
		! ( summary instanceof HTMLElement ) ||
		! ( issues instanceof HTMLElement )
	) {
		return;
	}

	issues.replaceChildren();
	for ( const issue of report.issues ) {
		const item = document.createElement( 'li' );
		item.textContent = `${ issue.severity.replaceAll( '_', ' ' ) }: ${
			issue.message
		}`;
		item.dataset.wpessentialTaxonomyValidationSeverity = issue.severity;
		issues.append( item );
	}

	const warningCount = report.issues.filter(
		( issue ) => issue.severity !== 'blocked'
	).length;
	if ( report.valid ) {
		summary.textContent =
			warningCount === 0
				? 'Validation passed. No blocking issues found.'
				: `Validation passed with ${ warningCount } warning or informational item(s).`;
	} else {
		const blockedCount = report.issues.filter(
			( issue ) => issue.severity === 'blocked'
		).length;
		summary.textContent = `Validation blocked by ${ blockedCount } issue(s). Resolve them before saving.`;
	}

	container.hidden = false;
	container.classList.add( 'notice' );
	container.classList.toggle( 'notice-error', ! report.valid );
	container.classList.toggle(
		'notice-warning',
		report.valid && report.issues.length > 0
	);
	container.classList.toggle(
		'notice-success',
		report.valid && report.issues.length === 0
	);
	renderDiagnostics( report.diagnostics );
}

async function postRoute(
	bootstrap: Bootstrap,
	route: Route,
	payload: TaxonomyPayload
): Promise< unknown > {
	const body = new URLSearchParams();
	body.set( 'action', bootstrap.ajaxAction );
	body.set( 'type', route.type );
	body.set( 'nonce', route.nonce );
	body.set( 'payload_json', JSON.stringify( payload ) );

	const response = await fetch( bootstrap.ajaxUrl, {
		method: 'POST',
		credentials: 'same-origin',
		headers: {
			'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
		},
		body: body.toString(),
	} );
	const value: unknown = await response.json();
	if ( ! isRecord( value ) || typeof value.success !== 'boolean' ) {
		throw new Error( 'WPEssential returned an invalid AJAX response.' );
	}

	const envelope = value as AjaxEnvelope;
	if ( ! response.ok || ! envelope.success ) {
		throw new Error(
			envelope.error?.message ??
				'The requested change could not be completed.'
		);
	}
	return envelope.data;
}

function cell( text: string ): HTMLTableCellElement {
	const element = document.createElement( 'td' );
	element.textContent = text;
	return element;
}

function actionButton(
	label: string,
	attributes: Record< string, string >
): HTMLButtonElement {
	const button = document.createElement( 'button' );
	button.type = 'button';
	button.className = 'button button-small';
	button.textContent = label;
	for ( const [ key, value ] of Object.entries( attributes ) ) {
		button.dataset[ key ] = value;
	}
	return button;
}

function sortedDefinitions(
	definitions: TaxonomyDefinition[]
): TaxonomyDefinition[] {
	return [ ...definitions ].sort( ( left, right ) => {
		const leftKey =
			typeof left.payload.taxonomy_key === 'string'
				? left.payload.taxonomy_key
				: left.id;
		const rightKey =
			typeof right.payload.taxonomy_key === 'string'
				? right.payload.taxonomy_key
				: right.id;
		return leftKey.localeCompare( rightKey );
	} );
}

function upsertDefinition(
	definitions: TaxonomyDefinition[],
	definition: TaxonomyDefinition
): TaxonomyDefinition[] {
	return sortedDefinitions( [
		...definitions.filter(
			( candidate ) => candidate.id !== definition.id
		),
		definition,
	] );
}

function humanizeState( value: string ): string {
	const label = value.replaceAll( '_', ' ' );
	return label.charAt( 0 ).toUpperCase() + label.slice( 1 );
}

function renderRows( definitions: TaxonomyDefinition[] ): void {
	const rows = document.getElementById( 'wpessential-taxonomy-rows' );
	if ( ! ( rows instanceof HTMLTableSectionElement ) ) {
		return;
	}
	rows.replaceChildren();

	if ( definitions.length === 0 ) {
		const row = document.createElement( 'tr' );
		row.dataset.wpessentialTaxonomyEmpty = '';
		const empty = cell( 'No taxonomies have been created yet.' );
		empty.colSpan = 8;
		row.append( empty );
		rows.append( row );
		return;
	}

	for ( const definition of definitions ) {
		const row = document.createElement( 'tr' );
		row.dataset.wpessentialTaxonomyRow = definition.id;
		const name =
			typeof definition.payload.name === 'string'
				? definition.payload.name
				: '';
		const key =
			typeof definition.payload.taxonomy_key === 'string'
				? definition.payload.taxonomy_key
				: '';
		const objectTypes = Array.isArray( definition.payload.object_types )
			? definition.payload.object_types
					.filter(
						( value ): value is string => typeof value === 'string'
					)
					.join( ', ' )
			: '';
		const runtimeState = definition.read_model.runtime_health.state;
		const runtimeHealth = cell( humanizeState( runtimeState ) );
		runtimeHealth.dataset.wpessentialTaxonomyRuntimeHealth = runtimeState;
		const dependencyCount = definition.read_model.dependency_usage.count;
		const dependencies = cell( String( dependencyCount ) );
		dependencies.dataset.wpessentialTaxonomyDependencyCount =
			String( dependencyCount );

		row.append(
			cell( name ),
			cell( key ),
			cell( objectTypes ),
			cell(
				definition.status.charAt( 0 ).toUpperCase() +
					definition.status.slice( 1 )
			),
			cell( String( definition.revision ) ),
			runtimeHealth,
			dependencies
		);

		const actions = document.createElement( 'td' );
		actions.append(
			actionButton( 'Edit', { wpessentialTaxonomyEdit: definition.id } ),
			document.createTextNode( ' ' )
		);
		if ( definition.status === 'published' ) {
			actions.append(
				actionButton( 'Disable', {
					wpessentialTaxonomyStatus: 'disabled',
					wpessentialTaxonomyId: definition.id,
				} ),
				document.createTextNode( ' ' )
			);
		} else {
			actions.append(
				actionButton( 'Publish', {
					wpessentialTaxonomyStatus: 'published',
					wpessentialTaxonomyId: definition.id,
				} ),
				document.createTextNode( ' ' )
			);
		}
		if ( definition.status !== 'archived' ) {
			actions.append(
				actionButton( 'Archive', {
					wpessentialTaxonomyStatus: 'archived',
					wpessentialTaxonomyId: definition.id,
				} )
			);
		}
		row.append( actions );
		rows.append( row );
	}
}

function resetForm(): void {
	const form = document.getElementById( 'wpessential-taxonomy-form' );
	if ( form instanceof HTMLFormElement ) {
		form.reset();
	}
	const id = textInput( 'wpessential-taxonomy-id' );
	const revision = textInput( 'wpessential-taxonomy-revision' );
	const key = textInput( 'wpessential-taxonomy-key' );
	if ( id ) {
		id.value = '';
	}
	if ( revision ) {
		revision.value = '';
	}
	if ( key ) {
		key.readOnly = false;
	}
	setObjectTypes( [ 'post' ] );
	resetTaxonomyLabels();
	resetTaxonomyVisibility();
	const status = selectInput( 'wpessential-taxonomy-status' );
	if ( status ) {
		status.value = 'draft';
	}
	const title = document.getElementById(
		'wpessential-taxonomy-editor-title'
	);
	if ( title ) {
		title.textContent = 'Add taxonomy';
	}
	const cancel = buttonInput( 'wpessential-taxonomy-cancel' );
	if ( cancel ) {
		cancel.hidden = true;
	}
	clearValidation();
}

function editDefinition( definition: TaxonomyDefinition ): void {
	const id = textInput( 'wpessential-taxonomy-id' );
	const revision = textInput( 'wpessential-taxonomy-revision' );
	const key = textInput( 'wpessential-taxonomy-key' );
	if ( id ) {
		id.value = definition.id;
	}
	if ( revision ) {
		revision.value = String( definition.revision );
	}
	setFieldValue( 'taxonomy_key', definition.payload.taxonomy_key );
	setFieldValue( 'name', definition.payload.name );
	setFieldValue( 'singular_name', definition.payload.singular_name );
	setFieldValue( 'description', definition.payload.description );
	setObjectTypes( definition.payload.object_types );
	setBoolInput(
		'wpessential-taxonomy-public',
		definition.payload.public,
		true
	);
	setBoolInput(
		'wpessential-taxonomy-rest',
		definition.payload.show_in_rest,
		true
	);
	setBoolInput(
		'wpessential-taxonomy-hierarchical',
		definition.payload.hierarchical
	);
	setBoolInput(
		'wpessential-taxonomy-admin-column',
		definition.payload.show_admin_column
	);
	setTaxonomyLabels( definition.payload );
	setTaxonomyVisibility( definition.payload );
	const status = selectInput( 'wpessential-taxonomy-status' );
	if ( status ) {
		status.value = definition.status;
	}
	if ( key ) {
		key.readOnly = true;
	}
	const title = document.getElementById(
		'wpessential-taxonomy-editor-title'
	);
	if ( title ) {
		title.textContent = 'Edit taxonomy';
	}
	const cancel = buttonInput( 'wpessential-taxonomy-cancel' );
	if ( cancel ) {
		cancel.hidden = false;
	}
	clearValidation();
	textInput( 'wpessential-taxonomy-name' )?.focus();
}

function collectEditor( definitions: TaxonomyDefinition[] ): EditorRequest {
	const id = textInput( 'wpessential-taxonomy-id' )?.value ?? '';
	const revision = Number(
		textInput( 'wpessential-taxonomy-revision' )?.value ?? 0
	);
	const existing = definitions.find( ( definition ) => definition.id === id );

	return {
		id,
		revision,
		status: selectInput( 'wpessential-taxonomy-status' )?.value ?? 'draft',
		payload: {
			...( existing?.payload ?? {} ),
			taxonomy_key: fieldValue( 'taxonomy_key' ),
			object_types: objectTypesFromInput(),
			name: fieldValue( 'name' ),
			singular_name: fieldValue( 'singular_name' ),
			description: fieldValue( 'description' ),
			...collectTaxonomyLabels(),
			...collectTaxonomyVisibility(),
			public: boolInput( 'wpessential-taxonomy-public' ),
			show_in_rest: boolInput( 'wpessential-taxonomy-rest' ),
			hierarchical: boolInput( 'wpessential-taxonomy-hierarchical' ),
			show_admin_column: boolInput( 'wpessential-taxonomy-admin-column' ),
		},
	};
}

function mutationRequest(
	editor: EditorRequest,
	validationOnly = false
): TaxonomyPayload {
	const request: TaxonomyPayload = { payload: editor.payload };
	if ( ! validationOnly ) {
		request.status = editor.status;
	}
	if ( editor.id !== '' ) {
		request.id = editor.id;
		if ( ! validationOnly ) {
			request.expected_revision = editor.revision;
		}
	}
	return request;
}

function boot(): void {
	const root = document.getElementById( 'wpessential-taxonomy-root' );
	const script = document.getElementById( 'wpessential-taxonomy-bootstrap' );
	if (
		! ( root instanceof HTMLElement ) ||
		! ( script instanceof HTMLScriptElement )
	) {
		return;
	}

	let raw: unknown;
	try {
		raw = JSON.parse( script.textContent ?? '{}' );
	} catch {
		root.dataset.wpessentialEnhanced = 'invalid-bootstrap';
		return;
	}

	const bootstrap = parseBootstrap( raw );
	if ( ! bootstrap ) {
		root.dataset.wpessentialEnhanced = 'invalid-bootstrap';
		return;
	}

	let definitions = sortedDefinitions( bootstrap.definitions );
	let busy = false;
	let editorSafety: TaxonomyEditorSafety | null = null;

	const refresh = async (): Promise< void > => {
		const data = await postRoute( bootstrap, bootstrap.routes.list, {} );
		if ( ! isRecord( data ) || ! Array.isArray( data.definitions ) ) {
			throw new Error( 'The Taxonomy list response was invalid.' );
		}
		const next = data.definitions.filter( isDefinition );
		if ( next.length !== data.definitions.length ) {
			throw new Error( 'The Taxonomy list contained invalid records.' );
		}
		definitions = sortedDefinitions( next );
		renderRows( definitions );
	};

	const validate = async (
		editor: EditorRequest
	): Promise< ValidationReport > => {
		const data = await postRoute(
			bootstrap,
			bootstrap.routes.validate,
			mutationRequest( editor, true )
		);
		const report = parseReport( data );
		if ( ! report ) {
			throw new Error( 'The Taxonomy validation response was invalid.' );
		}
		renderValidation( report );
		return report;
	};

	const run = async ( operation: () => Promise< void > ): Promise< void > => {
		if ( busy ) {
			return;
		}
		busy = true;
		root.setAttribute( 'aria-busy', 'true' );
		setNotice( '' );
		try {
			await operation();
		} catch ( error ) {
			setNotice(
				error instanceof Error
					? error.message
					: 'The requested change could not be completed.',
				true
			);
		} finally {
			busy = false;
			root.removeAttribute( 'aria-busy' );
		}
	};

	const form = document.getElementById( 'wpessential-taxonomy-form' );
	if ( form instanceof HTMLFormElement ) {
		editorSafety = bindTaxonomyEditorSafety(
			root,
			form,
			() => JSON.stringify( collectEditor( definitions ) )
		);
		form.addEventListener( 'input', clearValidation );
		form.addEventListener( 'change', clearValidation );
		form.addEventListener( 'submit', ( event ) => {
			event.preventDefault();
			void run( async () => {
				const editor = collectEditor( definitions );
				const report = await validate( editor );
				if ( ! report.valid ) {
					setNotice(
						'Taxonomy was not saved because validation found blocking issues.',
						true
					);
					return;
				}

				const data = await postRoute(
					bootstrap,
					bootstrap.routes.save,
					mutationRequest( editor )
				);
				const saved = parseMutationDefinition( data );
				if ( ! saved ) {
					throw new Error(
						'The Taxonomy save response was invalid.'
					);
				}
				definitions = upsertDefinition( definitions, saved );
				renderRows( definitions );
				resetForm();
				editorSafety?.acceptBaseline();
				setNotice(
					editor.id === '' ? 'Taxonomy created.' : 'Taxonomy updated.'
				);
			} );
		} );
	}

	buttonInput( 'wpessential-taxonomy-validate' )?.addEventListener(
		'click',
		() => {
			void run( async () => {
				await validate( collectEditor( definitions ) );
			} );
		}
	);
	buttonInput( 'wpessential-taxonomy-cancel' )?.addEventListener(
		'click',
		() => {
			resetForm();
			editorSafety?.acceptBaseline();
			setNotice( '' );
		}
	);
	buttonInput( 'wpessential-taxonomy-refresh' )?.addEventListener(
		'click',
		() => {
			void run( async () => {
				await refresh();
				setNotice( 'Taxonomies refreshed.' );
			} );
		}
	);

	root.addEventListener( 'click', ( event ) => {
		const target = event.target;
		if ( ! ( target instanceof HTMLButtonElement ) ) {
			return;
		}
		const editId = target.dataset.wpessentialTaxonomyEdit;
		if ( editId ) {
			const definition = definitions.find(
				( candidate ) => candidate.id === editId
			);
			if ( definition ) {
				editDefinition( definition );
				editorSafety?.acceptBaseline();
			}
			return;
		}

		const status = target.dataset.wpessentialTaxonomyStatus;
		const id = target.dataset.wpessentialTaxonomyId;
		if ( ! status || ! id ) {
			return;
		}
		const definition = definitions.find(
			( candidate ) => candidate.id === id
		);
		if ( ! definition ) {
			setNotice( 'The selected taxonomy is no longer available.', true );
			return;
		}

		void run( async () => {
			const data = await postRoute( bootstrap, bootstrap.routes.status, {
				id,
				expected_revision: definition.revision,
				status,
			} );
			const changed = parseMutationDefinition( data );
			if ( ! changed ) {
				throw new Error( 'The Taxonomy status response was invalid.' );
			}
			definitions = upsertDefinition( definitions, changed );
			renderRows( definitions );
			if ( status === 'published' ) {
				await refresh();
			}
			if ( textInput( 'wpessential-taxonomy-id' )?.value === id ) {
				resetForm();
				editorSafety?.acceptBaseline();
			}
			setNotice( `Taxonomy status changed to ${ status }.` );
		} );
	} );

	ensureDiagnosticsPanel();
	bindTaxonomyLabelEditor();
	bindTaxonomyVisibility();
	setObjectTypes( [ 'post' ] );
	renderRows( definitions );
	editorSafety?.acceptBaseline();
	root.dataset.wpessentialEnhanced = 'ready';
	window.dispatchEvent(
		new CustomEvent( 'wpessential:admin-ready', {
			detail: { surface: 'taxonomies', payload: bootstrap },
		} )
	);
}

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', boot, { once: true } );
} else {
	boot();
}
