import React from 'react';
import type { Html } from './data';

/** Escaped toolkit HTML from the server (icons, badges, pills, toggle forms). */
export function Raw( { html, as = 'span', className }: { html: Html; as?: 'span' | 'div'; className?: string } ) {
	return React.createElement( as, { className, dangerouslySetInnerHTML: { __html: html } } );
}

export function PageHeader( { eyebrow, title, lead, actions }: { eyebrow: string; title: string; lead: string; actions?: React.ReactNode } ) {
	return (
		<div className="wpe-ui-page-header">
			<div className="wpe-ui-page-header__copy">
				<p className="wpe-ui-eyebrow">{ eyebrow }</p>
				<h1>{ title }</h1>
				<p className="wpe-ui-page-header__desc">{ lead }</p>
			</div>
			{ actions ? <div className="wpe-ui-page-header__actions">{ actions }</div> : null }
		</div>
	);
}

export function Count( { n }: { n: number } ) {
	return <span className="wpe-ui-count">{ n }</span>;
}

export const buttonClass = ( variant: 'primary' | 'secondary' | 'ghost', small = false ): string =>
	`wpe-ui-button wpe-ui-button--${ variant }${ small ? ' wpe-ui-button--sm' : '' }`;
