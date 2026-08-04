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
	//
	// Also skipped while the language switcher is open: its mobile panel is
	// position:fixed, anchored to the viewport — but a `transform` on ANY
	// ancestor (which .ww-header--hidden applies, to slide the header away)
	// creates a new containing block for fixed descendants, so if that class
	// lands while the switcher's already open, the panel would suddenly
	// reposition itself relative to the (now off-screen) header instead of
	// the viewport, i.e. break.
	var header = document.querySelector( '.ww-header' );
	// Back-to-top button: appears the moment the header hides (same signal,
	// so it shows up right as the nav's screen space frees up), scrolls
	// smoothly to the top on click.
	var backToTop = document.getElementById( 'ww-back-to-top' );
	if ( header ) {
		var lastScrollY = window.scrollY;
		var updateHeaderScrollState = function () {
			var currentScrollY = window.scrollY;
			header.classList.toggle( 'is-scrolled', currentScrollY > 8 );

			var navIsOpen = primaryNav && primaryNav.classList.contains( 'is-open' );
			var langSwitcherIsOpen = !! header.querySelector( '.ww-lang-switcher.is-open' );
			if ( currentScrollY <= header.offsetHeight || navIsOpen || langSwitcherIsOpen ) {
				header.classList.remove( 'ww-header--hidden' );
			} else if ( currentScrollY > lastScrollY ) {
				header.classList.add( 'ww-header--hidden' );
			} else if ( currentScrollY < lastScrollY ) {
				header.classList.remove( 'ww-header--hidden' );
			}
			lastScrollY = currentScrollY;

			if ( backToTop ) {
				backToTop.classList.toggle( 'is-visible', header.classList.contains( 'ww-header--hidden' ) );
			}
		};
		updateHeaderScrollState();
		window.addEventListener( 'scroll', updateHeaderScrollState, { passive: true } );
	}
	if ( backToTop ) {
		backToTop.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			window.scrollTo( { top: 0, behavior: 'smooth' } );
		} );

		// Keeps the button clear of the footer (5px gap above it) once it
		// scrolls into view, instead of overlapping it at the usual offset.
		var footer = document.querySelector( '.ww-footer' );
		if ( footer ) {
			var mobileQuery = window.matchMedia( '(max-width: 782px)' );
			var updateBackToTopPosition = function () {
				var baseGap = mobileQuery.matches ? 16 : 24;
				var overlap = window.innerHeight - footer.getBoundingClientRect().top;
				backToTop.style.setProperty( '--ww-btt-bottom', Math.max( baseGap, overlap + 5 ) + 'px' );
			};
			updateBackToTopPosition();
			window.addEventListener( 'scroll', updateBackToTopPosition, { passive: true } );
			window.addEventListener( 'resize', updateBackToTopPosition );
		}
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

	// Language switcher: floating dropdown on desktop, bottom sheet on
	// mobile (CSS handles which, based on viewport — this only drives the
	// shared .is-open toggle, focus management, keyboard nav and the
	// optional search filter). The language list itself is rendered
	// server-side from Polylang's configured languages, nothing here is
	// hardcoded to a specific language or count.
	document.querySelectorAll( '[data-ww-lang-switcher]' ).forEach( function ( root ) {
		var trigger = root.querySelector( '.ww-lang-switcher__trigger' );
		var search = root.querySelector( '.ww-lang-switcher__search' );
		var emptyMsg = root.querySelector( '.ww-lang-switcher__empty' );
		var items = Array.prototype.slice.call( root.querySelectorAll( '.ww-lang-switcher__item' ) );
		if ( ! trigger || ! items.length ) {
			return;
		}

		var isOpen = false;
		var openWidth = null;

		var visibleLinks = function () {
			return items.filter( function ( li ) { return ! li.hidden; } )
				.map( function ( li ) { return li.querySelector( 'a' ); } );
		};

		var focusAt = function ( index, links ) {
			links = links || visibleLinks();
			if ( ! links.length ) {
				return;
			}
			links[ Math.max( 0, Math.min( index, links.length - 1 ) ) ].focus();
		};

		var filterList = function ( query ) {
			query = query.trim().toLowerCase();
			var count = 0;
			items.forEach( function ( li ) {
				var isMatch = ! query || ( li.getAttribute( 'data-search' ) || '' ).indexOf( query ) !== -1;
				li.hidden = ! isMatch;
				count += isMatch ? 1 : 0;
			} );
			if ( emptyMsg ) {
				emptyMsg.hidden = count !== 0;
			}
		};

		var open = function () {
			if ( isOpen ) {
				return;
			}
			isOpen = true;
			openWidth = window.innerWidth;
			root.classList.add( 'is-open' );
			trigger.setAttribute( 'aria-expanded', 'true' );
			if ( search ) {
				search.value = '';
				filterList( '' );
				// Wait for the open transition to be under way so mobile
				// Safari doesn't jump-scroll the page to bring the
				// about-to-be-focused (still translating into place) input
				// into view.
				window.setTimeout( function () { search.focus(); }, 60 );
			} else {
				var current = root.querySelector( '.ww-lang-switcher__item.is-active a' ) || visibleLinks()[ 0 ];
				if ( current ) {
					window.setTimeout( function () { current.focus(); }, 60 );
				}
			}
		};

		var close = function ( returnFocus ) {
			if ( ! isOpen ) {
				return;
			}
			isOpen = false;
			root.classList.remove( 'is-open' );
			trigger.setAttribute( 'aria-expanded', 'false' );
			if ( false !== returnFocus ) {
				trigger.focus();
			}
		};

		trigger.addEventListener( 'click', function () {
			isOpen ? close() : open(); // eslint-disable-line no-unused-expressions
		} );

		var backdrop = root.querySelector( '[data-ww-lang-backdrop]' );
		if ( backdrop ) {
			backdrop.addEventListener( 'click', function () { close(); } );
		}

		document.addEventListener( 'click', function ( e ) {
			if ( isOpen && ! root.contains( e.target ) ) {
				close( false );
			}
		} );

		// Width-only check: focusing the search input below pops the on-
		// screen keyboard on touch devices, which fires a resize event too
		// (viewport height shrinks) — closing the panel the instant it
		// opened. A real orientation change or window resize changes the
		// width, which the keyboard never does, so that's what this
		// actually needs to react to.
		window.addEventListener( 'resize', function () {
			if ( isOpen && window.innerWidth !== openWidth ) {
				close( false );
			}
		} );

		document.addEventListener( 'keydown', function ( e ) {
			if ( ! isOpen ) {
				return;
			}
			if ( 'Escape' === e.key ) {
				e.preventDefault();
				close();
				return;
			}
			if ( 'Tab' === e.key ) {
				var focusable = ( search ? [ search ] : [] ).concat( visibleLinks() );
				if ( ! focusable.length ) {
					return;
				}
				var first = focusable[ 0 ];
				var last = focusable[ focusable.length - 1 ];
				if ( e.shiftKey && document.activeElement === first ) {
					e.preventDefault();
					last.focus();
				} else if ( ! e.shiftKey && document.activeElement === last ) {
					e.preventDefault();
					first.focus();
				}
				return;
			}
			// Everything below is list navigation — let the search input
			// handle its own normal typing/caret movement, except
			// ArrowDown, which hands focus off to the first result.
			if ( document.activeElement === search && 'ArrowDown' !== e.key ) {
				return;
			}
			var links = visibleLinks();
			var index = links.indexOf( document.activeElement );
			if ( 'ArrowDown' === e.key ) {
				e.preventDefault();
				focusAt( index + 1, links );
			} else if ( 'ArrowUp' === e.key ) {
				e.preventDefault();
				if ( index <= 0 ) {
					search ? search.focus() : focusAt( links.length - 1, links ); // eslint-disable-line no-unused-expressions
				} else {
					focusAt( index - 1, links );
				}
			} else if ( 'Home' === e.key ) {
				e.preventDefault();
				focusAt( 0, links );
			} else if ( 'End' === e.key ) {
				e.preventDefault();
				focusAt( links.length - 1, links );
			}
		} );

		if ( search ) {
			search.addEventListener( 'input', function () { filterList( search.value ); } );
		}
	} );
} )();
