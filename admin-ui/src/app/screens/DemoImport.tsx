import React, { useCallback, useEffect, useRef, useState } from 'react';
import { buttonClass } from '../ui';

/* One-click demo import: start, then run the job in small slices until it finishes. The job is
   stored on the server, so closing the page and coming back resumes it. */

export interface ImportRest {
	root: string;
	nonce: string;
}

export type ImportText = Record< string, string >;

interface Step {
	key: string;
	label: string;
	status: string;
}

interface Job {
	id: string;
	title: string;
	status: 'running' | 'failed' | 'done' | 'cancelled' | string;
	progress: number;
	index: number;
	steps: Step[];
	seq: number;
	log: Array< { seq: number; level: string; msg: string; hint?: string } >;
	links: { site: string; customize: string } | null;
}

export async function importCall< T >( rest: ImportRest, path: string, method: 'GET' | 'POST' = 'GET', body?: unknown ): Promise< T > {
	const init: RequestInit = {
		method,
		credentials: 'same-origin',
		headers: { 'X-WP-Nonce': rest.nonce, 'Content-Type': 'application/json' },
	};
	if ( body !== undefined ) {
		init.body = JSON.stringify( body );
	}
	const response = await fetch( rest.root + 'wpessential/v1/demos/import/' + path, init );
	const data = await response.json().catch( () => null );
	if ( ! response.ok ) {
		throw new Error( ( data && typeof data.message === 'string' && data.message ) || `HTTP ${ response.status }` );
	}
	return data as T;
}

