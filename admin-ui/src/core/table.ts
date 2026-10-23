// List-table tools for builder list pages, laid out like WordPress list tables (wp-admin
// edit.php): status views with counts ("All (12) | Published (5) | Draft (4)"), a search box with
// a Search button, filter dropdowns with a Filter button, "N items" with « ‹ 1 of 2 › » paging
// above and below the table, and sortable column headers. Works on the rows the page script
// renders and re-applies itself whenever they are re-rendered (refresh, status change).
export interface TableOptions {
	/** Rows that are data (not the empty-state row). */
	rowSelector: string;
	/** Column (0-based) whose values become the status views above the table. */
	viewsColumn?: number;
	/** Columns (0-based) that get a filter dropdown, with its label ("All runtime health"). */
	filters?: Array< { column: number; label: string } >;
	/** Columns (0-based) that can be sorted; numeric columns sort as numbers. */
	sortable?: Array< { column: number; numeric?: boolean } >;
	/** Plural item name for the search button and counts, e.g. "Post Types". */
	itemsLabel: string;
	perPage?: number;
	/** Fixed views matched on row state (All / Published / Draft / Locked / Trash); replaces viewsColumn. */
	views?: Array< { key: string; label: string; match: ( row: HTMLTableRowElement ) => boolean } >;
	/** Bulk actions: adds a checkbox column with select-all, and a "Bulk actions" select with Apply. */
	bulk?: {
		actions: ( view: string ) => Array< [ string, string ] >;
		run: ( action: string, ids: string[] ) => void | Promise< void >;
		/** Row attribute holding the item id. */
		idOf: ( row: HTMLTableRowElement ) => string;
	};
}

type Direction = 'ascending' | 'descending';

function cellText( row: HTMLTableRowElement, column: number, offset = 0 ): string {
	return ( row.cells[ column + offset ]?.textContent ?? '' ).replace( /\s+/g, ' ' ).trim();
}

function el< K extends keyof HTMLElementTagNameMap >(
	tag: K,
	attrs: Record< string, string > = {},
	text = ''
): HTMLElementTagNameMap[ K ] {
	const node = document.createElement( tag );
	for ( const [ name, value ] of Object.entries( attrs ) ) {
		node.setAttribute( name, value );
	}
	if ( text !== '' ) {
		node.textContent = text;
	}
	return node;
}

/** « ‹ [n] of N › » like WordPress' .tablenav-pages. */
function pagerControls( id: string, onPage: ( page: number ) => void ) {
	const wrap = el( 'div', { class: 'wpe-ui-tablenav__pages' } );
	const count = el( 'span', { class: 'wpe-ui-tablenav__count' } );
	const first = el( 'button', { type: 'button', class: 'wpe-ui-pagebtn', 'aria-label': 'First page' }, '«' );
	const prev = el( 'button', { type: 'button', class: 'wpe-ui-pagebtn', 'aria-label': 'Previous page' }, '‹' );
	const input = el( 'input', { type: 'number', min: '1', class: 'wpe-ui-pageinput', id, 'aria-label': 'Current page' } );
	const total = el( 'span', { class: 'wpe-ui-tablenav__total' } );
	const next = el( 'button', { type: 'button', class: 'wpe-ui-pagebtn', 'aria-label': 'Next page' }, '›' );
	const last = el( 'button', { type: 'button', class: 'wpe-ui-pagebtn', 'aria-label': 'Last page' }, '»' );
	const paging = el( 'span', { class: 'wpe-ui-tablenav__paging' } );
	paging.append( first, prev, input, total, next, last );
	wrap.append( count, paging );
	let pages = 1;
	first.addEventListener( 'click', () => onPage( 1 ) );
	prev.addEventListener( 'click', () => onPage( Number( input.value ) - 1 ) );
	next.addEventListener( 'click', () => onPage( Number( input.value ) + 1 ) );
	last.addEventListener( 'click', () => onPage( pages ) );
	input.addEventListener( 'change', () => onPage( Number( input.value ) ) );
	return {
		wrap,
		update( items: number, page: number, pageCount: number ): void {
			pages = pageCount;
			count.textContent = `${ items } item${ items === 1 ? '' : 's' }`;
			input.value = String( page );
			input.max = String( pageCount );
			total.textContent = ` of ${ pageCount } `;
			paging.hidden = pageCount <= 1;
			first.disabled = prev.disabled = page <= 1;
			next.disabled = last.disabled = page >= pageCount;
		},
	};
}

