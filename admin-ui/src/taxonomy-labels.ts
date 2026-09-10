type RecordValue = Record< string, unknown >;

type LabelEditorPayload = {
	automatic_labels?: boolean;
	labels?: Record< string, string >;
};

let loadedAutomaticLabels: boolean | undefined;
let loadedHadLabels = false;

function isRecord( value: unknown ): value is RecordValue {
	return (
		typeof value === 'object' && value !== null && ! Array.isArray( value )
	);
}

function labelInputs(): HTMLInputElement[] {
	return Array.from(
		document.querySelectorAll< HTMLInputElement >(
			'[data-wpessential-taxonomy-label-field]'
		)
	);
}

function automaticLabelsInput(): HTMLInputElement | null {
	const input = document.getElementById(
		'wpessential-taxonomy-automatic-labels'
	);
	return input instanceof HTMLInputElement ? input : null;
}

function hierarchicalInput(): HTMLInputElement | null {
	const input = document.getElementById(
		'wpessential-taxonomy-hierarchical'
	);
	return input instanceof HTMLInputElement ? input : null;
}

function labelKey( input: HTMLInputElement ): string {
	return input.dataset.wpessentialTaxonomyLabelField ?? '';
}

function isGeneratedLabel(
	input: HTMLInputElement,
	automatic: boolean,
	hierarchical: boolean
): boolean {
	if ( ! automatic ) {
		return false;
	}

	const mode = input.dataset.wpessentialTaxonomyLabelGeneration ?? 'wordpress';
	return (
		mode === 'common' ||
		( hierarchical && mode === 'hierarchical' ) ||
		( ! hierarchical && mode === 'flat' )
	);
}

function setState( key: string, state: string ): void {
	const target = document.querySelector(
		`[data-wpessential-taxonomy-label-state="${ key }"]`
	);
	if ( target instanceof HTMLElement ) {
		target.textContent = state;
	}
	const row = document.querySelector(
		`[data-wpessential-taxonomy-label-row="${ key }"]`
	);
	if ( row instanceof HTMLElement ) {
		row.dataset.wpessentialTaxonomyLabelStatus = state
			.toLowerCase()
			.replaceAll( ' ', '-' );
	}
}

export function updateTaxonomyLabelStates(): void {
	const automatic = automaticLabelsInput()?.checked ?? true;
	const hierarchical = hierarchicalInput()?.checked ?? false;
	const generation = document.querySelector(
		'[data-wpessential-taxonomy-label-generation-state]'
	);
	if ( generation instanceof HTMLElement ) {
		generation.textContent = automatic
			? `Generated label family: ${
					hierarchical ? 'category-like' : 'tag-like'
			  }. Validate to preview exact compiled values.`
			: 'Automatic generation is off. Blank label fields use WordPress defaults.';
	}

	for ( const input of labelInputs() ) {
		const key = labelKey( input );
		if ( key === '' ) {
			continue;
		}
		let state = 'WordPress default';
		if ( input.value.trim() !== '' ) {
			state = 'Explicit override';
		} else if ( isGeneratedLabel( input, automatic, hierarchical ) ) {
			state = 'Generated';
		}
		setState( key, state );
	}
}

export function collectTaxonomyLabels(): LabelEditorPayload {
	const labels: Record< string, string > = {};
	for ( const input of labelInputs() ) {
		const key = labelKey( input );
		const value = input.value.trim();
		if ( key !== '' && value !== '' ) {
			labels[ key ] = value;
		}
	}

	const result: LabelEditorPayload = {};
	const automatic = automaticLabelsInput()?.checked ?? true;
	if ( loadedAutomaticLabels !== undefined || automatic === false ) {
		result.automatic_labels = automatic;
	}
	if ( Object.keys( labels ).length > 0 || loadedHadLabels ) {
		result.labels = labels;
	}
	return result;
}

export function setTaxonomyLabels( payload: RecordValue ): void {
	loadedAutomaticLabels =
		typeof payload.automatic_labels === 'boolean'
			? payload.automatic_labels
			: undefined;
	loadedHadLabels = isRecord( payload.labels );

	const automatic = automaticLabelsInput();
	if ( automatic ) {
		automatic.checked = loadedAutomaticLabels ?? true;
	}

	const labels = isRecord( payload.labels ) ? payload.labels : {};
	let hasExplicitOverride = false;
	for ( const input of labelInputs() ) {
		const value = labels[ labelKey( input ) ];
		input.value = typeof value === 'string' ? value : '';
		hasExplicitOverride ||= input.value.trim() !== '';
	}

	const details = document.getElementById( 'wpessential-taxonomy-labels' );
	if ( details instanceof HTMLDetailsElement ) {
		details.open = hasExplicitOverride || automatic?.checked === false;
	}
	updateTaxonomyLabelStates();
}

export function resetTaxonomyLabels(): void {
	loadedAutomaticLabels = undefined;
	loadedHadLabels = false;
	const automatic = automaticLabelsInput();
	if ( automatic ) {
		automatic.checked = true;
	}
	for ( const input of labelInputs() ) {
		input.value = '';
	}
	const details = document.getElementById( 'wpessential-taxonomy-labels' );
	if ( details instanceof HTMLDetailsElement ) {
		details.open = false;
	}
	updateTaxonomyLabelStates();
}

function notifyFormChanged( source: HTMLElement ): void {
	source.dispatchEvent( new Event( 'input', { bubbles: true } ) );
}

export function bindTaxonomyLabelEditor(): void {
	automaticLabelsInput()?.addEventListener( 'change', updateTaxonomyLabelStates );
	hierarchicalInput()?.addEventListener( 'change', updateTaxonomyLabelStates );
	for ( const input of labelInputs() ) {
		input.addEventListener( 'input', updateTaxonomyLabelStates );
	}

	for ( const button of Array.from(
		document.querySelectorAll< HTMLButtonElement >(
			'[data-wpessential-taxonomy-label-reset]'
		)
	) ) {
		button.addEventListener( 'click', () => {
			const key = button.dataset.wpessentialTaxonomyLabelReset;
			if ( ! key ) {
				return;
			}
			const input = document.querySelector< HTMLInputElement >(
				`[data-wpessential-taxonomy-label-field="${ key }"]`
			);
			if ( ! input ) {
				return;
			}
			input.value = '';
			updateTaxonomyLabelStates();
			notifyFormChanged( input );
		} );
	}

	const resetAll = document.getElementById(
		'wpessential-taxonomy-label-reset-all'
	);
	if ( resetAll instanceof HTMLButtonElement ) {
		resetAll.addEventListener( 'click', () => {
			const inputs = labelInputs();
			for ( const input of inputs ) {
				input.value = '';
			}
			updateTaxonomyLabelStates();
			if ( inputs[ 0 ] ) {
				notifyFormChanged( inputs[ 0 ] );
			}
		} );
	}

	updateTaxonomyLabelStates();
}
