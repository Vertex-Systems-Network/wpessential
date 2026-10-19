import './custom-tables.scss';

type JsonObject = Record< string, unknown >;
type Route = { type: string; nonce: string };
type Action =
	| 'prepare'
	| 'submit'
	| 'approve'
	| 'revalidate'
	| 'cancel'
	| 'confirm'
	| 'execute';
type Table = {
	definition_id: string;
	key: string;
	label: string;
	revision: number;
};
type Bootstrap = {
	surface: 'custom-tables';
	ajaxUrl: string;
	ajaxAction: string;
	routes: Record< Action, Route >;
	tables: Table[];
};
type Run = { run_id: string; state: string; state_revision: number };
type AjaxEnvelope = {
	success: boolean;
	data?: unknown;
	error?: { code?: string; message?: string };
};

const ACTIONS: Action[] = [
	'prepare',
	'submit',
	'approve',
	'revalidate',
	'cancel',
	'confirm',
	'execute',
];

const CANCELLABLE = [ 'planned', 'awaiting_review', 'approved', 'revalidating' ];

const REASONS: Record< string, string > = {
	plan_empty: 'The table already matches its definition; there is nothing to migrate.',
	plan_risk_above_r1:
		'This change is not purely additive and cannot run from this screen.',
	operation_not_additive:
		'This change is not purely additive and cannot run from this screen.',
	recovery_evidence_missing:
		'No verified database backup bound to this plan was found for that backup id.',
	recovery_verification_failed:
		'The backup id could not be verified as a database backup for this plan.',
	confirmation_missing: 'Confirm the execution before running it.',
	reviewed_plan_changed:
		'The table changed since review. Prepare a new migration.',
	definition_revision_changed:
		'The table definition changed since review; this run is superseded.',
};

function isObject( value: unknown ): value is JsonObject {
	return (
		typeof value === 'object' && value !== null && ! Array.isArray( value )
	);
}

function isRoute( value: unknown ): value is Route {
	return (
		isObject( value ) &&
		typeof value.type === 'string' &&
		typeof value.nonce === 'string'
	);
}

function isTable( value: unknown ): value is Table {
	return (
		isObject( value ) &&
		typeof value.definition_id === 'string' &&
		typeof value.key === 'string' &&
		typeof value.label === 'string' &&
		typeof value.revision === 'number'
	);
}

function isRun( value: unknown ): value is Run {
	return (
		isObject( value ) &&
		typeof value.run_id === 'string' &&
		typeof value.state === 'string' &&
		typeof value.state_revision === 'number'
	);
}

function parseBootstrap( value: unknown ): Bootstrap | null {
	if (
		! isObject( value ) ||
		value.surface !== 'custom-tables' ||
		typeof value.ajaxUrl !== 'string' ||
		typeof value.ajaxAction !== 'string' ||
		! isObject( value.routes ) ||
		! Array.isArray( value.tables ) ||
		! value.tables.every( isTable )
	) {
		return null;
	}
	const routes = value.routes;
	if ( ! ACTIONS.every( ( action ) => isRoute( routes[ action ] ) ) ) {
		return null;
	}

	return value as unknown as Bootstrap;
}

function reasonText( reasons: unknown ): string {
	if ( ! Array.isArray( reasons ) || reasons.length === 0 ) {
		return '';
	}

	return reasons
		.map( ( reason ) =>
			typeof reason === 'string' ? ( REASONS[ reason ] ?? reason ) : ''
		)
		.filter( ( text, index, all ) => text !== '' && all.indexOf( text ) === index )
		.join( ' ' );
}

function setNotice( message: string, error = false ): void {
	const notice = document.getElementById( 'wpessential-custom-tables-notice' );
	if ( ! ( notice instanceof HTMLElement ) ) {
		return;
	}
	const paragraph = notice.querySelector( 'p' );
	if ( paragraph ) {
		paragraph.textContent = message;
	}
	notice.hidden = message === '';
	notice.setAttribute( 'role', error ? 'alert' : 'status' );
	notice.classList.toggle( 'notice-error', error );
	notice.classList.toggle( 'notice-success', ! error && message !== '' );
}

async function post(
	bootstrap: Bootstrap,
	action: Action,
	payload: JsonObject
): Promise< JsonObject > {
	const route = bootstrap.routes[ action ];
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
	const decoded: unknown = await response.json();
	if ( ! isObject( decoded ) || typeof decoded.success !== 'boolean' ) {
		throw new Error( 'WPEssential returned an invalid AJAX response.' );
	}
	const envelope = decoded as AjaxEnvelope;
	if ( ! response.ok || ! envelope.success || ! isObject( envelope.data ) ) {
		throw new Error(
			envelope.error?.message ?? 'The Custom Tables migration step failed.'
		);
	}

	return envelope.data;
}

