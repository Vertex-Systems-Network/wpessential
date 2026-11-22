document.querySelectorAll( '.wpe-toc' ).forEach( ( toc ) => {
	const list = toc.querySelector( '.wpe-toc__list' );
	const root = toc.closest( 'main, article, .entry-content' ) || document.body;
	const used = new Set();
	root.querySelectorAll( toc.dataset.levels || 'h2,h3' ).forEach( ( h ) => {
		if ( toc.contains( h ) || ! h.textContent.trim() ) {
			return;
		}
		if ( ! h.id ) {
			let base = h.textContent.trim().toLowerCase().replace( /[^a-z0-9]+/g, '-' ).replace( /^-|-$/g, '' ) || 'section';
			let slug = base;
			for ( let n = 2; used.has( slug ) || document.getElementById( slug ); n++ ) {
				slug = base + '-' + n;
			}
			h.id = slug;
		}
		used.add( h.id );
		const li = document.createElement( 'li' );
		li.className = 'is-' + h.tagName.toLowerCase();
		const a = document.createElement( 'a' );
		a.href = '#' + h.id;
		a.textContent = h.textContent.trim();
		li.append( a );
		list.append( li );
	} );
	if ( ! list.children.length ) {
		toc.dataset.empty = '1';
	}
} );
