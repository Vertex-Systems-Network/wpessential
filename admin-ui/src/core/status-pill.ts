// Definition lifecycle status as a toolkit pill. Mirrors CustomPostTypeAdminController::statusPill()
// so rows rebuilt in the browser match the server-rendered ones.
const STATUS_TONES: Record< string, string > = {
	published: 'running',
	draft: 'waiting',
	disabled: 'off',
	archived: 'locked',
};

export function statusCell( status: string ): HTMLTableCellElement {
	const cell = document.createElement( 'td' );
	const pill = document.createElement( 'span' );
	pill.className = `wpe-ui-pill wpe-ui-pill--${ STATUS_TONES[ status ] ?? 'off' }`;
	pill.textContent = status.charAt( 0 ).toUpperCase() + status.slice( 1 );
	cell.append( pill );
	return cell;
}
