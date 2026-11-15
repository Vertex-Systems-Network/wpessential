/**
 * WPEssential Data Table: progressive enhancement for client-side sorting and
 * filtering. Table contents remain fully readable with JavaScript disabled.
 */
( function () {
	'use strict';

	function init( root ) {
		if ( root.dataset.wpeTableReady ) {
			return;
		}
		root.dataset.wpeTableReady = '1';
		var table = root.querySelector( '.wpe-table__table' );
		if ( ! table || ! table.tBodies.length ) {
			return;
		}
		var body = table.tBodies[ 0 ];
		var rows = Array.prototype.slice.call( body.querySelectorAll( 'tr[data-wpe-row]' ) );
		var buttons = Array.prototype.slice.call( table.querySelectorAll( '[data-wpe-sort]' ) );
		var search = root.querySelector( '.wpe-table__search' );
		var empty = root.querySelector( '.wpe-table__no-results' );
		var status = root.querySelector( '.wpe-table__status' );
		var collator = window.Intl && window.Intl.Collator ? new window.Intl.Collator( undefined, { numeric: true, sensitivity: 'base' } ) : null;
		var activeColumn = -1;
		var descending = false;
		var order = rows.map( function ( row, i ) {
			return { row: row, index: i };
		} );

		function filtered() {
			var query = search ? search.value.trim().toLocaleLowerCase() : '';
			var visible = 0;
			rows.forEach( function ( row ) {
				var show = ! query || row.textContent.toLocaleLowerCase().indexOf( query ) !== -1;
				row.hidden = ! show;
				if ( show ) {
					visible++;
				}
			} );
			if ( empty ) {
				empty.hidden = visible !== 0;
			}
			if ( status ) {
				var template = visible === 1 ? root.dataset.rowSingular : root.dataset.rowPlural;
				status.textContent = ( template || '%d rows shown' ).replace( '%d', String( visible ) );
			}
		}

		buttons.forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				var column = Number( button.dataset.wpeSort );
				if ( ! Number.isInteger( column ) || column < 0 ) {
					return;
				}
				descending = column === activeColumn ? ! descending : false;
				activeColumn = column;
				order.sort( function ( a, b ) {
					var left = a.row.cells[ column ] ? a.row.cells[ column ].textContent.trim() : '';
					var right = b.row.cells[ column ] ? b.row.cells[ column ].textContent.trim() : '';
					var result = collator ? collator.compare( left, right ) : left.localeCompare( right );
					return result ? ( descending ? -result : result ) : a.index - b.index;
				} );
				order.forEach( function ( item ) {
					body.appendChild( item.row );
				} );
				buttons.forEach( function ( candidate ) {
					var header = candidate.closest( 'th' );
					if ( header ) {
						header.setAttribute( 'aria-sort', candidate === button ? ( descending ? 'descending' : 'ascending' ) : 'none' );
					}
				} );
				filtered();
			} );
		} );
		if ( search ) {
			search.addEventListener( 'input', filtered );
		}
	}
	function boot() {
		document.querySelectorAll( '[data-wpe-table]' ).forEach( init );
	}
	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}
	if ( 'MutationObserver' in window ) {
		new window.MutationObserver( function ( mutations ) {
			mutations.forEach( function ( mutation ) {
				mutation.addedNodes.forEach( function ( node ) {
					if ( 1 !== node.nodeType ) {
						return;
					}
					if ( node.matches( '[data-wpe-table]' ) ) {
						init( node );
					}
					node.querySelectorAll( '[data-wpe-table]' ).forEach( init );
				} );
			} );
		} ).observe( document.documentElement, { childList: true, subtree: true } );
	}
}() );
