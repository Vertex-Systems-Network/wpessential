// Builder editors fill themselves in from the names, as WordPress does when it registers a post
// type or taxonomy: the singular name follows the plural, the key follows the singular, and every
// label is written from the names. A value the user types stops following; "Reset" brings the
// generated value back. Generated labels are not saved, so WordPress keeps writing them.

/** Label templates: {P}/{S} are the plural/singular names, {p}/{s} the same in lower case. */
export type LabelTemplates = Record< string, string >;

export const CPT_LABEL_TEMPLATES: LabelTemplates = {
	add_new: 'Add New {S}',
	add_new_item: 'Add New {S}',
	edit_item: 'Edit {S}',
	new_item: 'New {S}',
	view_item: 'View {S}',
	view_items: 'View {P}',
	search_items: 'Search {P}',
	not_found: 'No {p} found.',
	not_found_in_trash: 'No {p} found in Trash.',
	parent_item_colon: 'Parent {S}:',
	all_items: 'All {P}',
	archives: '{S} Archives',
	attributes: '{S} Attributes',
	insert_into_item: 'Insert into {s}',
	uploaded_to_this_item: 'Uploaded to this {s}',
	featured_image: 'Featured image',
	set_featured_image: 'Set featured image',
	remove_featured_image: 'Remove featured image',
	use_featured_image: 'Use as featured image',
	menu_name: '{P}',
	name_admin_bar: '{S}',
	filter_items_list: 'Filter {p} list',
	filter_by_date: 'Filter by date',
	items_list_navigation: '{P} list navigation',
	items_list: '{P} list',
	item_published: '{S} published.',
	item_published_privately: '{S} published privately.',
	item_reverted_to_draft: '{S} reverted to draft.',
	item_trashed: '{S} trashed.',
	item_scheduled: '{S} scheduled.',
	item_updated: '{S} updated.',
	item_link: '{S} Link',
	item_link_description: 'A link to a {s}.',
	template_name: 'Single item: {S}',
};

export const TAXONOMY_LABEL_TEMPLATES: LabelTemplates = {
	menu_name: '{P}',
	all_items: 'All {P}',
	edit_item: 'Edit {S}',
	view_item: 'View {S}',
	update_item: 'Update {S}',
	add_new_item: 'Add New {S}',
	new_item_name: 'New {S} Name',
	parent_item: 'Parent {S}',
	parent_item_colon: 'Parent {S}:',
	search_items: 'Search {P}',
	popular_items: 'Popular {P}',
	separate_items_with_commas: 'Separate {p} with commas',
	add_or_remove_items: 'Add or remove {p}',
	choose_from_most_used: 'Choose from the most used {p}',
	not_found: 'No {p} found.',
	no_terms: 'No {p}',
	filter_by_item: 'Filter by {s}',
	items_list_navigation: '{P} list navigation',
	items_list: '{P} list',
	most_used: 'Most Used',
	back_to_items: '← Go to {P}',
	item_link: '{S} Link',
	item_link_description: 'A link to a {s}.',
	name_field_description: 'The name is how it appears on your site.',
	slug_field_description: 'The “slug” is the URL-friendly version of the name.',
	parent_field_description: 'Assign a parent term to create a hierarchy.',
	desc_field_description: 'The description is not prominent by default; however, some themes may show it.',
};

/** "Books" → "Book", "Categories" → "Category", "Boxes" → "Box". */
export function singularOf( plural: string ): string {
	const word = plural.trim();
	// "-ie" words keep their e: Movies → Movie, Cookies → Cookie; Series and Species do not change.
	if ( /(series|species)$/i.test( word ) ) {
		return word;
	}
	if ( /(^|\s)(movie|cookie|zombie|rookie|pie|tie|lie|calorie|selfie|hippie|smoothie|goodie|newbie|auntie|freebie)s$/i.test( word ) ) {
		return word.slice( 0, -1 );
	}
	if ( /ies$/i.test( word ) ) {
		return word.slice( 0, -3 ) + ( /IES$/.test( word ) ? 'Y' : 'y' );
	}
	if ( /(s|x|z|ch|sh)es$/i.test( word ) ) {
		return word.slice( 0, -2 );
	}
	if ( /[^s]s$/i.test( word ) ) {
		return word.slice( 0, -1 );
	}
	return word;
}

