document.addEventListener( 'click', ( e ) => {
	const open = e.target.closest?.( '.wpe-oc__open' );
	if ( open ) {
		document.getElementById( open.getAttribute( 'aria-controls' ) )?.showModal();
		return;
	}
	const dialog = e.target.closest?.( '.wpe-oc__panel' );
	// A click on the close button, or on the backdrop (outside the panel's box), closes it.
	const r = dialog?.getBoundingClientRect();
	const outside = r && ( e.clientX < r.left || e.clientX > r.right || e.clientY < r.top || e.clientY > r.bottom );
	if ( dialog && ( e.target.closest( '.wpe-oc__close' ) || ( e.target === dialog && outside ) ) ) {
		dialog.close();
	}
} );
