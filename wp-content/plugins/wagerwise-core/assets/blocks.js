/**
 * Editor-side registration for the WagerWise custom blocks.
 * Deliberately plain wp.element (no JSX/build step) — the front end is
 * always rendered server-side (PHP render_callback), so the editor only
 * needs an Inspector UI + a live ServerSideRender preview.
 */
( function ( wp ) {
	var el = wp.element.createElement;
	var registerBlockType = wp.blocks.registerBlockType;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var ServerSideRender = wp.serverSideRender;
	var PanelBody = wp.components.PanelBody;
	var TextControl = wp.components.TextControl;
	var RangeControl = wp.components.RangeControl;
	var ToggleControl = wp.components.ToggleControl;
	var __ = wp.i18n.__;

	function withServerPreview( name, controlsFn ) {
		return function ( props ) {
			var blockProps = useBlockProps();
			return el(
				'div',
				blockProps,
				controlsFn ? el( InspectorControls, {}, controlsFn( props ) ) : null,
				el( ServerSideRender, { block: name, attributes: props.attributes } )
			);
		};
	}

	function numberControl( label, key, props, min, max ) {
		return el( RangeControl, {
			label: label,
			value: props.attributes[ key ],
			min: min || 1,
			max: max || 20,
			onChange: function ( value ) {
				var attrs = {};
				attrs[ key ] = value;
				props.setAttributes( attrs );
			},
		} );
	}

	// registerBlockType() silently no-ops (just a console.warn) without a
	// title — every block below was missing one, so none of them were ever
	// actually registered client-side; the editor showed all of them as
	// "doesn't include support for this block" even though the PHP side
	// (server-side rendering, both on the front end and in the
	// ServerSideRender preview below) worked fine the whole time.

	registerBlockType( 'wagerwise/top-casinos', {
		title: __( 'Top Casinos', 'wagerwise' ),
		category: 'widgets',
		attributes: {
			number: { type: 'number', default: 5 },
			categoryId: { type: 'number', default: 0 },
			featuredOnly: { type: 'boolean', default: false },
			layout: { type: 'string', default: 'list' },
		},
		edit: withServerPreview( 'wagerwise/top-casinos', function ( props ) {
			return el(
				PanelBody,
				{ title: __( 'Top Casinos', 'wagerwise' ) },
				numberControl( __( 'Number to show', 'wagerwise' ), 'number', props ),
				el( ToggleControl, {
					label: __( 'Featured only', 'wagerwise' ),
					checked: props.attributes.featuredOnly,
					onChange: function ( value ) { props.setAttributes( { featuredOnly: value } ); },
				} )
			);
		} ),
		save: function () { return null; },
	} );

	registerBlockType( 'wagerwise/casino-comparison-table', {
		title: __( 'Casino Comparison Table', 'wagerwise' ),
		category: 'widgets',
		attributes: {
			number: { type: 'number', default: 5 },
			categoryId: { type: 'number', default: 0 },
		},
		edit: withServerPreview( 'wagerwise/casino-comparison-table', function ( props ) {
			return el(
				PanelBody,
				{ title: __( 'Comparison Table', 'wagerwise' ) },
				numberControl( __( 'Number of casinos', 'wagerwise' ), 'number', props )
			);
		} ),
		save: function () { return null; },
	} );

	registerBlockType( 'wagerwise/bonus-grid', {
		title: __( 'Bonus Grid', 'wagerwise' ),
		category: 'widgets',
		attributes: {
			number: { type: 'number', default: 6 },
			bonusTypeId: { type: 'number', default: 0 },
		},
		edit: withServerPreview( 'wagerwise/bonus-grid', function ( props ) {
			return el(
				PanelBody,
				{ title: __( 'Bonus Grid', 'wagerwise' ) },
				numberControl( __( 'Number of bonuses', 'wagerwise' ), 'number', props )
			);
		} ),
		save: function () { return null; },
	} );

	registerBlockType( 'wagerwise/pros-cons', {
		title: __( 'Pros & Cons', 'wagerwise' ),
		category: 'widgets',
		attributes: {},
		edit: withServerPreview( 'wagerwise/pros-cons', null ),
		save: function () { return null; },
	} );

	registerBlockType( 'wagerwise/cta-button', {
		title: __( 'CTA Button', 'wagerwise' ),
		category: 'widgets',
		attributes: {
			url: { type: 'string', default: '' },
			label: { type: 'string', default: 'Play Now' },
		},
		edit: withServerPreview( 'wagerwise/cta-button', function ( props ) {
			return el(
				PanelBody,
				{ title: __( 'CTA Button', 'wagerwise' ) },
				el( TextControl, {
					label: __( 'Label', 'wagerwise' ),
					value: props.attributes.label,
					onChange: function ( value ) { props.setAttributes( { label: value } ); },
				} ),
				el( TextControl, {
					label: __( 'URL (blank = use casino affiliate link)', 'wagerwise' ),
					value: props.attributes.url,
					onChange: function ( value ) { props.setAttributes( { url: value } ); },
				} )
			);
		} ),
		save: function () { return null; },
	} );

	registerBlockType( 'wagerwise/blog-grid', {
		title: __( 'Blog Grid', 'wagerwise' ),
		category: 'widgets',
		attributes: {
			number: { type: 'number', default: 3 },
			categoryId: { type: 'number', default: 0 },
		},
		edit: withServerPreview( 'wagerwise/blog-grid', function ( props ) {
			return el(
				PanelBody,
				{ title: __( 'Blog Grid', 'wagerwise' ) },
				numberControl( __( 'Number of posts', 'wagerwise' ), 'number', props, 1, 12 )
			);
		} ),
		save: function () { return null; },
	} );

	registerBlockType( 'wagerwise/stats-strip', {
		title: __( 'Stats Strip', 'wagerwise' ),
		category: 'widgets',
		attributes: {},
		edit: withServerPreview( 'wagerwise/stats-strip', null ),
		save: function () { return null; },
	} );

	registerBlockType( 'wagerwise/provider-strip', {
		title: __( 'Provider Strip', 'wagerwise' ),
		category: 'widgets',
		attributes: {
			number: { type: 'number', default: 8 },
		},
		edit: withServerPreview( 'wagerwise/provider-strip', function ( props ) {
			return el(
				PanelBody,
				{ title: __( 'Provider Strip', 'wagerwise' ) },
				numberControl( __( 'Number of providers', 'wagerwise' ), 'number', props, 1, 20 )
			);
		} ),
		save: function () { return null; },
	} );

	registerBlockType( 'wagerwise/category-strip', {
		title: __( 'Taxonomy Strip', 'wagerwise' ),
		category: 'widgets',
		attributes: {
			taxonomy: { type: 'string', default: 'casino_category' },
			style: { type: 'string', default: 'chip' },
		},
		edit: withServerPreview( 'wagerwise/category-strip', function ( props ) {
			return el(
				PanelBody,
				{ title: __( 'Taxonomy Strip', 'wagerwise' ) },
				el( TextControl, {
					label: __( 'Taxonomy slug', 'wagerwise' ),
					help: __( 'e.g. casino_category, country, payment_method, licence, software_provider, game_category, bonus_type', 'wagerwise' ),
					value: props.attributes.taxonomy,
					onChange: function ( value ) { props.setAttributes( { taxonomy: value } ); },
				} )
			);
		} ),
		save: function () { return null; },
	} );

	registerBlockType( 'wagerwise/game-grid', {
		title: __( 'Game Grid', 'wagerwise' ),
		category: 'widgets',
		attributes: {
			number: { type: 'number', default: 8 },
			categoryId: { type: 'number', default: 0 },
			providerId: { type: 'number', default: 0 },
		},
		edit: withServerPreview( 'wagerwise/game-grid', function ( props ) {
			return el(
				PanelBody,
				{ title: __( 'Game Grid', 'wagerwise' ) },
				numberControl( __( 'Number of games', 'wagerwise' ), 'number', props, 1, 20 )
			);
		} ),
		save: function () { return null; },
	} );

	registerBlockType( 'wagerwise/review-grid', {
		title: __( 'Editorial Review Grid', 'wagerwise' ),
		category: 'widgets',
		attributes: {
			number: { type: 'number', default: 6 },
		},
		edit: withServerPreview( 'wagerwise/review-grid', function ( props ) {
			return el(
				PanelBody,
				{ title: __( 'Editorial Review Grid', 'wagerwise' ) },
				numberControl( __( 'Number of reviews', 'wagerwise' ), 'number', props, 1, 12 )
			);
		} ),
		save: function () { return null; },
	} );

	registerBlockType( 'wagerwise/hero-search', {
		title: __( 'Hero', 'wagerwise' ),
		category: 'widgets',
		attributes: {
			heading: { type: 'string', default: '' },
			subheading: { type: 'string', default: '' },
		},
		edit: withServerPreview( 'wagerwise/hero-search', function ( props ) {
			return el(
				PanelBody,
				{ title: __( 'Hero', 'wagerwise' ) },
				el( TextControl, {
					label: __( 'Heading (blank = use WagerWise Settings default)', 'wagerwise' ),
					value: props.attributes.heading,
					onChange: function ( value ) { props.setAttributes( { heading: value } ); },
				} ),
				el( TextControl, {
					label: __( 'Subheading', 'wagerwise' ),
					value: props.attributes.subheading,
					onChange: function ( value ) { props.setAttributes( { subheading: value } ); },
				} )
			);
		} ),
		save: function () { return null; },
	} );

	registerBlockType( 'wagerwise/tournament-grid', {
		title: __( 'Tournament Grid', 'wagerwise' ),
		category: 'widgets',
		attributes: {
			number: { type: 'number', default: 3 },
		},
		edit: withServerPreview( 'wagerwise/tournament-grid', function ( props ) {
			return el(
				PanelBody,
				{ title: __( 'Tournament Grid', 'wagerwise' ) },
				numberControl( __( 'Number of tournaments', 'wagerwise' ), 'number', props, 1, 12 )
			);
		} ),
		save: function () { return null; },
	} );
} )( window.wp );
