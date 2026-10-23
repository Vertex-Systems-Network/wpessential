// View models for the dashboard app. The server prints one per screen in
// <script type="application/json" data-wpe-app-data="{page}">; a screen opened by the router
// without its data loads it from that page's HTML, once, and keeps it for the visit.
export type Html = string;

export interface ScreenData {
	screen: 'dashboard' | 'modules' | 'demos' | 'elements';
	title: string;
	text: Record< string, string >;
	[ key: string ]: unknown;
}

const cache = new Map< string, ScreenData >();

function parse( root: ParentNode, page: string ): ScreenData | null {
	const script = root.querySelector( `script[data-wpe-app-data="${ CSS.escape( page ) }"]` );
	if ( ! script?.textContent ) {
		return null;
	}
	try {
		const data = JSON.parse( script.textContent ) as ScreenData;
		return typeof data === 'object' && data !== null && typeof data.screen === 'string' ? data : null;
	} catch {
		return null;
	}
}

/** Data printed on the current page, read once at start. */
export function initialData( page: string ): ScreenData | null {
	const data = parse( document, page );
	if ( data ) {
		cache.set( page, data );
	}
	return data;
}

export function cachedData( page: string ): ScreenData | undefined {
	return cache.get( page );
}

/** Fetches a screen's data (always fresh: toggles change it); falls back to the cache offline. */
export async function loadData( page: string ): Promise< ScreenData > {
	const url = new URL( window.location.href );
	url.search = '';
	url.searchParams.set( 'page', page );
	const response = await fetch( url.toString(), { credentials: 'same-origin' } );
	if ( ! response.ok ) {
		throw new Error( `Could not load ${ page }` );
	}
	const doc = new DOMParser().parseFromString( await response.text(), 'text/html' );
	const data = parse( doc, page );
	if ( ! data ) {
		throw new Error( `No data for ${ page }` );
	}
	cache.set( page, data );
	return data;
}
