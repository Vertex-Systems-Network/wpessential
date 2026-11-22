document.addEventListener( 'click', ( e ) => {
	const btn = e.target.closest?.( '[data-wpe-dismiss]' );
	if ( ! btn ) {
		return;
	}
	const box = btn.closest( '[data-wpe-key]' );
	document.querySelectorAll( '[data-wpe-key]' ).forEach( ( b ) => {
		if ( b.dataset.wpeKey === box.dataset.wpeKey ) {
			b.hidden = true;
		}
	} );
	try {
		localStorage.setItem( box.dataset.wpeKey, btn.dataset.wpeDismiss || '1' );
	} catch ( err ) {}
} );
document.querySelectorAll( '[data-wpe-key]' ).forEach( ( box ) => {
	let seen = null;
	try {
		seen = localStorage.getItem( box.dataset.wpeKey );
	} catch ( err ) {}
	box.hidden = !! seen;
} );
try {
	const c = localStorage.getItem( 'wpe-consent' );
	if ( c ) {
		document.documentElement.dataset.wpeConsent = c;
	}
} catch ( err ) {}
document.addEventListener( 'click', ( e ) => {
	const b = e.target.closest?.( '.wpe-cookie [data-wpe-dismiss]' );
	if ( b ) {
		document.documentElement.dataset.wpeConsent = b.dataset.wpeDismiss;
	}
} );
