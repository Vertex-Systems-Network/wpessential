import React from 'react';
import { Link } from 'react-router';
import type { Html, ScreenData } from '../data';
import { MODULES, to } from '../routes';
import { Count, Raw, buttonClass } from '../ui';

interface DashboardData extends ScreenData {
	stats: Array< [ string, string ] >;
	active: Array< { id: string; name: string; url: string; iconHtml: Html; statusHtml: Html } >;
	categories: Array< { key: string; label: string; count: number } >;
	statusUrl: string;
	upsellHtml: Html;
}

export function Dashboard( { data }: { data: ScreenData } ) {
	const d = data as DashboardData;
	const t = d.text;
	return (
		<>
			<section className="wpe-ui-hero">
				<div>
					<p className="wpe-ui-eyebrow">{ t.eyebrow }</p>
					<h1>{ t.heading }</h1>
					<p>{ t.lead }</p>
					<div className="wpe-ui-hero__actions">
						<Link className={ buttonClass( 'primary' ) } to={ to( MODULES ) }>
							{ t.manageModules }
						</Link>
						<a className={ buttonClass( 'ghost' ) } href={ d.statusUrl }>
							{ t.systemStatus }
						</a>
					</div>
				</div>
				<div className="wpe-ui-hero__stats">
					{ d.stats.map( ( [ value, label ] ) => (
						<div className="wpe-ui-stat" key={ label }>
							<span className="wpe-ui-stat__value">{ value }</span>
							<span className="wpe-ui-stat__label">{ label }</span>
						</div>
					) ) }
				</div>
			</section>
			<div className="wpe-ui-columns">
				<section className="wpe-ui-panel">
					<div className="wpe-ui-panel__head">
						<h2>{ t.activeModules }</h2>
						<Link to={ to( MODULES ) }>{ t.viewAll } &rarr;</Link>
					</div>
					{ d.active.length === 0 ? (
						<p className="wpe-ui-muted">{ t.noneActive }</p>
					) : (
						<div className="wpe-ui-quick-grid">
							{ d.active.map( ( module ) => (
								<a className="wpe-ui-quick" href={ module.url } key={ module.id }>
									<Raw html={ module.iconHtml } />
									<span className="wpe-ui-quick__name">{ module.name }</span>
									<Raw html={ module.statusHtml } />
								</a>
							) ) }
						</div>
					) }
				</section>
				<aside>
					{ d.upsellHtml ? <Raw as="div" html={ d.upsellHtml } /> : null }
					<section className="wpe-ui-panel">
						<h2>{ t.categories }</h2>
						<ul className="wpe-ui-list">
							{ d.categories.map( ( category ) => (
								<li key={ category.key }>
									<Link to={ to( MODULES, { category: category.key } ) }>{ category.label }</Link>
									<Count n={ category.count } />
								</li>
							) ) }
						</ul>
					</section>
				</aside>
			</div>
		</>
	);
}
