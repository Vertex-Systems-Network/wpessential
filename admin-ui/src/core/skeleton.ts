// Skeleton placeholders: shown while a screen loads (in-place navigation and the dashboard app),
// in the same shape as a screen (header, toolbar, card grid) so nothing jumps when it arrives.
// Buttons and links that start a page load or a request get a spinner (initClickLoaders).

/** Markup of a loading screen. Purely decorative; the container carries the status for screen readers. */
export function skeletonHtml( cards = 6 ): string {
	const card = '<div class="wpe-ui-skel-card"><span class="wpe-ui-skel wpe-ui-skel--media"></span><span class="wpe-ui-skel wpe-ui-skel--line wpe-ui-skel--w60"></span><span class="wpe-ui-skel wpe-ui-skel--line"></span><span class="wpe-ui-skel wpe-ui-skel--line wpe-ui-skel--w80"></span></div>';
	return (
		'<div class="wpe-ui-skeleton" aria-hidden="true">' +
		'<div class="wpe-ui-skel-head"><span class="wpe-ui-skel wpe-ui-skel--eyebrow"></span><span class="wpe-ui-skel wpe-ui-skel--title"></span><span class="wpe-ui-skel wpe-ui-skel--line wpe-ui-skel--w50"></span></div>' +
		'<div class="wpe-ui-skel-bar"><span class="wpe-ui-skel wpe-ui-skel--pill"></span><span class="wpe-ui-skel wpe-ui-skel--pill"></span><span class="wpe-ui-skel wpe-ui-skel--pill"></span></div>' +
		'<div class="wpe-ui-skel-grid">' +
		card.repeat( cards ) +
		'</div></div>'
	);
}

/** Replaces a screen's content (everything after the top bar) with the skeleton. */
export function showScreenSkeleton( wrap: HTMLElement, label: string ): void {
	if ( wrap.querySelector( ':scope > .wpe-ui-skeleton-host' ) ) {
		return;
	}
	const host = document.createElement( 'div' );
	host.className = 'wpe-ui-skeleton-host';
	host.setAttribute( 'role', 'status' );
	host.setAttribute( 'aria-live', 'polite' );
	host.innerHTML = '<span class="screen-reader-text"></span>' + skeletonHtml();
	( host.firstElementChild as HTMLElement ).textContent = label;
	for ( const child of Array.from( wrap.children ) ) {
		if ( ! child.matches( '.wpe-ui-topbar, script' ) ) {
			( child as HTMLElement ).hidden = true;
		}
	}
	wrap.append( host );
}

const LOADING = 'is-loading';

function markLoading( el: HTMLElement ): void {
	el.classList.add( LOADING );
	el.setAttribute( 'aria-busy', 'true' );
	// Back/forward cache: a page restored from history must not keep the spinner.
	window.addEventListener(
		'pageshow',
		() => {
			el.classList.remove( LOADING );
			el.removeAttribute( 'aria-busy' );
		},
		{ once: true }
	);
}

/**
 * Spinners for clicks that leave the page or post a form the normal way. Clicks handled in place
 * (in-place navigation, AJAX forms, the app's own buttons) call preventDefault and are skipped.
 */
export function initClickLoaders(): void {
	document.addEventListener( 'click', ( event ) => {
		if ( event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey ) {
			return;
		}
		const link = event.target instanceof Element ? event.target.closest< HTMLAnchorElement >( '.wpe-ui a.wpe-ui-button[href]' ) : null;
		if ( ! link || link.target === '_blank' || link.hasAttribute( 'download' ) ) {
			return;
		}
		const href = link.getAttribute( 'href' ) ?? '';
		if ( href === '' || href.startsWith( '#' ) || href.startsWith( 'javascript:' ) ) {
			return;
		}
		// Decide after every other click handler ran: in-place navigation prevents the default.
		window.setTimeout( () => {
			if ( ! event.defaultPrevented ) {
				markLoading( link );
			}
		}, 0 );
	} );
	document.addEventListener( 'submit', ( event ) => {
		const form = event.target;
		if ( ! ( form instanceof HTMLFormElement ) || ! form.closest( '.wpe-ui' ) ) {
			return;
		}
		const submitter = ( event as SubmitEvent ).submitter ?? form.querySelector< HTMLElement >( '[type="submit"]' );
		// AJAX forms prevent the default in their own (later) submit handler.
		window.setTimeout( () => {
			if ( ! event.defaultPrevented && submitter instanceof HTMLElement ) {
				markLoading( submitter );
			}
		}, 0 );
	} );
}
