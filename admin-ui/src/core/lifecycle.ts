// Shared row actions for builder lists (Post Types, Taxonomies), WordPress-style: Edit, Publish /
// Disable, Lock / Unlock, Trash; in the Trash: Restore and Delete permanently. Destructive actions
// confirm in the toolkit dialog. Buttons use the toolkit button colours for their state.
import { confirmDialog } from './dialog';

export type LifecycleAction = 'trash' | 'restore' | 'delete' | 'lock' | 'unlock' | 'publish' | 'disable';

export interface LifecycleDefinition {
	id: string;
	status: string;
	revision: number;
	payload: Record< string, unknown >;
}

export interface LifecycleContext< T extends LifecycleDefinition = LifecycleDefinition > {
	/** Plural and singular nouns for messages: "custom post types" / "custom post type". */
	noun: { one: string; many: string };
	/** Sends one action for one definition through the page's AJAX routes. */
	send: ( action: LifecycleAction, definition: T ) => Promise< void >;
}

export const isLocked = ( definition: LifecycleDefinition ): boolean => definition.payload.locked === true;
export const isTrashed = ( definition: LifecycleDefinition ): boolean => definition.payload.trashed === true;

function button( label: string, variant: string, action: LifecycleAction | 'edit', id: string ): HTMLButtonElement {
	const element = document.createElement( 'button' );
	element.type = 'button';
	element.className = `wpe-ui-button wpe-ui-button--${ variant } wpe-ui-button--sm`;
	element.textContent = label;
	element.dataset.wpeRowAction = action;
	element.dataset.wpeRowId = id;
	return element;
}

/** The row's action buttons for its current state. */
export function rowActions( definition: LifecycleDefinition ): HTMLElement {
	const wrap = document.createElement( 'div' );
	wrap.className = 'wpe-ui-row-actions';
	const id = definition.id;
	if ( isTrashed( definition ) ) {
		wrap.append( button( 'Restore', 'secondary', 'restore', id ), button( 'Delete permanently', 'danger', 'delete', id ) );
		return wrap;
	}
	wrap.append( button( 'Edit', 'secondary', 'edit', id ) );
	wrap.append(
		definition.status === 'published' ? button( 'Disable', 'ghost', 'disable', id ) : button( 'Publish', 'primary', 'publish', id )
	);
	wrap.append( isLocked( definition ) ? button( 'Unlock', 'ghost', 'unlock', id ) : button( 'Lock', 'ghost', 'lock', id ) );
	const trash = button( 'Trash', 'danger', 'trash', id );
	if ( isLocked( definition ) ) {
		trash.disabled = true;
		trash.title = 'Locked: unlock it to move it to the Trash.';
	}
	wrap.append( trash );
	return wrap;
}

/** Marks a row with its state so list views and filters can select it. */
export function markRow( row: HTMLElement, definition: LifecycleDefinition ): void {
	row.dataset.wpeStatus = definition.status;
	row.dataset.wpeLocked = isLocked( definition ) ? '1' : '0';
	row.dataset.wpeTrashed = isTrashed( definition ) ? '1' : '0';
	row.dataset.wpeRowId = definition.id;
}

const CONFIRM: Partial< Record< LifecycleAction, ( n: number, ctx: { noun: { one: string; many: string } } ) => { title: string; message: string; confirm: string } > > = {
	trash: ( n, ctx ) => ( {
		title: n === 1 ? `Move this ${ ctx.noun.one } to the Trash?` : `Move ${ n } ${ ctx.noun.many } to the Trash?`,
		message: 'Published items are disabled first. You can restore them from the Trash.',
		confirm: 'Move to Trash',
	} ),
	delete: ( n, ctx ) => ( {
		title: n === 1 ? `Delete this ${ ctx.noun.one } permanently?` : `Delete ${ n } ${ ctx.noun.many } permanently?`,
		message: 'This cannot be undone.',
		confirm: 'Delete permanently',
	} ),
};

/**
 * Runs one action on one or more definitions (bulk actions pass several). Items the action does
 * not apply to are skipped: locked items are never trashed or deleted, as in the backend.
 * Returns the number of items changed.
 */
export async function runLifecycle< T extends LifecycleDefinition >( action: LifecycleAction, definitions: T[], ctx: LifecycleContext< T > ): Promise< number > {
	const applicable = definitions.filter( ( definition ) => {
		switch ( action ) {
			case 'trash':
				return ! isTrashed( definition ) && ! isLocked( definition );
			case 'delete':
				return isTrashed( definition ) && ! isLocked( definition );
			case 'restore':
				return isTrashed( definition );
			case 'lock':
				return ! isLocked( definition ) && ! isTrashed( definition );
			case 'unlock':
				return isLocked( definition );
			case 'publish':
				return definition.status !== 'published' && ! isTrashed( definition );
			case 'disable':
				return definition.status === 'published';
		}
		return false;
	} );
	if ( applicable.length === 0 ) {
		return 0;
	}
	const prompt = CONFIRM[ action ]?.( applicable.length, ctx );
	if ( prompt ) {
		const ok = await confirmDialog( { title: prompt.title, message: prompt.message, confirmLabel: prompt.confirm, tone: 'danger' } );
		if ( ! ok ) {
			return 0;
		}
	}
	for ( const definition of applicable ) {
		await ctx.send( action, definition );
	}
	return applicable.length;
}

export const BULK_ACTIONS = ( view: string ): Array< [ LifecycleAction, string ] > =>
	view === 'trash'
		? [
				[ 'restore', 'Restore' ],
				[ 'delete', 'Delete permanently' ],
		  ]
		: [
				[ 'publish', 'Publish' ],
				[ 'disable', 'Disable' ],
				[ 'lock', 'Lock' ],
				[ 'unlock', 'Unlock' ],
				[ 'trash', 'Move to Trash' ],
		  ];

/** List views: All (not trashed), Published, Draft, Locked, Trash. */
export const LIFECYCLE_VIEWS = [
	{ key: 'all', label: 'All', match: ( row: HTMLElement ) => row.dataset.wpeTrashed !== '1' },
	{ key: 'published', label: 'Published', match: ( row: HTMLElement ) => row.dataset.wpeTrashed !== '1' && row.dataset.wpeStatus === 'published' },
	{ key: 'draft', label: 'Draft', match: ( row: HTMLElement ) => row.dataset.wpeTrashed !== '1' && row.dataset.wpeStatus === 'draft' },
	{ key: 'locked', label: 'Locked', match: ( row: HTMLElement ) => row.dataset.wpeLocked === '1' },
	{ key: 'trash', label: 'Trash', match: ( row: HTMLElement ) => row.dataset.wpeTrashed === '1' },
];