export function enhanceTable( table: HTMLTableElement, options: TableOptions ): void {
	const maybeBody = table.tBodies[ 0 ];
	if ( ! maybeBody || table.dataset.wpeTable === 'ready' ) {
		return;
	}
	const body: HTMLTableSectionElement = maybeBody;
	table.dataset.wpeTable = 'ready';
	const id = table.id || 'wpe-table';
	// The bulk checkbox column comes first; option columns count from the first data column.
	const offset = options.bulk ? 1 : 0;
	const text = ( row: HTMLTableRowElement, column: number ): string => cellText( row, column, offset );
	const state = {
		view: '',
		query: '',
		filters: new Map< number, string >(),
		sort: null as null | { column: number; direction: Direction; numeric: boolean },
		page: 1,
		size: options.perPage ?? 20,
	};

	// Row 1: status views (left) and search box (right).
	const top = el( 'div', { class: 'wpe-ui-tablenav__head' } );
	// Status views use the toolkit tabs (Figma UI/Tabs) with a count badge on each.
	const views = el( 'nav', { class: 'wpe-ui-tabs wpe-ui-views', 'aria-label': 'Filter by status' } );
	const searchForm = el( 'form', { class: 'wpe-ui-tablesearch', role: 'search' } );
	const searchLabel = `Search ${ options.itemsLabel }`;
	const search = el( 'input', { type: 'search', id: `${ id }-search`, class: 'wpe-ui-input', 'aria-label': searchLabel } );
	const searchButton = el( 'button', { type: 'submit', class: 'wpe-ui-button wpe-ui-button--secondary wpe-ui-button--sm' }, searchLabel );
	searchForm.append( search, searchButton );
	top.append( views, searchForm );

	// Row 2: filter dropdowns + Filter button (left), item count and paging (right).
	const nav = el( 'div', { class: 'wpe-ui-tablenav' } );
	const filterBox = el( 'div', { class: 'wpe-ui-tablenav__filters' } );
	const selects: Array< { column: number; label: string; select: HTMLSelectElement } > = [];
	for ( const filter of options.filters ?? [] ) {
		const select = el( 'select', { class: 'wpe-ui-select', 'aria-label': `Filter by ${ filter.label.toLowerCase() }` } );
		selects.push( { column: filter.column, label: filter.label, select } );
		filterBox.append( select );
	}
	const bulkSelect = el( 'select', { class: 'wpe-ui-select', id: `${ id }-bulk`, 'aria-label': 'Bulk actions' } );
	const bulkApply = el( 'button', { type: 'button', class: 'wpe-ui-button wpe-ui-button--secondary wpe-ui-button--sm' }, 'Apply' );
	const bulkBox = el( 'div', { class: 'wpe-ui-tablenav__bulk' } );
	bulkBox.append( bulkSelect, bulkApply );
	const filterButton = el( 'button', { type: 'button', class: 'wpe-ui-button wpe-ui-button--secondary wpe-ui-button--sm' }, 'Filter' );
	if ( selects.length ) {
		filterBox.append( filterButton );
	}
	const goTo = ( page: number ): void => {
		state.page = page;
		apply();
	};
	const topPager = pagerControls( `${ id }-page`, goTo );
	nav.append( ...( options.bulk ? [ bulkBox ] : [] ), filterBox, topPager.wrap );
	const bottomNav = el( 'div', { class: 'wpe-ui-tablenav wpe-ui-tablenav--bottom' } );
	const bottomPager = pagerControls( `${ id }-page-bottom`, goTo );
	bottomNav.append( bottomPager.wrap );

	table.before( top, nav );
	table.after( bottomNav );

	// Bulk selection: a checkbox column with select-all in the header (and footer).
	const selectAll: HTMLInputElement[] = [];
	const checkCell = ( tag: 'th' | 'td', label: string ): { cell: HTMLTableCellElement; box: HTMLInputElement } => {
		const cell = el( tag, { class: 'wpe-ui-table__check' } );
		const box = el( 'input', { type: 'checkbox', class: 'wpe-ui-checkbox', 'aria-label': label } );
		cell.append( box );
		return { cell, box };
	};
	const headRow = table.tHead?.rows[ 0 ];
	if ( options.bulk && headRow ) {
		const { cell, box } = checkCell( 'th', 'Select all' );
		cell.setAttribute( 'scope', 'col' );
		headRow.prepend( cell );
		selectAll.push( box );
	}

	// Sortable headers: a button inside each th, aria-sort on the th.
	const headers = Array.from( headRow?.cells ?? [] );
	for ( const sortable of options.sortable ?? [] ) {
		const th = headers[ sortable.column + offset ];
		if ( ! th ) {
			continue;
		}
		const button = el( 'button', { type: 'button', class: 'wpe-ui-sort' }, th.textContent?.trim() ?? '' );
		th.replaceChildren( button );
		th.setAttribute( 'aria-sort', 'none' );
		button.addEventListener( 'click', () => {
			const same = state.sort?.column === sortable.column;
			const direction: Direction = same && state.sort?.direction === 'ascending' ? 'descending' : 'ascending';
			state.sort = { column: sortable.column, direction, numeric: sortable.numeric ?? false };
			for ( const header of headers ) {
				if ( header.hasAttribute( 'aria-sort' ) ) {
					header.setAttribute( 'aria-sort', header === th ? direction : 'none' );
				}
			}
			apply();
		} );
	}

	// Footer: the column headings again, as in WordPress list tables.
	if ( headRow && ! table.tFoot ) {
		const footRow = el( 'tr' );
		for ( const [ index, header ] of headers.entries() ) {
			if ( options.bulk && index === 0 ) {
				const { cell, box } = checkCell( 'th', 'Select all' );
				selectAll.push( box );
				footRow.append( cell );
				continue;
			}
			footRow.append( el( 'th', { scope: 'col' }, header.textContent?.trim() ?? '' ) );
		}
		table.createTFoot().append( footRow );
	}

	const empty = el( 'tr', { class: 'wpe-ui-table-nomatch', hidden: '' } );
	empty.append( el( 'td', { colspan: String( headers.length || 1 ) }, `No ${ options.itemsLabel.toLowerCase() } found.` ) );

	let observer: MutationObserver | null = null;
	const rows = (): HTMLTableRowElement[] => Array.from( body.querySelectorAll< HTMLTableRowElement >( options.rowSelector ) );

	const viewMatches = ( row: HTMLTableRowElement ): boolean => {
		if ( options.views ) {
			return ( options.views.find( ( view ) => view.key === ( state.view || options.views?.[ 0 ]?.key ) )?.match ?? ( () => true ) )( row );
		}
		return options.viewsColumn === undefined || state.view === '' || text( row, options.viewsColumn ) === state.view;
	};

	function renderFixedViews( all: HTMLTableRowElement[] ): void {
		const list = options.views ?? [];
		if ( ! list.some( ( view ) => view.key === state.view ) ) {
			state.view = list[ 0 ]?.key ?? '';
		}
		views.replaceChildren(
			...list
				.map( ( view ) => ( { view, n: all.filter( view.match ).length } ) )
				// Like WordPress, a view with no items is not listed (except the first, All).
				.filter( ( { view, n }, index ) => index === 0 || n > 0 || view.key === state.view )
				.map( ( { view, n } ) => {
					const current = state.view === view.key;
					const link = el( 'a', { href: '#', class: `wpe-ui-tab${ current ? ' is-active' : '' }`, 'data-wpe-view': view.key } );
					if ( current ) {
						link.setAttribute( 'aria-current', 'page' );
					}
					link.append( view.label, el( 'span', { class: 'wpe-ui-count' }, String( n ) ) );
					link.addEventListener( 'click', ( event ) => {
						event.preventDefault();
						state.view = view.key;
						state.page = 1;
						apply();
					} );
					return link;
				} )
		);
	}

	function renderBulk( all: HTMLTableRowElement[] ): void {
		if ( ! options.bulk ) {
			return;
		}
		const current = bulkSelect.value;
		const actions = options.bulk.actions( state.view );
		bulkSelect.replaceChildren( el( 'option', { value: '' }, 'Bulk actions' ), ...actions.map( ( [ value, label ] ) => el( 'option', { value }, label ) ) );
		bulkSelect.value = actions.some( ( [ value ] ) => value === current ) ? current : '';
		for ( const row of all ) {
			if ( ! row.querySelector( ':scope > .wpe-ui-table__check' ) ) {
				const name = text( row, 0 );
				row.prepend( checkCell( 'td', `Select ${ name }` ).cell );
			}
		}
		// Non-data rows (the empty state) span the extra column.
		for ( const row of Array.from( body.rows ) ) {
			const only = row.cells.length === 1 ? row.cells[ 0 ] : undefined;
			if ( only && only.colSpan < headers.length ) {
				only.colSpan = headers.length;
			}
		}
	}

	const checkedIds = (): string[] =>
		rows()
			.filter( ( row ) => ! row.hidden && row.querySelector< HTMLInputElement >( ':scope > .wpe-ui-table__check input' )?.checked )
			.map( ( row ) => options.bulk?.idOf( row ) ?? '' )
			.filter( Boolean );

	function renderViews( all: HTMLTableRowElement[] ): void {
		if ( options.views ) {
			renderFixedViews( all );
			return;
		}
		if ( options.viewsColumn === undefined ) {
			views.hidden = true;
			return;
		}
		const column = options.viewsColumn;
		const counts = new Map< string, number >();
		for ( const row of all ) {
			const value = text( row, column );
			counts.set( value, ( counts.get( value ) ?? 0 ) + 1 );
		}
		if ( state.view !== '' && ! counts.has( state.view ) ) {
			state.view = '';
		}
		const items: Array< [ string, string, number ] > = [ [ '', 'All', all.length ], ...[ ...counts.entries() ].sort().map( ( [ value, n ] ): [ string, string, number ] => [ value, value, n ] ) ];
		views.replaceChildren(
			...items.map( ( [ value, label, n ] ) => {
				const current = state.view === value;
				const link = el( 'a', { href: '#', class: `wpe-ui-tab${ current ? ' is-active' : '' }` } );
				if ( current ) {
					link.setAttribute( 'aria-current', 'page' );
				}
				link.append( label, el( 'span', { class: 'wpe-ui-count' }, String( n ) ) );
				link.addEventListener( 'click', ( event ) => {
					event.preventDefault();
					state.view = value;
					state.page = 1;
					apply();
				} );
				return link;
			} )
		);
	}

	function renderFilterOptions( all: HTMLTableRowElement[] ): void {
		for ( const { column, label, select } of selects ) {
			const current = select.value;
			const values = Array.from( new Set( all.map( ( row ) => text( row, column ) ).filter( Boolean ) ) ).sort();
			select.replaceChildren( el( 'option', { value: '' }, `All ${ label.toLowerCase() }` ), ...values.map( ( value ) => el( 'option', { value }, value ) ) );
			select.value = values.includes( current ) ? current : '';
			if ( ! values.includes( state.filters.get( column ) ?? '' ) ) {
				state.filters.set( column, '' );
			}
		}
	}

	function apply(): void {
		observer?.disconnect();
		const all = rows();
		renderBulk( all );
		renderViews( all );
		renderFilterOptions( all );
		for ( const box of selectAll ) {
			box.checked = false;
		}
		const query = state.query.toLowerCase();
		const matching = all.filter( ( row ) => {
			if ( ! viewMatches( row ) ) {
				return false;
			}
			if ( query !== '' && ! ( row.textContent ?? '' ).toLowerCase().includes( query ) ) {
				return false;
			}
			for ( const [ column, value ] of state.filters ) {
				if ( value !== '' && text( row, column ) !== value ) {
					return false;
				}
			}
			return true;
		} );
		const sort = state.sort;
		if ( sort ) {
			const factor = sort.direction === 'ascending' ? 1 : -1;
			matching.sort( ( a, b ) => {
				const x = text( a, sort.column );
				const y = text( b, sort.column );
				return factor * ( sort.numeric ? Number( x ) - Number( y ) : x.localeCompare( y, undefined, { numeric: true, sensitivity: 'base' } ) );
			} );
			for ( const row of matching ) {
				body.append( row );
			}
		}
		const pages = Math.max( 1, Math.ceil( matching.length / state.size ) );
		state.page = Math.min( Math.max( 1, state.page ), pages );
		const start = ( state.page - 1 ) * state.size;
		const visible = new Set( matching.slice( start, start + state.size ) );
		for ( const row of all ) {
			row.hidden = ! visible.has( row );
		}
		const hasData = all.length > 0;
		empty.hidden = ! hasData || matching.length > 0;
		if ( empty.parentElement !== body ) {
			body.append( empty );
		}
		top.hidden = nav.hidden = ! hasData;
		if ( table.tFoot ) {
			table.tFoot.hidden = ! hasData;
		}
		bottomNav.hidden = ! hasData || pages <= 1;
		topPager.update( matching.length, state.page, pages );
		bottomPager.update( matching.length, state.page, pages );
		observer?.observe( body, { childList: true } );
	}

	searchForm.addEventListener( 'submit', ( event ) => {
		event.preventDefault();
		state.query = search.value.trim();
		state.page = 1;
		apply();
	} );
	// Clearing the box (the search field's own ×) shows everything again, as in WordPress.
	search.addEventListener( 'search', () => {
		if ( search.value === '' ) {
			state.query = '';
			apply();
		}
	} );
	filterButton.addEventListener( 'click', () => {
		for ( const { column, select } of selects ) {
			state.filters.set( column, select.value );
		}
		state.page = 1;
		apply();
	} );

	for ( const box of selectAll ) {
		box.addEventListener( 'change', () => {
			for ( const other of selectAll ) {
				other.checked = box.checked;
			}
			for ( const row of rows() ) {
				const check = row.querySelector< HTMLInputElement >( ':scope > .wpe-ui-table__check input' );
				if ( check && ! row.hidden ) {
					check.checked = box.checked;
				}
			}
		} );
	}
	bulkApply.addEventListener( 'click', () => {
		const action = bulkSelect.value;
		const ids = checkedIds();
		if ( action === '' || ids.length === 0 || ! options.bulk ) {
			return;
		}
		void options.bulk.run( action, ids );
	} );

	// Rows are re-rendered by the page script on refresh and status changes.
	observer = new MutationObserver( apply );
	apply();
}
