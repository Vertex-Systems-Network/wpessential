type RecordValue = Record< string, unknown >;

type RouteBootstrap = {
	type: string;
	nonce: string;
};
type MainBootstrap = {
	ajaxUrl: string;
	ajaxAction: string;
};
type PreviewIssue = {
	id: string;
	severity: string;
	field: string;
	message: string;
};
type PreviewReport = {
	valid: boolean;
	payload: RecordValue;
	rejected_fields: string[];
	unsupported_fields: string[];
	issues: PreviewIssue[];
};

const SECTION_ID = 'wpessential-taxonomy-cptui-preview';
const SOURCE_ID = 'wpessential-taxonomy-cptui-source';
const PREVIEW_ID = 'wpessential-taxonomy-cptui-preview-button';
const APPLY_ID = 'wpessential-taxonomy-cptui-apply-button';
const STATUS_ID = 'wpessential-taxonomy-cptui-preview-status';
const PAYLOAD_ID = 'wpessential-taxonomy-cptui-preview-payload';
const ISSUES_ID = 'wpessential-taxonomy-cptui-preview-issues';

let lastReport: PreviewReport | null = null;

function isRecord( value: unknown ): value is RecordValue {
	return (
		typeof value === 'object' && value !== null && ! Array.isArray( value )
	);
}

function textInput( id: string ): HTMLInputElement | null {
	const element = document.getElementById( id );
	return element instanceof HTMLInputElement ? element : null;
}

function selectInput( id: string ): HTMLSelectElement | null {
	const element = document.getElementById( id );
	return element instanceof HTMLSelectElement ? element : null;
}

function parseScript( id: string ): RecordValue | null {
	const script = document.getElementById( id );
	if ( ! ( script instanceof HTMLScriptElement ) ) {
		return null;
	}
	try {
		const value: unknown = JSON.parse( script.textContent ?? '{}' );
		return isRecord( value ) ? value : null;
	} catch {
		return null;
	}
}

function mainBootstrap(): MainBootstrap | null {
	const value = parseScript( 'wpessential-taxonomy-bootstrap' );
	if (
		! value ||
		typeof value.ajaxUrl !== 'string' ||
		typeof value.ajaxAction !== 'string'
	) {
		return null;
	}
	return { ajaxUrl: value.ajaxUrl, ajaxAction: value.ajaxAction };
}

function routeBootstrap(): RouteBootstrap | null {
	const value = parseScript(
		'wpessential-taxonomy-import-preview-bootstrap'
	);
	if (
		! value ||
		typeof value.type !== 'string' ||
		typeof value.nonce !== 'string'
	) {
		return null;
	}
	return { type: value.type, nonce: value.nonce };
}

function stringList( value: unknown ): string[] | null {
	if ( ! Array.isArray( value ) ) {
		return null;
	}
	if ( ! value.every( ( item ) => typeof item === 'string' ) ) {
		return null;
	}
	return value as string[];
}

function parseReport( value: unknown ): PreviewReport | null {
	if (
		! isRecord( value ) ||
		typeof value.valid !== 'boolean' ||
		! isRecord( value.payload ) ||
		! Array.isArray( value.issues )
	) {
		return null;
	}
	const rejectedFields = stringList( value.rejected_fields );
	const unsupportedFields = stringList( value.unsupported_fields );
	if ( rejectedFields === null || unsupportedFields === null ) {
		return null;
	}

	const issues: PreviewIssue[] = [];
	for ( const issue of value.issues ) {
		if (
			! isRecord( issue ) ||
			typeof issue.id !== 'string' ||
			typeof issue.severity !== 'string' ||
			typeof issue.field !== 'string' ||
			typeof issue.message !== 'string'
		) {
			return null;
		}
		issues.push( {
			id: issue.id,
			severity: issue.severity,
			field: issue.field,
			message: issue.message,
		} );
	}

	return {
		valid: value.valid,
		payload: value.payload,
		rejected_fields: rejectedFields,
		unsupported_fields: unsupportedFields,
		issues,
	};
}

async function requestPreview( source: RecordValue ): Promise< PreviewReport > {
	const main = mainBootstrap();
	const route = routeBootstrap();
	if ( ! main || ! route ) {
		throw new Error( 'CPT UI import preview is unavailable on this page.' );
	}

	const body = new URLSearchParams();
	body.set( 'action', main.ajaxAction );
	body.set( 'type', route.type );
	body.set( 'nonce', route.nonce );
	body.set( 'payload_json', JSON.stringify( { source } ) );
	const response = await fetch( main.ajaxUrl, {
		method: 'POST',
		credentials: 'same-origin',
		headers: {
			'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
		},
		body: body.toString(),
	} );
	const envelope: unknown = await response.json();
	if (
		! isRecord( envelope ) ||
		envelope.success !== true ||
		! isRecord( envelope.data )
	) {
		let message = 'CPT UI import preview failed.';
		if (
			isRecord( envelope ) &&
			isRecord( envelope.error ) &&
			typeof envelope.error.message === 'string'
		) {
			message = envelope.error.message;
		}
		throw new Error( message );
	}

	const report = parseReport( envelope.data );
	if ( ! report ) {
		throw new Error(
			'CPT UI import preview returned an invalid response.'
		);
	}
	return report;
}

