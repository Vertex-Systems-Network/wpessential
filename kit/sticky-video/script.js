document.querySelectorAll( '.wpe-sv' ).forEach( ( fig ) => {
	let closed = false;
	const video = fig.querySelector( 'video' );
	// A file video floats only once played; embed iframes cannot report play, so they always float.
	let started = ! video;
	video?.addEventListener( 'play', () => ( started = true ) );
	new IntersectionObserver( ( [ entry ] ) => {
		fig.classList.toggle( 'is-floating', started && ! closed && ! entry.isIntersecting && entry.boundingClientRect.top < 0 );
	} ).observe( fig.querySelector( '.wpe-sv__frame' ) );
	fig.querySelector( '.wpe-sv__close' ).addEventListener( 'click', () => {
		closed = true;
		fig.classList.remove( 'is-floating' );
	} );
} );
