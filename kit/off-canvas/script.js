document.addEventListener( 'click', ( e ) => {
	const open = e.target.closest?.( '.wpe-oc__open' );
	if ( open ) {
		const d = document.getElementById( open.getAttribute( 'aria-controls' ) );
		// Fallback for browsers without native modal dialogs: plain open attribute.
		d?.showModal ? d.showModal() : d?.setAttribute( 'open', '' );
		return;
	}
	const dialog = e.target.closest?.( '.wpe-oc__panel' );
	// A click on the close button, or on the backdrop (outside the panel's box), closes it.
	const r = dialog?.getBoundingClientRect();
	const outside = r && ( e.clientX < r.left || e.clientX > r.right || e.clientY < r.top || e.clientY > r.bottom );
	if ( dialog && ( e.target.closest( '.wpe-oc__close' ) || ( e.target === dialog && outside ) ) ) {
		dialog.close ? dialog.close() : dialog.removeAttribute( 'open' );
	}
} );
