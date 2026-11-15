/**
 * Kit per-layer parallax and named sticky-stop, loaded only for opted-in widgets.
 * Scroll work is throttled to animation frames; reduced motion disables parallax.
 */
( function () {
	'use strict';

	var roots = new Set();
	var scheduled = false;
	var motion = window.matchMedia ? window.matchMedia( '(prefers-reduced-motion: reduce)' ) : null;

	function clamp( number, low, high ) {
		return Math.min( high, Math.max( low, number ) );
	}

	function clearLayer( node ) {
		node.classList.remove( 'wpek-parallax-layer' );
		node.style.removeProperty( '--wpek-parallax-x' );
		node.style.removeProperty( '--wpek-parallax-y' );
	}

	function updateParallax( root ) {
		var inner = root.classList.contains( 'wpe-container' ) ? root.querySelector( ':scope > .wpe-container__inner' ) : null;
		var children = Array.prototype.slice.call( ( inner || root ).children, 0, 4 );
		var css = window.getComputedStyle( root );
		var bounds = root.getBoundingClientRect();
		var beyondViewport = bounds.bottom < -100 || bounds.top > window.innerHeight + 100;
		var reduced = motion && motion.matches;
		var midpoint = bounds.top + bounds.height / 2;
		var range = Math.max( 1, ( window.innerHeight + bounds.height ) / 2 );
		var progress = clamp( ( window.innerHeight / 2 - midpoint ) / range, -1, 1 );
		var horizontal = root.classList.contains( 'wpek-parallax-x' );

		children.forEach( function ( child, index ) {
			var amplitude = Number.parseFloat( css.getPropertyValue( '--wpek-parallax-layer-' + ( index + 1 ) ) );
			if ( ! Number.isFinite( amplitude ) || Math.abs( amplitude ) > 240 || amplitude === 0 || reduced || beyondViewport ) {
				clearLayer( child );
				return;
			}
			var offset = Math.round( amplitude * progress * 100 ) / 100 + 'px';
			child.classList.add( 'wpek-parallax-layer' );
			child.style.setProperty( '--wpek-parallax-x', horizontal ? offset : '0px' );
			child.style.setProperty( '--wpek-parallax-y', horizontal ? '0px' : offset );
		} );
	}

	function updateSticky( root ) {
		var stop = document.getElementById( root.dataset.wpekStickyStop || '' );
		var style = window.getComputedStyle( root );
		// Do not offset a mobile-disabled sticky, an invalid target or a target
		// that is nested in / before the sticky element itself.
		if ( ! stop || stop === root || root.contains( stop ) ||
			! ( root.compareDocumentPosition( stop ) & Node.DOCUMENT_POSITION_FOLLOWING ) ||
			style.position !== 'sticky' ) {
			root.style.removeProperty( '--wpek-sticky-stop-push' );
			return;
		}
		var old = Number.parseFloat( style.getPropertyValue( '--wpek-sticky-stop-push' ) ) || 0;
		var baseBottom = root.getBoundingClientRect().bottom - old;
		var gap = 0;
		var push = Math.min( 0, stop.getBoundingClientRect().top - baseBottom - gap );
		root.style.setProperty( '--wpek-sticky-stop-push', Math.round( push * 100 ) / 100 + 'px' );
	}

	function update() {
		scheduled = false;
		roots.forEach( function ( node ) {
			if ( ! node.isConnected ) {
				roots.delete( node );
				return;
			}
			if ( node.classList.contains( 'wpek-parallax' ) ) {
				updateParallax( node );
			}
			if ( node.classList.contains( 'wpek-sticky-stop' ) ) {
				updateSticky( node );
			}
		} );
	}

	function schedule() {
		if ( ! scheduled ) {
			scheduled = true;
			requestAnimationFrame( update );
		}
	}

	function scan( scope ) {
		var selector = '.wpek-parallax,.wpek-sticky-stop[data-wpek-sticky-stop]';
		if ( scope.matches && scope.matches( selector ) ) {
			roots.add( scope );
		}
		scope.querySelectorAll( selector ).forEach( function ( node ) {
			roots.add( node );
		} );
		schedule();
	}

	function init() {
		scan( document );
		window.addEventListener( 'scroll', schedule, { passive: true } );
		// Some themes scroll nested containers instead of the document.
		document.addEventListener( 'scroll', schedule, true );
		window.addEventListener( 'resize', schedule, { passive: true } );
		if ( motion ) {
			if ( motion.addEventListener ) {
				motion.addEventListener( 'change', schedule );
			} else if ( motion.addListener ) {
				motion.addListener( schedule );
			}
		}
		if ( 'MutationObserver' in window ) {
			var observer = new MutationObserver( function ( mutations ) {
				mutations.forEach( function ( mutation ) {
					mutation.addedNodes.forEach( function ( node ) {
						if ( node.nodeType === 1 ) {
							scan( node );
						}
					} );
				} );
				schedule();
			} );
			observer.observe( document.documentElement, { childList: true, subtree: true } );
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init, { once: true } );
	} else {
		init();
	}
}() );
