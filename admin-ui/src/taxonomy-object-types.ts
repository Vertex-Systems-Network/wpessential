type RecordValue = Record< string, unknown >;

type ObjectTypeEntry = {
	key: string;
	label: string;
	source: string;
	status: string;
	runtime_registered: boolean;
	state: string;
};

type ObjectTypeGroup = {
	key: string;
	label: string;
	sources: string[];
};

const groups: ObjectTypeGroup[] = [
	{
		key: 'wordpress',
		label: 'Core WordPress post types',
		sources: [ 'wordpress' ],
	},
	{
		key: 'wpessential',
		label: 'WPE CPT definitions',
		sources: [ 'wpessential' ],
	},
	{
		key: 'runtime',
		label: 'External runtime post types',
		sources: [ 'runtime' ],
	},
];

function isRecord( value: unknown ): value is RecordValue {
	return (
		typeof value === 'object' && value !== null && ! Array.isArray( value )
	);
}

function isObjectTypeEntry( value: unknown ): value is ObjectTypeEntry {
	return (
		isRecord( value ) &&
		typeof value.key === 'string' &&
		typeof value.label === 'string' &&
		typeof value.source === 'string' &&
		typeof value.status === 'string' &&
		typeof value.runtime_registered === 'boolean' &&
		typeof value.state === 'string'
	);
}

function catalogEntries(): ObjectTypeEntry[] {
	const script = document.getElementById( 'wpessential-taxonomy-bootstrap' );
	if ( ! ( script instanceof HTMLScriptElement ) ) {
		return [];
	}

	try {
		const bootstrap: unknown = JSON.parse( script.textContent ?? '{}' );
		if ( ! isRecord( bootstrap ) || ! Array.isArray( bootstrap.objectTypes ) ) {
			return [];
		}
		return bootstrap.objectTypes.filter( isObjectTypeEntry );
	} catch {
		return [];
	}
}

function splitKeys( value: string ): string[] {
	return Array.from(
		new Set(
			value
				.split( ',' )
				.map( ( key ) => key.trim() )
				.filter( ( key ) => key !== '' )
		)
	);
}

function createGroup( key: string, label: string ): HTMLElement {
	const section = document.createElement( 'section' );
	section.className = 'wpessential-taxonomy-object-type-group';
	section.dataset.wpessentialTaxonomyObjectTypeGroup = key;

	const heading = document.createElement( 'h4' );
	heading.textContent = label;
	const options = document.createElement( 'div' );
	options.className = 'wpessential-taxonomy-object-type-group-options';
	options.dataset.wpessentialTaxonomyObjectTypeGroupOptions = key;

	section.append( heading, options );
	return section;
}

function createBadge( state: string ): HTMLElement {
	const badge = document.createElement( 'span' );
	badge.className = 'wpessential-taxonomy-object-type-state';
	badge.dataset.wpessentialTaxonomyObjectTypeState = state;
	badge.textContent = state;
	return badge;
}

function createKnownRow(
	entry: ObjectTypeEntry,
	input: HTMLInputElement,
	label: HTMLLabelElement
): HTMLElement {
	label.querySelector( '.description' )?.remove();
	label.classList.add( 'wpessential-taxonomy-object-type-option' );
	label.append( document.createTextNode( ' ' ), createBadge( entry.state ) );

	const row = document.createElement( 'div' );
	row.className = 'wpessential-taxonomy-object-type-discovery-option';
	row.dataset.wpessentialTaxonomyObjectTypeDiscoveryOption = '';
	row.dataset.wpessentialTaxonomyObjectTypeKey = entry.key;
	row.dataset.wpessentialTaxonomyObjectTypeSearch = [
		entry.label,
		entry.key,
		entry.source,
		entry.status,
		entry.state,
	]
		.join( ' ' )
		.toLowerCase();
	row.append( label );
	input.dataset.wpessentialTaxonomyObjectTypeSource = entry.source;
	return row;
}

