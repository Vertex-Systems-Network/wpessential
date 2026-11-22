document.addEventListener( 'click', ( e ) => {
	const open = e.target.closest?.( '.wpe-oc__open' );
	if ( open ) {
		document.getElementById( open.getAttribute( 'aria-controls' ) )?.showModal();
		return;
	}
	const dialog = e.target.closest?.( '.wpe-oc__panel' );
	// A click on the close button, or on the backdrop (the dialog itself, outside its content), closes it.
	if ( dialog && ( e.target.closest( '.wpe-oc__close' ) || e.target === dialog ) ) {
		dialog.close();
	}
} );
