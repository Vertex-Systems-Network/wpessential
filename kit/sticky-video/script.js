const wpeSvAll = [];
document.querySelectorAll( '.wpe-sv' ).forEach( ( fig ) => {
	const video = fig.querySelector( 'video' );
	const sv = { fig, closed: false, out: false };
	// A file video floats only once played; embed iframes cannot report play, so they always float.
	let started = ! video || ! video.paused;
	const update = () => {
		const want = started && ! sv.closed && sv.out;
		// Only one floating player at a time: the newest one wins.
		if ( want ) {
			wpeSvAll.forEach( ( o ) => o !== sv && o.fig.classList.remove( 'is-floating' ) );
		}
		fig.classList.toggle( 'is-floating', want );
	};
	wpeSvAll.push( sv );
	video?.addEventListener( 'play', () => {
		started = true;
		update();
	} );
	fig.querySelector( '.wpe-sv__close' ).addEventListener( 'click', () => {
		sv.closed = true;
		fig.classList.remove( 'is-floating' );
		video?.pause();
		const iframe = fig.querySelector( 'iframe' );
		if ( iframe ) {
			iframe.src = iframe.src; // Reloading the embed stops its playback.
		}
	} );
	if ( 'IntersectionObserver' in window ) {
		new IntersectionObserver( ( [ entry ] ) => {
			sv.out = ! entry.isIntersecting && entry.boundingClientRect.top < 0;
			update();
		} ).observe( fig.querySelector( '.wpe-sv__frame' ) );
	}
} );
