( () => {
	const SEL = '.wpe-container__inner,.wpe-gallery__items,.wpe-posts-grid__items,.wpe-products__grid,.wpe-woo-grid,.wpe-price-list__items,.wpe-social-feed__grid,.wpe-fg,.wpe-dl';
	const label = ( t ) => window.wpeSwipeL10n?.[ t ] || t;
	const setup = ( track ) => {
		const nav = document.createElement( 'div' );
		nav.className = 'wpe-swnav';
		nav.hidden = true;
		nav.innerHTML = '<button type="button" class="wpe-swnav__btn" data-d="-1" aria-label="' + label( 'Previous' ) + '">‹</button><div class="wpe-swnav__dots"></div><button type="button" class="wpe-swnav__btn" data-d="1" aria-label="' + label( 'Next' ) + '">›</button>';
		track.after( nav );
		const dots = nav.querySelector( '.wpe-swnav__dots' );
		const [ prev, next ] = nav.querySelectorAll( '.wpe-swnav__btn' );
		const items = () => [ ...track.children ];
		const current = () => {
			const x = track.scrollLeft + track.clientWidth / 2;
			let best = 0;
			items().forEach( ( el, i ) => {
				if ( el.offsetLeft - track.offsetLeft <= x ) {
					best = i;
				}
			} );
			return best;
		};
		const go = ( i ) => {
			const el = items()[ Math.max( 0, Math.min( items().length - 1, i ) ) ];
			el && track.scrollTo( { left: el.offsetLeft - track.offsetLeft - ( parseFloat( getComputedStyle( track ).scrollPaddingInlineStart ) || 0 ) } );
		};
		const sync = () => {
			// Only when the swipe layout is active (horizontal scroll) for this device.
			const on = 'auto' === getComputedStyle( track ).overflowX && track.scrollWidth > track.clientWidth + 1;
			nav.hidden = ! on;
			if ( ! on ) {
				return;
			}
			const n = items().length;
			if ( dots.children.length !== n ) {
				dots.innerHTML = '';
				for ( let i = 0; i < n; i++ ) {
					const b = document.createElement( 'button' );
					b.type = 'button';
					b.className = 'wpe-swnav__dot';
					b.setAttribute( 'aria-label', label( 'Go to item' ) + ' ' + ( i + 1 ) );
					b.onclick = () => go( i );
					dots.append( b );
				}
			}
			const c = current();
			[ ...dots.children ].forEach( ( d, i ) => d.setAttribute( 'aria-current', String( i === c ) ) );
			prev.disabled = track.scrollLeft <= 1;
			next.disabled = track.scrollLeft + track.clientWidth >= track.scrollWidth - 1;
		};
		nav.addEventListener( 'click', ( e ) => {
			const d = e.target.closest( '.wpe-swnav__btn' )?.dataset.d;
			d && go( current() + Number( d ) );
		} );
		track.addEventListener( 'scroll', () => requestAnimationFrame( sync ), { passive: true } );
		new ResizeObserver( sync ).observe( track );
	};
	document.querySelectorAll( SEL ).forEach( setup );
} )();
