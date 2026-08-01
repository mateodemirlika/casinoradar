( function () {
	'use strict';

	// Mobile primary nav (burger menu). Markup: .ww-nav-toggle button +
	// #ww-primary-nav, rendered by wagerwise_render_block_site_nav().
	var navToggle = document.querySelector( '.ww-nav-toggle' );
	var primaryNav = document.getElementById( 'ww-primary-nav' );

	// Sticky header: gains a translucent, blurred background once the page
	// scrolls past the top (position:sticky, in CSS, is what keeps it from
	// disappearing), and slides out of view on scroll-down / back in on
	// scroll-up to give scrolling content more room. Skipped while the
	// mobile menu is open, so it doesn't slide away out from under someone
	// mid-scroll through the open menu, and skipped until scrollY passes
	// the header's own height — sticky elements don't reflow content into
	// their space when hidden via transform, so hiding any earlier leaves
	// a blank gap the size of the not-yet-scrolled-past remainder.
	var header = document.querySelector( '.ww-header' );
	if ( header ) {
		var lastScrollY = window.scrollY;
		var updateHeaderScrollState = function () {
			var currentScrollY = window.scrollY;
			header.classList.toggle( 'is-scrolled', currentScrollY > 8 );

			var navIsOpen = primaryNav && primaryNav.classList.contains( 'is-open' );
			if ( currentScrollY <= header.offsetHeight || navIsOpen ) {
				header.classList.remove( 'ww-header--hidden' );
			} else if ( currentScrollY > lastScrollY ) {
				header.classList.add( 'ww-header--hidden' );
			} else if ( currentScrollY < lastScrollY ) {
				header.classList.remove( 'ww-header--hidden' );
			}
			lastScrollY = currentScrollY;
		};
		updateHeaderScrollState();
		window.addEventListener( 'scroll', updateHeaderScrollState, { passive: true } );
	}
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

	// Category-strip filters: filter cards already on the page instead of
	// navigating to the term archive, as long as both a strip and a matching
	// grid are present within the same archive container (progressive
	// enhancement — with JS disabled the pills' real hrefs still work as
	// normal links to the taxonomy archives). Scoped to a container because
	// the homepage reuses both the strip and a "Top Picks" grid block as two
	// separate, unrelated sections — without scoping, clicking a homepage
	// category pill would wrongly filter that unrelated widget instead of
	// navigating.
	var initCategoryFilter = function ( containerSelector, taxonomy, gridSelector, cardSelector, dataAttr ) {
		var container = document.querySelector( containerSelector );
		if ( ! container ) {
			return;
		}
		var strip = container.querySelector( '.ww-category-strip[data-taxonomy="' + taxonomy + '"]' );
		var grid = container.querySelector( gridSelector );
		if ( ! strip || ! grid ) {
			return;
		}
		var pills = strip.querySelectorAll( '.ww-category-pill' );
		var cards = grid.querySelectorAll( cardSelector );

		// Small/curated grids (e.g. the homepage's 3 featured casinos) can
		// easily have zero cards in a given category — show a message
		// instead of just leaving an empty gap.
		var emptyMsg = document.createElement( 'p' );
		emptyMsg.className = 'ww-filter-empty';
		emptyMsg.hidden = true;
		emptyMsg.textContent = ( window.wagerwiseI18n && window.wagerwiseI18n.filterEmpty ) || 'No matches in this category yet.';
		grid.insertAdjacentElement( 'afterend', emptyMsg );

		pills.forEach( function ( pill ) {
			pill.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				var slug = pill.getAttribute( 'data-term-slug' ) || '';

				pills.forEach( function ( p ) {
					p.classList.remove( 'is-active' );
				} );
				pill.classList.add( 'is-active' );

				var visibleCount = 0;
				cards.forEach( function ( card ) {
					var terms = ( card.getAttribute( dataAttr ) || '' ).split( ' ' );
					var matches = ! slug || terms.indexOf( slug ) !== -1;
					card.classList.toggle( 'is-hidden', ! matches );
					if ( matches ) {
						visibleCount++;
					}
				} );
				emptyMsg.hidden = visibleCount !== 0;
			} );
		} );
	};

	initCategoryFilter( '.ww-archive-bonus', 'bonus_type', '.ww-bonus-grid', '.ww-bonus-card', 'data-bonus-types' );
	initCategoryFilter( '.ww-archive-casino', 'casino_category', '.ww-top-casinos', '.ww-casino-card', 'data-casino-categories' );
	initCategoryFilter( '.ww-front-page', 'casino_category', '.ww-top-casinos', '.ww-casino-card', 'data-casino-categories' );
} )();
