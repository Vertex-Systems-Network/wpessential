/**
 * Progress indicators animate only when visible. The server renders the final
 * state by default, so no-JS or reduced-motion users never lose information.
 */
( function () {
	'use strict';

	function initialize( root ) {
		if ( root.dataset.wpeProgressReady ) {
			return;
		}
		root.dataset.wpeProgressReady = '1';
		if ( '1' !== root.dataset.animate || ! window.IntersectionObserver ||
			! window.requestAnimationFrame || ( window.matchMedia &&
			window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) ) {
			return;
		}
		var items = Array.prototype.slice.call( root.querySelectorAll( '.wpe-progress__item[data-target]' ) );
		var durationMatch = root.style.getPropertyValue( '--wpek-progress-duration' ).match( /^(\d+)ms$/ );
		var duration = Math.min( 5000, Math.max( 200, durationMatch ? Number( durationMatch[ 1 ] ) : 1100 ) );
		if ( ! items.length ) {
			return;
		}
		items.forEach( function ( item ) {
			item.style.setProperty( '--wpek-active-progress', '0%' );
			item.classList.add( 'wpe-progress__item--ready' );
		} );

		var observer = new window.IntersectionObserver( function ( entries ) {
			if ( ! entries.some( function ( entry ) { return entry.isIntersecting; } ) ) {
				return;
			}
			observer.disconnect();
			var began;
			function tick( timestamp ) {
				if ( ! root.isConnected ) {
					return;
				}
				if ( undefined === began ) {
					began = timestamp;
				}
				var elapsed = Math.min( 1, ( timestamp - began ) / duration );
				var ease = 1 - Math.pow( 1 - elapsed, 3 );
				items.forEach( function ( item ) {
					var target = Math.min( 100, Math.max( 0, Number( item.dataset.target ) || 0 ) );
					item.style.setProperty( '--wpek-active-progress', ( target * ease ).toFixed( 2 ) + '%' );
				} );
				if ( elapsed < 1 ) {
					window.requestAnimationFrame( tick );
				} else {
					items.forEach( function ( item ) {
						item.style.removeProperty( '--wpek-active-progress' );
						item.classList.remove( 'wpe-progress__item--ready' );
					} );
				}
			}
			window.requestAnimationFrame( tick );
		}, { threshold: 0.1 } );
		observer.observe( root );
	}

	function boot() {
		document.querySelectorAll( '[data-wpe-progress]' ).forEach( initialize );
	}
	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}
	// Support Gutenberg and Elementor live editor previews.
	if ( 'MutationObserver' in window ) {
		new window.MutationObserver( function ( mutations ) {
			mutations.forEach( function ( mutation ) {
				mutation.addedNodes.forEach( function ( node ) {
					if ( 1 !== node.nodeType ) {
						return;
					}
					if ( node.matches( '[data-wpe-progress]' ) ) {
						initialize( node );
					}
					node.querySelectorAll( '[data-wpe-progress]' ).forEach( initialize );
				} );
			} );
		} ).observe( document.documentElement, { childList: true, subtree: true } );
	}
}() );
