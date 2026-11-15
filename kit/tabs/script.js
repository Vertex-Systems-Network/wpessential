/**
 * WPEssential kit: Tabs. Arrow keys, Home and End move between tabs (WAI-ARIA tabs pattern),
 * #name links open a tab, optional autoplay pauses on hover and focus, and small screens can
 * turn the tabs into an accordion.
 */
( function () {
	'use strict';

	function init( root ) {
		if ( root.dataset.ready ) {
			return;
		}
		root.dataset.ready = '1';
		root.classList.add( 'is-ready' );
		var tabs = Array.prototype.slice.call( root.querySelectorAll( ':scope > .wpe-tabs__nav > .wpe-tabs__tab' ) );
		var panels = Array.prototype.slice.call( root.querySelectorAll( ':scope > .wpe-tabs__panels > .wpe-tabs__panel' ) );
		var nav = root.querySelector( ':scope > .wpe-tabs__nav' );
		var timer = null;
		var paused = false;

		function select( i, focus ) {
			tabs.forEach( function ( t, n ) {
				var on = n === i;
				t.setAttribute( 'aria-selected', on ? 'true' : 'false' );
				t.tabIndex = on ? 0 : -1;
				panels[ n ].hidden = ! on;
				var acc = panels[ n ].querySelector( '.wpe-tabs__accordion-title' );
				if ( acc ) {
					acc.setAttribute( 'aria-expanded', on ? 'true' : 'false' );
				}
			} );
			if ( focus ) {
				tabs[ i ].focus();
			}
			if ( tabs[ i ].scrollIntoView && nav.scrollWidth > nav.clientWidth ) {
				tabs[ i ].scrollIntoView( { block: 'nearest', inline: 'nearest' } );
			}
		}
		function current() {
			return Math.max( 0, tabs.findIndex( function ( t ) {
				return 'true' === t.getAttribute( 'aria-selected' );
			} ) );
		}

		tabs.forEach( function ( t, i ) {
			t.addEventListener( 'click', function () {
				select( i );
				if ( '1' === root.dataset.deepLink && t.dataset.anchor && window.history.replaceState ) {
					window.history.replaceState( null, '', '#' + t.dataset.anchor );
				}
			} );
		} );
		nav.addEventListener( 'keydown', function ( e ) {
			var i = current();
			var vertical = 'column' === getComputedStyle( nav ).flexDirection;
			var next = { ArrowRight: vertical ? null : 1, ArrowLeft: vertical ? null : -1, ArrowDown: vertical ? 1 : null, ArrowUp: vertical ? -1 : null }[ e.key ];
			if ( 'Home' === e.key ) {
				i = 0;
			} else if ( 'End' === e.key ) {
				i = tabs.length - 1;
			} else if ( next ) {
				i = ( i + next + tabs.length ) % tabs.length;
			} else {
				return;
			}
			e.preventDefault();
			select( i, true );
		} );
		// Accordion mode (small screens): the title inside each panel toggles it.
		panels.forEach( function ( p, i ) {
			var acc = p.querySelector( '.wpe-tabs__accordion-title' );
			if ( acc ) {
				acc.addEventListener( 'click', function () {
					select( i );
				} );
			}
		} );
		// Deep link: #name opens that tab.
		function fromHash() {
			var h = decodeURIComponent( window.location.hash.slice( 1 ) );
			if ( '1' !== root.dataset.deepLink || ! h ) {
				return;
			}
			tabs.forEach( function ( t, i ) {
				if ( t.dataset.anchor === h || t.id === h ) {
					select( i );
				}
			} );
		}
		fromHash();
		window.addEventListener( 'hashchange', fromHash );
		// Autoplay, paused on hover, focus and for reduced motion.
		var seconds = parseInt( root.dataset.autoplay || '0', 10 );
		var reduce = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
		if ( seconds > 0 && ! reduce ) {
			timer = window.setInterval( function () {
				if ( ! paused ) {
					select( ( current() + 1 ) % tabs.length );
				}
			}, seconds * 1000 );
			[ 'mouseenter', 'focusin' ].forEach( function ( ev ) {
				root.addEventListener( ev, function () {
					paused = true;
				} );
			} );
			[ 'mouseleave', 'focusout' ].forEach( function ( ev ) {
				root.addEventListener( ev, function () {
					paused = false;
				} );
			} );
		}
		// Orientation for screen readers follows the layout.
		function orient() {
			nav.setAttribute( 'aria-orientation', 'column' === getComputedStyle( nav ).flexDirection ? 'vertical' : 'horizontal' );
		}
		orient();
		window.addEventListener( 'resize', orient );
		void timer;
	}

	function boot() {
		document.querySelectorAll( '[data-wpe-tabs]' ).forEach( init );
	}
	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}
	// Builders (Elementor's editor, the block editor preview) add tabs later: pick them up as they appear.
	if ( 'MutationObserver' in window ) {
		new window.MutationObserver( function ( list ) {
			list.forEach( function ( m ) {
				m.addedNodes.forEach( function ( n ) {
					if ( 1 !== n.nodeType ) {
						return;
					}
					if ( n.matches( '[data-wpe-tabs]' ) ) {
						init( n );
					}
					n.querySelectorAll( '[data-wpe-tabs]' ).forEach( init );
				} );
			} );
		} ).observe( document.documentElement, { childList: true, subtree: true } );
	}
}() );
