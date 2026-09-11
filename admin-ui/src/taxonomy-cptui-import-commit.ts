type RecordValue = Record< string, unknown >;

type MainBootstrap = {
	ajaxUrl: string;
	ajaxAction: string;
};
type CommitRoute = {
	type: string;
	nonce: string;
};
type TaxonomyDefinition = {
	id: string;
	status: string;
	revision: number;
	payload: RecordValue;
};

const SECTION_ID = 'wpessential-taxonomy-cptui-preview';
const SOURCE_ID = 'wpessential-taxonomy-cptui-source';
const APPLY_ID = 'wpessential-taxonomy-cptui-apply-button';
const PAYLOAD_ID = 'wpessential-taxonomy-cptui-preview-payload';
const CREATE_ID = 'wpessential-taxonomy-cptui-import-create';
const UPDATE_ID = 'wpessential-taxonomy-cptui-import-update';
const STATUS_ID = 'wpessential-taxonomy-cptui-import-status';

function isRecord( value: unknown ): value is RecordValue {
	return (
		typeof value === 'object' && value !== null && ! Array.isArray( value )
	);
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

function commitRoute(): CommitRoute | null {
	const value = parseScript(
		'wpessential-taxonomy-import-preview-bootstrap'
	);
	if (
		! value ||
		typeof value.commit_type !== 'string' ||
		typeof value.commit_nonce !== 'string'
	) {
		return null;
	}
	return { type: value.commit_type, nonce: value.commit_nonce };
}

function sourceObject(): RecordValue | null {
	const source = document.getElementById( SOURCE_ID );
	if ( ! ( source instanceof HTMLTextAreaElement ) ) {
		return null;
	}
	try {
		const value: unknown = JSON.parse( source.value );
		return isRecord( value ) ? value : null;
	} catch {
		return null;
	}
}

function previewPayload(): RecordValue | null {
	const preview = document.getElementById( PAYLOAD_ID );
	if ( ! ( preview instanceof HTMLElement ) || preview.textContent === '' ) {
		return null;
	}
	try {
		const value: unknown = JSON.parse( preview.textContent );
		return isRecord( value ) ? value : null;
	} catch {
		return null;
	}
}

function textInput( id: string ): HTMLInputElement | null {
	const input = document.getElementById( id );
	return input instanceof HTMLInputElement ? input : null;
}

function button( id: string ): HTMLButtonElement | null {
	const element = document.getElementById( id );
	return element instanceof HTMLButtonElement ? element : null;
}

function isDefinition( value: unknown ): value is TaxonomyDefinition {
	return (
		isRecord( value ) &&
		typeof value.id === 'string' &&
		typeof value.status === 'string' &&
		typeof value.revision === 'number' &&
		Number.isInteger( value.revision ) &&
		value.revision > 0 &&
		isRecord( value.payload )
	);
}

function parseSavedDefinition( value: unknown ): TaxonomyDefinition | null {
	if ( ! isRecord( value ) || ! isDefinition( value.definition ) ) {
		return null;
	}
	return value.definition;
}

function importStatus(): HTMLElement | null {
	const status = document.getElementById( STATUS_ID );
	return status instanceof HTMLElement ? status : null;
}

function setImportStatus( message: string ): void {
	const status = importStatus();
	if ( status ) {
		status.textContent = message;
	}
}

function previewIsValid(): boolean {
	const apply = button( APPLY_ID );
	return apply instanceof HTMLButtonElement && ! apply.hidden;
}

function currentEditorTarget(): {
	id: string;
	revision: number;
	key: string;
} | null {
	const id = textInput( 'wpessential-taxonomy-id' )?.value.trim() ?? '';
	const revision = Number(
		textInput( 'wpessential-taxonomy-revision' )?.value ?? 0
	);
	const key = textInput( 'wpessential-taxonomy-key' )?.value.trim() ?? '';
	if ( id === '' || ! Number.isInteger( revision ) || revision < 1 || key === '' ) {
		return null;
	}
	return { id, revision, key };
}

function updateCommitControls(): void {
	const create = button( CREATE_ID );
	const update = button( UPDATE_ID );
	if ( ! create || ! update ) {
		return;
	}

	const payload = previewPayload();
	const valid = previewIsValid() && payload !== null;
	create.hidden = ! valid;
	update.hidden = true;
	if ( ! valid ) {
		return;
	}

	const target = currentEditorTarget();
	const previewKey = payload.taxonomy_key;
	if ( ! target ) {
		setImportStatus(
			'Valid preview can be imported as a new draft. Open an existing taxonomy to enable a revision-safe update.'
		);
		return;
	}
	if ( typeof previewKey !== 'string' || previewKey !== target.key ) {
		setImportStatus(
			'Update is unavailable because the preview taxonomy key differs from the current Definition. Taxonomy-key migration is separately gated.'
		);
		return;
	}

	update.hidden = false;
	setImportStatus(
		`Valid preview may create a new draft or update the current Definition at revision ${ target.revision }.`
	);
}

async function requestCommit( payload: RecordValue ): Promise< TaxonomyDefinition > {
	const main = mainBootstrap();
	const route = commitRoute();
	if ( ! main || ! route ) {
		throw new Error( 'CPT UI import commit is unavailable on this page.' );
	}

	const body = new URLSearchParams();
	body.set( 'action', main.ajaxAction );
	body.set( 'type', route.type );
	body.set( 'nonce', route.nonce );
	body.set( 'payload_json', JSON.stringify( payload ) );
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
		let message = 'CPT UI import commit failed.';
		if (
			isRecord( envelope ) &&
			isRecord( envelope.error ) &&
			typeof envelope.error.message === 'string'
		) {
			message = envelope.error.message;
		}
		throw new Error( message );
	}

	const definition = parseSavedDefinition( envelope.data );
	if ( ! definition ) {
		throw new Error( 'CPT UI import commit returned an invalid response.' );
	}
	return definition;
}

