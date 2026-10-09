/**
 * Editor side of the esteban/logo block.
 * Plain JS against WordPress globals, so it runs without a build step.
 */
( function ( blocks, element, blockEditor, components, i18n ) {
	const el = element.createElement;
	const { useBlockProps, InspectorControls } = blockEditor;
	const { PanelBody, TextControl, ToggleControl } = components;
	const __ = i18n.__;

	blocks.registerBlockType( 'esteban/logo', {
		edit( { attributes, setAttributes } ) {
			const blockProps = useBlockProps( { className: 'ep-logo' } );
			return el(
				element.Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Logo', 'esteban-portfolio' ) },
						el( TextControl, {
							label: __( 'Initials', 'esteban-portfolio' ),
							value: attributes.text,
							onChange: ( text ) => setAttributes( { text } ),
							__nextHasNoMarginBottom: true,
						} ),
						el( ToggleControl, {
							label: __( 'Blinking caret', 'esteban-portfolio' ),
							checked: attributes.showCaret,
							onChange: ( showCaret ) => setAttributes( { showCaret } ),
							__nextHasNoMarginBottom: true,
						} )
					)
				),
				el(
					'span',
					blockProps,
					el(
						'span',
						{ className: 'ep-logo__chip' },
						el( 'span', { className: 'ep-logo__bracket ep-logo__bracket--open' }, '<' ),
						el( 'span', null, attributes.text ),
						el( 'span', { className: 'ep-logo__bracket ep-logo__bracket--close' }, '/>' ),
						attributes.showCaret ? el( 'span', { className: 'ep-logo__caret' } ) : null
					)
				)
			);
		},
		save: () => null, // Rendered on the server by render.php.
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.i18n );
