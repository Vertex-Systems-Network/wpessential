// Essential / Advanced / Expert switch for builder screens (PHP: Ui\Levels). Sections marked
// data-wpessential-{prefix}-tier-min stay hidden below their level; values in hidden sections are
// kept and counted, so nobody loses a setting by switching to a simpler view.
export type Level = 'essential' | 'advanced' | 'expert';

const RANK: Record< Level, number > = { essential: 0, advanced: 1, expert: 2 };

function isLevel( value: unknown ): value is Level {
	return value === 'essential' || value === 'advanced' || value === 'expert';
}

function storageKey( prefix: string ): string {
	return `wpessential-${ prefix }-level`;
}

function hasValue( el: Element ): boolean {
	if ( el instanceof HTMLInputElement ) {
		return el.type === 'checkbox' ? el.checked : el.value.trim() !== '';
	}
	return el instanceof HTMLSelectElement && el.value !== '';
}

/** Number of non-default controls inside sections the given level hides. */
function hiddenValues( prefix: string, level: Level ): number {
	let count = 0;
	for ( const section of Array.from(
		document.querySelectorAll< HTMLElement >(
			`[data-wpessential-${ prefix }-tier-min]`
		)
	) ) {
		const min = section.getAttribute( `data-wpessential-${ prefix }-tier-min` );
		if ( isLevel( min ) && RANK[ min ] > RANK[ level ] ) {
			count += Array.from(
				section.querySelectorAll( 'input:not([type="hidden"]), select' )
			).filter( hasValue ).length;
		}
	}
	return count;
}

function statusText( prefix: string, level: Level ): string {
	const base =
		level === 'essential'
			? 'Essential controls are shown: the settings most sites need.'
			: level === 'advanced'
			? 'Essential and Advanced controls are shown.'
			: 'Essential, Advanced and Expert controls are shown.';
	const hidden = hiddenValues( prefix, level );
	return hidden > 0
		? `${ base } ${ hidden } changed setting${
				hidden === 1 ? ' is' : 's are'
		  } kept in a higher level.`
		: base;
}

export function setLevel( prefix: string, level: Level ): void {
	for ( const button of Array.from(
		document.querySelectorAll< HTMLButtonElement >(
			`[data-wpessential-${ prefix }-tier]`
		)
	) ) {
		const active =
			button.getAttribute( `data-wpessential-${ prefix }-tier` ) === level;
		button.setAttribute( 'aria-pressed', active ? 'true' : 'false' );
		button.parentElement?.classList.toggle( 'is-active', active );
	}
	for ( const section of Array.from(
		document.querySelectorAll< HTMLElement >(
			`[data-wpessential-${ prefix }-tier-min]`
		)
	) ) {
		const min = section.getAttribute( `data-wpessential-${ prefix }-tier-min` );
		section.hidden = ! isLevel( min ) || RANK[ min ] > RANK[ level ];
	}
	const status = document.getElementById( `wpessential-${ prefix }-tier-status` );
	if ( status ) {
		status.textContent = statusText( prefix, level );
	}
	try {
		window.localStorage.setItem( storageKey( prefix ), level );
	} catch {
		// Storage can be unavailable (private mode); the level then resets on reload.
	}
}

export function currentLevel( prefix: string ): Level {
	const pressed = document.querySelector(
		`[data-wpessential-${ prefix }-tier][aria-pressed="true"]`
	);
	const value = pressed?.getAttribute( `data-wpessential-${ prefix }-tier` );
	return isLevel( value ) ? value : 'essential';
}

/** Re-counts hidden changed settings (call after filling the form). */
export function refreshLevel( prefix: string ): void {
	setLevel( prefix, currentLevel( prefix ) );
}

export function bindLevels( prefix: string ): void {
	for ( const button of Array.from(
		document.querySelectorAll< HTMLButtonElement >(
			`[data-wpessential-${ prefix }-tier]`
		)
	) ) {
		button.addEventListener( 'click', () => {
			const level = button.getAttribute( `data-wpessential-${ prefix }-tier` );
			if ( isLevel( level ) ) {
				setLevel( prefix, level );
			}
		} );
	}
	let saved: string | null = null;
	try {
		saved = window.localStorage.getItem( storageKey( prefix ) );
	} catch {
		saved = null;
	}
	setLevel( prefix, isLevel( saved ) ? saved : 'essential' );
}

/** Fills a machine key from a human name until the user edits the key themselves. */
export function bindKeyFromName(
	key: HTMLInputElement,
	name: HTMLInputElement,
	maxLength: number
): void {
	key.maxLength = maxLength;
	let touched = key.value !== '';
	key.addEventListener( 'input', () => {
		touched = key.value !== '';
	} );
	key.form?.addEventListener( 'reset', () => {
		touched = false;
	} );
	name.addEventListener( 'input', () => {
		if ( touched || key.readOnly ) {
			return;
		}
		key.value = name.value
			.toLowerCase()
			.normalize( 'NFKD' )
			.replace( /[^\w\s-]/g, '' )
			.trim()
			.replace( /[\s-]+/g, '_' )
			.slice( 0, maxLength );
		key.dispatchEvent( new Event( 'wpe:autofill' ) );
	} );
}