function announceCommitted( definition: TaxonomyDefinition ): void {
	window.dispatchEvent(
		new CustomEvent( 'wpessential:taxonomy-import-committed', {
			detail: { definition },
		} )
	);
}

async function commitCreate(): Promise< void > {
	const source = sourceObject();
	if ( ! previewIsValid() || ! source ) {
		setImportStatus( 'Preview the current CPT UI source successfully before importing.' );
		return;
	}
	const create = button( CREATE_ID );
	const update = button( UPDATE_ID );
	if ( create ) {
		create.disabled = true;
	}
	if ( update ) {
		update.disabled = true;
	}
	setImportStatus( 'Importing through the canonical Taxonomy save path…' );
	try {
		const definition = await requestCommit( { source } );
		announceCommitted( definition );
		setImportStatus( 'Taxonomy imported as a new draft through the canonical save path.' );
	} catch ( error ) {
		setImportStatus(
			error instanceof Error ? error.message : 'CPT UI import commit failed.'
		);
	} finally {
		if ( create ) {
			create.disabled = false;
		}
		if ( update ) {
			update.disabled = false;
		}
	}
}

async function commitUpdate(): Promise< void > {
	const source = sourceObject();
	const target = currentEditorTarget();
	const payload = previewPayload();
	if (
		! previewIsValid() ||
		! source ||
		! target ||
		! payload ||
		payload.taxonomy_key !== target.key
	) {
		setImportStatus(
			'Update requires a valid same-key preview and a current Definition revision.'
		);
		return;
	}
	const create = button( CREATE_ID );
	const update = button( UPDATE_ID );
	if ( create ) {
		create.disabled = true;
	}
	if ( update ) {
		update.disabled = true;
	}
	setImportStatus(
		`Updating current Definition from revision ${ target.revision } through canonical CAS…`
	);
	try {
		const definition = await requestCommit( {
			source,
			id: target.id,
			expected_revision: target.revision,
		} );
		announceCommitted( definition );
		setImportStatus(
			`Current Taxonomy Definition updated to revision ${ definition.revision }.`
		);
	} catch ( error ) {
		setImportStatus(
			error instanceof Error ? error.message : 'CPT UI import update failed.'
		);
	} finally {
		if ( create ) {
			create.disabled = false;
		}
		if ( update ) {
			update.disabled = false;
		}
	}
}

function ensureCommitControls(): void {
	const section = document.getElementById( SECTION_ID );
	const apply = button( APPLY_ID );
	if ( ! ( section instanceof HTMLElement ) || ! apply ) {
		return;
	}
	if ( document.getElementById( CREATE_ID ) ) {
		updateCommitControls();
		return;
	}

	const actions = document.createElement( 'p' );
	const create = document.createElement( 'button' );
	create.type = 'button';
	create.className = 'button button-primary';
	create.id = CREATE_ID;
	create.textContent = 'Import as new draft';
	create.hidden = true;
	const update = document.createElement( 'button' );
	update.type = 'button';
	update.className = 'button';
	update.id = UPDATE_ID;
	update.textContent = 'Update current definition';
	update.hidden = true;
	actions.append( create, document.createTextNode( ' ' ), update );

	const status = document.createElement( 'p' );
	status.id = STATUS_ID;
	status.className = 'description';
	status.setAttribute( 'role', 'status' );
	status.setAttribute( 'aria-live', 'polite' );
	status.textContent =
		'Commit controls appear only after the current source passes preview validation.';

	const previewStatus = document.getElementById(
		'wpessential-taxonomy-cptui-preview-status'
	);
	if ( previewStatus instanceof HTMLElement ) {
		previewStatus.insertAdjacentElement( 'afterend', actions );
		actions.insertAdjacentElement( 'afterend', status );
	} else {
		section.append( actions, status );
	}

	create.addEventListener( 'click', () => {
		void commitCreate();
	} );
	update.addEventListener( 'click', () => {
		void commitUpdate();
	} );
	apply.addEventListener( 'click', updateCommitControls );
	new MutationObserver( updateCommitControls ).observe( apply, {
		attributes: true,
		attributeFilter: [ 'hidden' ],
	} );
	document.getElementById( SOURCE_ID )?.addEventListener( 'input', () => {
		create.hidden = true;
		update.hidden = true;
		setImportStatus( 'Source changed. Preview the current JSON again before importing.' );
	} );
	window.addEventListener( 'wpessential:taxonomy-editor-changed', updateCommitControls );
	updateCommitControls();
}

export function bindTaxonomyCptUiImportCommit(): void {
	ensureCommitControls();
}