function renderReport( report: PreviewReport ): void {
	lastReport = report;
	const status = document.getElementById( STATUS_ID );
	const payload = document.getElementById( PAYLOAD_ID );
	const issues = document.getElementById( ISSUES_ID );
	const apply = document.getElementById( APPLY_ID );

	if ( status ) {
		if ( report.valid ) {
			status.textContent =
				'Preview passed canonical Taxonomy validation. Nothing has been saved.';
		} else {
			status.textContent =
				'Preview is blocked. Resolve rejected or invalid source fields before applying.';
		}
	}
	if ( payload ) {
		payload.textContent = JSON.stringify( report.payload, null, 2 );
	}
	if ( issues ) {
		issues.replaceChildren();
		for ( const issue of report.issues ) {
			const item = document.createElement( 'li' );
			item.textContent = `${ issue.severity.replaceAll( '_', ' ' ) }: ${
				issue.message
			}`;
			issues.append( item );
		}
	}
	if ( apply instanceof HTMLButtonElement ) {
		apply.hidden = ! report.valid;
	}
}

function setCheckbox( id: string, value: unknown ): void {
	const input = textInput( id );
	if ( input && typeof value === 'boolean' ) {
		input.checked = value;
	}
}

function optionalBooleanValue( value: unknown ): string {
	if ( value === true ) {
		return 'true';
	}
	if ( value === false ) {
		return 'false';
	}
	return '';
}

function setOptionalBoolean( id: string, value: unknown ): void {
	const select = selectInput( id );
	if ( select ) {
		select.value = optionalBooleanValue( value );
	}
}

function setStringInput( id: string, value: unknown ): void {
	const input = textInput( id );
	if ( input ) {
		input.value = typeof value === 'string' ? value : '';
	}
}

function rewriteModeValue( value: unknown ): string {
	if ( value === true ) {
		return 'true';
	}
	if ( value === false ) {
		return 'false';
	}
	if ( isRecord( value ) ) {
		return 'custom';
	}
	return '';
}

function queryModeValue( value: unknown ): string {
	if ( value === true ) {
		return 'true';
	}
	if ( value === false ) {
		return 'false';
	}
	if ( typeof value === 'string' ) {
		return 'custom';
	}
	return '';
}

function applyPayload( payload: RecordValue ): void {
	const fields: Record< string, string > = {
		taxonomy_key: 'wpessential-taxonomy-key',
		name: 'wpessential-taxonomy-name',
		singular_name: 'wpessential-taxonomy-singular',
		description: 'wpessential-taxonomy-description',
	};
	for ( const [ key, id ] of Object.entries( fields ) ) {
		setStringInput( id, payload[ key ] );
	}

	const objectTypes = Array.isArray( payload.object_types )
		? payload.object_types.filter(
				( item ): item is string => typeof item === 'string'
		  )
		: [];
	const remaining = new Set( objectTypes );
	for ( const input of Array.from(
		document.querySelectorAll< HTMLInputElement >(
			'[data-wpessential-taxonomy-object-type]'
		)
	) ) {
		input.checked = remaining.has( input.value );
		remaining.delete( input.value );
	}
	const extra = textInput( 'wpessential-taxonomy-object-types-extra' );
	if ( extra ) {
		extra.value = Array.from( remaining ).join( ', ' );
	}

	for ( const [ key, id ] of Object.entries( {
		public: 'wpessential-taxonomy-public',
		show_in_rest: 'wpessential-taxonomy-rest',
		hierarchical: 'wpessential-taxonomy-hierarchical',
		show_admin_column: 'wpessential-taxonomy-admin-column',
	} ) ) {
		setCheckbox( id, payload[ key ] );
	}
	for ( const [ key, id ] of Object.entries( {
		show_ui: 'wpessential-taxonomy-show-ui',
		publicly_queryable: 'wpessential-taxonomy-publicly-queryable',
		show_in_menu: 'wpessential-taxonomy-show-in-menu',
		show_in_nav_menus: 'wpessential-taxonomy-show-in-nav-menus',
		show_tagcloud: 'wpessential-taxonomy-show-tagcloud',
		show_in_quick_edit: 'wpessential-taxonomy-show-in-quick-edit',
	} ) ) {
		setOptionalBoolean( id, payload[ key ] );
	}

	const automatic = textInput( 'wpessential-taxonomy-automatic-labels' );
	if ( automatic && typeof payload.automatic_labels === 'boolean' ) {
		automatic.checked = payload.automatic_labels;
	}
	const labels = isRecord( payload.labels ) ? payload.labels : {};
	for ( const input of Array.from(
		document.querySelectorAll< HTMLInputElement >(
			'[data-wpessential-taxonomy-label-field]'
		)
	) ) {
		const key = input.dataset.wpessentialTaxonomyLabelField ?? '';
		input.value = typeof labels[ key ] === 'string' ? labels[ key ] : '';
	}

	setStringInput( 'wpessential-taxonomy-rest-base', payload.rest_base );
	setStringInput(
		'wpessential-taxonomy-rest-namespace',
		payload.rest_namespace
	);

	const rewriteMode = selectInput( 'wpessential-taxonomy-rewrite-mode' );
	if ( rewriteMode ) {
		rewriteMode.value = rewriteModeValue( payload.rewrite );
	}
	const rewrite = isRecord( payload.rewrite ) ? payload.rewrite : {};
	setStringInput( 'wpessential-taxonomy-rewrite-slug', rewrite.slug );
	setOptionalBoolean(
		'wpessential-taxonomy-rewrite-with-front',
		rewrite.with_front
	);
	setOptionalBoolean(
		'wpessential-taxonomy-rewrite-hierarchical',
		rewrite.hierarchical
	);

	const queryMode = selectInput( 'wpessential-taxonomy-query-var-mode' );
	if ( queryMode ) {
		queryMode.value = queryModeValue( payload.query_var );
	}
	setStringInput( 'wpessential-taxonomy-query-var-name', payload.query_var );

	const form = document.getElementById( 'wpessential-taxonomy-form' );
	form?.dispatchEvent( new Event( 'input', { bubbles: true } ) );
	form?.dispatchEvent( new Event( 'change', { bubbles: true } ) );
}