function element< K extends keyof HTMLElementTagNameMap >(
	tag: K,
	text = '',
	className = ''
): HTMLElementTagNameMap[ K ] {
	const node = document.createElement( tag );
	if ( text !== '' ) {
		node.textContent = text;
	}
	if ( className !== '' ) {
		node.className = className;
	}

	return node;
}

function definitionRow( list: HTMLDListElement, term: string, value: string ): void {
	list.append( element( 'dt', term ), element( 'dd', value ) );
}

function renderPreview( container: HTMLElement, preview: JsonObject ): void {
	const section = element( 'section', '', 'wpessential-custom-tables-preview' );
	section.append( element( 'h3', 'Migration review' ) );

	const list = element( 'dl' );
	definitionRow( list, 'Risk', String( preview.risk ?? '' ) );
	definitionRow( list, 'Database', String( preview.provider ?? '' ) );
	definitionRow(
		list,
		'Operations',
		Array.isArray( preview.operations ) ? preview.operations.join( ', ' ) : ''
	);
	definitionRow(
		list,
		'Affected rows (estimate)',
		String( preview.affected_rows_estimate ?? '' )
	);
	definitionRow( list, 'Backup requirement', String( preview.backup_requirement ?? '' ) );
	definitionRow( list, 'Rollback strategy', String( preview.rollback_strategy ?? '' ) );
	definitionRow( list, 'Data compatibility', String( preview.data_compatibility ?? '' ) );
	definitionRow( list, 'Dependency impact', String( preview.dependency_impact ?? '' ) );
	section.append( list );

	if ( Array.isArray( preview.lock_notes ) && preview.lock_notes.length > 0 ) {
		section.append( element( 'h4', 'Locking' ) );
		const notes = element( 'ul' );
		for ( const note of preview.lock_notes ) {
			notes.append( element( 'li', String( note ) ) );
		}
		section.append( notes );
	}

	section.append( element( 'h4', 'Schema changes' ) );
	const sql = element( 'pre', '', 'wpessential-custom-tables-sql' );
	sql.append(
		element(
			'code',
			Array.isArray( preview.schema_diff ) ? preview.schema_diff.join( ';\n\n' ) : ''
		)
	);
	section.append( sql );
	container.append( section );
}

