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

	registerBlockType( 'wagerwise/top-casinos', {
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
		edit: withServerPreview( 'wagerwise/pros-cons', null ),
		save: function () { return null; },
	} );

	registerBlockType( 'wagerwise/cta-button', {
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
		edit: withServerPreview( 'wagerwise/stats-strip', null ),
		save: function () { return null; },
	} );

	registerBlockType( 'wagerwise/provider-strip', {
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
} )( window.wp );
