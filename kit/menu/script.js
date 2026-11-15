/**
 * WPEssential Navigation: accessible submenu toggles for mouse, touch and keyboard.
 * Without JS, native links and mobile nested lists remain visible.
 */
( function () {
	'use strict';

	function init( root ) {
		if ( root.dataset.wpekMenuReady ) {
			return;
		}
		root.dataset.wpekMenuReady = '1';

		var mobile = root.querySelector( '.wpe-menu__mobile' );
		var desktop = root.querySelector( '.wpe-menu__desktop' );
		var clickOnly = root.classList.contains( 'is-trigger-click' );
		var counter = 0;

		function attach( nav, isMobile ) {
			if ( ! nav ) {
				return;
			}
			var items = nav.querySelectorAll( 'li' );
			items.forEach( function ( item ) {
				var link = item.querySelector( ':scope > a' );
				var children = item.querySelector( ':scope > .sub-menu' );
				var panel = item.querySelector( ':scope > .wpe-menu__mega-content' );
				if ( ! link || ( ! children && ! panel ) ) {
					return;
				}
				counter += 1;
				var label = ( link.textContent || '' ).trim() || 'submenu';
				var target = children || panel;
				target.id = target.id || root.id + '-sub-' + counter;
				var button = document.createElement( 'button' );
				button.type = 'button';
				button.className = 'wpe-menu__submenu-button';
				button.setAttribute( 'aria-label', 'Toggle ' + label );
				button.setAttribute( 'aria-expanded', 'false' );
				button.setAttribute( 'aria-controls', target.id );
				button.textContent = '⌄';
				link.insertAdjacentElement( 'afterend', button );

				function setOpen( open ) {
					item.classList.toggle( 'is-open', open );
					item.classList.remove( 'is-collapsed' );
					button.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
				}
				button.addEventListener( 'click', function ( event ) {
					event.preventDefault();
					var next = ! item.classList.contains( 'is-open' );
					if ( ! isMobile && next ) {
						nav.querySelectorAll( 'li.is-open' ).forEach( function ( sibling ) {
							if ( sibling !== item ) {
								sibling.classList.remove( 'is-open' );
								var otherButton = sibling.querySelector( ':scope > .wpe-menu__submenu-button' );
								if ( otherButton ) {
									otherButton.setAttribute( 'aria-expanded', 'false' );
								}
							}
						} );
					}
					setOpen( next );
					if ( ! next && ! isMobile ) {
						item.classList.add( 'is-collapsed' );
					}
				} );
				item.addEventListener( 'keydown', function ( event ) {
					if ( 'Escape' !== event.key ) {
						return;
					}
					if ( item.classList.contains( 'is-open' ) || item.matches( ':hover' ) || item.contains( document.activeElement ) ) {
						event.preventDefault();
						event.stopPropagation();
						setOpen( false );
						if ( ! isMobile ) {
							item.classList.add( 'is-collapsed' );
						}
						button.focus();
					}
				} );
				if ( ! isMobile ) {
					item.addEventListener( 'pointerenter', function () {
						item.classList.remove( 'is-collapsed' );
						if ( ! clickOnly ) {
							button.setAttribute( 'aria-expanded', 'true' );
						}
					} );
					item.addEventListener( 'pointerleave', function () {
						if ( ! clickOnly && ! item.classList.contains( 'is-open' ) ) {
							button.setAttribute( 'aria-expanded', 'false' );
						}
					} );
					item.addEventListener( 'focusin', function () {
						if ( item.classList.contains( 'is-collapsed' ) ) {
							return;
						}
						if ( ! clickOnly ) {
							button.setAttribute( 'aria-expanded', 'true' );
						}
					} );
					item.addEventListener( 'focusout', function ( event ) {
						if ( ! item.contains( event.relatedTarget ) ) {
							item.classList.remove( 'is-collapsed' );
						}
					} );
				}
			} );
			nav.classList.add( 'is-js' );
		}

		attach( desktop, false );
		attach( mobile, true );
		root.classList.add( 'is-js' );

		document.addEventListener( 'click', function ( event ) {
			if ( root.contains( event.target ) && ! event.target.closest( '.wpe-menu__desktop' ) ) {
				return;
			}
			if ( root.contains( event.target ) && event.target.closest( '.wpe-menu__submenu-button' ) ) {
				return;
			}
			var item = event.target.closest( '.wpe-menu__desktop li' );
			if ( item ) {
				return;
			}
			if ( desktop ) {
				desktop.querySelectorAll( 'li.is-open' ).forEach( function ( open ) {
					open.classList.remove( 'is-open' );
					var button = open.querySelector( ':scope > .wpe-menu__submenu-button' );
					if ( button ) {
						button.setAttribute( 'aria-expanded', 'false' );
					}
				} );
			}
		} );
	}

	function boot( node ) {
		( node || document ).querySelectorAll( '.wpe-menu' ).forEach( init );
	}
	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', function () { boot(); } );
	} else {
		boot();
	}
	document.addEventListener( 'wpe-kit-content-loaded', function ( event ) {
		boot( event.target );
	} );
}() );
