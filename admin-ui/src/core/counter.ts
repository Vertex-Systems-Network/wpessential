// Character counters for builder text fields: "12 / 20" where WordPress sets a limit (the post
// type key is 20 characters, the taxonomy key 32), "12" elsewhere. Turns red at the limit.
const FIELDS = 'input[type="text"]:not([hidden]), input:not([type]), textarea';

export function bindCounters( form: HTMLFormElement ): void {
	const counters: Array< () => void > = [];
	for ( const input of Array.from( form.querySelectorAll< HTMLInputElement | HTMLTextAreaElement >( FIELDS ) ) ) {
		if ( input.closest( '.wpe-ui-field' ) === null || input.dataset.wpeCounter === 'off' ) {
			continue;
		}
		const counter = document.createElement( 'span' );
		counter.className = 'wpe-ui-counter';
		counter.id = `${ input.id || `wpe-field-${ counters.length }` }-count`;
		const label = input.id ? form.querySelector( `label[for="${ CSS.escape( input.id ) }"]` ) : null;
		( label ?? input ).after( counter );
		if ( label ) {
			label.parentElement?.classList.add( 'wpe-ui-field--counted' );
		}
		input.setAttribute( 'aria-describedby', `${ input.getAttribute( 'aria-describedby' ) ?? '' } ${ counter.id }`.trim() );
		const update = (): void => {
			const max = input.maxLength > 0 ? input.maxLength : 0;
			const length = input.value.length;
			counter.textContent = max ? `${ length } / ${ max }` : String( length );
			counter.title = max ? `${ length } of ${ max } characters (WordPress limit)` : `${ length } characters`;
			counter.classList.toggle( 'is-limit', max > 0 && length >= max );
		};
		input.addEventListener( 'input', update );
		input.addEventListener( 'wpe:autofill', update );
		counters.push( update );
		update();
	}
	// Values written by script (reset, loading a definition, generated labels).
	const all = (): void => {
		for ( const update of counters ) {
			update();
		}
	};
	form.addEventListener( 'change', all );
	form.addEventListener( 'reset', () => window.setTimeout( all ) );
}