function preservedRow(
	key: string,
	extra: HTMLInputElement,
	onChange: () => void
): HTMLElement {
	const row = document.createElement( 'div' );
	row.className = 'wpessential-taxonomy-object-type-discovery-option';
	row.dataset.wpessentialTaxonomyObjectTypeDiscoveryOption = '';
	row.dataset.wpessentialTaxonomyObjectTypeKey = key;
	row.dataset.wpessentialTaxonomyObjectTypeSearch = `${ key } preserved external missing`;

	const label = document.createElement( 'label' );
	label.className = 'wpessential-taxonomy-object-type-option';
	const checkbox = document.createElement( 'input' );
	checkbox.type = 'checkbox';
	checkbox.checked = true;
	checkbox.value = key;
	checkbox.dataset.wpessentialTaxonomyPreservedObjectType = '';
	checkbox.id = `wpessential-taxonomy-preserved-object-type-${ key.replace(
		/[^a-zA-Z0-9_-]/g,
		'-'
	) }`;
	label.htmlFor = checkbox.id;
	label.append(
		checkbox,
		document.createTextNode( ` ${ key } ` ),
		createBadge( 'preserved' )
	);

	checkbox.addEventListener( 'change', () => {
		if ( checkbox.checked ) {
			return;
		}
		const next = splitKeys( extra.value ).filter(
			( candidate ) => candidate !== key
		);
		extra.value = next.join( ', ' );
		extra.dispatchEvent( new Event( 'input', { bubbles: true } ) );
		onChange();
	} );

	row.append( label );
	return row;
}