function previewSource(): HTMLTextAreaElement | null {
	const source = document.getElementById( SOURCE_ID );
	return source instanceof HTMLTextAreaElement ? source : null;
}

function previewStatus(): HTMLElement | null {
	const status = document.getElementById( STATUS_ID );
	return status instanceof HTMLElement ? status : null;
}

function hideApplyButton(): void {
	const apply = document.getElementById( APPLY_ID );
	if ( apply instanceof HTMLButtonElement ) {
		apply.hidden = true;
	}
}

async function handlePreview(): Promise< void > {
	const source = previewSource();
	if ( ! source ) {
		return;
	}
	const status = previewStatus();
	if ( status ) {
		status.textContent = 'Validating CPT UI mapping…';
	}
	try {
		const raw: unknown = JSON.parse( source.value );
		if ( ! isRecord( raw ) ) {
			throw new Error( 'CPT UI source must be one JSON object.' );
		}
		renderReport( await requestPreview( raw ) );
	} catch ( error ) {
		lastReport = null;
		hideApplyButton();
		if ( status ) {
			status.textContent =
				error instanceof Error
					? error.message
					: 'CPT UI preview failed.';
		}
	}
}

function handleApply(): void {
	if ( ! lastReport?.valid ) {
		return;
	}
	applyPayload( lastReport.payload );
	const status = previewStatus();
	if ( status ) {
		status.textContent =
			'Preview values applied to local editor controls. Nothing has been saved.';
	}
}

function ensureSection(): void {
	if ( document.getElementById( SECTION_ID ) ) {
		return;
	}
	const advanced = document.getElementById(
		'wpessential-taxonomy-tier-advanced'
	);
	if ( ! ( advanced instanceof HTMLElement ) ) {
		return;
	}

	const section = document.createElement( 'section' );
	section.id = SECTION_ID;
	section.setAttribute( 'aria-labelledby', `${ SECTION_ID }-title` );
	section.innerHTML = `
		<h4 id="${ SECTION_ID }-title">CPT UI import preview</h4>
		<p class="description">Paste one CPT UI taxonomy export record as JSON. Preview maps and validates it server-side without creating or updating a Taxonomy Definition. Raw callback/class fields are rejected.</p>
		<p><label for="${ SOURCE_ID }"><strong>CPT UI taxonomy JSON</strong></label><br><textarea id="${ SOURCE_ID }" class="large-text code" rows="10"></textarea></p>
		<p><button type="button" class="button" id="${ PREVIEW_ID }">Preview mapping</button> <button type="button" class="button" id="${ APPLY_ID }" hidden>Apply preview to editor</button></p>
		<p id="${ STATUS_ID }" class="description" role="status" aria-live="polite">Nothing has been imported or saved.</p>
		<ul id="${ ISSUES_ID }"></ul>
		<details><summary>Mapped canonical payload</summary><pre id="${ PAYLOAD_ID }"></pre></details>`;
	advanced.append( section );

	document.getElementById( PREVIEW_ID )?.addEventListener( 'click', () => {
		void handlePreview();
	} );
	document
		.getElementById( APPLY_ID )
		?.addEventListener( 'click', handleApply );
}

export function bindTaxonomyCptUiPreview(): void {
	ensureSection();
}
