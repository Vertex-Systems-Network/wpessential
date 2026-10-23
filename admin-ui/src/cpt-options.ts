// Advanced Custom Post Type options (CustomPostTypeDefinitionProjector keys beyond the basics).
// Each control declares its payload key and shape; "Default" leaves the key out of the payload
// so the projector's WordPress default applies.
type Payload = Record< string, unknown >;

/** Payload keys owned by this module: they are rebuilt from the form on every save. */
export const ADVANCED_KEYS = [
	'show_ui',
	'show_in_menu',
	'menu_position',
	'menu_icon',
	'show_in_nav_menus',
	'show_in_admin_bar',
	'exclude_from_search',
	'publicly_queryable',
	'rewrite',
	'query_var',
	'can_export',
	'rest_base',
	'rest_namespace',
	'capability_type',
	'capabilities',
	'map_meta_cap',
	'delete_with_user',
	'taxonomies',
	'automatic_labels',
	'labels',
] as const;

function all< T extends Element >( selector: string ): T[] {
	return Array.from( document.querySelectorAll< T >( selector ) );
}

function field< T extends HTMLInputElement | HTMLSelectElement >(
	selector: string
): T | null {
	return document.querySelector< T >( selector );
}

function triValue( value: string ): boolean | undefined {
	return value === 'true' ? true : value === 'false' ? false : undefined;
}

function triString( value: unknown ): string {
	return value === true ? 'true' : value === false ? 'false' : '';
}

function customInput( key: string ): HTMLInputElement | null {
	return field< HTMLInputElement >(
		`[data-wpessential-cpt-opt-custom="${ key }"]`
	);
}

function rewriteValue(): unknown {
	const mode = field( '[data-wpessential-cpt-opt="rewrite"]' )?.value ?? '';
	if ( mode === 'false' ) {
		return false;
	}
	if ( mode !== 'custom' ) {
		return undefined;
	}
	const map: Payload = {};
	const slug =
		field( '[data-wpessential-cpt-rewrite="slug"]' )?.value.trim() ?? '';
	if ( slug !== '' ) {
		map.slug = slug;
	}
	for ( const key of [ 'with_front', 'feeds', 'pages' ] ) {
		const value = triValue(
			field( `[data-wpessential-cpt-rewrite="${ key }"]` )?.value ?? ''
		);
		if ( value !== undefined ) {
			map[ key ] = value;
		}
	}
	return Object.keys( map ).length ? map : true;
}

/** Reads every advanced control into payload keys (absent = default). */
export function collectAdvanced(): Payload {
	const out: Payload = {};
	for ( const el of all< HTMLInputElement | HTMLSelectElement >(
		'[data-wpessential-cpt-opt]'
	) ) {
		const key = el.dataset.wpessentialCptOpt ?? '';
		const value = el.value.trim();
		let result: unknown;
		switch ( el.dataset.kind ) {
			case 'tri':
				result = triValue( value );
				break;
			case 'int':
				result = value === '' ? undefined : Number.parseInt( value, 10 );
				break;
			case 'text':
				result = value === '' ? undefined : value;
				break;
			case 'bool-text':
				result =
					value === 'custom'
						? customInput( key )?.value.trim() || undefined
						: triValue( value );
				break;
			case 'pair': {
				const plural =
					field< HTMLInputElement >(
						'#wpessential-cpt-capability-plural'
					)?.value.trim() ?? '';
				result =
					value === ''
						? undefined
						: plural === ''
						? value
						: [ value, plural ];
				break;
			}
			case 'rewrite':
				result = rewriteValue();
				break;
		}
		if ( key !== '' && result !== undefined ) {
			out[ key ] = result;
		}
	}

	const caps: Record< string, string > = {};
	for ( const el of all< HTMLInputElement >( '[data-wpessential-cpt-cap]' ) ) {
		if ( el.value.trim() !== '' ) {
			caps[ el.dataset.wpessentialCptCap ?? '' ] = el.value.trim();
		}
	}
	if ( Object.keys( caps ).length ) {
		out.capabilities = caps;
	}

	const labels: Record< string, string > = {};
	for ( const el of all< HTMLInputElement >(
		'[data-wpessential-cpt-label]'
	) ) {
		if ( el.value.trim() !== '' ) {
			labels[ el.dataset.wpessentialCptLabel ?? '' ] = el.value.trim();
		}
	}
	if ( Object.keys( labels ).length ) {
		out.labels = labels;
	}
	return out;
}

