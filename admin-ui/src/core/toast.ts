// Toolkit toasts, announced through the polite status region the top bar renders.
export function toast( message: string, tone: 'success' | 'info' | 'error' = 'success' ): void {
	const region = document.querySelector< HTMLElement >( '[data-wpe-toasts]' );
	if ( ! region ) {
		return;
	}
	const item = document.createElement( 'div' );
	item.className = `wpe-ui-toast wpe-ui-toast--${ tone }`;
	item.textContent = message;
	region.append( item );
	window.setTimeout( () => {
		item.classList.add( 'is-leaving' );
		window.setTimeout( () => item.remove(), 260 );
	}, tone === 'error' ? 6000 : 3200 );
}
