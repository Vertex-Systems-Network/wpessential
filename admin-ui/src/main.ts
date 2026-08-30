import './admin.scss';

type BootstrapRecord = Record<string, unknown>;

function isBootstrapRecord( value: unknown ): value is BootstrapRecord {
	return typeof value === 'object' && value !== null && ! Array.isArray( value );
}

function bootRuntimeObservatory(): void {
	const root = document.getElementById( 'wpessential-admin-root' );
	const bootstrap = document.getElementById( 'wpessential-admin-bootstrap' );

	if ( ! ( root instanceof HTMLElement ) || ! ( bootstrap instanceof HTMLScriptElement ) ) {
		return;
	}

	try {
		const payload: unknown = JSON.parse( bootstrap.textContent ?? '{}' );
		if ( ! isBootstrapRecord( payload ) ) {
			root.dataset.wpessentialEnhanced = 'invalid-bootstrap';
			return;
		}

		root.dataset.wpessentialEnhanced = 'ready';
		window.dispatchEvent(
			new CustomEvent( 'wpessential:admin-ready', {
				detail: {
					surface: root.dataset.wpessentialSurface ?? 'runtime-observatory',
					payload,
				},
			} )
		);
	} catch {
		root.dataset.wpessentialEnhanced = 'invalid-bootstrap';
	}
}

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', bootRuntimeObservatory, { once: true } );
} else {
	bootRuntimeObservatory();
}
