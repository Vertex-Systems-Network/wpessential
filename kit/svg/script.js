/* Animate built-in SVGs only after intersection; static SVG without JS. */
( function () {
	'use strict';
	function init( root ) {
		if ( root.dataset.wpeSvgReady ) { return; }
		root.dataset.wpeSvgReady = '1';
		if ( window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) { return; }
		if ( ! window.IntersectionObserver ) { root.dataset.visible = '1'; return; }
		var observer = new window.IntersectionObserver( function ( entries ) {
			if ( entries.some( function ( entry ) { return entry.isIntersecting; } ) ) {
				root.dataset.visible = '1';
				observer.disconnect();
			}
		}, { threshold: 0.15 } );
		observer.observe( root );
	}
	function boot() { document.querySelectorAll( '[data-wpe-svg]' ).forEach( init ); }
	if ( document.readyState === 'loading' ) { document.addEventListener( 'DOMContentLoaded', boot ); } else { boot(); }
	if ( window.MutationObserver ) {
		new window.MutationObserver( function ( records ) {
			records.forEach( function ( record ) { record.addedNodes.forEach( function ( node ) {
				if ( node.nodeType !== 1 ) { return; }
				if ( node.matches( '[data-wpe-svg]' ) ) { init( node ); }
				node.querySelectorAll( '[data-wpe-svg]' ).forEach( init );
			} ); } );
		} ).observe( document.documentElement, { childList: true, subtree: true } );
	}
}() );
