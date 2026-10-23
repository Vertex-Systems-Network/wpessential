import React, { useMemo, useState } from 'react';
import { useSearchParams } from 'react-router';
import type { Html, ScreenData } from '../data';
import { PageHeader, Raw } from '../ui';

interface Module {
	id: string;
	name: string;
	description: string;
	edition: 'free' | 'pro';
	category: string;
	active: boolean;
	options: string;
	settingsUrl: string;
	iconHtml: Html;
	badgeHtml: Html;
	toggleHtml: Html;
}

interface ModulesData extends ScreenData {
	editions: Array< [ string, string ] >;
	countsHtml: Html;
	upsellHtml: Html;
	categories: Array< { key: string; label: string } >;
	modules: Module[];
}

export function Modules( { data }: { data: ScreenData } ) {
	const d = data as ModulesData;
	const t = d.text;
	const [ params, setParams ] = useSearchParams();
	const category = params.get( 'category' ) ?? '';
	const [ query, setQuery ] = useState( '' );
	const [ edition, setEdition ] = useState( 'all' );

	const visible = useMemo( () => {
		const needle = query.trim().toLowerCase();
		return d.modules.filter( ( module ) => {
			if ( category !== '' && module.category !== category ) {
				return false;
			}
			if ( edition === 'free' || edition === 'pro' ) {
				if ( module.edition !== edition ) {
					return false;
				}
			} else if ( edition === 'active' && ! module.active ) {
				return false;
			} else if ( edition === 'inactive' && module.active ) {
				return false;
			}
			return needle === '' || `${ module.name } ${ module.description }`.toLowerCase().includes( needle );
		} );
	}, [ d.modules, category, edition, query ] );

	const setCategory = ( value: string ): void => {
		const next = new URLSearchParams( params );
		if ( value === '' ) {
			next.delete( 'category' );
		} else {
			next.set( 'category', value );
		}
		setParams( next, { replace: true } );
	};

	return (
		<>
			<PageHeader eyebrow={ t.eyebrow ?? '' } title={ t.heading ?? '' } lead={ t.lead ?? '' } actions={ <Raw html={ d.countsHtml } /> } />
			<div className="wpe-ui-toolbar">
				<label className="screen-reader-text" htmlFor="wpe-module-search">
					{ t.search }
				</label>
				<input
					type="search"
					id="wpe-module-search"
					className="wpe-ui-input"
					placeholder={ t.searchHint }
					value={ query }
					onChange={ ( event ) => setQuery( event.target.value ) }
				/>
				<div className="wpe-ui-tabs" role="group" aria-label={ t.edition }>
					{ d.editions.map( ( [ value, label ] ) => (
						<button type="button" className="wpe-ui-tab" aria-pressed={ edition === value } key={ value } onClick={ () => setEdition( value ) }>
							{ label }
						</button>
					) ) }
				</div>
				<select className="wpe-ui-select" aria-label={ t.category } value={ category } onChange={ ( event ) => setCategory( event.target.value ) }>
					<option value="">{ t.allCategories }</option>
					{ d.categories.map( ( item ) => (
						<option value={ item.key } key={ item.key }>
							{ item.label }
						</option>
					) ) }
				</select>
			</div>
			{ d.upsellHtml ? <Raw as="div" html={ d.upsellHtml } /> : null }
			{ d.categories.map( ( group ) => {
				const cards = visible.filter( ( module ) => module.category === group.key );
				return cards.length === 0 ? null : (
					<section key={ group.key }>
						<h2 className="wpe-ui-category">{ group.label }</h2>
						<div className="wpe-ui-module-grid">
							{ cards.map( ( module ) => (
								<article
									className={ `wpe-ui-module${ module.active ? '' : ' is-off' }` }
									data-wpe-module=""
									data-edition={ module.edition }
									data-category={ module.category }
									data-active={ module.active ? '1' : '0' }
									key={ module.id }
								>
									<div className="wpe-ui-module__head">
										<Raw html={ module.iconHtml } />
										<Raw html={ module.badgeHtml } />
									</div>
									<h3 className="wpe-ui-module__name">{ module.name }</h3>
									<p className="wpe-ui-module__desc">{ module.description }</p>
									<p className="wpe-ui-module__meta">{ module.options }</p>
									<div className="wpe-ui-module__foot">
										<a href={ module.settingsUrl }>{ t.settings } &rarr;</a>
										<Raw html={ module.toggleHtml } />
									</div>
								</article>
							) ) }
						</div>
					</section>
				);
			} ) }
			{ visible.length === 0 ? (
				<div className="wpe-ui-empty">
					<h2>{ t.noResults }</h2>
					<p>{ t.noResultsHint }</p>
				</div>
			) : null }
		</>
	);
}
