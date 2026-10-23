// In-place navigation for the WPEssential top bar (Dashboard, Modules, Demo Library, Elements,
// AI Connect, Account, System Status, Docs): the next screen is fetched and swapped in without a
// full page reload, and the address bar and Back button keep working. A screen that needs a
// script this page has not loaded (or runs inline script) falls back to a normal page load.
const LINKS = 'a.wpe-ui-nav__item, .wpe-ui-topbar a.wpe-ui-button[href*="page=wpessential"], .wpe-ui-topbar a.wpe-ui-logo';

function scriptKey( src: string ): string {
	try {
		const url = new URL( src, window.location.href );
		return url.origin + url.pathname;
	} catch {
		return src;
	}
}

function needsFullLoad( next: Document ): boolean {
	const loaded = new Set(
		Array.from( document.scripts )
			.map( ( script ) => script.src )
			.filter( Boolean )
			.map( scriptKey )
	);
	const missing = Array.from( next.scripts )
		.map( ( script ) => script.src )
		.filter( Boolean )
		.map( scriptKey )
		.some( ( src ) => ! loaded.has( src ) );
	const wrap = next.querySelector( '.wrap.wpe-ui' );
	const inline = wrap?.querySelector( 'script:not([type]), script[type="text/javascript"], script[type="module"]' );

	return missing || inline !== null && inline !== undefined;
}

function syncStyles( next: Document ): void {
	const have = new Set( Array.from( document.querySelectorAll< HTMLLinkElement >( 'link[rel="stylesheet"]' ) ).map( ( link ) => scriptKey( link.href ) ) );
	for ( const link of Array.from( next.querySelectorAll< HTMLLinkElement >( 'link[rel="stylesheet"]' ) ) ) {
		if ( ! have.has( scriptKey( link.href ) ) ) {
			document.head.append( link.cloneNode() );
		}
	}
}

async function go( url: URL, push: boolean, reinit: () => void ): Promise< void > {
	const current = document.querySelector< HTMLElement >( '.wrap.wpe-ui' );
	if ( ! current ) {
		window.location.assign( url.toString() );
		return;
	}
	current.setAttribute( 'aria-busy', 'true' );
	current.classList.add( 'is-navigating' );
	try {
		const response = await fetch( url.toString(), { credentials: 'same-origin' } );
		if ( ! response.ok ) {
			throw new Error( 'navigation failed' );
		}
		const next = new DOMParser().parseFromString( await response.text(), 'text/html' );
		const wrap = next.querySelector( '.wrap.wpe-ui' );
		if ( ! wrap || needsFullLoad( next ) ) {
			window.location.assign( url.toString() );
			return;
		}
		syncStyles( next );
		current.replaceWith( document.importNode( wrap, true ) );
		document.title = next.title;
		document.body.className = next.body.className;
		const menu = next.getElementById( 'adminmenu' );
		const ownMenu = document.getElementById( 'adminmenu' );
		if ( menu && ownMenu ) {
			ownMenu.replaceWith( document.importNode( menu, true ) );
		}
		if ( push ) {
			window.history.pushState( { wpeNav: true }, '', url );
		}
		window.scrollTo( 0, 0 );
		reinit();
		// Move focus to the new screen's heading, as a page load would.
		const heading = document.querySelector< HTMLElement >( '.wrap.wpe-ui h1' );
		if ( heading ) {
			heading.tabIndex = -1;
			heading.focus( { preventScroll: true } );
		}
	} catch {
		window.location.assign( url.toString() );
	}
}

export function initAjaxNav( reinit: () => void ): void {
	if ( typeof window.fetch !== 'function' || typeof DOMParser === 'undefined' ) {
		return;
	}
	document.addEventListener( 'click', ( event ) => {
		if ( event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey ) {
			return;
		}
		const link = event.target instanceof Element ? event.target.closest< HTMLAnchorElement >( LINKS ) : null;
		if ( ! link || link.target === '_blank' ) {
			return;
		}
		const url = new URL( link.href, window.location.href );
		if ( url.origin !== window.location.origin || ! ( url.searchParams.get( 'page' ) ?? '' ).startsWith( 'wpessential' ) ) {
			return;
		}
		event.preventDefault();
		void go( url, true, reinit );
	} );
	window.addEventListener( 'popstate', () => {
		void go( new URL( window.location.href ), false, reinit );
	} );
}
