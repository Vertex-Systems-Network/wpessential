/**
 * Source-code copy action. Clipboard only activates on explicit user click.
 */
( function () {
	'use strict';

	function init( root ) {
		if ( root.dataset.wpeCodeReady ) {
			return;
		}
		root.dataset.wpeCodeReady = '1';
		var button = root.querySelector( '.wpe-code__copy' );
		var source = root.querySelector( '.wpe-code__source' );
		var status = root.querySelector( '.wpe-code__copy-status' );
		if ( ! button || ! source || ! status ) {
			return;
		}
		var original = button.textContent;
		button.addEventListener( 'click', function () {
			if ( ! navigator.clipboard || ! navigator.clipboard.writeText ) {
				status.textContent = 'Clipboard is unavailable in this browser.';
				return;
			}
			navigator.clipboard.writeText( source.textContent ).then( function () {
				status.textContent = 'Code copied.';
				button.textContent = 'Copied';
			}, function () {
				status.textContent = 'Copy failed. Select and copy the code manually.';
				button.textContent = original;
			} );
		} );
	}
	function boot() {
		document.querySelectorAll( '.wpe-code.is-code' ).forEach( init );
	}
	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}
	if ( 'MutationObserver' in window ) {
		new window.MutationObserver( function ( changes ) {
			changes.forEach( function ( change ) {
				change.addedNodes.forEach( function ( node ) {
					if ( 1 !== node.nodeType ) {
						return;
					}
					if ( node.matches( '.wpe-code.is-code' ) ) {
						init( node );
					}
					node.querySelectorAll( '.wpe-code.is-code' ).forEach( init );
				} );
			} );
		} ).observe( document.documentElement, { childList: true, subtree: true } );
	}
}() );
