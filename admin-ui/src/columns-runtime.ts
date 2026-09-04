import {
	mountAdminColumnsScaffold,
	parseAdminColumnsBootstrap,
} from './columns';

function failBootstrap( root: HTMLElement ): void {
	root.dataset.wpessentialEnhanced = 'invalid-bootstrap';
	root.textContent =
		'Admin Columns editor is unavailable because its bootstrap contract is invalid.';
}

function boot(): void {
	const root = document.getElementById( 'wpessential-columns-root' );
	const script = document.getElementById( 'wpessential-columns-bootstrap' );
	if (
		! ( root instanceof HTMLElement ) ||
		! ( script instanceof HTMLScriptElement )
	) {
		return;
	}

	try {
		const bootstrap = parseAdminColumnsBootstrap(
			JSON.parse( script.textContent ?? '{}' )
		);
		if ( bootstrap === null ) {
			failBootstrap( root );
			return;
		}
		mountAdminColumnsScaffold( root, bootstrap );
		window.dispatchEvent(
			new CustomEvent( 'wpessential:admin-ready', {
				detail: { surface: 'columns', payload: bootstrap },
			} )
		);
	} catch {
		failBootstrap( root );
	}
}

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', boot, { once: true } );
} else {
	boot();
}
