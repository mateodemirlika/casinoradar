( function () {
	'use strict';

	// Sticky header: gains a translucent, blurred background once the page
	// scrolls past the top, instead of staying a flat opaque bar the whole
	// time. position:sticky (in CSS) is what keeps it from disappearing;
	// this just toggles the look.
	var header = document.querySelector( '.ww-header' );
	if ( header ) {
		var updateHeaderScrollState = function () {
			header.classList.toggle( 'is-scrolled', window.scrollY > 8 );
		};
		updateHeaderScrollState();
		window.addEventListener( 'scroll', updateHeaderScrollState, { passive: true } );
	}

	// Mobile primary nav (burger menu). Markup: .ww-nav-toggle button +
	// #ww-primary-nav, rendered by wagerwise_render_block_site_nav().
	var navToggle = document.querySelector( '.ww-nav-toggle' );
	var primaryNav = document.getElementById( 'ww-primary-nav' );
	if ( navToggle && primaryNav ) {
		var closeNav = function () {
			primaryNav.classList.remove( 'is-open' );
			navToggle.setAttribute( 'aria-expanded', 'false' );
		};
		navToggle.addEventListener( 'click', function () {
			var isOpen = primaryNav.classList.toggle( 'is-open' );
			navToggle.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
		} );
		primaryNav.addEventListener( 'click', function ( e ) {
			if ( 'A' === e.target.tagName ) {
				closeNav();
			}
		} );
		document.addEventListener( 'click', function ( e ) {
			if ( ! primaryNav.contains( e.target ) && ! navToggle.contains( e.target ) ) {
				closeNav();
			}
		} );
		document.addEventListener( 'keydown', function ( e ) {
			if ( 'Escape' === e.key ) {
				closeNav();
			}
		} );
		window.addEventListener( 'resize', function () {
			if ( window.innerWidth > 782 ) {
				closeNav();
			}
		} );
	}

	// 18+ age gate (only present in the DOM when enabled in WagerWise Settings).
	var gate = document.getElementById( 'ww-age-gate' );
	if ( gate ) {
		if ( ! window.localStorage.getItem( 'ww_age_verified' ) ) {
			gate.hidden = false;
			document.body.style.overflow = 'hidden';
		}
		var yesBtn = gate.querySelector( '[data-ww-age-gate="yes"]' );
		if ( yesBtn ) {
			yesBtn.addEventListener( 'click', function () {
				window.localStorage.setItem( 'ww_age_verified', '1' );
				gate.hidden = true;
				document.body.style.overflow = '';
			} );
		}
	}
} )();
