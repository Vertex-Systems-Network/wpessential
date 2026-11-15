/* No-JS notices remain readable; dismiss button is revealed only when functional. */
( function () {
	'use strict';
	function init( root ) {
		if ( root.dataset.wpeAlertReady ) { return; }
		root.dataset.wpeAlertReady = '1';
		var button = root.querySelector( '.wpe-alert__dismiss' );
		if ( ! button ) { return; }
		var key = 'wpe-alert:' + root.dataset.alertKey;
		var remember = '1' === root.dataset.remember;
		function stored() {
			if ( ! remember ) { return false; }
			try { return window.sessionStorage.getItem( key ) === 'dismissed'; } catch ( ignored ) { return false; }
		}
		if ( stored() ) { root.hidden = true; return; }
		button.hidden = false;
		button.addEventListener( 'click', function () {
			root.hidden = true;
			if ( remember ) {
				try { window.sessionStorage.setItem( key, 'dismissed' ); } catch ( ignored ) { /* Private browsing may disallow storage. */ }
			}
		} );
	}
	function boot() { document.querySelectorAll( '[data-wpe-alert]' ).forEach( init ); }
	if ( document.readyState === 'loading' ) { document.addEventListener( 'DOMContentLoaded', boot ); } else { boot(); }
	if ( window.MutationObserver ) {
		new window.MutationObserver( function ( recs ) {
			recs.forEach( function ( rec ) { rec.addedNodes.forEach( function ( node ) {
				if ( node.nodeType !== 1 ) { return; }
				if ( node.matches( '[data-wpe-alert]' ) ) { init( node ); }
				node.querySelectorAll( '[data-wpe-alert]' ).forEach( init );
			} ); } );
		} ).observe( document.documentElement, { childList: true, subtree: true } );
	}
}() );
