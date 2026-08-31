import './admin.scss';

type RecordValue = Record< string, unknown >;
type Route = { type: string; nonce: string };
type Bootstrap = {
	surface: 'configuration-packages';
	ajaxUrl: string;
	ajaxAction: string;
	maxBytes: number;
	routes: { export: Route; preflight: Route; import: Route };
};
type AjaxEnvelope = {
	success: boolean;
	data?: unknown;
	error?: { code?: string; message?: string };
};
type PreflightItem = {
	id: string;
	type: string;
	key: string;
	action: string;
	code: string;
	message: string;
};
type Preflight = {
	valid: boolean;
	strategy: string;
	package_checksum: string;
	counts: Record< string, number >;
	items: PreflightItem[];
};

function isRecord( value: unknown ): value is RecordValue {
	return (
		typeof value === 'object' && value !== null && ! Array.isArray( value )
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
		value.surface !== 'configuration-packages' ||
		typeof value.ajaxUrl !== 'string' ||
		typeof value.ajaxAction !== 'string' ||
		typeof value.maxBytes !== 'number' ||
		! Number.isInteger( value.maxBytes ) ||
		value.maxBytes < 1 ||
		! isRecord( value.routes ) ||
		! isRoute( value.routes.export ) ||
		! isRoute( value.routes.preflight ) ||
		! isRoute( value.routes.import )
	) {
		return null;
	}
	return {
		surface: 'configuration-packages',
		ajaxUrl: value.ajaxUrl,
		ajaxAction: value.ajaxAction,
		maxBytes: value.maxBytes,
		routes: {
			export: value.routes.export,
			preflight: value.routes.preflight,
			import: value.routes.import,
		},
	};
}

function isPreflightItem( value: unknown ): value is PreflightItem {
	return (
		isRecord( value ) &&
		typeof value.id === 'string' &&
		typeof value.type === 'string' &&
		typeof value.key === 'string' &&
		typeof value.action === 'string' &&
		typeof value.code === 'string' &&
		typeof value.message === 'string'
	);
}

function parseCounts( value: unknown ): Record< string, number > | null {
	if ( ! isRecord( value ) ) {
		return null;
	}
	const counts: Record< string, number > = {};
	for ( const [ key, count ] of Object.entries( value ) ) {
		if (
			typeof count !== 'number' ||
			! Number.isInteger( count ) ||
			count < 0
		) {
			return null;
		}
		counts[ key ] = count;
	}
	return counts;
}

function parsePreflight( value: unknown ): Preflight | null {
	if (
		! isRecord( value ) ||
		typeof value.valid !== 'boolean' ||
		typeof value.strategy !== 'string' ||
		typeof value.package_checksum !== 'string' ||
		! Array.isArray( value.items ) ||
		! value.items.every( isPreflightItem )
	) {
		return null;
	}
	const counts = parseCounts( value.counts );
	if ( ! counts ) {
		return null;
	}
	return {
		valid: value.valid,
		strategy: value.strategy,
		package_checksum: value.package_checksum,
		counts,
		items: value.items,
	};
}

function input( id: string ): HTMLInputElement | null {
	const element = document.getElementById( id );
	return element instanceof HTMLInputElement ? element : null;
}

function textarea( id: string ): HTMLTextAreaElement | null {
	const element = document.getElementById( id );
	return element instanceof HTMLTextAreaElement ? element : null;
}

function select( id: string ): HTMLSelectElement | null {
	const element = document.getElementById( id );
	return element instanceof HTMLSelectElement ? element : null;
}

function button( id: string ): HTMLButtonElement | null {
	const element = document.getElementById( id );
	return element instanceof HTMLButtonElement ? element : null;
}

function setNotice( message: string, error = false ): void {
	const notice = document.getElementById(
		'wpessential-import-export-notice'
	);
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

async function postRoute(
	bootstrap: Bootstrap,
	route: Route,
	payload: RecordValue
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
				'The requested package operation could not be completed.'
		);
	}
	return envelope.data;
}

function renderPreflight( report: Preflight ): void {
	const container = document.getElementById( 'wpessential-package-report' );
	if ( ! ( container instanceof HTMLElement ) ) {
		return;
	}
	const summary = container.querySelector(
		'[data-wpessential-package-report-summary]'
	);
	const items = container.querySelector(
		'[data-wpessential-package-report-items]'
	);
	if (
		! ( summary instanceof HTMLElement ) ||
		! ( items instanceof HTMLElement )
	) {
		return;
	}

	const create = report.counts.create ?? 0;
	const update = report.counts.update ?? 0;
	const noChange = report.counts.no_change ?? 0;
	const blocked = report.counts.blocked ?? 0;
	summary.textContent = report.valid
		? `Preflight passed: ${ create } create, ${ update } update, ${ noChange } no change.`
		: `Preflight blocked: ${ blocked } blocking item(s), ${ create } create, ${ update } update, ${ noChange } no change.`;

	items.replaceChildren();
	for ( const item of report.items ) {
		const row = document.createElement( 'li' );
		row.dataset.wpessentialPackageAction = item.action;
		row.textContent = `${ item.action.replaceAll( '_', ' ' ) }: ${
			item.type
		} ${ item.key } — ${ item.message }`;
		items.append( row );
	}
	container.hidden = false;
	container.classList.add( 'notice' );
	container.classList.toggle( 'notice-success', report.valid );
	container.classList.toggle( 'notice-error', ! report.valid );
}

