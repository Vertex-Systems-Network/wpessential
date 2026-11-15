import React, { useCallback, useEffect, useRef, useState } from 'react';
import { buttonClass } from '../ui';

/* One-click demo import: start, then run the job in small slices until it finishes. The job is
   stored on the server, so closing the page and coming back resumes it. */

export interface ImportRest {
	root: string;
	nonce: string;
}

export type ImportText = Record<
	| 'import' | 'importWith' | 'unlock' | 'importing' | 'starting' | 'progress' | 'done' | 'failed' | 'saved'
	| 'viewSite' | 'close' | 'resume' | 'cancel' | 'interrupted' | 'installed' | 'remove' | 'removeConfirm'
	| 'replaceConfirm' | 'doneNotice',
	string
>;

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
	log: Array< { seq: number; level: string; message: string; hint?: string } >;
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
}: {
	rest: ImportRest;
	text: ImportText;
	start?: { demo: string; builder: string; reset: boolean } | undefined;
	resume?: boolean | undefined;
	onClose: ( finished: boolean ) => void;
} ) {
	const [ job, setJob ] = useState< Job | null >( null );
	const [ error, setError ] = useState( '' );
	const [ lines, setLines ] = useState< Job[ 'log' ] >( [] );
	const since = useRef( 0 );
	const stopped = useRef( false );

	const absorb = useCallback( ( next: Job ) => {
		setJob( next );
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
					absorb( await importCall< Job >( rest, 'start', 'POST', { demo: start.demo, builder: start.builder, options: { reset: start.reset } } ) );
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
						{ line.message }
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
