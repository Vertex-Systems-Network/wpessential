export type TaxonomyEditorSafety = {
	acceptBaseline: () => void;
	syncDirtyState: () => void;
};

export function bindTaxonomyEditorSafety(
	root: HTMLElement,
	form: HTMLFormElement,
	snapshot: () => string
): TaxonomyEditorSafety {
	let baseline = '';

	const setDirty = ( dirty: boolean ): void => {
		root.dataset.wpessentialTaxonomyDirty = dirty ? 'true' : 'false';
	};

	const syncDirtyState = (): void => {
		setDirty( baseline !== '' && snapshot() !== baseline );
	};

	const acceptBaseline = (): void => {
		baseline = snapshot();
		setDirty( false );
	};

	form.addEventListener( 'input', syncDirtyState );
	form.addEventListener( 'change', syncDirtyState );

	window.addEventListener( 'beforeunload', ( event ) => {
		if ( root.dataset.wpessentialTaxonomyDirty !== 'true' ) {
			return;
		}
		event.preventDefault();
		event.returnValue = '';
	} );

	const save = document.getElementById( 'wpessential-taxonomy-save' );
	const commandBar = save?.closest( '.submit' );
	if ( commandBar instanceof HTMLElement ) {
		commandBar.classList.add( 'wpessential-taxonomy-sticky-actions' );
		commandBar.dataset.wpessentialTaxonomyStickyActions = '';
	}

	return { acceptBaseline, syncDirtyState };
}
