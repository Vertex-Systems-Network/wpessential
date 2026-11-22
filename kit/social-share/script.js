document.addEventListener( 'click', ( e ) => {
	const btn = e.target.closest?.( '.wpe-share__btn.is-copy' );
	if ( ! btn ) {
		return;
	}
	const status = btn.parentElement.querySelector( '.wpe-share__status' );
	const say = ( text ) => {
		if ( status ) {
			status.textContent = text;
		}
	};
	if ( ! navigator.clipboard?.writeText ) {
		say( btn.dataset.fail );
		return;
	}
	navigator.clipboard.writeText( btn.dataset.url ).then(
		() => {
			say( btn.dataset.done );
			btn.classList.add( 'is-done' );
		},
		() => say( btn.dataset.fail )
	);
} );
