document.addEventListener( 'click', ( e ) => {
	const tab = e.target.closest?.( '.wpe-login [role=tab]' );
	if ( tab ) {
		select( tab );
	}
} );
document.addEventListener( 'keydown', ( e ) => {
	const tab = e.target.closest?.( '.wpe-login [role=tab]' );
	if ( tab && [ 'ArrowLeft', 'ArrowRight' ].includes( e.key ) ) {
		const other = [ ...tab.parentElement.children ].find( ( t ) => t !== tab );
		select( other );
		other.focus();
	}
} );
function select( tab ) {
	tab.parentElement.querySelectorAll( '[role=tab]' ).forEach( ( t ) => {
		const on = t === tab;
		t.setAttribute( 'aria-selected', on ? 'true' : 'false' );
		t.tabIndex = on ? 0 : -1;
		document.getElementById( t.getAttribute( 'aria-controls' ) ).hidden = ! on;
	} );
}
// Panels stay visible without JavaScript; hide the inactive one once this runs.
document.querySelectorAll( '.wpe-login [role=tab][aria-selected=true]' ).forEach( select );
