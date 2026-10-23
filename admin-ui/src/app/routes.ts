// The four app screens, keyed by their WordPress admin page slug (Shell::APP_SLUGS).
export const DASHBOARD = 'wpessential';
export const MODULES = 'wpessential-modules';
export const DEMOS = 'wpessential-demos';
export const ELEMENTS = 'wpessential-elements';
export const APP_PAGES = [ DASHBOARD, MODULES, DEMOS, ELEMENTS ];

/** Router target for an app screen: same admin.php path, new ?page= (and filters). */
export function to( page: string, params: Record< string, string > = {} ): { search: string } {
	const search = new URLSearchParams( { page, ...params } );
	return { search: `?${ search.toString() }` };
}

/** The app page an admin URL points to, or null when the router must not take it. */
export function appPageOf( href: string ): string | null {
	try {
		const url = new URL( href, window.location.href );
		const page = url.searchParams.get( 'page' ) ?? '';
		return url.origin === window.location.origin && url.pathname === window.location.pathname && APP_PAGES.includes( page ) ? page : null;
	} catch {
		return null;
	}
}
