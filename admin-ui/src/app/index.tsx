// WPEssential dashboard app: Dashboard, Modules, Demo Library and Elements are child components
// of one React tree, switched by React Router without reloading the page. React is WordPress's own
// (script handles react / react-dom). The server-rendered screen is the first paint and the
// no-JavaScript fallback; the app takes over its view (.wpe-ui-app-view).
import React, { useEffect, useState } from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter, useNavigate, useSearchParams } from 'react-router';
import { type ScreenData, cachedData, initialData, loadData } from './data';
import { APP_PAGES, DASHBOARD, DEMOS, ELEMENTS, MODULES, appPageOf } from './routes';
import { Dashboard } from './screens/Dashboard';
import { Demos } from './screens/Demos';
import { Elements } from './screens/Elements';
import { Modules } from './screens/Modules';
import './app.scss';

const SCREENS: Record< string, [ ScreenData[ 'screen' ], ( props: { data: ScreenData } ) => React.ReactNode ] > = {
	[ DASHBOARD ]: [ 'dashboard', Dashboard ],
	[ MODULES ]: [ 'modules', Modules ],
	[ DEMOS ]: [ 'demos', Demos ],
	[ ELEMENTS ]: [ 'elements', Elements ],
};

/** Keeps the server-rendered top bar, title and redirect notices in step with the route. */
function syncChrome( page: string, title: string, first: boolean ): void {
	for ( const link of Array.from( document.querySelectorAll< HTMLAnchorElement >( '.wpe-ui-nav__item' ) ) ) {
		const current = appPageOf( link.href ) === page;
		link.classList.toggle( 'is-current', current );
		if ( current ) {
			link.setAttribute( 'aria-current', 'page' );
		} else {
			link.removeAttribute( 'aria-current' );
		}
	}
	const parts = document.title.split( ' ‹ ' );
	document.title = [ title, ...parts.slice( 1 ) ].join( ' ‹ ' );
	if ( ! first ) {
		// Notices belong to the request that printed them (?wpe-notice=…).
		for ( const notice of Array.from( document.querySelectorAll( '.wrap.wpe-ui > .wpe-ui-notice' ) ) ) {
			notice.remove();
		}
	}
}

function App( { firstPage }: { firstPage: string } ) {
	const [ params ] = useSearchParams();
	const navigate = useNavigate();
	const page = params.get( 'page' ) ?? firstPage;
	const [ data, setData ] = useState< ScreenData | undefined >( () => cachedData( page ) );
	const [ failed, setFailed ] = useState( false );

	// Links to the four app screens (top bar, logo, in-page links) are routed, not reloaded.
	useEffect( () => {
		const onClick = ( event: MouseEvent ): void => {
			if ( event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey ) {
				return;
			}
			const link = event.target instanceof Element ? event.target.closest< HTMLAnchorElement >( 'a[href]' ) : null;
			const target = link && link.target !== '_blank' ? appPageOf( link.href ) : null;
			if ( ! link || ! target ) {
				return;
			}
			event.preventDefault();
			navigate( { search: new URL( link.href ).search } );
		};
		document.addEventListener( 'click', onClick, true );
		return () => document.removeEventListener( 'click', onClick, true );
	}, [ navigate ] );

	useEffect( () => {
		let live = true;
		const cached = cachedData( page );
		setData( cached );
		setFailed( false );
		if ( page !== firstPage || cached === undefined ) {
			loadData( page )
				.then( ( fresh ) => live && setData( fresh ) )
				.catch( () => {
					if ( live && cached === undefined ) {
						// Could not load the screen's data: open it the normal way.
						setFailed( true );
						window.location.reload();
					}
				} );
		}
		return () => {
			live = false;
		};
	}, [ page, firstPage ] );

	const [ first, setFirst ] = useState( true );
	useEffect( () => {
		if ( data && data.screen === SCREENS[ page ]?.[ 0 ] ) {
			syncChrome( page, data.title, first );
			if ( ! first ) {
				window.scrollTo( 0, 0 );
				const heading = document.querySelector< HTMLElement >( '.wpe-ui-app-view h1' );
				if ( heading ) {
					heading.tabIndex = -1;
					heading.focus( { preventScroll: true } );
				}
			}
			setFirst( false );
		}
	}, [ data, page ] );

	const route = SCREENS[ page ];
	if ( ! route ) {
		return null;
	}
	const [ screen, Screen ] = route;
	// Until the new screen's data arrives, the previous screen's data must not reach it.
	if ( ! data || data.screen !== screen || failed ) {
		return <div className="wpe-ui-app-loading" role="status" aria-live="polite" aria-busy="true" />;
	}
	return <Screen data={ data } />;
}

let mounted: HTMLElement | null = null;

/** Mounts once per server-rendered view (again after an in-place navigation brings a new one). */
function mount(): void {
	const view = document.querySelector< HTMLElement >( '[data-wpe-app-view]' );
	const React18 = ( window as unknown as { ReactDOM?: { createRoot?: unknown } } ).ReactDOM;
	if ( ! view || view === mounted || typeof React18?.createRoot !== 'function' ) {
		return;
	}
	const page = view.dataset.wpeAppView ?? '';
	if ( ! APP_PAGES.includes( page ) || ! initialData( page ) ) {
		return;
	}
	mounted = view;
	view.dataset.wpeAppRoot = '';
	createRoot( view ).render(
		<BrowserRouter>
			<App firstPage={ page } />
		</BrowserRouter>
	);
}

mount();
document.addEventListener( 'wpe:screen', mount );
