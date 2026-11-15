/* global wpessentialOptions */
( function () {
	'use strict';

	var cfg = window.wpessentialOptions;
	var root = document.getElementById( 'wpe-options-app' );
	if ( ! cfg || ! root ) {
		return;
	}
	var __ = window.wp.i18n ? window.wp.i18n.__ : function ( s ) {
		return s;
	};
	var esc = function ( s ) {
		return String( s == null ? '' : s ).replace( /[&<>"']/g, function ( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ];
		} );
	};
	var state = {
		snippets: cfg.snippets.slice(),
		current: cfg.snippets.length ? cfg.snippets[ 0 ].id : null,
		dirty: false,
	};
	var editor = null;

	function save( data, btn ) {
		if ( btn ) {
			btn.disabled = true;
		}
		return window.wp.apiFetch( { path: '/wpessential/v1/options', method: 'POST', data: data } ).then( function ( res ) {
			state.snippets = res.snippets;
			state.dirty = false;
			notice( 'success', cfg.i18n.saved );
			render();
		} ).catch( function ( e ) {
			notice( 'error', cfg.i18n.error + ': ' + ( e.message || '' ) );
		} ).finally( function () {
			if ( btn ) {
				btn.disabled = false;
			}
		} );
	}

	function notice( type, text ) {
		var n = document.createElement( 'div' );
		n.className = 'wpe-opt-toast wpe-opt-toast--' + type;
		n.textContent = text;
		document.body.appendChild( n );
		setTimeout( function () {
			n.remove();
		}, 3000 );
	}

	function current() {
		return state.snippets.find( function ( s ) {
			return s.id === state.current;
		} );
	}

	function render() {
		root.innerHTML =
			'<div class="wpe-opt-body">' + codeView() + '</div>';
		bindCode();
	}

	function codeView() {
		var s = current();
		var list = state.snippets.map( function ( x ) {
			return '<li><button data-pick="' + esc( x.id ) + '" class="' + ( x.id === state.current ? 'is-current' : '' ) + '"><span class="wpe-opt-type wpe-opt-type--' + x.type + '">' + x.type.toUpperCase() + '</span><span class="wpe-opt-name">' + esc( x.title ) + '</span><i class="wpe-opt-dot ' + ( x.enabled ? 'is-on' : '' ) + '" title="' + esc( x.enabled ? __( 'Active', 'wpessential' ) : __( 'Off', 'wpessential' ) ) + '"></i></button></li>';
		} ).join( '' );
		return '<div class="wpe-opt-code"><aside><div class="wpe-opt-add"><button class="button" data-add="css">+ CSS</button>' + ( cfg.canJs ? '<button class="button" data-add="js">+ JavaScript</button>' : '' ) + '</div>' +
			( list ? '<ul>' + list + '</ul>' : '<p class="description">' + esc( cfg.i18n.empty ) + '</p>' ) +
			'<p class="description wpe-opt-safe">' + esc( __( 'Broke something? Open your site in safe mode to load it without snippets:', 'wpessential' ) ) + ' <a href="' + esc( cfg.safe ) + '" target="_blank" rel="noopener">' + esc( __( 'Safe mode', 'wpessential' ) ) + '</a></p></aside>' +
			( s ? '<section class="wpe-opt-editor">' +
				'<div class="wpe-opt-row"><input type="text" class="regular-text" data-f="title" value="' + esc( s.title ) + '" aria-label="' + esc( __( 'Snippet name', 'wpessential' ) ) + '">' +
				'<label class="wpe-opt-switch"><input type="checkbox" data-f="enabled" ' + ( s.enabled ? 'checked' : '' ) + '> ' + esc( __( 'Active', 'wpessential' ) ) + '</label></div>' +
				'<div class="wpe-opt-row">' + ( s.type === 'css'
					? '<label>' + esc( __( 'Devices', 'wpessential' ) ) + ' <select data-f="scope">' + [ [ 'all', __( 'All devices', 'wpessential' ) ], [ 'desktop', __( 'Desktop (1025px+)', 'wpessential' ) ], [ 'tablet', __( 'Tablet (768–1024px)', 'wpessential' ) ], [ 'mobile', __( 'Mobile (≤767px)', 'wpessential' ) ] ].map( function ( o ) {
						return '<option value="' + o[ 0 ] + '"' + ( s.scope === o[ 0 ] ? ' selected' : '' ) + '>' + esc( o[ 1 ] ) + '</option>';
					} ).join( '' ) + '</select></label>'
					: '<label>' + esc( __( 'Load in', 'wpessential' ) ) + ' <select data-f="location"><option value="head"' + ( s.location === 'head' ? ' selected' : '' ) + '>&lt;head&gt;</option><option value="footer"' + ( s.location === 'footer' ? ' selected' : '' ) + '>' + esc( __( 'Footer (recommended)', 'wpessential' ) ) + '</option></select></label>' ) +
				'<span class="description">' + esc( s.type === 'js' ? __( 'Write JavaScript only, without <script> tags.', 'wpessential' ) : __( 'Write CSS only, without <style> tags.', 'wpessential' ) ) + '</span></div>' +
				'<textarea id="wpe-opt-code" rows="18">' + esc( s.code ) + '</textarea>' +
				'<div class="wpe-opt-actions"><button class="button button-primary" data-save>' + esc( __( 'Save snippets', 'wpessential' ) ) + '</button><button class="button-link button-link-delete" data-del>' + esc( __( 'Delete', 'wpessential' ) ) + '</button></div>' +
				'</section>' : '<section class="wpe-opt-editor wpe-opt-empty"><p>' + esc( cfg.i18n.empty ) + '</p></section>' ) + '</div>';
	}

	function syncEditor() {
		var s = current();
		if ( s && editor ) {
			s.code = editor.codemirror.getValue();
		}
	}

	function bindCode() {
		var s = current();
		root.querySelectorAll( '[data-add]' ).forEach( function ( b ) {
			b.addEventListener( 'click', function () {
				syncEditor();
				var id = Math.random().toString( 36 ).slice( 2, 10 );
				state.snippets.push( { id: id, title: b.dataset.add === 'js' ? __( 'New script', 'wpessential' ) : __( 'New styles', 'wpessential' ), type: b.dataset.add, scope: 'all', location: 'footer', enabled: false, code: '' } );
				state.current = id;
				state.dirty = true;
				render();
			} );
		} );
		root.querySelectorAll( '[data-pick]' ).forEach( function ( b ) {
			b.addEventListener( 'click', function () {
				syncEditor();
				state.current = b.dataset.pick;
				render();
			} );
		} );
		if ( ! s ) {
			return;
		}
		var area = document.getElementById( 'wpe-opt-code' );
		var settings = cfg.editor[ s.type ];
		editor = settings && window.wp.codeEditor ? window.wp.codeEditor.initialize( area, settings ) : null;
		root.querySelectorAll( '[data-f]' ).forEach( function ( f ) {
			f.addEventListener( 'change', function () {
				s[ f.dataset.f ] = f.type === 'checkbox' ? f.checked : f.value;
				state.dirty = true;
				if ( f.dataset.f === 'enabled' || f.dataset.f === 'title' ) {
					syncEditor();
					render();
				}
			} );
		} );
		root.querySelector( '[data-save]' ).addEventListener( 'click', function ( e ) {
			syncEditor();
			save( { snippets: state.snippets }, e.currentTarget );
		} );
		root.querySelector( '[data-del]' ).addEventListener( 'click', function () {
			// eslint-disable-next-line no-alert
			if ( window.confirm( cfg.i18n.confirm ) ) {
				state.snippets = state.snippets.filter( function ( x ) {
					return x.id !== s.id;
				} );
				state.current = state.snippets.length ? state.snippets[ 0 ].id : null;
				save( { snippets: state.snippets } );
			}
		} );
	}

	window.addEventListener( 'beforeunload', function ( e ) {
		if ( state.dirty ) {
			e.preventDefault();
			e.returnValue = '';
		}
	} );
	render();
}() );