function bindTaxonomyObjectTypeDiscovery(): void {
	const fieldset = document.getElementById( 'wpessential-taxonomy-object-types' );
	const options = fieldset?.querySelector(
		'[data-wpessential-taxonomy-object-type-options]'
	);
	const extra = document.getElementById(
		'wpessential-taxonomy-object-types-extra'
	);
	const root = document.getElementById( 'wpessential-taxonomy-root' );
	if (
		! ( fieldset instanceof HTMLFieldSetElement ) ||
		! ( options instanceof HTMLElement ) ||
		! ( extra instanceof HTMLInputElement ) ||
		! ( root instanceof HTMLElement ) ||
		fieldset.dataset.wpessentialTaxonomyObjectTypeDiscovery === 'ready'
	) {
		return;
	}

	const entries = catalogEntries();
	const knownKeys = new Set( entries.map( ( entry ) => entry.key ) );
	const inputs = new Map< string, HTMLInputElement >();
	for ( const input of Array.from(
		options.querySelectorAll< HTMLInputElement >(
			'[data-wpessential-taxonomy-object-type]'
		)
	) ) {
		inputs.set( input.value, input );
	}

	const searchWrap = document.createElement( 'p' );
	searchWrap.className = 'wpessential-taxonomy-object-type-search';
	const searchLabel = document.createElement( 'label' );
	searchLabel.htmlFor = 'wpessential-taxonomy-object-type-search';
	const searchStrong = document.createElement( 'strong' );
	searchStrong.textContent = 'Search linked post types';
	const search = document.createElement( 'input' );
	search.type = 'search';
	search.className = 'regular-text';
	search.id = 'wpessential-taxonomy-object-type-search';
	search.setAttribute(
		'aria-controls',
		'wpessential-taxonomy-object-type-options'
	);
	search.setAttribute(
		'aria-describedby',
		'wpessential-taxonomy-object-type-search-status'
	);
	search.placeholder = 'Search by name, key, origin, or state';
	searchLabel.append( searchStrong, document.createElement( 'br' ), search );
	const searchStatus = document.createElement( 'span' );
	searchStatus.id = 'wpessential-taxonomy-object-type-search-status';
	searchStatus.className = 'description';
	searchStatus.setAttribute( 'aria-live', 'polite' );
	searchWrap.append( searchLabel, searchStatus );
	options.id = 'wpessential-taxonomy-object-type-options';
	options.before( searchWrap );

	const knownGroups = new Map< string, HTMLElement >();
	const originalLabels = new Map< string, HTMLLabelElement >();
	for ( const [ key, input ] of inputs ) {
		const label = input.closest( 'label' );
		if ( label instanceof HTMLLabelElement ) {
			originalLabels.set( key, label );
		}
	}

	options.replaceChildren();
	for ( const group of groups ) {
		const matching = entries.filter( ( entry ) =>
			group.sources.includes( entry.source )
		);
		if ( matching.length === 0 ) {
			continue;
		}
		const section = createGroup( group.key, group.label );
		const groupOptions = section.querySelector(
			'[data-wpessential-taxonomy-object-type-group-options]'
		);
		if ( ! ( groupOptions instanceof HTMLElement ) ) {
			continue;
		}
		for ( const entry of matching ) {
			const input = inputs.get( entry.key );
			const label = originalLabels.get( entry.key );
			if ( input && label ) {
				groupOptions.append( createKnownRow( entry, input, label ) );
			}
		}
		if ( groupOptions.children.length > 0 ) {
			knownGroups.set( group.key, section );
			options.append( section );
		}
	}

	const preserved = createGroup(
		'preserved',
		'Preserved missing / external keys'
	);
	const preservedOptions = preserved.querySelector(
		'[data-wpessential-taxonomy-object-type-group-options]'
	);
	if ( ! ( preservedOptions instanceof HTMLElement ) ) {
		return;
	}
	preserved.hidden = true;
	options.append( preserved );

	const noResults = document.createElement( 'p' );
	noResults.className = 'description';
	noResults.dataset.wpessentialTaxonomyObjectTypeNoResults = '';
	noResults.textContent = 'No linked post types match this search.';
	noResults.hidden = true;
	options.after( noResults );

	const applyFilter = (): void => {
		const query = search.value.trim().toLowerCase();
		const rows = Array.from(
			options.querySelectorAll< HTMLElement >(
				'[data-wpessential-taxonomy-object-type-discovery-option]'
			)
		);
		let visible = 0;
		for ( const row of rows ) {
			const haystack =
				row.dataset.wpessentialTaxonomyObjectTypeSearch ??
				row.textContent?.toLowerCase() ??
				'';
			row.hidden = query !== '' && ! haystack.includes( query );
			if ( ! row.hidden ) {
				visible += 1;
			}
		}

		for ( const group of [ ...knownGroups.values(), preserved ] ) {
			const hasVisible = Array.from(
				group.querySelectorAll< HTMLElement >(
					'[data-wpessential-taxonomy-object-type-discovery-option]'
				)
			).some( ( row ) => ! row.hidden );
			group.hidden = ! hasVisible;
		}

		searchStatus.textContent =
			query === ''
				? `${ rows.length } discovered or preserved post types available.`
				: `Showing ${ visible } of ${ rows.length } linked post types.`;
		noResults.hidden = query === '' || visible > 0;
	};

	const syncPreserved = (): void => {
		preservedOptions.replaceChildren();
		const preservedKeys = splitKeys( extra.value ).filter(
			( key ) => ! knownKeys.has( key )
		);
		for ( const key of preservedKeys ) {
			preservedOptions.append( preservedRow( key, extra, syncPreserved ) );
		}
		applyFilter();
	};

	search.addEventListener( 'input', syncPreserved );
	extra.addEventListener( 'input', syncPreserved );
	root.addEventListener( 'click', ( event ) => {
		const target = event.target;
		if ( ! ( target instanceof HTMLButtonElement ) ) {
			return;
		}
		if (
			target.dataset.wpessentialTaxonomyEdit ||
			target.id === 'wpessential-taxonomy-cancel'
		) {
			queueMicrotask( syncPreserved );
		}
	} );
	new MutationObserver( () => {
		if ( ! root.hasAttribute( 'aria-busy' ) ) {
			syncPreserved();
		}
	} ).observe( root, { attributes: true, attributeFilter: [ 'aria-busy' ] } );

	fieldset.dataset.wpessentialTaxonomyObjectTypeDiscovery = 'ready';
	syncPreserved();
}

window.addEventListener( 'wpessential:admin-ready', () => {
	bindTaxonomyObjectTypeDiscovery();
} );
