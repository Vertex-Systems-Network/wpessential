// Wizard mode for builder editors: the same form, one section per step, each with a plain
// explanation and an illustration, Back / Next and a step list. Sections hidden by the settings
// level (Essential / Advanced / Expert) are skipped. Turning the wizard off shows the full form
// again; nothing is lost either way because it is one form.

export interface WizardOptions {
	/** Settings prefix ("cpt", "taxonomy"): remembers the mode per editor. */
	prefix: string;
	/** Plain-language explanations by step title; the section's own hint is used otherwise. */
	explanations?: Record< string, string >;
}

interface Step {
	title: string;
	nodes: HTMLElement[];
	explain: string;
}

const START = 'fieldset.wpe-ui-checks, section.wpe-ui-builder__group';

/** Steps in document order: fieldsets, level groups, and a top-level heading with what follows it. */
function collectSteps( form: HTMLFormElement, explanations: Record< string, string > ): Step[] {
	const steps: Step[] = [];
	const starts = Array.from( form.querySelectorAll< HTMLElement >( `${ START }, :scope > h3.wpe-ui-builder__section` ) );
	for ( const start of starts ) {
		const nodes: HTMLElement[] = [ start ];
		if ( start.matches( 'h3' ) ) {
			let next = start.nextElementSibling;
			while ( next instanceof HTMLElement && ! next.matches( `${ START }, h3.wpe-ui-builder__section` ) && next.querySelector( START ) === null ) {
				nodes.push( next );
				next = next.nextElementSibling;
			}
		}
		const heading = start.querySelector( 'legend, h3' ) ?? start;
		const title = ( start.matches( 'h3' ) ? start.textContent : heading.textContent )?.trim() ?? '';
		const hintNode = nodes
			.map( ( node ) => ( node.matches( 'p.wpe-ui-muted' ) ? node : node.querySelector( ':scope > p.wpe-ui-muted, :scope > .wpe-ui-checks__hint' ) ) )
			.find( ( found ) => found !== null );
		const hint = hintNode?.textContent?.trim();
		// The wizard shows the title and explanation itself; the section's own copies step aside.
		hintNode?.setAttribute( 'data-wpe-wizard-copy', '' );
		( start.matches( 'h3' ) ? start : start.querySelector( ':scope > legend, :scope > h3' ) )?.setAttribute( 'data-wpe-wizard-copy', '' );
		steps.push( { title, nodes, explain: explanations[ title ] ?? hint ?? '' } );
	}
	return steps;
}

/** Placeholder illustration in the toolkit style: an editor window with the step's area lit up. */
function illustration( index: number, total: number ): string {
	const rows = Math.max( 3, Math.min( 6, total ) );
	const active = index % rows;
	const lines = Array.from( { length: rows }, ( _, row ) => {
		const y = 52 + row * 22;
		const on = row === active;
		return `<rect x="34" y="${ y }" width="${ on ? 200 : 150 + ( ( row * 37 ) % 60 ) }" height="12" rx="6" fill="${ on ? 'var(--wpe-orange)' : 'var(--wpe-line)' }"${ on ? '' : ' opacity=".9"' }/>`;
	} ).join( '' );
	return `<svg viewBox="0 0 280 ${ 76 + rows * 22 }" role="img" aria-label="Step ${ index + 1 } illustration" xmlns="http://www.w3.org/2000/svg">
		<rect x="8" y="8" width="264" height="${ 60 + rows * 22 }" rx="14" fill="var(--wpe-card)" stroke="var(--wpe-line)"/>
		<rect x="8" y="8" width="264" height="28" rx="14" fill="var(--wpe-surface)"/>
		<circle cx="26" cy="22" r="4" fill="var(--wpe-danger)"/><circle cx="40" cy="22" r="4" fill="var(--wpe-orange)"/><circle cx="54" cy="22" r="4" fill="var(--wpe-success, #22c55e)"/>
		${ lines }
		<circle cx="246" cy="${ 58 + active * 22 }" r="11" fill="var(--wpe-indigo)"/>
		<text x="246" y="${ 62 + active * 22 }" text-anchor="middle" font-size="11" font-weight="700" fill="var(--wpe-on-ink, #fff)">${ index + 1 }</text>
	</svg>`;
}

function button( label: string, variant: string ): HTMLButtonElement {
	const element = document.createElement( 'button' );
	element.type = 'button';
	element.className = `wpe-ui-button wpe-ui-button--${ variant } wpe-ui-button--sm`;
	element.textContent = label;
	return element;
}