/** Taxonomies: checked boxes plus stored keys that have no box on this site. */
export function collectTaxonomies( existing: unknown ): string[] | undefined {
	const boxes = all< HTMLInputElement >( '[data-wpessential-cpt-taxonomy]' );
	const shown = boxes.map( ( b ) => b.dataset.wpessentialCptTaxonomy ?? '' );
	const kept = Array.isArray( existing )
		? existing.filter(
				( t ): t is string =>
					typeof t === 'string' && ! shown.includes( t )
		  )
		: [];
	const picked = boxes
		.filter( ( b ) => b.checked )
		.map( ( b ) => b.dataset.wpessentialCptTaxonomy ?? '' );
	const list = [ ...kept, ...picked ];
	return list.length ? list : undefined;
}

function setSelect( el: HTMLSelectElement, value: string ): void {
	if ( value !== '' && ! Array.from( el.options ).some( ( o ) => o.value === value ) ) {
		el.add( new Option( value, value ) ); // Keep stored values the list does not offer.
	}
	el.value = value;
}

function setValue( el: HTMLInputElement | HTMLSelectElement, value: string ): void {
	if ( el instanceof HTMLSelectElement ) {
		setSelect( el, value );
	} else {
		el.value = value;
	}
}

/** Fills every advanced control from a stored payload (missing keys = Default). */
export function fillAdvanced( payload: Payload ): void {
	for ( const el of all< HTMLInputElement | HTMLSelectElement >(
		'[data-wpessential-cpt-opt]'
	) ) {
		const key = el.dataset.wpessentialCptOpt ?? '';
		const value = payload[ key ];
		switch ( el.dataset.kind ) {
			case 'tri':
				setValue( el, triString( value ) );
				break;
			case 'int':
			case 'text':
				setValue(
					el,
					typeof value === 'number' || typeof value === 'string'
						? String( value )
						: ''
				);
				break;
			case 'bool-text': {
				const custom = customInput( key );
				if ( typeof value === 'string' ) {
					setValue( el, 'custom' );
					if ( custom ) {
						custom.value = value;
					}
				} else {
					setValue( el, triString( value ) );
					if ( custom ) {
						custom.value = '';
					}
				}
				break;
			}
			case 'pair': {
				const plural = field< HTMLInputElement >(
					'#wpessential-cpt-capability-plural'
				);
				const pair = Array.isArray( value ) ? value : [ value ];
				setValue( el, typeof pair[ 0 ] === 'string' ? pair[ 0 ] : '' );
				if ( plural ) {
					plural.value = typeof pair[ 1 ] === 'string' ? pair[ 1 ] : '';
				}
				break;
			}
			case 'rewrite': {
				const map =
					value && typeof value === 'object' ? ( value as Payload ) : {};
				setValue(
					el,
					value === false ? 'false' : value && typeof value === 'object' ? 'custom' : ''
				);
				const slug = field< HTMLInputElement >(
					'[data-wpessential-cpt-rewrite="slug"]'
				);
				if ( slug ) {
					slug.value = typeof map.slug === 'string' ? map.slug : '';
				}
				for ( const part of [ 'with_front', 'feeds', 'pages' ] ) {
					const sel = field< HTMLSelectElement >(
						`[data-wpessential-cpt-rewrite="${ part }"]`
					);
					if ( sel ) {
						sel.value = triString( map[ part ] );
					}
				}
				break;
			}
		}
	}
	const caps =
		payload.capabilities && typeof payload.capabilities === 'object'
			? ( payload.capabilities as Payload )
			: {};
	for ( const el of all< HTMLInputElement >( '[data-wpessential-cpt-cap]' ) ) {
		const cap = caps[ el.dataset.wpessentialCptCap ?? '' ];
		el.value = typeof cap === 'string' ? cap : '';
	}
	const labels =
		payload.labels && typeof payload.labels === 'object'
			? ( payload.labels as Payload )
			: {};
	for ( const el of all< HTMLInputElement >(
		'[data-wpessential-cpt-label]'
	) ) {
		const label = labels[ el.dataset.wpessentialCptLabel ?? '' ];
		el.value = typeof label === 'string' ? label : '';
	}
	const taxonomies = Array.isArray( payload.taxonomies )
		? payload.taxonomies
		: [];
	for ( const el of all< HTMLInputElement >(
		'[data-wpessential-cpt-taxonomy]'
	) ) {
		el.checked = taxonomies.includes( el.dataset.wpessentialCptTaxonomy );
	}
	const archive = field< HTMLInputElement >( '#wpessential-cpt-archive-slug' );
	if ( archive ) {
		archive.value =
			typeof payload.has_archive === 'string' ? payload.has_archive : '';
	}
}
