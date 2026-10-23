// Confirmation dialog in the toolkit style (Figma: UI/Table · Modal · Tooltip · Empty, Kind=Modal).
// Built on <dialog>: the browser keeps focus inside it and Esc cancels. Resolves true on confirm.
export interface ConfirmOptions {
	title: string;
	message: string;
	confirmLabel: string;
	cancelLabel?: string;
	/** "danger" for destructive actions (trash, delete permanently). */
	tone?: 'danger' | 'primary';
}

export function confirmDialog( options: ConfirmOptions ): Promise< boolean > {
	return new Promise( ( resolve ) => {
		const dialog = document.createElement( 'dialog' );
		dialog.className = `wpe-ui-dialog wpe-ui-dialog--${ options.tone ?? 'primary' }`;
		dialog.setAttribute( 'aria-labelledby', 'wpe-ui-dialog-title' );
		dialog.setAttribute( 'aria-describedby', 'wpe-ui-dialog-message' );

		const title = document.createElement( 'h2' );
		title.id = 'wpe-ui-dialog-title';
		title.className = 'wpe-ui-dialog__title';
		title.textContent = options.title;

		const message = document.createElement( 'p' );
		message.id = 'wpe-ui-dialog-message';
		message.className = 'wpe-ui-dialog__message';
		message.textContent = options.message;

		const actions = document.createElement( 'div' );
		actions.className = 'wpe-ui-dialog__actions';
		const cancel = document.createElement( 'button' );
		cancel.type = 'button';
		cancel.className = 'wpe-ui-button wpe-ui-button--ghost';
		cancel.textContent = options.cancelLabel ?? 'Cancel';
		const confirm = document.createElement( 'button' );
		confirm.type = 'button';
		confirm.className = `wpe-ui-button wpe-ui-button--${ options.tone === 'danger' ? 'danger' : 'primary' }`;
		confirm.textContent = options.confirmLabel;
		actions.append( cancel, confirm );

		dialog.append( title, message, actions );
		// Inside .wpe-ui so the toolkit tokens (and dark mode) apply.
		( document.querySelector( '.wpe-ui' ) ?? document.body ).append( dialog );

		let answered = false;
		const close = ( result: boolean ): void => {
			if ( answered ) {
				return;
			}
			answered = true;
			dialog.close();
			dialog.remove();
			resolve( result );
		};
		cancel.addEventListener( 'click', () => close( false ) );
		confirm.addEventListener( 'click', () => close( true ) );
		dialog.addEventListener( 'cancel', ( event ) => {
			event.preventDefault();
			close( false );
		} );
		// A click on the backdrop (outside the card) cancels.
		dialog.addEventListener( 'click', ( event ) => {
			if ( event.target === dialog ) {
				close( false );
			}
		} );

		dialog.showModal();
		// The safe choice gets focus first for destructive actions.
		( options.tone === 'danger' ? cancel : confirm ).focus();
	} );
}
