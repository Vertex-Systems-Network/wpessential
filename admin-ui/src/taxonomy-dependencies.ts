// Which Taxonomy setting needs which other setting (see core/dependencies.ts). Soft rules: the
// notes explain the link and where the other setting lives, without disabling the field.
import type { Dependency } from './core/dependencies';

const isOn = ( el: HTMLInputElement | HTMLSelectElement ): boolean =>
	el instanceof HTMLInputElement && el.checked;
const is =
	( value: string ) =>
	( el: HTMLInputElement | HTMLSelectElement ): boolean =>
		el.value === value;
const isNot =
	( value: string ) =>
	( el: HTMLInputElement | HTMLSelectElement ): boolean =>
		el.value !== value;
const filled = ( el: HTMLInputElement | HTMLSelectElement ): boolean =>
	el.value.trim() !== '';

export const TAXONOMY_DEPENDENCIES: Dependency[] = [
	{ field: 'wpessential-taxonomy-rest-base', on: 'wpessential-taxonomy-rest', when: isOn, need: 'is on', soft: true },
	{ field: 'wpessential-taxonomy-rest-namespace', on: 'wpessential-taxonomy-rest', when: isOn, need: 'is on', soft: true },
	{ field: 'wpessential-taxonomy-rewrite-slug', on: 'wpessential-taxonomy-rewrite-mode', when: is( 'custom' ), need: 'is set to Custom', soft: true },
	{ field: 'wpessential-taxonomy-rewrite-with-front', on: 'wpessential-taxonomy-rewrite-mode', when: is( 'custom' ), need: 'is set to Custom', soft: true },
	{ field: 'wpessential-taxonomy-rewrite-hierarchical', on: 'wpessential-taxonomy-rewrite-mode', when: is( 'custom' ), need: 'is set to Custom', soft: true },
	{ field: 'wpessential-taxonomy-query-var-name', on: 'wpessential-taxonomy-query-var-mode', when: is( 'custom' ), need: 'is set to Custom', soft: true },
	{ field: 'wpessential-taxonomy-default-term-slug', on: 'wpessential-taxonomy-default-term-name', when: filled, need: 'is filled in', soft: true },
	{ field: 'wpessential-taxonomy-default-term-description', on: 'wpessential-taxonomy-default-term-name', when: filled, need: 'is filled in', soft: true },
	{ field: 'wpessential-taxonomy-show-in-menu', on: 'wpessential-taxonomy-show-ui', when: isNot( 'false' ), need: 'is not No', soft: true },
];
