import React from 'react';
import { Link, useSearchParams } from 'react-router';
import type { Html, ScreenData } from '../data';
import { DEMOS, to } from '../routes';
import { Count, PageHeader, Raw, buttonClass } from '../ui';

interface Demo {
	id: string;
	title: string;
	tagline: string;
	niche: string[];
	badgeHtml: Html;
	thumbnail: string;
	preview: string;
	builders: Array< { label: string; iconHtml: Html } >;
}

interface DemosData extends ScreenData {
	refreshHtml: Html;
	gridIcon: Html;
	imageIcon: Html;
	demos: Demo[];
}

export function Demos( { data }: { data: ScreenData } ) {
	const d = data as DemosData;
	const t = d.text;
	const [ params ] = useSearchParams();
	const niche = params.get( 'niche' ) ?? '';
	const niches = new Map< string, number >();
	for ( const demo of d.demos ) {
		for ( const item of demo.niche ) {
			niches.set( item, ( niches.get( item ) ?? 0 ) + 1 );
		}
	}
	const demos = d.demos.filter( ( demo ) => niche === '' || demo.niche.includes( niche ) );
	const tab = ( key: string, label: string, count: number ) => {
		const active = key === niche;
		return (
			<Link className={ `wpe-ui-tab${ active ? ' is-active' : '' }` } to={ to( DEMOS, key === '' ? {} : { niche: key } ) } aria-current={ active ? 'page' : undefined } key={ key }>
				{ label } <Count n={ count } />
			</Link>
		);
	};

	return (
		<>
			<PageHeader eyebrow={ t.eyebrow ?? '' } title={ t.heading ?? '' } lead={ t.lead ?? '' } actions={ <Raw html={ d.refreshHtml } /> } />
			{ niches.size > 0 ? (
				<div className="wpe-ui-toolbar">
					<nav className="wpe-ui-tabs" aria-label={ t.categories }>
						{ tab( '', t.all ?? '', d.demos.length ) }
						{ [ ...niches ].map( ( [ key, count ] ) => tab( key, key.charAt( 0 ).toUpperCase() + key.slice( 1 ), count ) ) }
					</nav>
				</div>
			) : null }
			{ demos.length === 0 ? (
				<div className="wpe-ui-empty wpe-ui-empty--dashed">
					<Raw className="wpe-ui-icon wpe-ui-icon--lg" html={ d.gridIcon } />
					<h2>{ t.emptyTitle }</h2>
					<p>{ t.emptyText }</p>
				</div>
			) : (
				<div className="wpe-ui-demo-grid">
					{ demos.map( ( demo ) => (
						<article className="wpe-ui-demo" key={ demo.id }>
							{ demo.thumbnail !== '' ? (
								<img className="wpe-ui-demo__shot" src={ demo.thumbnail } alt="" loading="lazy" />
							) : (
								<Raw as="div" className="wpe-ui-demo__shot" html={ d.imageIcon } />
							) }
							<div className="wpe-ui-demo__body">
								<div className="wpe-ui-demo__head">
									<h2>{ demo.title }</h2>
									<Raw html={ demo.badgeHtml } />
								</div>
								<p className="wpe-ui-muted">{ demo.tagline }</p>
								<div className="wpe-ui-demo__foot">
									<span className="wpe-ui-demo__builders">
										{ demo.builders.map( ( builder ) => (
											<span title={ builder.label } key={ builder.label }>
												<Raw html={ builder.iconHtml } />
												<span className="screen-reader-text">{ builder.label }</span>
											</span>
										) ) }
									</span>
									{ demo.preview !== '' ? (
										<a className={ buttonClass( 'ghost', true ) } href={ demo.preview } target="_blank" rel="noopener noreferrer">
											{ t.preview }
										</a>
									) : null }
								</div>
							</div>
						</article>
					) ) }
				</div>
			) }
		</>
	);
}
