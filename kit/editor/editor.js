/**
 * Block editor side of the WPEssential kit. Every kit element is registered from its
 * element.json (passed in window.wpessentialKit): the sidebar panels and controls are built from
 * the options, responsive options get a breakpoint picker, and the preview is the same PHP
 * render the front end and Elementor use. No build step, no JSX.
 */
( function ( wp ) {
	'use strict';

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var useState = wp.element.useState;
	var __ = wp.i18n.__;
	var be = wp.blockEditor;
	var c = wp.components;
	var ServerSideRender = wp.serverSideRender;
	var kit = window.wpessentialKit || { elements: {}, breakpoints: [ 'base' ], palette: {} };

	var BP_LABEL = { base: '1440 · desktop', 1920: '1920+', 1280: '1280', 1024: '1024', 768: '768', 560: '560', 480: '480', 360: '360', 320: '320' };

	function options( map ) {
		return Object.keys( map || {} ).map( function ( k ) {
			return { value: k, label: map[ k ] };
		} );
	}

	var LENGTH = kit.lengthUnits || [ 'px', 'em', 'rem', '%', 'vw', 'vh' ];

	// Units an option offers; mirrors Kit::units() in PHP.
	function unitsOf( opt ) {
		var u = undefined === opt.unit ? 'px' : opt.unit;
		if ( opt.units ) {
			return opt.units;
		}
		if ( opt.fluid || ( LENGTH.indexOf( u ) < 0 && 'ch' !== u ) ) {
			return [ u ];
		}
		return [ u ].concat( LENGTH.filter( function ( x ) {
			return x !== u;
		} ) );
	}

	// "24px" -> { n: 24, u: 'px' }; plain numbers use the option's own unit.
	function parseLen( value, own ) {
		if ( '' === value || undefined === value || null === value ) {
			return { n: undefined, u: own };
		}
		var m = String( value ).match( /^(-?[\d.]+)\s*([a-z%]*)$/i );
		return m ? { n: Number( m[ 1 ] ), u: m[ 2 ] || own } : { n: undefined, u: own };
	}

	function RangeField( p ) {
		var opt = p.opt;
		var own = undefined === opt.unit ? 'px' : opt.unit;
		var units = unitsOf( opt );
		var cur = parseLen( p.value, own );
		var unitState = useState( cur.u );
		var unit = units.indexOf( cur.u ) >= 0 && undefined !== cur.n ? cur.u : unitState[ 0 ];
		var max = opt.max;
		var step = opt.step || 1;
		var min = opt.min;
		if ( unit !== own ) {
			if ( 'em' === unit || 'rem' === unit ) {
				max = Math.max( 1, Math.round( ( opt.max || 200 ) / 1.6 ) / 10 );
				step = 0.1;
				min = 0;
			} else {
				max = 100;
				step = 1;
				min = 0;
			}
		}
		var emit = function ( n, u ) {
			if ( undefined === n || '' === n || null === n ) {
				p.onChange( '' );
				return;
			}
			p.onChange( u === own ? n : n + u );
		};
		return el( 'div', { className: 'wpek-range' },
			el( 'div', { className: 'wpek-range__head' },
				el( 'span', { className: 'wpek-label' }, p.label ),
				units.length > 1 ? el( 'div', { className: 'wpek-units', role: 'group', 'aria-label': __( 'Unit', 'wpessential' ) }, units.map( function ( u ) {
					return el( 'button', { key: u, type: 'button', className: 'wpek-unit' + ( u === unit ? ' is-active' : '' ), 'aria-pressed': u === unit, onClick: function () {
						unitState[ 1 ]( u );
						if ( undefined !== cur.n ) {
							emit( cur.n, u );
						}
					} }, u || '–' );
				} ) ) : ( own ? el( 'span', { className: 'wpek-unit is-active' }, own ) : null )
			),
			el( c.RangeControl, { value: cur.n, min: min, max: max, step: step, allowReset: true, withInputField: true, onChange: function ( v ) {
				emit( v, unit );
			}, __nextHasNoMarginBottom: true } ),
			el( c.TextControl, { className: 'wpek-range__custom', value: undefined === cur.n && p.value ? String( p.value ) : '', placeholder: __( 'Or type any value: 2.5rem, clamp(…), calc(…)', 'wpessential' ), onChange: function ( v ) {
				p.onChange( v );
			}, __nextHasNoMarginBottom: true } )
		);
	}

	function svgIcon( name, size ) {
		var body = ( kit.icons || {} )[ name ];
		return body ? el( 'svg', { width: size || 20, height: size || 20, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: 2, strokeLinecap: 'round', strokeLinejoin: 'round', 'aria-hidden': true, dangerouslySetInnerHTML: { __html: body } } ) : null;
	}

	function IconField( p ) {
		var open = useState( false );
		var q = useState( '' );
		var legacy = { arrow: 'arrow-up-right', right: 'arrow-right' };
		var v = p.value && 'object' === typeof p.value ? p.value : {};
		if ( 'string' === typeof p.value && p.value && 'none' !== p.value ) {
			v = { library: 'wpessential', value: legacy[ p.value ] || p.value };
		}
		var preview = null;
		if ( 'svg' === v.library && v.value && v.value.url ) {
			preview = el( 'img', { src: v.value.url, alt: '', width: 24, height: 24 } );
		} else if ( v.value && 'string' === typeof v.value ) {
			preview = svgIcon( v.value.replace( /^wpei\s+wpei-/, '' ), 24 ) || el( 'span', { className: 'wpek-icon-lib' }, v.value );
		}
		var names = Object.keys( kit.icons || {} ).filter( function ( n ) {
			return ! q[ 0 ] || n.indexOf( q[ 0 ].toLowerCase().trim() ) >= 0;
		} );
		return el( 'div', { className: 'wpek-icon-field' },
			el( 'span', { className: 'wpek-label' }, p.label ),
			el( 'div', { className: 'wpek-icon-field__row' },
				el( 'button', { type: 'button', className: 'wpek-icon-field__preview', onClick: function () {
					open[ 1 ]( true );
				}, 'aria-label': __( 'Choose icon', 'wpessential' ) }, preview || el( 'span', { className: 'wpek-muted' }, __( 'None', 'wpessential' ) ) ),
				el( 'div', { className: 'wpek-icon-field__actions' },
					el( c.Button, { variant: 'secondary', onClick: function () {
						open[ 1 ]( true );
					} }, __( 'Choose from library', 'wpessential' ) ),
					el( be.MediaUploadCheck, null, el( be.MediaUpload, {
						allowedTypes: [ 'image' ],
						onSelect: function ( m ) {
							p.onChange( { library: 'svg', value: { id: m.id, url: m.url } } );
						},
						render: function ( o ) {
							return el( c.Button, { variant: 'tertiary', onClick: o.open }, __( 'Upload SVG / image', 'wpessential' ) );
						},
					} ) ),
					v.value ? el( c.Button, { variant: 'link', isDestructive: true, onClick: function () {
						p.onChange( {} );
					} }, __( 'Remove icon', 'wpessential' ) ) : null
				)
			),
			open[ 0 ] ? el( c.Modal, { title: __( 'Choose an icon', 'wpessential' ), onRequestClose: function () {
				open[ 1 ]( false );
			}, className: 'wpek-icon-modal', size: 'medium' },
				el( c.SearchControl, { value: q[ 0 ], onChange: q[ 1 ], placeholder: __( 'Search icons…', 'wpessential' ), __nextHasNoMarginBottom: true } ),
				el( 'div', { className: 'wpek-icon-grid' }, names.map( function ( n ) {
					var on = v.library === 'wpessential' && v.value === n;
					return el( 'button', { key: n, type: 'button', className: 'wpek-icon-grid__item' + ( on ? ' is-active' : '' ), title: n, onClick: function () {
						p.onChange( { library: 'wpessential', value: n } );
						open[ 1 ]( false );
					} }, svgIcon( n, 22 ), el( 'span', null, n.replace( /-/g, ' ' ) ) );
				} ) ),
				names.length ? null : el( 'p', null, __( 'No icon found.', 'wpessential' ) )
			) : null
		);
	}

	function ImageField( p ) {
		var img = p.value && 'object' === typeof p.value ? p.value : {};
		// Saved with only an id (e.g. imported content): read the URL from the media library.
		var media = wp.data.useSelect( function ( select ) {
			return img.id && ! img.url ? select( 'core' ).getMedia( img.id ) : null;
		}, [ img.id, img.url ] );
		if ( media && media.source_url ) {
			img = { id: img.id, url: media.source_url };
		}
		return el( 'div', { className: 'wpek-image-field' },
			el( 'span', { className: 'wpek-label' }, p.label ),
			el( be.MediaUploadCheck, { fallback: el( 'p', null, __( 'You need permission to upload images.', 'wpessential' ) ) }, el( be.MediaUpload, {
				allowedTypes: [ 'image' ],
				value: img.id,
				onSelect: function ( m ) {
					p.onChange( { id: m.id, url: m.url } );
				},
				render: function ( o ) {
					return img.url ? el( 'div', { className: 'wpek-image-field__has' },
						el( 'button', { type: 'button', className: 'wpek-image-field__thumb', onClick: o.open, 'aria-label': __( 'Replace image', 'wpessential' ) }, el( 'img', { src: img.url, alt: '' } ) ),
						el( 'div', { className: 'wpek-image-field__actions' },
							el( c.Button, { variant: 'secondary', onClick: o.open }, __( 'Replace', 'wpessential' ) ),
							el( c.Button, { variant: 'link', isDestructive: true, onClick: function () {
								p.onChange( {} );
							} }, __( 'Remove', 'wpessential' ) )
						)
					) : el( 'button', { type: 'button', className: 'wpek-image-field__drop', onClick: o.open },
						svgIcon( 'upload', 26 ),
						el( 'strong', null, __( 'Upload or choose an image', 'wpessential' ) ),
						el( 'span', null, __( 'Opens the Media Library: drag a file in, or pick one you uploaded before.', 'wpessential' ) )
					);
				},
			} ) ),
			el( c.TextControl, { className: 'wpek-image-field__url', label: __( 'Or paste an image URL', 'wpessential' ), value: img.id ? '' : img.url || '', type: 'url', onChange: function ( u ) {
				p.onChange( u ? { id: 0, url: u } : {} );
			}, __nextHasNoMarginBottom: true } )
		);
	}

	function RepeaterField( p ) {
		var items = Array.isArray( p.value ) ? p.value : [];
		var openState = useState( -1 );
		var fields = p.opt.fields || {};
		var titleKey = p.opt.title_field || 'text';
		var update = function ( next ) {
			p.onChange( next );
		};
		var blank = function () {
			var item = {};
			Object.keys( fields ).forEach( function ( k ) {
				if ( undefined !== fields[ k ][ 'default' ] ) {
					item[ k ] = fields[ k ][ 'default' ];
				}
			} );
			return item;
		};
		var move = function ( i, d ) {
			var next = items.slice();
			var t = next[ i ];
			next[ i ] = next[ i + d ];
			next[ i + d ] = t;
			update( next );
			openState[ 1 ]( i + d );
		};
		return el( 'div', { className: 'wpek-repeater' },
			el( 'span', { className: 'wpek-label' }, p.label + ' (' + items.length + ')' ),
			items.map( function ( item, i ) {
				var open = openState[ 0 ] === i;
				var title = item[ titleKey ] ? String( item[ titleKey ] ).replace( /<[^>]+>/g, '' ) : __( 'Item', 'wpessential' ) + ' ' + ( i + 1 );
				return el( 'div', { key: i, className: 'wpek-repeater__item' + ( open ? ' is-open' : '' ) },
					el( 'div', { className: 'wpek-repeater__head' },
						el( 'button', { type: 'button', className: 'wpek-repeater__title', 'aria-expanded': open, onClick: function () {
							openState[ 1 ]( open ? -1 : i );
						} }, title ),
						el( c.Button, { icon: 'arrow-up-alt2', label: __( 'Move up', 'wpessential' ), size: 'small', disabled: 0 === i, onClick: function () {
							move( i, -1 );
						} } ),
						el( c.Button, { icon: 'arrow-down-alt2', label: __( 'Move down', 'wpessential' ), size: 'small', disabled: i === items.length - 1, onClick: function () {
							move( i, 1 );
						} } ),
						el( c.Button, { icon: 'admin-page', label: __( 'Duplicate', 'wpessential' ), size: 'small', onClick: function () {
							var next = items.slice();
							next.splice( i + 1, 0, Object.assign( {}, item ) );
							update( next );
						} } ),
						el( c.Button, { icon: 'trash', label: __( 'Remove', 'wpessential' ), size: 'small', isDestructive: true, onClick: function () {
							update( items.filter( function ( x, j ) {
								return j !== i;
							} ) );
						} } )
					),
					open ? el( 'div', { className: 'wpek-repeater__body' }, Object.keys( fields ).map( function ( k ) {
						return el( 'div', { key: k, style: { marginBottom: 12 } }, control( k, fields[ k ], item[ k ], function ( v ) {
							var next = items.slice();
							next[ i ] = Object.assign( {}, item );
							next[ i ][ k ] = v;
							update( next );
						} ) );
					} ) ) : null
				);
			} ),
			el( c.Button, { variant: 'secondary', icon: 'plus', className: 'wpek-repeater__add', onClick: function () {
				update( items.concat( [ blank() ] ) );
				openState[ 1 ]( items.length );
			} }, __( 'Add item', 'wpessential' ) )
		);
	}

	function control( key, opt, value, onChange ) {
		var label = opt.label || key;
		switch ( opt.type ) {
			case 'image':
				return el( ImageField, { label: label, value: value, onChange: onChange } );
			case 'icon':
				return el( IconField, { label: label, value: value, onChange: onChange } );
			case 'repeater':
				return el( RepeaterField, { label: label, opt: opt, value: value, onChange: onChange } );
			case 'html':
			case 'textarea':
				return el( c.TextareaControl, { label: label, value: value || '', onChange: onChange, rows: opt.code ? 6 : 3, className: opt.code ? 'wpek-code' : undefined, help: opt.code ? __( 'Example: selector { border-radius: 20px; } selector:hover { opacity: .8; }', 'wpessential' ) : undefined, __nextHasNoMarginBottom: true } );
			case 'select':
				return el( c.SelectControl, { label: label, value: value, options: options( opt.options ), onChange: onChange, __nextHasNoMarginBottom: true } );
			case 'color':
				return el( c.SelectControl, { label: label, value: value || '', options: [ { value: '', label: __( 'Default', 'wpessential' ) } ].concat( options( kit.palette ) ), onChange: onChange, __nextHasNoMarginBottom: true } );
			case 'toggle':
				return el( c.ToggleControl, { label: label, checked: !! value, onChange: onChange, __nextHasNoMarginBottom: true } );
			case 'range':
				return el( RangeField, { label: label, opt: opt, value: value, onChange: onChange } );
			default:
				return el( c.TextControl, { label: label, value: value || '', type: 'url' === opt.type ? 'url' : 'text', onChange: onChange, __nextHasNoMarginBottom: true } );
		}
	}

	Object.keys( kit.elements ).forEach( function ( name ) {
		var def = kit.elements[ name ];
		var panels = def.panels || { content: __( 'Content', 'wpessential' ) };
		var isContainer = 'container' === name;
		var hasInner = isContainer || !! def.innerBlocks;
		wp.blocks.registerBlockType( 'wpessential/' + name, {
			apiVersion: 3,
			title: def.title,
			icon: def.icon || 'star-filled',
			category: 'wpessential',
			edit: function ( props ) {
				var bp = useState( 'base' );
				var device = bp[ 0 ];
				var setDevice = bp[ 1 ];
				var a = props.attributes;
				var blockProps = be.useBlockProps( isContainer ? {
					className: 'wpe-container is-' + ( a.layout === 'grid' ? 'grid' : 'flex' ) + ( a.equalChildren ? ' is-equal' : '' ) + ( a.contentWidth === 'full' ? ' is-content-full' : '' )
				} : {} );
				var picker = el( c.SelectControl, {
					label: __( 'Editing breakpoint', 'wpessential' ),
					help: __( 'Responsive options below save a value for this screen width.', 'wpessential' ),
					value: device,
					options: kit.breakpoints.map( function ( b ) {
						return { value: b, label: BP_LABEL[ b ] || b };
					} ),
					onChange: setDevice,
					__nextHasNoMarginBottom: true,
				} );
				var set = function ( key, v ) {
					var change = {};
					change[ key ] = v;
					props.setAttributes( change );
				};
				// One field (plain or responsive) read from / written to `obj[ field ]`.
				var field = function ( key, opt, value, onChange ) {
					if ( 'breakpoints' === opt.type ) {
						var cur = value || {};
						return el( 'fieldset', { key: key, style: { marginBottom: 16 } },
							el( 'legend', { style: { fontWeight: 600, marginBottom: 8 } }, opt.label || key ),
							kit.breakpoints.map( function ( b ) {
								return el( c.CheckboxControl, { key: b, label: BP_LABEL[ b ] || b, checked: !! cur[ b ], onChange: function ( on ) {
									var next = Object.assign( {}, cur );
									next[ b ] = on;
									onChange( next );
								}, __nextHasNoMarginBottom: true } );
							} )
						);
					}
					if ( opt.responsive ) {
						var obj = value && 'object' === typeof value ? value : {};
						return el( 'div', { key: key, style: { marginBottom: 16 } }, control( key, Object.assign( {}, opt, { label: ( opt.label || key ) + ' · ' + ( BP_LABEL[ device ] || device ) } ), obj[ device ], function ( v ) {
							var next = Object.assign( {}, obj );
							next[ device ] = v;
							onChange( next );
						} ) );
					}
					return el( 'div', { key: key, style: { marginBottom: 16 } }, control( key, opt, value, onChange ) );
				};
				var sections = [];
				Object.keys( panels ).forEach( function ( panel, i ) {
					var rows = [];
					var hasResponsive = false;
					Object.keys( def.attributes ).forEach( function ( key ) {
						var opt = def.attributes[ key ];
						if ( ( opt.panel || 'content' ) !== panel || ( isContainer && 'content' === key ) ) {
							return;
						}
						hasResponsive = hasResponsive || !! opt.responsive;
						rows.push( field( key, opt, a[ key ], function ( v ) {
							set( key, v );
						} ) );
					} );
					if ( rows.length ) {
						sections.push( el( c.PanelBody, { key: panel, title: panels[ panel ], initialOpen: 0 === i }, hasResponsive ? el( 'div', { style: { marginBottom: 16 } }, picker ) : null, rows ) );
					}
					// Each group of this panel gets its own collapsible panel.
					Object.keys( def.groups || {} ).forEach( function ( gkey ) {
						var g = def.groups[ gkey ];
						if ( ( g.panel || 'style' ) !== panel ) {
							return;
						}
						var fields = kit.groupFields[ g.type ] || {};
						var val = a[ gkey ] && 'object' === typeof a[ gkey ] && ! Array.isArray( a[ gkey ] ) ? a[ gkey ] : {};
						var anyResponsive = Object.keys( fields ).some( function ( f ) {
							return fields[ f ].responsive;
						} );
						sections.push( el( c.PanelBody, { key: gkey, title: g.label || gkey, initialOpen: false },
							anyResponsive ? el( 'div', { style: { marginBottom: 16 } }, picker ) : null,
							Object.keys( fields ).map( function ( f ) {
								return field( gkey + '.' + f, fields[ f ], val[ f ], function ( v ) {
									var next = Object.assign( {}, val );
									next[ f ] = v;
									set( gkey, next );
								} );
							} )
						) );
					} );
				} );
				var getLayoutValue = function ( key, fallback ) {
					var value = a[ key ];
					if ( value && 'object' === typeof value && ! Array.isArray( value ) ) {
						return value[ device ] || value.base || fallback;
					}
					return value || fallback;
				};
				var containerPreview = {
					'--wpek-direction': getLayoutValue( 'direction', 'row' ),
					'--wpek-wrap': getLayoutValue( 'wrap', 'wrap' ),
					'--wpek-cols': getLayoutValue( 'columns', 3 ),
					'--wpek-gap': getLayoutValue( 'gap', 24 ) + ( 'number' === typeof getLayoutValue( 'gap', 24 ) ? 'px' : '' ),
					'--wpek-row-gap': getLayoutValue( 'rowGap', 24 ) + ( 'number' === typeof getLayoutValue( 'rowGap', 24 ) ? 'px' : '' )
				};
				return el( Fragment, null,
					el( be.InspectorControls, null, sections ),
					isContainer ?
						el( 'div', blockProps,
							el( 'div', { className: 'wpe-container__inner', style: containerPreview },
								el( be.InnerBlocks, { renderAppender: be.InnerBlocks.ButtonBlockAppender } )
							)
						) :
						def.innerBlocks ?
						el( 'div', blockProps, el( 'p', { className: 'wpe-inner-label' }, def.title ), el( be.InnerBlocks, { renderAppender: be.InnerBlocks.ButtonBlockAppender } ) ) :
						el( 'div', blockProps, el( ServerSideRender, { block: 'wpessential/' + name, attributes: a, skipBlockSupportAttributes: true } ) )
				);
			},
			save: function () {
				return hasInner ? el( be.InnerBlocks.Content ) : null;
			},
		} );
	} );
}( window.wp ) );
