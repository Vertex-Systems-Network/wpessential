/**
 * Optional, progressive motion triggers for shared WPEssential Kit elements.
 * Native CSS animations are the no-JS fallback. No preventDefault() on links.
 */
( function () {
	'use strict';

	function activate( node ) {
		node.classList.remove( 'is-motion-active' );
		// Reset an existing animation so repeated keyboard/click activations replay it.
		void node.offsetWidth;
		node.classList.add( 'is-motion-active' );
	}

	function setup( node ) {
		if ( node.dataset.wpekMotionAttached ) {
			return;
		}
		node.dataset.wpekMotionAttached = '1';
		var reduced = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
		var type = [ 'scroll', 'hover', 'click', 'load' ].find( function ( trigger ) {
			return node.classList.contains( 'wpek-motion-trigger-' + trigger );
		} );
		if ( ! type ) {
			return;
		}
		// Make the control active only after its event handlers are installed.
		if ( 'scroll' === type ) {
			if ( reduced || ! ( 'IntersectionObserver' in window ) ) {
				node.classList.add( 'wpek-motion-ready', 'is-motion-active' );
				return;
			}
			var observer = new IntersectionObserver( function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						activate( entry.target );
						observer.unobserve( entry.target );
					}
				} );
			}, { threshold: 0.08 } );
			node.classList.add( 'wpek-motion-ready' );
			observer.observe( node );
			return;
		}
		node.classList.add( 'wpek-motion-ready' );
		if ( 'load' === type ) {
			requestAnimationFrame( function () {
				activate( node );
			} );
		} else if ( 'hover' === type ) {
			node.addEventListener( 'pointerenter', function () { activate( node ); } );
			node.addEventListener( 'focusin', function () { activate( node ); } );
			node.addEventListener( 'pointerleave', function () { node.classList.remove( 'is-motion-active' ); } );
			node.addEventListener( 'focusout', function ( event ) {
				if ( ! node.contains( event.relatedTarget ) ) {
					node.classList.remove( 'is-motion-active' );
				}
			} );
		} else if ( 'click' === type ) {
			node.addEventListener( 'click', function () {
				activate( node );
			} );
		}
	}

	function boot( root ) {
		( root || document ).querySelectorAll( '.wpek-anim.wpek-motion-trigger-scroll,.wpek-anim.wpek-motion-trigger-hover,.wpek-anim.wpek-motion-trigger-click,.wpek-anim.wpek-motion-trigger-load' ).forEach( setup );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', function () { boot( document ); } );
	} else {
		boot( document );
	}
	// Gutenberg/Elementor may insert or replace a live widget in a preview.
	if ( 'MutationObserver' in window ) {
		var mutationObserver = new MutationObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				entry.addedNodes.forEach( function ( node ) {
					if ( node.nodeType !== 1 ) {
						return;
					}
					if ( node.matches && node.matches( '.wpek-anim[class*="wpek-motion-trigger-"]' ) ) {
						setup( node );
					}
					boot( node );
				} );
			} );
		} );
		var startObserving = function () {
			if ( document.documentElement ) {
				mutationObserver.observe( document.documentElement, { childList: true, subtree: true } );
			}
		};
		if ( 'loading' === document.readyState ) {
			document.addEventListener( 'DOMContentLoaded', startObserving, { once: true } );
		} else {
			startObserving();
		}
	}
}() );
