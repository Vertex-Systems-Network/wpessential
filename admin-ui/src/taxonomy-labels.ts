type RecordValue = Record< string, unknown >;

type LabelEditorPayload = {
	automatic_labels: boolean;
	labels: Record< string, string >;
};

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
		setState(
			key,
			input.value.trim() !== ''
				? 'Explicit override'
				: automatic
					? 'Generated'
					: 'WordPress default'
		);
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

	return {
		automatic_labels: automaticLabelsInput()?.checked ?? true,
		labels,
	};
}

export function setTaxonomyLabels( payload: RecordValue ): void {
	const automatic = automaticLabelsInput();
	if ( automatic ) {
		automatic.checked =
			typeof payload.automatic_labels === 'boolean'
				? payload.automatic_labels
				: true;
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
