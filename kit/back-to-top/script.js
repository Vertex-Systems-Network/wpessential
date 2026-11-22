(() => {
	const all = document.querySelectorAll( '.wpe-btt' );
	if ( ! all.length ) {
		return;
	}
	const update = () => {
		const max = document.documentElement.scrollHeight - innerHeight;
		const p = max > 0 ? Math.min( 1, scrollY / max ) : 0;
		all.forEach( ( el ) => {
			el.style.setProperty( '--wpek-btt-p', p.toFixed( 4 ) );
			el.classList.toggle( 'is-shown', scrollY > innerHeight / 2 );
		} );
	};
	addEventListener( 'scroll', update, { passive: true } );
	update();
	all.forEach( ( el ) => el.querySelector( '.wpe-btt__btn' )?.addEventListener( 'click', ( e ) => {
		e.preventDefault();
		scrollTo( { top: 0, behavior: matchMedia( '(prefers-reduced-motion: reduce)' ).matches ? 'auto' : 'smooth' } );
		document.body.focus?.();
	} ) );
})();