function render( template: string, plural: string, singular: string ): string {
	return template
		.replaceAll( '{P}', plural )
		.replaceAll( '{S}', singular )
		.replaceAll( '{p}', plural.toLowerCase() )
		.replaceAll( '{s}', singular.toLowerCase() );
}

export const isGenerated = ( input: HTMLInputElement ): boolean => input.dataset.wpeAuto === '1';

interface AutofillOptions {
	form: HTMLFormElement;
	plural: HTMLInputElement;
	singular: HTMLInputElement;
	/** The label inputs, each with its label key. */
	labels: () => Array< { input: HTMLInputElement; key: string } >;
	templates: LabelTemplates;
}

/**
 * Binds the plural → singular → labels chain. Re-syncs whenever the page script fills the form
 * (it dispatches a "change" event on the form itself after reset or loading a definition).
 */
export function bindAutofill( options: AutofillOptions ): void {
	const { form, plural, singular, templates } = options;
	const names = (): [ string, string ] => {
		const p = plural.value.trim();
		const s = singular.value.trim() || singularOf( p );
		return [ p || s, s ];
	};
	const generated = ( key: string ): string => {
		const template = templates[ key ];
		const [ p, s ] = names();
		return template && ( p || s ) ? render( template, p, s ) : '';
	};

	const resetButtons = new Map< HTMLInputElement, HTMLButtonElement >();
	const mark = ( input: HTMLInputElement, auto: boolean ): void => {
		input.dataset.wpeAuto = auto ? '1' : '0';
		const reset = resetButtons.get( input );
		if ( reset ) {
			reset.hidden = auto;
		}
	};
	const refresh = (): void => {
		for ( const { input, key } of options.labels() ) {
			if ( isGenerated( input ) ) {
				input.value = generated( key );
				input.dispatchEvent( new Event( 'wpe:autofill' ) );
			}
		}
	};

	for ( const { input, key } of options.labels() ) {
		const reset = document.createElement( 'button' );
		reset.type = 'button';
		reset.className = 'wpe-ui-link-button wpe-ui-autofill-reset';
		reset.textContent = 'Reset';
		reset.setAttribute( 'aria-label', `Reset ${ key.replaceAll( '_', ' ' ) } to the generated text` );
		reset.hidden = true;
		reset.addEventListener( 'click', () => {
			mark( input, true );
			input.value = generated( key );
			input.dispatchEvent( new Event( 'wpe:autofill' ) );
		} );
		input.after( reset );
		resetButtons.set( input, reset );
		input.addEventListener( 'input', () => mark( input, input.value.trim() === '' ) );
		input.addEventListener( 'blur', () => {
			if ( isGenerated( input ) ) {
				input.value = generated( key );
			}
		} );
	}

	// The singular follows the plural until it is typed in; the key then follows the singular.
	let singularTouched = false;
	singular.addEventListener( 'input', ( event ) => {
		if ( event.isTrusted ) {
			singularTouched = singular.value !== '';
		}
		refresh();
	} );
	plural.addEventListener( 'input', () => {
		if ( ! singularTouched && ! singular.readOnly ) {
			singular.value = singularOf( plural.value );
			singular.dispatchEvent( new Event( 'input' ) );
			return;
		}
		refresh();
	} );

	const sync = (): void => {
		singularTouched = singular.value !== '' && singular.value !== singularOf( plural.value );
		for ( const { input, key } of options.labels() ) {
			const value = input.value.trim();
			mark( input, value === '' || value === generated( key ) );
		}
		refresh();
	};
	form.addEventListener( 'change', ( event ) => {
		if ( event.target === form ) {
			sync();
		}
	} );
	sync();
}
