import './ui.scss';
import { initAjaxForms } from './core/forms';

function matches( element: HTMLElement, query: string ): boolean {
	return query === '' || ( element.dataset.search ?? '' ).includes( query );
}

function initModuleFilters( root: HTMLElement ): void {
	const cards = Array.from( root.querySelectorAll< HTMLElement >( '[data-wpe-module]' ) );
	if ( cards.length === 0 ) {
		return;
	}

	const search = root.querySelector< HTMLInputElement >( '[data-wpe-search]' );
	const category = root.querySelector< HTMLSelectElement >( '[data-wpe-category]' );
	const segments = Array.from( root.querySelectorAll< HTMLButtonElement >( '[data-wpe-edition]' ) );
	const groups = Array.from( root.querySelectorAll< HTMLElement >( '[data-wpe-category-group]' ) );
	const empty = root.querySelector< HTMLElement >( '[data-wpe-no-results]' );
	let edition = 'all';

	const apply = (): void => {
		const query = ( search?.value ?? '' ).trim().toLowerCase();
		const selectedCategory = category?.value ?? '';
		let visible = 0;
		for ( const card of cards ) {
			const editionMatch =
				edition === 'all' ||
				( edition === 'active' && card.dataset.active === '1' ) ||
				( edition === 'inactive' && card.dataset.active !== '1' ) ||
				card.dataset.edition === edition;
			const show =
				editionMatch &&
				matches( card, query ) &&
				( selectedCategory === '' || card.dataset.category === selectedCategory );
			card.hidden = ! show;
			visible += show ? 1 : 0;
		}
		for ( const group of groups ) {
			group.hidden = group.querySelector( '[data-wpe-module]:not([hidden])' ) === null;
		}
		if ( empty ) {
			empty.hidden = visible > 0;
		}
	};

	for ( const segment of segments ) {
		segment.addEventListener( 'click', () => {
			edition = segment.dataset.wpeEdition ?? 'all';
			for ( const other of segments ) {
				other.setAttribute( 'aria-pressed', other === segment ? 'true' : 'false' );
			}
			apply();
		} );
	}
	search?.addEventListener( 'input', apply );
	category?.addEventListener( 'change', apply );
	apply();
}

function initOptionFilters( root: HTMLElement ): void {
	const groups = Array.from( root.querySelectorAll< HTMLDetailsElement >( '[data-wpe-group]' ) );
	if ( groups.length === 0 ) {
		return;
	}

	const tabs = Array.from( root.querySelectorAll< HTMLButtonElement >( '[data-wpe-tier]' ) );
	const search = root.querySelector< HTMLInputElement >( '[data-wpe-option-search]' );
	const empty = root.querySelector< HTMLElement >( '[data-wpe-no-options]' );
	let tier = '';

	const apply = (): void => {
		const query = ( search?.value ?? '' ).trim().toLowerCase();
		let total = 0;
		for ( const group of groups ) {
			let visible = 0;
			for ( const option of Array.from( group.querySelectorAll< HTMLElement >( '[data-wpe-option]' ) ) ) {
				const show = ( tier === '' || option.dataset.tier === tier ) && matches( option, query );
				option.hidden = ! show;
				visible += show ? 1 : 0;
			}
			group.hidden = visible === 0;
			if ( query !== '' && visible > 0 ) {
				group.open = true;
			}
			total += visible;
		}
		if ( empty ) {
			empty.hidden = total > 0;
		}
	};

	for ( const tab of tabs ) {
		tab.addEventListener( 'click', () => {
			tier = tab.dataset.wpeTier ?? '';
			for ( const other of tabs ) {
				other.setAttribute( 'aria-selected', other === tab ? 'true' : 'false' );
			}
			apply();
		} );
	}
	search?.addEventListener( 'input', apply );
}

// In "system" mode the server cannot know the effective scheme, so submit its opposite.
function initModeToggle(): void {
	const target = document.querySelector< HTMLInputElement >( '[data-wpe-mode-target]' );
	if ( ! target || ! document.body.classList.contains( 'wpe-mode-system' ) ) {
		return;
	}
	const dark = window.matchMedia( '(prefers-color-scheme: dark)' ).matches;
	target.value = dark ? 'light' : 'dark';
}

initModeToggle();
initAjaxForms();

const root = document.querySelector< HTMLElement >( '[data-wpessential-surface="core"]' );
if ( root ) {
	initModuleFilters( root );
	initOptionFilters( root );
}
