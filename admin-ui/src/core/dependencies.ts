// Field dependencies for builder screens. A rule says a field only applies when another setting
// has a certain value. Unmet: the field is disabled and a note names the setting to change and
// where it lives (level › section). Met but the setting is out of view: the note still says what
// the field depends on. A controlling setting whose dependents are out of view lists them.
export interface Dependency {
	/** Element id of the dependent control. */
	field: string;
	/** Element id of the controlling control. */
	on: string;
	/** Whether the controlling value allows the field. */
	when: ( control: HTMLInputElement | HTMLSelectElement ) => boolean;
	/** What the controlling setting must be, in plain words ("is on", "is Custom"). */
	need: string;
	/** Soft rules only explain and dim the field; they never disable it. */
	soft?: boolean;
}

const LEVEL_NAMES: Record< string, string > = {
	advanced: 'Advanced',
	expert: 'Expert',
};

function control( id: string ): HTMLInputElement | HTMLSelectElement | null {
	const el = document.getElementById( id );
	return el instanceof HTMLInputElement || el instanceof HTMLSelectElement ? el : null;
}

function labelOf( el: HTMLElement ): string {
	const label = el.id ? document.querySelector( `label[for="${ el.id }"]` ) : null;
	const text = ( label ?? el.closest( 'label' ) )?.textContent ?? el.id;
	return text.replace( /\s+/g, ' ' ).trim();
}

function visible( el: HTMLElement ): boolean {
	return el.offsetParent !== null || el.getClientRects().length > 0;
}

/** "Advanced › Web address" from the level section and the nearest heading or legend. */
function whereOf( el: HTMLElement, prefix: string ): string {
	const tier = el.closest( `[data-wpessential-${ prefix }-tier-min]` )?.getAttribute( `data-wpessential-${ prefix }-tier-min` ) ?? '';
	const group = el.closest( 'fieldset, section, .wpe-ui-builder__group' );
	const heading = group?.querySelector( ':scope > legend, :scope > h3' )?.textContent?.trim() ?? '';
	return [ LEVEL_NAMES[ tier ] ?? 'Essential', heading ].filter( Boolean ).join( ' › ' );
}

function holder( el: HTMLElement ): HTMLElement {
	// Notes never go inside a <label>, so they do not change the control's accessible name.
	return el.closest< HTMLElement >( '.wpe-ui-field' ) ?? el.closest< HTMLElement >( '.wpe-ui-check' ) ?? el;
}

function setNote( owner: HTMLElement, key: string, text: string, blocked: boolean ): void {
	const box = holder( owner );
	const inField = box.classList.contains( 'wpe-ui-field' );
	const parent = inField ? box : box.parentElement;
	if ( ! parent ) {
		return;
	}
	let note = parent.querySelector< HTMLElement >( `:scope > .wpe-ui-dep-note[data-for="${ key }"]` );
	if ( text === '' ) {
		note?.remove();
		return;
	}
	if ( ! note ) {
		note = document.createElement( 'span' );
		note.className = 'wpe-ui-dep-note';
		note.dataset.for = key;
		note.id = `${ key }-dep-note`;
		if ( inField ) {
			box.append( note );
		} else {
			box.after( note );
		}
		owner.setAttribute( 'aria-describedby', [ owner.getAttribute( 'aria-describedby' ), note.id ].filter( Boolean ).join( ' ' ) );
	}
	note.textContent = text;
	note.classList.toggle( 'is-blocked', blocked );
}

export function applyDependencies( rules: Dependency[], prefix: string ): void {
	const affects = new Map< string, string[] >();
	for ( const rule of rules ) {
		const field = control( rule.field );
		const on = control( rule.on );
		if ( ! field || ! on ) {
			continue;
		}
		const met = rule.when( on );
		const onName = `“${ labelOf( on ) }”`;
		const where = visible( on ) ? '' : ` (${ whereOf( on, prefix ) })`;
		if ( ! rule.soft ) {
			field.disabled = ! met;
		}
		holder( field ).classList.toggle( 'is-dep-blocked', ! met );
		setNote(
			field,
			`${ rule.field }-dep`,
			met ? ( where ? `Uses ${ onName }${ where }.` : '' ) : `Applies when ${ onName } ${ rule.need }${ where }.`,
			! met
		);
		if ( ! visible( field ) ) {
			const list = affects.get( rule.on ) ?? [];
			list.push( `${ labelOf( field ) } (${ whereOf( field, prefix ) })` );
			affects.set( rule.on, list );
		}
	}
	for ( const id of new Set( rules.map( ( rule ) => rule.on ) ) ) {
		const on = control( id );
		if ( on ) {
			const list = affects.get( id ) ?? [];
			setNote( on, `${ id }-affects`, list.length ? `Also affects: ${ list.join( ', ' ) }.` : '', false );
		}
	}
}

/** Re-applies the rules whenever the form changes or the level switch is used. */
export function bindDependencies( form: HTMLElement, rules: Dependency[], prefix: string ): () => void {
	const run = (): void => applyDependencies( rules, prefix );
	form.addEventListener( 'change', run );
	form.addEventListener( 'input', run );
	form.addEventListener( 'reset', () => window.setTimeout( run ) );
	document.addEventListener( 'click', ( event ) => {
		if ( event.target instanceof Element && event.target.closest( `[data-wpessential-${ prefix }-tier]` ) ) {
			run();
		}
	} );
	run();
	return run;
}
