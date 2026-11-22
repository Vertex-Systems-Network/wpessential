document.addEventListener( 'click', ( e ) => {
	const btn = e.target.closest?.( '[data-wpe-dismiss]' );
	if ( ! btn ) {
		return;
	}
	const box = btn.closest( '[data-wpe-key]' );
	box.hidden = true;
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
