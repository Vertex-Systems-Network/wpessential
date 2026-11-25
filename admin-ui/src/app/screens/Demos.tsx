import React, { useState } from 'react';
import { Link, useSearchParams } from 'react-router';
import type { Html, ScreenData } from '../data';
import { DEMOS, to } from '../routes';
import { Count, PageHeader, Raw, buttonClass } from '../ui';
import { ImportWizard, importCall, type ImportRest, type ImportText } from './DemoImport';

interface Demo {
	id: string;
	title: string;
	tagline: string;
	niche: string[];
	badgeHtml: Html;
	type: string;
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
	autostart: { demo: string; builder: string } | null;
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
	const [ panel, setPanel ] = useState< Demo | 'resume' | null >( () => ( d.autostart && ! d.imported && ! d.job ? d.demos.find( ( x ) => x.id === d.autostart?.demo && ! x.locked ) ?? null : null ) );
	const [ imported, setImported ] = useState( d.imported );
	const [ pending, setPending ] = useState( d.job );
	const [ notice, setNotice ] = useState( '' );
	const begin = ( demo: Demo ) => {
		setNotice( '' );
		setPanel( demo );
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
	const [ query, setQuery ] = useState( '' );
	const [ type, setType ] = useState( '' );
	const [ builder, setBuilder ] = useState( '' );
	const q = query.trim().toLowerCase();
	const demos = d.demos.filter(
		( demo ) =>
			( niche === '' || demo.niche.includes( niche ) ) &&
			( type === '' || demo.type === type ) &&
			( builder === '' || demo.builders.some( ( b ) => b.label === builder ) ) &&
			( q === '' || `${ demo.title } ${ demo.tagline } ${ demo.niche.join( ' ' ) }`.toLowerCase().includes( q ) )
	);
	const builderNames = [ ...new Set( d.demos.flatMap( ( demo ) => demo.builders.map( ( b ) => b.label ) ) ) ];
	const filtered = niche !== '' || type !== '' || builder !== '' || q !== '';
	const tab = ( key: string, label: string, count: number ) => {
		const active = key === niche;
		return (
			<li key={ key }>
				<Link className={ `wpe-ui-demo-filter__item${ active ? ' is-active' : '' }` } to={ to( DEMOS, key === '' ? {} : { niche: key } ) } aria-current={ active ? 'page' : undefined }>
					<span>{ label }</span> <Count n={ count } />
				</Link>
			</li>
		);
	};
	const radios = ( name: string, value: string, set: ( v: string ) => void, options: Array< [ string, string ] > ) => (
		<div className="wpe-ui-demo-filter__radios" role="radiogroup">
			{ options.map( ( [ key, label ] ) => (
				<label key={ key } className={ value === key ? 'is-active' : '' }>
					<input type="radio" name={ name } checked={ value === key } onChange={ () => set( key ) } />
					{ label }
				</label>
			) ) }
		</div>
	);

	return (
		<>
			<PageHeader eyebrow={ t.eyebrow ?? '' } title={ t.heading ?? '' } lead={ t.lead ?? '' } actions={ <Raw html={ d.refreshHtml } /> } />
			{ notice !== '' ? (
				<div className="wpe-ui-notice wpe-ui-notice--info" role="status">
					<p>{ notice }</p>
				</div>
			) : null }
			{ panel !== null ? (
				<ImportWizard rest={ d.rest } text={ it } demo={ panel === 'resume' ? null : panel } imported={ imported } resume={ panel === 'resume' } onClose={ finished } />
			) : null }
			{ pending && panel === null ? (
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
			<div className="wpe-ui-demo-layout">
				<aside className="wpe-ui-demo-filter" aria-label={ t.filters }>
					<label className="wpe-ui-demo-filter__search">
						<span className="screen-reader-text">{ t.search }</span>
						<input type="search" value={ query } placeholder={ t.search } onChange={ ( e ) => setQuery( e.target.value ) } />
					</label>
					<div className="wpe-ui-demo-filter__group">
						<h2>{ t.type }</h2>
						{ radios( 'wpe-demo-type', type, setType, [ [ '', t.all ?? '' ], [ 'free', t.free ?? '' ], [ 'pro', t.premium ?? '' ] ] ) }
					</div>
					{ builderNames.length > 1 ? (
						<div className="wpe-ui-demo-filter__group">
							<h2>{ t.builder }</h2>
							{ radios( 'wpe-demo-builder', builder, setBuilder, [ [ '', t.all ?? '' ], ...builderNames.map( ( b ): [ string, string ] => [ b, b ] ) ] ) }
						</div>
					) : null }
					{ niches.size > 0 ? (
						<nav className="wpe-ui-demo-filter__group" aria-label={ t.categories }>
							<h2>{ t.categories }</h2>
							<ul>
								{ tab( '', t.all ?? '', d.demos.length ) }
								{ [ ...niches ].sort( ( a, b ) => a[ 0 ].localeCompare( b[ 0 ] ) ).map( ( [ key, count ] ) => tab( key, key.charAt( 0 ).toUpperCase() + key.slice( 1 ), count ) ) }
							</ul>
						</nav>
					) : null }
					{ filtered ? (
						<Link className={ buttonClass( 'ghost', true ) } to={ to( DEMOS, {} ) } onClick={ () => { setQuery( '' ); setType( '' ); setBuilder( '' ); } }>
							{ t.reset }
						</Link>
					) : null }
				</aside>
				<div className="wpe-ui-demo-results">
					<p className="wpe-ui-muted" aria-live="polite">{ ( t.showing ?? '%d' ).replace( '%d', String( demos.length ) ) }</p>
			{ demos.length === 0 ? (
				<div className="wpe-ui-empty wpe-ui-empty--dashed">
					<Raw className="wpe-ui-icon wpe-ui-icon--lg" html={ d.gridIcon } />
					<h2>{ filtered ? t.noMatch : t.emptyTitle }</h2>
					{ filtered ? null : <p>{ t.emptyText }</p> }
				</div>
			) : (
				<div className="wpe-ui-demo-grid">
					{ demos.map( ( demo ) => (
						<article className={ `wpe-ui-demo${ imported?.demo === demo.id ? ' is-active' : '' }` } key={ demo.id }>
							{ imported?.demo === demo.id ? <span className="wpe-ui-demo__active">✓ { it.active }</span> : null }
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
										<button type="button" className={ buttonClass( imported?.demo === demo.id ? 'secondary' : 'primary', true ) } disabled={ panel !== null || demo.importable.length === 0 } onClick={ () => begin( demo ) }>
											{ imported?.demo === demo.id ? it.reimport : it.import }
										</button>
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
				</div>
			</div>
		</>
	);
}
