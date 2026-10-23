// Shows what WordPress will actually use for every option left on its default, so no field reads
// just "Default": "Default (WordPress): Yes", and text fields show the value as their placeholder.
// Follows register_post_type(): most visibility options inherit "Public" or "Show admin screens".
const checked = ( id: string ): boolean => ( document.getElementById( id ) as HTMLInputElement | null )?.checked ?? false;
const value = ( selector: string ): string => ( document.querySelector< HTMLInputElement | HTMLSelectElement >( selector )?.value ?? '' ).trim();

function tri( key: string, fallback: boolean ): boolean {
	const chosen = value( `[data-wpessential-cpt-opt="${ key }"]` );
	return chosen === '' ? fallback : chosen !== 'false';
}

function setDefaultText( key: string, text: string ): void {
	const option = document.querySelector< HTMLOptionElement >( `[data-wpessential-cpt-opt="${ key }"] option[value=""]` );
	if ( option ) {
		option.textContent = `Default: ${ text }`;
	}
}

const yes = ( on: boolean ): string => ( on ? 'Yes' : 'No' );

export function refreshCptDefaults(): void {
	const isPublic = checked( 'wpessential-cpt-public' );
	const key = value( '#wpessential-cpt-key' ) || 'book';
	const showUi = tri( 'show_ui', isPublic );
	const inMenu = tri( 'show_in_menu', showUi );
	setDefaultText( 'show_ui', `${ yes( isPublic ) } (as Public)` );
	setDefaultText( 'show_in_menu', `${ yes( showUi ) } (as admin screens)` );
	setDefaultText( 'show_in_nav_menus', `${ yes( isPublic ) } (as Public)` );
	setDefaultText( 'show_in_admin_bar', `${ yes( inMenu ) } (as admin menu)` );
	setDefaultText( 'exclude_from_search', `${ yes( ! isPublic ) } (not Public)` );
	setDefaultText( 'publicly_queryable', `${ yes( isPublic ) } (as Public)` );
	setDefaultText( 'query_var', `Yes, "${ key }"` );
	setDefaultText( 'can_export', 'Yes' );
	setDefaultText( 'delete_with_user', ( document.querySelector< HTMLInputElement >( '[data-wpessential-cpt-support="author"]' )?.checked ?? false ) ? 'Yes (supports Author)' : 'Only if it supports Author' );
	setDefaultText( 'map_meta_cap', value( '[data-wpessential-cpt-opt="capability_type"]' ) === '' ? 'Yes' : 'No' );
	setDefaultText( 'automatic_labels', 'Yes' );

	const placeholders: Record< string, string > = {
		'#wpessential-cpt-archive-slug': key,
		'#wpessential-cpt-rewrite-slug': key,
		'[data-wpessential-cpt-opt-custom="query_var"]': key,
		'[data-wpessential-cpt-opt="rest_base"]': key,
		'[data-wpessential-cpt-opt="rest_namespace"]': 'wp/v2',
		'[data-wpessential-cpt-opt="capability_type"]': 'post',
		'[data-wpessential-cpt-opt="menu_position"]': '25 (below Comments)',
	};
	for ( const [ selector, text ] of Object.entries( placeholders ) ) {
		const input = document.querySelector< HTMLInputElement >( selector );
		if ( input ) {
			input.placeholder = text;
		}
	}
}

export function bindCptDefaults( form: HTMLFormElement ): void {
	for ( const type of [ 'change', 'input', 'wpe:autofill' ] ) {
		form.addEventListener( type, refreshCptDefaults, true );
	}
	refreshCptDefaults();
}
