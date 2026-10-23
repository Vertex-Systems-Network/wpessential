// Shared client for the WPEssential AJAX gateway (Platform/WordPress/Ajax). One request contract:
// POST action, type, nonce, payload_json -> { success: true, data } | { success: false, error }.

export interface AjaxConfig {
	url: string;
	action: string;
	nonces: Record< string, string >;
}

export class AjaxError extends Error {
	constructor( message: string, public readonly code: string ) {
		super( message );
	}
}

let cached: AjaxConfig | null | undefined;

export function ajaxConfig(): AjaxConfig | null {
	if ( cached !== undefined ) {
		return cached;
	}
	const node = document.getElementById( 'wpe-ui-ajax' );
	try {
		cached = node ? ( JSON.parse( node.textContent ?? '' ) as AjaxConfig ) : null;
	} catch {
		cached = null;
	}
	return cached;
}

export async function ajax< T = unknown >( type: string, payload: Record< string, unknown >, config = ajaxConfig() ): Promise< T > {
	const nonce = config?.nonces[ type ];
	if ( ! config || ! nonce ) {
		throw new AjaxError( 'This action is not available.', 'unknown_request_type' );
	}
	const body = new URLSearchParams( { action: config.action, type, nonce, payload_json: JSON.stringify( payload ) } );
	let response: Response;
	try {
		response = await fetch( config.url, { method: 'POST', credentials: 'same-origin', body } );
	} catch {
		throw new AjaxError( 'The site could not be reached. Check your connection and try again.', 'network' );
	}
	let envelope: { success?: boolean; data?: T; error?: { code?: string; message?: string } };
	try {
		envelope = await response.json();
	} catch {
		throw new AjaxError( 'The server returned an unexpected response.', 'invalid_response' );
	}
	if ( ! envelope.success ) {
		throw new AjaxError( envelope.error?.message ?? 'The request failed.', envelope.error?.code ?? 'error' );
	}
	return envelope.data as T;
}