export function ImportPanel( {
	rest,
	text,
	start,
	resume,
	onClose,
	onStatus,
}: {
	rest: ImportRest;
	text: ImportText;
	start?: { demo: string; builder: string; reset: boolean; options?: Record< string, boolean > } | undefined;
	resume?: boolean | undefined;
	onClose: ( finished: boolean ) => void;
	onStatus?: ( status: string ) => void;
} ) {
	const [ job, setJob ] = useState< Job | null >( null );
	const [ error, setError ] = useState( '' );
	const [ lines, setLines ] = useState< Job[ 'log' ] >( [] );
	const since = useRef( 0 );
	const stopped = useRef( false );

	const absorb = useCallback( ( next: Job ) => {
		setJob( next );
		onStatus?.( next.status );
		const last = next.log[ next.log.length - 1 ];
		if ( last ) {
			since.current = last.seq;
			setLines( ( old ) => [ ...old, ...next.log ].slice( -6 ) );
		}
	}, [] );

	const loop = useCallback(
		async ( retry: boolean ) => {
			setError( '' );
			let first = retry;
			while ( ! stopped.current ) {
				try {
					const next = await importCall< Job >( rest, 'run', 'POST', { since: since.current, retry: first } );
					first = false;
					absorb( next );
					if ( next.status !== 'running' ) {
						return;
					}
				} catch ( e ) {
					setError( ( e as Error ).message );
					return;
				}
			}
		},
		[ rest, absorb ]
	);

	useEffect( () => {
		stopped.current = false;
		( async () => {
			try {
				if ( start ) {
					absorb( await importCall< Job >( rest, 'start', 'POST', { demo: start.demo, builder: start.builder, options: { ...start.options, reset: start.reset } } ) );
				}
				await loop( !! resume );
			} catch ( e ) {
				setError( ( e as Error ).message );
			}
		} )();
		return () => {
			stopped.current = true;
		};
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [] );

	const cancel = async () => {
		stopped.current = true;
		try {
			await importCall( rest, 'cancel', 'POST' );
		} catch {
			/* The job stays resumable either way. */
		}
		onClose( false );
	};

	const running = job?.status === 'running' && error === '';
	const step = job ? job.steps[ Math.min( job.index, job.steps.length - 1 ) ] : null;

	return (
		<div className="wpe-ui-import" role="region" aria-label={ text.importing } aria-busy={ running }>
			<div className="wpe-ui-import__head">
				<h2>{ job ? job.title : text.starting }</h2>
				<span className="wpe-ui-muted">
					{ job?.status === 'done' ? text.done : job?.status === 'failed' ? text.failed : step ? step.label : text.starting }
				</span>
			</div>
			<progress className="wpe-ui-import__bar" max={ 100 } value={ job?.progress ?? 0 } aria-label={ text.progress }>
				{ job?.progress ?? 0 }%
			</progress>
			<ol className="wpe-ui-import__log" aria-live="polite">
				{ lines.map( ( line ) => (
					<li key={ line.seq } className={ `is-${ line.level }` }>
						{ line.msg }
						{ line.hint ? <span className="wpe-ui-muted"> { line.hint }</span> : null }
					</li>
				) ) }
			</ol>
			{ error !== '' ? (
				<p className="wpe-ui-import__error" role="alert">
					{ error } { text.saved }
				</p>
			) : null }
			<div className="wpe-ui-import__actions">
				{ job?.status === 'done' && job.links ? (
					<>
						<a className={ buttonClass( 'primary' ) } href={ job.links.site } target="_blank" rel="noopener noreferrer">
							{ text.viewSite }
						</a>
						<button type="button" className={ buttonClass( 'ghost' ) } onClick={ () => onClose( true ) }>
							{ text.close }
						</button>
					</>
				) : error !== '' || job?.status === 'failed' ? (
					<>
						<button type="button" className={ buttonClass( 'primary' ) } onClick={ () => { stopped.current = false; void loop( true ); } }>
							{ text.resume }
						</button>
						<button type="button" className={ buttonClass( 'ghost' ) } onClick={ cancel }>
							{ text.cancel }
						</button>
					</>
				) : (
					<button type="button" className={ buttonClass( 'ghost' ) } onClick={ cancel }>
						{ text.cancel }
					</button>
				) }
			</div>
		</div>
	);
}

interface WizardDemo {
	id: string;
	title: string;
	thumbnail: string;
	locked: boolean;
	importable: Array< { key: string; label: string } >;
}

interface PluginRow {
	slug: string;
	name: string;
	state: string;
	status: string;
	version: string;
	action: string;
}

/* Import wizard modal: builder, plugins, content + consent, import, done. */
export function ImportWizard( {
	rest,
	text,
	demo,
	imported,
	resume,
	onClose,
}: {
	rest: ImportRest;
	text: ImportText;
	demo: WizardDemo | null;
	imported: { title: string } | null;
	resume?: boolean | undefined;
	onClose: ( finished: boolean ) => void;
} ) {
	const steps = [ text.stepBuilder, text.stepPlugins, text.stepContent, text.stepImport, text.stepDone ];
	const builders = demo?.importable ?? [];
	const [ step, setStep ] = useState( resume ? 3 : builders.length > 1 ? 0 : 1 );
	const [ builder, setBuilder ] = useState( builders[ 0 ]?.key ?? 'gutenberg' );
	const [ plugins, setPlugins ] = useState< PluginRow[] | null >( null );
	const [ opts, setOpts ] = useState( { content: true, media: true, products: true, settings: true } );
	const [ consent, setConsent ] = useState( false );
	const [ status, setStatus ] = useState( '' );
	const first = builders.length > 1 ? 0 : 1;
	const dialog = useRef< HTMLDivElement >( null );

	useEffect( () => {
		dialog.current?.focus();
	}, [ step ] );

	useEffect( () => {
		if ( step !== 1 || ! demo ) {
			return;
		}
		setPlugins( null );
		importCall< PluginRow[] >( rest, `plugins?demo=${ encodeURIComponent( demo.id ) }&builder=${ encodeURIComponent( builder ) }` )
			.then( setPlugins )
			.catch( () => setPlugins( [] ) );
	}, [ step, builder, demo, rest ] );

	const shown = status === 'done' ? 4 : step;
	const busy = step === 3 && status !== 'done' && status !== 'failed';
	const close = ( finished: boolean ) => {
		if ( ! busy ) {
			onClose( finished );
		}
	};
	const pill = ( p: PluginRow ) => {
		const key = p.action === 'install' ? 'plgInstall' : p.action === 'activate' ? 'plgActivate' : p.action === 'deactivate' ? 'plgDeactivate' : 'plgActive';
		return <span className={ `wpe-ui-demowiz__pill is-${ p.action }` }>{ text[ key ] }</span>;
	};
	const check = ( key: keyof typeof opts ) => (
		<label className="wpe-ui-demowiz__option" key={ key }>
			<input type="checkbox" checked={ opts[ key ] } onChange={ ( e ) => setOpts( { ...opts, [ key ]: e.target.checked } ) } />
			<span>{ text[ 'opt' + key.charAt( 0 ).toUpperCase() + key.slice( 1 ) ] }</span>
		</label>
	);

	return (
		<div className="wpe-ui-demowiz" role="presentation" onKeyDown={ ( e ) => e.key === 'Escape' && close( false ) }>
			<div className="wpe-ui-demowiz__modal" role="dialog" aria-modal="true" aria-labelledby="wpe-wizard-title" tabIndex={ -1 } ref={ dialog }>
				<aside className="wpe-ui-demowiz__rail">
					<div className="wpe-ui-demowiz__demo">
						{ demo?.thumbnail ? <img src={ demo.thumbnail } alt="" /> : null }
						<div>
							<strong>{ demo?.title ?? text.importing }</strong>
							<span>{ demo?.locked === false ? text.wizardSub : '' }</span>
						</div>
					</div>
					<ol className="wpe-ui-demowiz__steps">
						{ steps.map( ( label, i ) => (
							<li key={ label } className={ i < shown ? 'is-done' : i === shown ? 'is-current' : '' } aria-current={ i === shown ? 'step' : undefined }>
								<span className="wpe-ui-demowiz__dot">{ i < shown ? '✓' : i + 1 }</span>
								{ label }
							</li>
						) ) }
					</ol>
					<p className="wpe-ui-demowiz__safe">{ text.safe }</p>
				</aside>
				<div className="wpe-ui-demowiz__main">
					<div className="wpe-ui-demowiz__body">
						<div className="wpe-ui-demowiz__head">
							<h2 id="wpe-wizard-title">{ [ text.builderTitle, text.pluginsTitle, text.contentTitle, text.importTitle, text.doneTitle ][ shown ] }</h2>
							<button type="button" className="wpe-ui-demowiz__close" aria-label={ text.close } disabled={ busy } onClick={ () => close( status === 'done' ) }>
								×
							</button>
						</div>
						{ step === 0 ? (
							<>
								<p className="wpe-ui-muted">{ text.builderText }</p>
								<div className="wpe-ui-demowiz__choices" role="radiogroup">
									{ builders.map( ( b ) => (
										<label key={ b.key } className={ `wpe-ui-demowiz__choice${ builder === b.key ? ' is-selected' : '' }` }>
											<input type="radio" name="wpe-builder" checked={ builder === b.key } onChange={ () => setBuilder( b.key ) } />
											<strong>{ b.label }</strong>
										</label>
									) ) }
								</div>
							</>
						) : step === 1 ? (
							<>
								<p className="wpe-ui-muted">{ text.pluginsText }</p>
								{ plugins === null ? (
									<p className="wpe-ui-muted">{ text.starting }</p>
								) : plugins.length === 0 ? (
									<p className="wpe-ui-muted">{ text.noPlugins }</p>
								) : (
									<ul className="wpe-ui-demowiz__list">
										{ plugins.map( ( p ) => (
											<li key={ p.slug }>
												<span className="wpe-ui-demowiz__icon" aria-hidden="true">{ p.name.charAt( 0 ) }</span>
												<span className="wpe-ui-demowiz__name">
													<strong>{ p.name }</strong>
													<span className="wpe-ui-muted">
														{ text[ p.state ] ?? p.state }
														{ p.version ? ` · v${ p.version }` : '' }
													</span>
												</span>
												{ pill( p ) }
											</li>
										) ) }
									</ul>
								) }
							</>
						) : step === 2 ? (
							<>
								<p className="wpe-ui-muted">{ text.contentText }</p>
								<div className="wpe-ui-demowiz__options">{ ( [ 'content', 'media', 'products', 'settings' ] as const ).map( check ) }</div>
								{ imported ? (
									<div className="wpe-ui-demowiz__consent" role="group" aria-labelledby="wpe-consent-title">
										<strong id="wpe-consent-title">{ text.replaceTitle.replace( '%s', imported.title ) }</strong>
										<ul>
											<li>{ text.replaceRemove }</li>
											<li>{ text.replaceRestore }</li>
											<li>{ text.replaceKeep }</li>
										</ul>
										<label>
											<input type="checkbox" checked={ consent } onChange={ ( e ) => setConsent( e.target.checked ) } />
											<span>{ text.replaceAgree.replace( '%s', imported.title ) }</span>
										</label>
									</div>
								) : null }
							</>
						) : (
							<ImportPanel
								rest={ rest }
								text={ text }
								start={ resume || ! demo ? undefined : { demo: demo.id, builder, reset: imported !== null, options: opts } }
								resume={ resume }
								onStatus={ setStatus }
								onClose={ onClose }
							/>
						) }
					</div>
					{ step < 3 ? (
						<div className="wpe-ui-demowiz__foot">
							{ step > first ? (
								<button type="button" className={ buttonClass( 'ghost' ) } onClick={ () => setStep( step - 1 ) }>
									← { text.back }
								</button>
							) : (
								<button type="button" className={ buttonClass( 'ghost' ) } onClick={ () => close( false ) }>
									{ text.close }
								</button>
							) }
							<button
								type="button"
								className={ buttonClass( 'primary' ) }
								disabled={ step === 2 && imported !== null && ! consent }
								onClick={ () => setStep( step + 1 ) }
							>
								{ step === 2 ? text.startImport : text.next } →
							</button>
						</div>
					) : null }
				</div>
			</div>
		</div>
	);
}
