(() => {
	// Queried on every update so elements added later (editor, AJAX) also work.
	const update = () => {
		const max = document.documentElement.scrollHeight - innerHeight;
		const p = max > 0 ? Math.min( 1, scrollY / max ) : 0;
		document.querySelectorAll( '.wpe-btt' ).forEach( ( el ) => {
			el.style.setProperty( '--wpek-btt-p', p.toFixed( 4 ) );
			el.classList.toggle( 'is-shown', scrollY > innerHeight / 2 );
		} );
	};
	addEventListener( 'scroll', update, { passive: true } );
	addEventListener( 'resize', update );
	addEventListener( 'load', update );
	update();
	document.addEventListener( 'click', ( e ) => {
		if ( ! e.target.closest?.( '.wpe-btt__btn' ) ) {
			return;
		}
		e.preventDefault();
		scrollTo( { top: 0, behavior: matchMedia( '(prefers-reduced-motion: reduce)' ).matches ? 'auto' : 'smooth' } );
		const target = document.querySelector( 'main, [role=main]' ) || document.body;
		if ( ! target.hasAttribute( 'tabindex' ) ) {
			target.setAttribute( 'tabindex', '-1' );
		}
		target.focus( { preventScroll: true } );
	} );
})();
