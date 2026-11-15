import React, { useState } from 'react';
import { Link, useSearchParams } from 'react-router';
import type { Html, ScreenData } from '../data';
import { DEMOS, to } from '../routes';
import { Count, PageHeader, Raw, buttonClass } from '../ui';
import { ImportPanel, importCall, type ImportRest, type ImportText } from './DemoImport';

interface Demo {
	id: string;
	title: string;
	tagline: string;
	niche: string[];
	badgeHtml: Html;
	thumbnail: string;
	preview: string;
	builders: Array< { label: string; iconHtml: Html } >;
	locked: boolean;
	importable: Array< { key: string; label: string } >;
}

interface DemosData extends ScreenData {
	refreshHtml: Html;
	gridIcon: Html;
	imageIcon: Html;
	demos: Demo[];
	rest: ImportRest;
	importText: ImportText;
	upgradeUrl: string;
	imported: { demo: string; title: string; builder: string } | null;
	job: { demo: string; title: string; builder: string; status: string } | null;
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
	const it = d.importText;
	const [ panel, setPanel ] = useState< { demo: string; builder: string; reset: boolean } | 'resume' | null >( null );
	const [ imported, setImported ] = useState( d.imported );
	const [ pending, setPending ] = useState( d.job );
	const [ notice, setNotice ] = useState( '' );
	const begin = ( demo: Demo, builder: string ) => {
		if ( imported && ! window.confirm( it.replaceConfirm.replace( '%s', imported.title ) ) ) {
			return;
		}
		setNotice( '' );
		setPanel( { demo: demo.id, builder, reset: imported !== null } );
	};
	const finished = async ( done: boolean ) => {
		setPanel( null );
		setPending( null );
		try {
			const lib = await importCall< { imported: DemosData[ 'imported' ]; job: DemosData[ 'job' ] } >( d.rest, 'library' );
			setImported( lib.imported );
			setPending( lib.job );
		} catch {
			/* Keep the current view. */
		}
		if ( done ) {
			setNotice( it.doneNotice );
		}
	};
	const remove = async () => {
		if ( ! imported || ! window.confirm( it.removeConfirm.replace( '%s', imported.title ) ) ) {
			return;
		}
		try {
			const res = await importCall< { message: string } >( d.rest, 'remove', 'POST' );
			setImported( null );
			setNotice( res.message );
		} catch ( e ) {
			setNotice( ( e as Error ).message );
		}
	};
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
			{ notice !== '' ? (
				<div className="wpe-ui-notice wpe-ui-notice--info" role="status">
					<p>{ notice }</p>
				</div>
			) : null }
			{ panel !== null ? (
				<ImportPanel rest={ d.rest } text={ it } start={ panel === 'resume' ? undefined : panel } resume={ panel === 'resume' } onClose={ finished } />
			) : pending ? (
				<div className="wpe-ui-notice wpe-ui-notice--warning">
					<p>{ it.interrupted.replace( '%s', pending.title ) }</p>
					<button type="button" className={ buttonClass( 'primary', true ) } onClick={ () => setPanel( 'resume' ) }>
						{ it.resume }
					</button>
				</div>
			) : imported ? (
				<div className="wpe-ui-notice wpe-ui-notice--success">
					<p>{ it.installed.replace( '%s', imported.title ) }</p>
					<button type="button" className={ buttonClass( 'secondary', true ) } onClick={ remove }>
						{ it.remove }
					</button>
				</div>
			) : null }
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
								{ demo.niche.length > 0 ? (
									<span className="wpe-ui-demo__niches">
										{ demo.niche.map( ( item ) => (
											<span className="wpe-ui-chip" key={ item }>
												{ item.charAt( 0 ).toUpperCase() + item.slice( 1 ) }
											</span>
										) ) }
									</span>
								) : null }
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
									{ demo.locked ? (
										<a className={ buttonClass( 'secondary', true ) } href={ d.upgradeUrl } target="_blank" rel="noopener noreferrer">
											{ it.unlock }
										</a>
									) : (
										demo.importable.map( ( b ) => (
											<button
												type="button"
												key={ b.key }
												className={ buttonClass( 'primary', true ) }
												disabled={ panel !== null }
												onClick={ () => begin( demo, b.key ) }
											>
												{ demo.importable.length > 1 ? it.importWith.replace( '%s', b.label ) : it.import }
											</button>
										) )
									) }
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
