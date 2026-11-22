if ( ! matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
	document.querySelectorAll( '.wpe-anim-head__word' ).forEach( ( el ) => {
		let words = [];
		try {
			words = JSON.parse( el.dataset.words || '[]' );
		} catch ( err ) {}
		if ( words.length < 2 ) {
			return;
		}
		let i = 0;
		setInterval( () => {
			el.classList.add( 'is-out' );
			setTimeout( () => {
				i = ( i + 1 ) % words.length;
				el.textContent = words[ i ];
				el.classList.remove( 'is-out' );
			}, 300 );
		}, 2500 );
	} );
}
