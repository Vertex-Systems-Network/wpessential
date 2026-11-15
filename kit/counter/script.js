/**
 * WPEssential Counter — animate numbers once when visible, without dependencies.
 * Server output is always the finished number; when JavaScript is disabled or reduced
 * motion is preferred, visitors see that final value immediately.
 */
( function () {
	'use strict';

	function formatted( value, decimals, point, separator ) {
		var negative = value < 0;
		var parts = Math.abs( value ).toFixed( decimals ).split( '.' );
		var integer = separator ? parts[ 0 ].replace( /\B(?=(\d{3})+(?!\d))/g, separator ) : parts[ 0 ];
		var sign = negative && Math.abs( value ) >= Math.pow( 10, -decimals ) / 2 ? '-' : '';
		return sign + integer + ( decimals ? point + parts[ 1 ] : '' );
	}

	function init( root ) {
		if ( root.dataset.wpeCounterReady ) {
			return;
		}
		root.dataset.wpeCounterReady = '1';

		var digits = root.querySelector( '.wpe-counter__digits' );
		if ( ! digits || '1' !== root.dataset.wpeAnimate ) {
			return;
		}
		var start = Number( root.dataset.wpeStart );
		var end = Number( root.dataset.wpeEnd );
		var decimals = Math.min( 4, Math.max( 0, parseInt( root.dataset.wpeDecimals, 10 ) || 0 ) );
		var separator = root.dataset.wpeThousands || '';
		var point = root.dataset.wpePoint === ',' ? ',' : '.';
		var duration = Math.min( 10000, Math.max( 200, Number( root.dataset.wpeDuration ) || 1600 ) );
		var easing = root.dataset.wpeEasing || 'ease-out';
		var reduce = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

		if ( reduce || ! window.IntersectionObserver || ! window.requestAnimationFrame ||
			! Number.isFinite( start ) || ! Number.isFinite( end ) || start === end ) {
			return;
		}

		function ease( t ) {
			if ( 'linear' === easing ) {
				return t;
			}
			if ( 'ease-in-out' === easing ) {
				return t < 0.5 ? 2 * t * t : 1 - Math.pow( -2 * t + 2, 2 ) / 2;
			}
			return 1 - Math.pow( 1 - t, 3 );
		}

		var observer = new window.IntersectionObserver( function ( entries ) {
			if ( ! entries.some( function ( entry ) { return entry.isIntersecting; } ) ) {
				return;
			}
			observer.disconnect();
			var began;
			digits.textContent = formatted( start, decimals, point, separator );
			function frame( stamp ) {
				if ( ! root.isConnected ) {
					return;
				}
				if ( undefined === began ) {
					began = stamp;
				}
				var progress = Math.min( 1, ( stamp - began ) / duration );
				digits.textContent = progress < 1 ?
					formatted( start + ( end - start ) * ease( progress ), decimals, point, separator ) :
					formatted( end, decimals, point, separator );
				if ( progress < 1 ) {
					window.requestAnimationFrame( frame );
				}
			}
			window.requestAnimationFrame( frame );
		}, { threshold: 0.1 } );
		observer.observe( root );
	}

	function boot() {
		document.querySelectorAll( '[data-wpe-counter]' ).forEach( init );
	}
	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}
	// Pick up blocks or widgets added in editors after page load.
	if ( 'MutationObserver' in window ) {
		new window.MutationObserver( function ( mutations ) {
			mutations.forEach( function ( mutation ) {
				mutation.addedNodes.forEach( function ( node ) {
					if ( 1 !== node.nodeType ) {
						return;
					}
					if ( node.matches( '[data-wpe-counter]' ) ) {
						init( node );
					}
					node.querySelectorAll( '[data-wpe-counter]' ).forEach( init );
				} );
			} );
		} ).observe( document.documentElement, { childList: true, subtree: true } );
	}
}() );
