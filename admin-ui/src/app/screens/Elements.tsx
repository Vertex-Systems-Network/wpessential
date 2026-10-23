import React from 'react';
import { Link, useSearchParams } from 'react-router';
import type { Html, ScreenData } from '../data';
import { ELEMENTS, to } from '../routes';
import { Count, PageHeader, Raw, buttonClass } from '../ui';

interface ElementType {
	key: string;
	label: string;
	iconHtml: Html;
	emptyIcon: Html;
	emptyTitle: string;
	noticeHtml: Html;
	elements: Array< { id: string; name: string; description: string; iconHtml: Html; badgeHtml: Html; toggleHtml: Html } >;
}

interface ElementsData extends ScreenData {
	docsUrl: string;
	types: ElementType[];
}

export function Elements( { data }: { data: ScreenData } ) {
	const d = data as ElementsData;
	const t = d.text;
	const [ params ] = useSearchParams();
	const current = d.types.find( ( type ) => type.key === params.get( 'type' ) ) ?? d.types[ 0 ];
	if ( ! current ) {
		return null;
	}

	return (
		<>
			<PageHeader eyebrow={ t.eyebrow ?? '' } title={ t.heading ?? '' } lead={ t.lead ?? '' } />
			<div className="wpe-ui-toolbar">
				<nav className="wpe-ui-tabs" aria-label={ t.builder }>
					{ d.types.map( ( type ) => {
						const active = type.key === current.key;
						return (
							<Link className={ `wpe-ui-tab${ active ? ' is-active' : '' }` } to={ to( ELEMENTS, { type: type.key } ) } aria-current={ active ? 'page' : undefined } key={ type.key }>
								<Raw html={ type.iconHtml } />
								{ type.label } <Count n={ type.elements.length } />
							</Link>
						);
					} ) }
				</nav>
			</div>
			{ current.noticeHtml ? <Raw as="div" html={ current.noticeHtml } /> : null }
			{ current.elements.length === 0 ? (
				<div className="wpe-ui-empty wpe-ui-empty--dashed">
					<Raw className="wpe-ui-icon wpe-ui-icon--lg" html={ current.emptyIcon } />
					<h2>{ current.emptyTitle }</h2>
					<p>{ t.emptyText }</p>
					<a className={ buttonClass( 'ghost' ) } href={ d.docsUrl }>
						{ t.guide }
					</a>
				</div>
			) : (
				<section className="wpe-ui-panel wpe-ui-panel--flush" aria-label={ current.label }>
					{ current.elements.map( ( element ) => (
						<div className="wpe-ui-option" key={ element.id }>
							<div className="wpe-ui-option__copy wpe-ui-option__copy--row">
								<Raw className="wpe-ui-icon" html={ element.iconHtml } />
								<span>
									<span className="wpe-ui-option__label">{ element.name }</span>
									<br />
									<span className="wpe-ui-muted">{ element.description }</span>
								</span>
							</div>
							<div className="wpe-ui-option__meta">
								<Raw html={ element.badgeHtml } />
								<Raw html={ element.toggleHtml } />
							</div>
						</div>
					) ) }
				</section>
			) }
		</>
	);
}
