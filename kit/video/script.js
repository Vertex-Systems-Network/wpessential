/**
 * Video Kit embeds: never contact YouTube/Vimeo until the visitor presses Play.
 * Links to original videos remain usable without JavaScript.
 */
( function () {
	'use strict';

	function init( root ) {
		if ( root.dataset.wpeMediaReady ) {
			return;
		}
		root.dataset.wpeMediaReady = '1';
		var button = root.querySelector( '.wpe-video__play' );
		if ( ! button ) {
			return;
		}
		var url = root.dataset.wpeEmbed || '';
		// An embed URL is canonicalized by PHP; guard client-side too.
		if ( ! /^https:\/\/(?:www\.youtube-nocookie\.com\/embed\/[A-Za-z0-9_-]{11}\?autoplay=1|player\.vimeo\.com\/video\/[0-9]{1,16}\?dnt=1&autoplay=1)$/.test( url ) ) {
			return;
		}
		button.addEventListener( 'click', function () {
			if ( root.dataset.wpeMediaStarted ) {
				return;
			}
			var iframe = document.createElement( 'iframe' );
			iframe.src = url;
			iframe.title = root.dataset.title || 'Video';
			iframe.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
			iframe.allowFullscreen = true;
			iframe.referrerPolicy = 'strict-origin-when-cross-origin';
			iframe.loading = 'eager';
			root.dataset.wpeMediaStarted = '1';
			button.replaceWith( iframe );
			iframe.focus();
		} );
	}
	function boot() {
		document.querySelectorAll( '.wpe-video__embed[data-wpe-embed]' ).forEach( init );
	}
	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}
	// Gutenberg/Elementor editor previews may add media widgets dynamically.
	if ( 'MutationObserver' in window ) {
		new window.MutationObserver( function ( mutations ) {
			mutations.forEach( function ( mutation ) {
				mutation.addedNodes.forEach( function ( node ) {
					if ( 1 !== node.nodeType ) {
						return;
					}
					if ( node.matches( '.wpe-video__embed[data-wpe-embed]' ) ) {
						init( node );
					}
					node.querySelectorAll( '.wpe-video__embed[data-wpe-embed]' ).forEach( init );
				} );
			} );
		} ).observe( document.documentElement, { childList: true, subtree: true } );
	}
}() );