function boot(): void {
	const root = document.getElementById( 'wpessential-import-export-root' );
	const bootstrapScript = document.getElementById(
		'wpessential-import-export-bootstrap'
	);
	if (
		! ( root instanceof HTMLElement ) ||
		! ( bootstrapScript instanceof HTMLScriptElement )
	) {
		return;
	}

	let raw: unknown;
	try {
		raw = JSON.parse( bootstrapScript.textContent ?? '{}' );
	} catch {
		root.dataset.wpessentialEnhanced = 'invalid-bootstrap';
		return;
	}
	const bootstrap = parseBootstrap( raw );
	if ( ! bootstrap ) {
		root.dataset.wpessentialEnhanced = 'invalid-bootstrap';
		return;
	}

	const exportJson = textarea( 'wpessential-package-export-json' );
	const importJson = textarea( 'wpessential-package-import-json' );
	const strategy = select( 'wpessential-package-strategy' );
	const apply = button( 'wpessential-package-import' );
	const download = button( 'wpessential-package-export-download' );
	let preflightChecksum = '';
	let preflightJson = '';
	let preflightStrategy = '';
	let busy = false;

	const invalidatePreflight = (): void => {
		preflightChecksum = '';
		preflightJson = '';
		preflightStrategy = '';
		if ( apply ) {
			apply.disabled = true;
		}
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
					: 'The requested package operation could not be completed.',
				true
			);
		} finally {
			busy = false;
			root.removeAttribute( 'aria-busy' );
		}
	};

	button( 'wpessential-package-export-generate' )?.addEventListener(
		'click',
		() => {
			void run( async () => {
				const data = await postRoute(
					bootstrap,
					bootstrap.routes.export,
					{
						include_cpt:
							input( 'wpessential-package-export-cpt' )
								?.checked ?? true,
						include_taxonomy:
							input( 'wpessential-package-export-taxonomy' )
								?.checked ?? true,
					}
				);
				if (
					! isRecord( data ) ||
					typeof data.package_json !== 'string'
				) {
					throw new Error(
						'Configuration package export response was invalid.'
					);
				}
				if ( exportJson ) {
					exportJson.value = data.package_json;
				}
				if ( download ) {
					download.disabled = false;
				}
				setNotice(
					`Configuration package generated with ${ String(
						data.definition_count ?? 0
					) } definition(s).`
				);
			} );
		}
	);

	download?.addEventListener( 'click', () => {
		const json = exportJson?.value ?? '';
		if ( json === '' ) {
			return;
		}
		const url = URL.createObjectURL(
			new Blob( [ json ], { type: 'application/json;charset=utf-8' } )
		);
		const anchor = document.createElement( 'a' );
		anchor.href = url;
		anchor.download = 'wpessential-definition-package.json';
		anchor.click();
		URL.revokeObjectURL( url );
	} );

	input( 'wpessential-package-file' )?.addEventListener(
		'change',
		( event ) => {
			const target = event.currentTarget;
			if ( ! ( target instanceof HTMLInputElement ) ) {
				return;
			}
			const file = target.files?.[ 0 ];
			if ( ! file ) {
				return;
			}
			if ( file.size > bootstrap.maxBytes ) {
				setNotice(
					'Selected package exceeds the allowed JSON size.',
					true
				);
				target.value = '';
				return;
			}
			void file.text().then( ( json ) => {
				if ( importJson ) {
					importJson.value = json;
				}
				invalidatePreflight();
			} );
		}
	);

	importJson?.addEventListener( 'input', invalidatePreflight );
	strategy?.addEventListener( 'change', invalidatePreflight );

	button( 'wpessential-package-preflight' )?.addEventListener(
		'click',
		() => {
			void run( async () => {
				const json = importJson?.value.trim() ?? '';
				const selectedStrategy = strategy?.value ?? 'create_only';
				if ( json === '' ) {
					throw new Error(
						'Paste or load a configuration package before preflight.'
					);
				}
				const data = await postRoute(
					bootstrap,
					bootstrap.routes.preflight,
					{
						package_json: json,
						strategy: selectedStrategy,
					}
				);
				const report = parsePreflight( data );
				if ( ! report ) {
					throw new Error(
						'Configuration package preflight response was invalid.'
					);
				}
				renderPreflight( report );
				if ( report.valid ) {
					preflightChecksum = report.package_checksum;
					preflightJson = json;
					preflightStrategy = selectedStrategy;
					if ( apply ) {
						apply.disabled = false;
					}
					setNotice( 'Configuration package preflight passed.' );
				} else {
					invalidatePreflight();
					setNotice(
						'Configuration package preflight found blocking conflicts.',
						true
					);
				}
			} );
		}
	);

	apply?.addEventListener( 'click', () => {
		void run( async () => {
			const json = importJson?.value.trim() ?? '';
			const selectedStrategy = strategy?.value ?? 'create_only';
			if (
				preflightChecksum === '' ||
				json !== preflightJson ||
				selectedStrategy !== preflightStrategy
			) {
				invalidatePreflight();
				throw new Error(
					'Run a fresh preflight for the exact package and strategy before importing.'
				);
			}
			const data = await postRoute( bootstrap, bootstrap.routes.import, {
				package_json: json,
				strategy: selectedStrategy,
				expected_package_checksum: preflightChecksum,
			} );
			if ( ! isRecord( data ) || ! isRecord( data.counts ) ) {
				throw new Error(
					'Configuration package import response was invalid.'
				);
			}
			const created = data.counts.created ?? 0;
			const updated = data.counts.updated ?? 0;
			const noChange = data.counts.no_change ?? 0;
			invalidatePreflight();
			setNotice(
				`Configuration package applied: ${ String(
					created
				) } created, ${ String( updated ) } updated, ${ String(
					noChange
				) } unchanged.`
			);
		} );
	} );

	root.dataset.wpessentialEnhanced = 'ready';
	window.dispatchEvent(
		new CustomEvent( 'wpessential:admin-ready', {
			detail: { surface: 'configuration-packages', payload: bootstrap },
		} )
	);
}

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', boot, { once: true } );
} else {
	boot();
}