function bootScreen( app: HTMLElement, bootstrap: Bootstrap ): void {
	let run: Run | null = null;
	let preview: JsonObject | null = null;
	let confirmed = false;
	let busy = false;
	const panel = element( 'div', '', 'wpessential-custom-tables-panel' );

	const call = async ( action: Action, payload: JsonObject ): Promise< JsonObject | null > => {
		if ( busy ) {
			return null;
		}
		busy = true;
		app.setAttribute( 'aria-busy', 'true' );
		setNotice( '' );
		try {
			return await post( bootstrap, action, payload );
		} catch ( error ) {
			setNotice( error instanceof Error ? error.message : String( error ), true );
			return null;
		} finally {
			busy = false;
			app.removeAttribute( 'aria-busy' );
			renderPanel();
		}
	};

	const step = async ( action: Action ): Promise< void > => {
		if ( run === null ) {
			return;
		}
		const data = await call( action, {
			run_id: run.run_id,
			expected_state_revision: run.state_revision,
		} );
		if ( data !== null && isRun( data.run ) ) {
			run = data.run;
			if ( action === 'confirm' ) {
				confirmed = data.confirmed === true;
			}
			setNotice( `Run is now ${ run.state.replace( /_/g, ' ' ) }.` );
			renderPanel();
		}
	};

	const execute = async ( backupId: string ): Promise< void > => {
		if ( run === null ) {
			return;
		}
		// The spec requires an explicit confirmation before a schema change runs.
		if (
			! window.confirm(
				'Apply this schema change to the live database now? A verified backup is required and the change is recorded in the audit log.'
			)
		) {
			return;
		}
		const data = await call( 'execute', { run_id: run.run_id, backup_id: backupId } );
		if ( data === null ) {
			return;
		}
		if ( data.applied === true ) {
			run = { ...run, state: 'applied' };
			setNotice( 'The migration was applied and verified.' );
		} else if ( data.superseded === true ) {
			run = { ...run, state: 'superseded' };
			setNotice( reasonText( data.reasons ), true );
		} else {
			if ( typeof data.state === 'string' ) {
				run = { ...run, state: data.state };
			}
			setNotice(
				reasonText( data.reasons ) ||
					( data.mutation_attempted === true
						? 'The migration failed after starting. Review the run before retrying.'
						: 'The migration was not executed.' ),
				true
			);
		}
		renderPanel();
	};

	const prepare = async ( table: Table ): Promise< void > => {
		const data = await call( 'prepare', { definition_id: table.definition_id } );
		if ( data === null ) {
			return;
		}
		if ( data.prepared !== true || ! isRun( data.run ) || ! isObject( data.preview ) ) {
			run = null;
			preview = null;
			setNotice( reasonText( data.reasons ) || 'No migration was prepared.', true );
		} else {
			run = data.run;
			preview = data.preview;
			confirmed = false;
			setNotice( `Migration prepared for ${ table.label }. Review it, then submit.` );
		}
		renderPanel();
	};

	const button = ( label: string, onClick: () => void, primary = false ): HTMLButtonElement => {
		const control = element( 'button', label, primary ? 'button button-primary' : 'button' );
		control.type = 'button';
		control.disabled = busy;
		control.addEventListener( 'click', onClick );

		return control;
	};

	function renderPanel(): void {
		panel.replaceChildren();
		if ( run === null || preview === null ) {
			return;
		}
		panel.append(
			element( 'h2', 'Migration run' ),
			element( 'p', `State: ${ run.state.replace( /_/g, ' ' ) }`, 'wpessential-custom-tables-state' )
		);
		renderPreview( panel, preview );

		const actions = element( 'div', '', 'wpessential-custom-tables-actions' );
		const current = run.state;
		if ( current === 'planned' ) {
			actions.append( button( 'Submit for review', () => void step( 'submit' ), true ) );
		} else if ( current === 'awaiting_review' ) {
			actions.append( button( 'Approve', () => void step( 'approve' ), true ) );
		} else if ( current === 'approved' ) {
			actions.append( button( 'Start revalidation', () => void step( 'revalidate' ), true ) );
		} else if ( current === 'revalidating' && ! confirmed ) {
			actions.append( button( 'Confirm execution', () => void step( 'confirm' ), true ) );
		} else if ( current === 'revalidating' ) {
			const label = element( 'label', 'Verified backup id' );
			const input = element( 'input', '', 'regular-text' );
			input.type = 'text';
			input.autocomplete = 'off';
			input.id = 'wpessential-custom-tables-backup-id';
			label.htmlFor = input.id;
			const run_button = button(
				'Execute migration',
				() => void execute( input.value.trim() ),
				true
			);
			run_button.disabled = true;
			input.addEventListener( 'input', () => {
				run_button.disabled = busy || input.value.trim() === '';
			} );
			actions.append( label, input, run_button );
		}
		if ( CANCELLABLE.includes( current ) ) {
			actions.append( button( 'Cancel run', () => void step( 'cancel' ) ) );
		}
		panel.append( actions );
	}

	if ( bootstrap.tables.length === 0 ) {
		app.append( element( 'p', 'No Custom Tables definitions exist yet.' ) );
		return;
	}

	const table = element( 'table', '', 'widefat striped' );
	const head = element( 'thead' );
	const headRow = element( 'tr' );
	for ( const title of [ 'Table', 'Key', 'Definition revision', '' ] ) {
		headRow.append( element( 'th', title ) );
	}
	head.append( headRow );
	const body = element( 'tbody' );
	for ( const entry of bootstrap.tables ) {
		const row = element( 'tr' );
		const cell = element( 'td' );
		cell.append( button( 'Review migration', () => void prepare( entry ) ) );
		row.append(
			element( 'td', entry.label ),
			element( 'td', entry.key ),
			element( 'td', String( entry.revision ) ),
			cell
		);
		body.append( row );
	}
	table.append( head, body );
	app.append( table, panel );
}

function boot(): void {
	const root = document.getElementById( 'wpessential-custom-tables-root' );
	const app = document.getElementById( 'wpessential-custom-tables-app' );
	const script = document.getElementById( 'wpessential-custom-tables-bootstrap' );
	if ( ! ( root instanceof HTMLElement ) || ! ( app instanceof HTMLElement ) || ! script ) {
		return;
	}

	let bootstrap: Bootstrap | null = null;
	try {
		bootstrap = parseBootstrap( JSON.parse( script.textContent ?? '' ) );
	} catch {
		bootstrap = null;
	}
	if ( bootstrap === null ) {
		root.dataset.wpessentialEnhanced = 'invalid-bootstrap';
		setNotice( 'The Custom Tables screen could not start. Reload the page.', true );
		return;
	}

	root.dataset.wpessentialEnhanced = 'ready';
	bootScreen( app, bootstrap );
}

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', boot );
} else {
	boot();
}
