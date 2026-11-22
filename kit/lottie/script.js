( () => {
	const els = document.querySelectorAll( '.wpe-lt[data-src]' );
	if ( ! els.length ) {
		return;
	}
	// The bundled player (lottie-web light, MIT) sits next to this file.
	const lib = document.createElement( 'script' );
	lib.src = document.currentScript.src.replace( /script\.js(\?.*)?$/, 'lottie.min.js$1' );
	lib.onload = () => els.forEach( ( el ) => {
		const reduce = matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
		const anim = window.lottie.loadAnimation( {
			container: el,
			renderer: 'svg',
			path: el.dataset.src,
			loop: '1' === el.dataset.loop && ! reduce,
			autoplay: false,
		} );
		anim.addEventListener( 'data_ready', () => {
			el.style.aspectRatio = anim.animationData.w + '/' + anim.animationData.h;
		} );
		if ( reduce ) {
			anim.addEventListener( 'DOMLoaded', () => anim.goToAndStop( 0, true ) );
		} else if ( 'hover' === el.dataset.trigger ) {
			el.addEventListener( 'mouseenter', () => anim.play() );
			el.addEventListener( 'mouseleave', () => anim.pause() );
		} else if ( 'load' === el.dataset.trigger ) {
			anim.play();
		} else {
			new IntersectionObserver( ( [ e ] ) => ( e.isIntersecting ? anim.play() : anim.pause() ) ).observe( el );
		}
	} );
	document.head.appendChild( lib );
} )();
