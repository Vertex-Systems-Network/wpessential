/**
 * House dropdown (ADR-0239 rule 3): every single <select> inside a .wpe-ui screen is shown as a
 * custom listbox. The native select stays in the DOM (visually hidden) so forms, AJAX saves and
 * existing change handlers keep working; the custom UI only mirrors and drives it.
 */

const DONE = 'wpeDropdown';
const SEARCH_FROM = 8;
let uid = 0;
const t = ( text: string ): string => ( window as unknown as { wp?: { i18n?: { __?: ( s: string, d: string ) => string } } } ).wp?.i18n?.__?.( text, 'wpessential' ) ?? text;

type Item = { value: string; label: string; disabled: boolean; group: string };

function items( select: HTMLSelectElement ): Item[] {
	return Array.from( select.options ).map( ( o ) => ( {
		value: o.value,
		label: o.textContent?.trim() || o.value || '—',
		disabled: o.disabled,
		group: o.parentElement instanceof HTMLOptGroupElement ? o.parentElement.label : '',
	} ) );
}

export function enhanceSelect( select: HTMLSelectElement ): void {
	if ( select.closest( '[data-wpe-app-view]' ) || select.dataset[ DONE ] || select.multiple || select.dataset.wpeNative !== undefined || select.size > 1 ) {
		return;
	}
	select.dataset[ DONE ] = '1';
	const id = `wpe-dd-${ ++uid }`;
	const wrap = document.createElement( 'div' );
	wrap.className = 'wpe-dd';
	const button = document.createElement( 'button' );
	button.type = 'button';
	button.className = 'wpe-dd__button';
	button.setAttribute( 'role', 'combobox' );
	button.setAttribute( 'aria-haspopup', 'listbox' );
	button.setAttribute( 'aria-expanded', 'false' );
	button.setAttribute( 'aria-controls', `${ id }-list` );
	const label = select.getAttribute( 'aria-label' ) || ( select.id ? document.querySelector( `label[for="${ CSS.escape( select.id ) }"]` )?.textContent?.trim() : '' ) || '';
	if ( label ) {
		button.setAttribute( 'aria-label', label );
	}
	const text = document.createElement( 'span' );
	text.className = 'wpe-dd__text';
	button.append( text );
	const panel = document.createElement( 'div' );
	panel.className = 'wpe-dd__panel';
	panel.hidden = true;
	const list = document.createElement( 'ul' );
	list.id = `${ id }-list`;
	list.className = 'wpe-dd__list';
	list.setAttribute( 'role', 'listbox' );
	if ( label ) {
		list.setAttribute( 'aria-label', label );
	}
	let search: HTMLInputElement | null = null;
	let active = -1;

	const options = (): HTMLLIElement[] => Array.from( list.querySelectorAll< HTMLLIElement >( '[role="option"]:not([hidden])' ) );

	const sync = (): void => {
		const current = select.options[ select.selectedIndex ];
		text.textContent = current ? current.textContent?.trim() || '—' : '—';
		button.disabled = select.disabled;
		for ( const li of Array.from( list.querySelectorAll< HTMLLIElement >( '[role="option"]' ) ) ) {
			li.setAttribute( 'aria-selected', li.dataset.value === select.value ? 'true' : 'false' );
		}
	};

	const build = (): void => {
		list.textContent = '';
		let group = '';
		items( select ).forEach( ( item, index ) => {
			if ( item.group && item.group !== group ) {
				group = item.group;
				const head = document.createElement( 'li' );
				head.className = 'wpe-dd__group';
				head.setAttribute( 'role', 'presentation' );
				head.textContent = group;
				list.append( head );
			}
			const li = document.createElement( 'li' );
			li.id = `${ id }-${ index }`;
			li.className = 'wpe-dd__option';
			li.setAttribute( 'role', 'option' );
			li.dataset.value = item.value;
			li.textContent = item.label;
			if ( item.disabled ) {
				li.setAttribute( 'aria-disabled', 'true' );
			}
			list.append( li );
		} );
		if ( select.options.length >= SEARCH_FROM && ! search ) {
			search = document.createElement( 'input' );
			search.type = 'search';
			search.className = 'wpe-dd__search';
			search.setAttribute( 'aria-label', t( 'Search options' ) );
			search.placeholder = t( 'Search…' );
			search.addEventListener( 'input', () => {
				const q = ( search?.value || '' ).toLowerCase();
				for ( const li of Array.from( list.querySelectorAll< HTMLLIElement >( '[role="option"]' ) ) ) {
					li.hidden = q !== '' && ! ( li.textContent || '' ).toLowerCase().includes( q );
				}
				move( 0, true );
			} );
			search.addEventListener( 'keydown', keys );
			panel.prepend( search );
		}
		sync();
	};

	const move = ( to: number, absolute = false ): void => {
		const all = options().filter( ( li ) => li.getAttribute( 'aria-disabled' ) !== 'true' );
		if ( all.length === 0 ) {
			active = -1;
			button.removeAttribute( 'aria-activedescendant' );
			return;
		}
		active = absolute ? Math.max( 0, Math.min( all.length - 1, to ) ) : Math.max( 0, Math.min( all.length - 1, active + to ) );
		for ( const li of options() ) {
			li.classList.toggle( 'is-active', li === all[ active ] );
		}
		const current = all[ active ];
		if ( current ) {
			current.scrollIntoView( { block: 'nearest' } );
			button.setAttribute( 'aria-activedescendant', current.id );
		}
	};

	const open = (): void => {
		if ( button.disabled || ! panel.hidden ) {
			return;
		}
		panel.hidden = false;
		wrap.classList.add( 'is-open' );
		button.setAttribute( 'aria-expanded', 'true' );
		const all = options().filter( ( li ) => li.getAttribute( 'aria-disabled' ) !== 'true' );
		move( Math.max( 0, all.findIndex( ( li ) => li.dataset.value === select.value ) ), true );
		search?.focus();
	};

	const close = ( focus = true ): void => {
		if ( panel.hidden ) {
			return;
		}
		panel.hidden = true;
		wrap.classList.remove( 'is-open' );
		button.setAttribute( 'aria-expanded', 'false' );
		button.removeAttribute( 'aria-activedescendant' );
		if ( search ) {
			search.value = '';
			search.dispatchEvent( new Event( 'input' ) );
		}
		if ( focus ) {
			button.focus();
		}
	};

	const choose = ( li: HTMLLIElement | undefined ): void => {
		if ( ! li || li.getAttribute( 'aria-disabled' ) === 'true' ) {
			return;
		}
		if ( select.value !== li.dataset.value ) {
			select.value = li.dataset.value ?? '';
			select.dispatchEvent( new Event( 'input', { bubbles: true } ) );
			select.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		}
		sync();
		close();
	};

	let typed = '';
	let typedAt = 0;
	function keys( event: KeyboardEvent ): void {
		const isOpen = ! panel.hidden;
		const all = options().filter( ( li ) => li.getAttribute( 'aria-disabled' ) !== 'true' );
		switch ( event.key ) {
			case 'ArrowDown':
			case 'ArrowUp':
				event.preventDefault();
				if ( ! isOpen ) {
					open();
				} else {
					move( event.key === 'ArrowDown' ? 1 : -1 );
				}
				return;
			case 'Home':
			case 'End':
				if ( isOpen ) {
					event.preventDefault();
					move( event.key === 'Home' ? 0 : all.length - 1, true );
				}
				return;
			case 'Enter':
			case ' ':
				if ( event.key === ' ' && event.target === search ) {
					return;
				}
				event.preventDefault();
				if ( isOpen ) {
					choose( all[ active ] );
				} else {
					open();
				}
				return;
			case 'Escape':
				if ( isOpen ) {
					event.preventDefault();
					close();
				}
				return;
			case 'Tab':
				close( false );
				return;
		}
		if ( event.key.length === 1 && event.target !== search && ! event.ctrlKey && ! event.metaKey ) {
			typed = Date.now() - typedAt > 700 ? event.key.toLowerCase() : typed + event.key.toLowerCase();
			typedAt = Date.now();
			const hit = all.findIndex( ( li ) => ( li.textContent || '' ).toLowerCase().startsWith( typed ) );
			if ( hit >= 0 ) {
				if ( isOpen ) {
					move( hit, true );
				} else {
					choose( all[ hit ] );
				}
			}
		}
	}

	button.addEventListener( 'click', () => ( panel.hidden ? open() : close() ) );
	button.addEventListener( 'keydown', keys );
	list.addEventListener( 'mousedown', ( event ) => event.preventDefault() );
	list.addEventListener( 'click', ( event ) => choose( ( event.target as HTMLElement ).closest< HTMLLIElement >( '[role="option"]' ) ?? undefined ) );
	document.addEventListener( 'click', ( event ) => {
		if ( ! wrap.contains( event.target as Node ) ) {
			close( false );
		}
	} );

	// Keep the custom UI in step when code changes the native select.
	const native = Object.getOwnPropertyDescriptor( HTMLSelectElement.prototype, 'value' );
	if ( native?.get && native.set ) {
		Object.defineProperty( select, 'value', {
			configurable: true,
			get: () => native.get?.call( select ),
			set: ( v: string ) => {
				native.set?.call( select, v );
				sync();
			},
		} );
	}
	select.addEventListener( 'change', sync );
	new MutationObserver( build ).observe( select, { childList: true, subtree: true, attributes: true, attributeFilter: [ 'disabled' ] } );
	select.closest( 'form' )?.addEventListener( 'reset', () => setTimeout( sync ) );

	select.classList.add( 'wpe-dd__native' );
	select.tabIndex = -1;
	select.setAttribute( 'aria-hidden', 'true' );
	select.before( wrap );
	panel.append( list );
	wrap.append( button, panel, select );
	build();
}

/** Enhances current and future selects inside WPEssential screens. */
export function initDropdowns( root: ParentNode = document ): void {
	const scan = ( node: ParentNode ): void => {
		for ( const select of Array.from( node.querySelectorAll< HTMLSelectElement >( '.wpe-ui select' ) ) ) {
			enhanceSelect( select );
		}
	};
	scan( root );
	if ( ! document.documentElement.dataset.wpeDdObserved ) {
		document.documentElement.dataset.wpeDdObserved = '1';
		new MutationObserver( () => scan( document ) ).observe( document.body, { childList: true, subtree: true } );
	}
}
