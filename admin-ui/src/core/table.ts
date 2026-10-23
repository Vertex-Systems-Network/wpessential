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
}

type Direction = 'ascending' | 'descending';

function cellText( row: HTMLTableRowElement, column: number ): string {
	return ( row.cells[ column ]?.textContent ?? '' ).replace( /\s+/g, ' ' ).trim();
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
	const filterButton = el( 'button', { type: 'button', class: 'wpe-ui-button wpe-ui-button--secondary wpe-ui-button--sm' }, 'Filter' );
	if ( selects.length ) {
		filterBox.append( filterButton );
	}
	const goTo = ( page: number ): void => {
		state.page = page;
		apply();
	};
	const topPager = pagerControls( `${ id }-page`, goTo );
	nav.append( filterBox, topPager.wrap );
	const bottomNav = el( 'div', { class: 'wpe-ui-tablenav wpe-ui-tablenav--bottom' } );
	const bottomPager = pagerControls( `${ id }-page-bottom`, goTo );
	bottomNav.append( bottomPager.wrap );

	table.before( top, nav );
	table.after( bottomNav );

	// Sortable headers: a button inside each th, aria-sort on the th.
	const headers = Array.from( table.tHead?.rows[ 0 ]?.cells ?? [] );
	for ( const sortable of options.sortable ?? [] ) {
		const th = headers[ sortable.column ];
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

	const empty = el( 'tr', { class: 'wpe-ui-table-nomatch', hidden: '' } );
	empty.append( el( 'td', { colspan: String( headers.length || 1 ) }, `No ${ options.itemsLabel.toLowerCase() } found.` ) );

	let observer: MutationObserver | null = null;
	const rows = (): HTMLTableRowElement[] => Array.from( body.querySelectorAll< HTMLTableRowElement >( options.rowSelector ) );

	function renderViews( all: HTMLTableRowElement[] ): void {
		if ( options.viewsColumn === undefined ) {
			views.hidden = true;
			return;
		}
		const column = options.viewsColumn;
		const counts = new Map< string, number >();
		for ( const row of all ) {
			const value = cellText( row, column );
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
			const values = Array.from( new Set( all.map( ( row ) => cellText( row, column ) ).filter( Boolean ) ) ).sort();
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
		renderViews( all );
		renderFilterOptions( all );
		const query = state.query.toLowerCase();
		const matching = all.filter( ( row ) => {
			if ( options.viewsColumn !== undefined && state.view !== '' && cellText( row, options.viewsColumn ) !== state.view ) {
				return false;
			}
			if ( query !== '' && ! ( row.textContent ?? '' ).toLowerCase().includes( query ) ) {
				return false;
			}
			for ( const [ column, value ] of state.filters ) {
				if ( value !== '' && cellText( row, column ) !== value ) {
					return false;
				}
			}
			return true;
		} );
		const sort = state.sort;
		if ( sort ) {
			const factor = sort.direction === 'ascending' ? 1 : -1;
			matching.sort( ( a, b ) => {
				const x = cellText( a, sort.column );
				const y = cellText( b, sort.column );
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

	// Rows are re-rendered by the page script on refresh and status changes.
	observer = new MutationObserver( apply );
	apply();
}