export function bindWizard( form: HTMLFormElement, options: WizardOptions ): void {
	const storageKey = `wpessential.${ options.prefix }.wizard`;
	const all = collectSteps( form, options.explanations ?? {} );
	if ( all.length < 2 ) {
		return;
	}

	// Mode switch beside the settings levels.
	const toggleWrap = document.createElement( 'label' );
	toggleWrap.className = 'wpe-ui-check wpe-ui-wizard-switch';
	const toggle = document.createElement( 'input' );
	toggle.type = 'checkbox';
	toggle.setAttribute( 'role', 'switch' );
	toggleWrap.append( toggle, document.createTextNode( ' Wizard mode' ) );
	const levels = form.querySelector( '.wpe-ui-levels' );
	( levels ?? form.firstElementChild )?.before( toggleWrap );

	// Step header: step list, explanation with illustration, Back / Next.
	const panel = document.createElement( 'section' );
	panel.className = 'wpe-ui-wizard';
	panel.hidden = true;
	panel.setAttribute( 'aria-label', 'Wizard' );
	const list = document.createElement( 'ol' );
	list.className = 'wpe-ui-wizard__steps';
	const body = document.createElement( 'div' );
	body.className = 'wpe-ui-wizard__body';
	const copy = document.createElement( 'div' );
	copy.className = 'wpe-ui-wizard__copy';
	const counter = document.createElement( 'p' );
	counter.className = 'wpe-ui-eyebrow';
	const title = document.createElement( 'h3' );
	title.className = 'wpe-ui-wizard__title';
	title.tabIndex = -1;
	const explain = document.createElement( 'p' );
	explain.className = 'wpe-ui-wizard__explain';
	const art = document.createElement( 'div' );
	art.className = 'wpe-ui-wizard__art';
	copy.append( counter, title, explain );
	body.append( copy, art );
	const nav = document.createElement( 'div' );
	nav.className = 'wpe-ui-wizard__nav';
	const back = button( '← Back', 'ghost' );
	const next = button( 'Next →', 'primary' );
	nav.append( back, next );
	panel.append( list, body );
	// After the level picker and its hint: the level decides how many steps there are.
	const levelHint = levels?.nextElementSibling;
	( levelHint?.matches( 'p' ) ? levelHint : ( levels ?? toggleWrap ) ).after( panel );
	const footer = nav;
	footer.hidden = true;

	let index = 0;
	const visibleSteps = (): Step[] => all.filter( ( step ) => step.nodes.every( ( node ) => ! node.hidden ) );

	const render = ( focus = false ): void => {
		const on = toggle.checked;
		form.classList.toggle( 'is-wizard', on );
		panel.hidden = ! on;
		footer.hidden = ! on;
		const steps = visibleSteps();
		index = Math.min( index, steps.length - 1 );
		for ( const step of all ) {
			const current = on && step === steps[ index ];
			for ( const node of step.nodes ) {
				if ( on && ! current ) {
					node.dataset.wpeWizardOff = '';
				} else {
					delete node.dataset.wpeWizardOff;
				}
			}
		}
		if ( ! on ) {
			return;
		}
		const step = steps[ index ];
		if ( ! step ) {
			return;
		}
		// The navigation sits right after the current step's last section.
		step.nodes[ step.nodes.length - 1 ]?.after( footer );
		counter.textContent = `Step ${ index + 1 } of ${ steps.length }`;
		title.textContent = step.title;
		explain.textContent = step.explain;
		art.innerHTML = illustration( index, steps.length );
		list.replaceChildren(
			...steps.map( ( item, position ) => {
				const li = document.createElement( 'li' );
				const jump = document.createElement( 'button' );
				jump.type = 'button';
				jump.className = 'wpe-ui-wizard__step';
				jump.dataset.state = position < index ? 'done' : position === index ? 'current' : 'todo';
				if ( position === index ) {
					jump.setAttribute( 'aria-current', 'step' );
				}
				jump.innerHTML = `<span class="wpe-ui-wizard__dot">${ position + 1 }</span>`;
				jump.append( item.title );
				jump.addEventListener( 'click', () => {
					index = position;
					render( true );
				} );
				li.append( jump );
				return li;
			} )
		);
		back.disabled = index === 0;
		next.hidden = index >= steps.length - 1;
		if ( focus ) {
			title.focus( { preventScroll: true } );
			panel.scrollIntoView( { block: 'start', behavior: 'smooth' } );
		}
	};

	back.addEventListener( 'click', () => {
		index = Math.max( 0, index - 1 );
		render( true );
	} );
	next.addEventListener( 'click', () => {
		index += 1;
		render( true );
	} );
	toggle.addEventListener( 'change', () => {
		try {
			window.localStorage.setItem( storageKey, toggle.checked ? '1' : '0' );
		} catch {
			// Storage blocked: the mode lasts for this visit only.
		}
		index = 0;
		render();
	} );
	// Level changes add or remove steps.
	form.addEventListener( 'click', ( event ) => {
		if ( event.target instanceof Element && event.target.closest( '.wpe-ui-levels' ) ) {
			window.setTimeout( () => render() );
		}
	} );
	form.addEventListener( 'reset', () => {
		index = 0;
		window.setTimeout( () => render() );
	} );

	try {
		toggle.checked = window.localStorage.getItem( storageKey ) === '1';
	} catch {
		toggle.checked = false;
	}
	render();
}

export const CPT_WIZARD_EXPLANATIONS: Record< string, string > = {
	Basics: 'Name your content type. The plural name is shown in the admin menu (Books), the singular in buttons (Add New Book). The key is the permanent ID WordPress stores with every item; it is filled in for you.',
	Behavior: 'Decide who sees it. Public shows items on your site; Show in REST API is needed for the block editor; Hierarchical lets items have parents, like Pages; Has archive adds a listing page such as /books/.',
	'Editor supports': 'Pick the boxes the edit screen shows: title, content editor, featured image, excerpt, comments and more. You can change these later without losing content.',
	Protection: 'Lock the entry so nobody deletes it by mistake. A locked item must be unlocked before it can go to the Trash.',
	'Where it appears': 'Choose the menu icon and position, and whether it shows in navigation menus, the admin bar and site search.',
	'Web address': 'Set the web address: the archive slug, pretty permalinks, hiding the base (/my-book/) or a custom pattern such as books/%genre%/%postname%.',
	Taxonomies: 'Connect categories, tags or your own taxonomies so items can be grouped and filtered.',
	Labels: 'Every word WordPress shows for this type. They are written from the names; change any of them, and Reset brings the generated text back.',
	'Permissions, export & REST': 'Who may edit, whether items are included in Tools → Export, what happens to them when their author is deleted, and the REST API address.',
	'Developer settings': 'REST namespace and permalink details for developers. Leave them on default unless you need them.',
	'Capability map': 'Rename single capabilities for custom roles. Leave them empty to use the ones made from the capability type.',
};
