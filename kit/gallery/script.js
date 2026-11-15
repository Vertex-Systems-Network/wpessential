/**
 * WPEssential Gallery: progressive enhancement for a native dialog lightbox.
 * Without JavaScript / dialog support, every lightbox image remains a normal link.
 */
( function () {
	'use strict';

	function init( root ) {
		if ( root.dataset.wpeGalleryReady ) {
			return;
		}
		root.dataset.wpeGalleryReady = '1';
		var dialog = root.querySelector( '.wpe-gallery__dialog' );
		if ( ! dialog || 'function' !== typeof dialog.showModal ) {
			return;
		}
		var links = Array.prototype.slice.call( root.querySelectorAll( '[data-wpe-gallery-open]' ) );
		if ( ! links.length ) {
			return;
		}
		var full = dialog.querySelector( '.wpe-gallery__full' );
		var caption = dialog.querySelector( '.wpe-gallery__viewer-caption' );
		var prev = dialog.querySelector( '[data-action="prev"]' );
		var next = dialog.querySelector( '[data-action="next"]' );
		var close = dialog.querySelector( '[data-action="close"]' );
		var index = 0;
		var opener = null;
		if ( ! full || ! caption || ! prev || ! next || ! close ) {
			return;
		}

		function show( i ) {
			index = ( i + links.length ) % links.length;
			var link = links[ index ];
			full.src = link.dataset.full || link.href;
			full.alt = link.dataset.alt || '';
			var text = '1' === dialog.dataset.showCaptions ? ( link.dataset.caption || '' ) : '';
			caption.textContent = text;
			caption.hidden = ! text;
			prev.hidden = next.hidden = 1 === links.length;
		}
		links.forEach( function ( link, i ) {
			link.addEventListener( 'click', function ( event ) {
				if ( ! ( link.dataset.full || link.href ) ) {
					return;
				}
				event.preventDefault();
				opener = link;
				show( i );
				dialog.showModal();
				close.focus();
			} );
		} );
		prev.addEventListener( 'click', function () { show( index - 1 ); } );
		next.addEventListener( 'click', function () { show( index + 1 ); } );
		close.addEventListener( 'click', function () { dialog.close(); } );
		dialog.addEventListener( 'keydown', function ( event ) {
			if ( 'ArrowLeft' === event.key ) {
				event.preventDefault();
				show( index - 1 );
			} else if ( 'ArrowRight' === event.key ) {
				event.preventDefault();
				show( index + 1 );
			}
		} );
		dialog.addEventListener( 'click', function ( event ) {
			if ( event.target === dialog ) {
				dialog.close();
			}
		} );
		dialog.addEventListener( 'close', function () {
			full.removeAttribute( 'src' );
			if ( opener && opener.isConnected ) {
				opener.focus();
			}
		} );
	}

	function boot() {
		document.querySelectorAll( '[data-wpe-gallery]' ).forEach( init );
	}
	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}

	// Dynamic editor previews can create new galleries after the initial page load.
	if ( 'MutationObserver' in window ) {
		new window.MutationObserver( function ( mutations ) {
			mutations.forEach( function ( mutation ) {
				mutation.addedNodes.forEach( function ( node ) {
					if ( 1 !== node.nodeType ) {
						return;
					}
					if ( node.matches( '[data-wpe-gallery]' ) ) {
						init( node );
					}
					node.querySelectorAll( '[data-wpe-gallery]' ).forEach( init );
				} );
			} );
		} ).observe( document.documentElement, { childList: true, subtree: true } );
	}
}() );
