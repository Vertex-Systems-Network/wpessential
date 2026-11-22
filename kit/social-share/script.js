document.addEventListener( 'click', ( e ) => {
	const btn = e.target.closest?.( '.wpe-share__btn.is-copy' );
	if ( ! btn ) {
		return;
	}
	const status = btn.parentElement.querySelector( '.wpe-share__status' );
	navigator.clipboard?.writeText( btn.dataset.url ).then( () => {
		if ( status ) {
			status.textContent = btn.dataset.done;
		}
		btn.classList.add( 'is-done' );
	} );
} );
