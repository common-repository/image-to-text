import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';
import { SelectControl, RangeControl, TextControl, ToggleControl, PanelBody } from '@wordpress/components';
import { InspectorControls, PanelColorSettings, useBlockProps } from '@wordpress/block-editor';

export default function Edit( { attributes, setAttributes } ) {

	let options_imagetotext = [];
	for ( let i = 0;  i < imagetotext_file.length;  i++ ) {
		options_imagetotext.push( { label: imagetotext_file[i], value: imagetotext_file[i], } );
	}

	const blockProps = useBlockProps();
	return (
		<div { ...blockProps }>
			<ServerSideRender
				block = 'image-to-text/imagetotext-block'
				attributes = { attributes }
			/>
			<TextControl
				label = { __( 'Text', 'image-to-text' ) }
				value = { attributes.text }
				onChange = { ( value ) => setAttributes( { text: value } ) }
			/>

			<InspectorControls>
				<PanelBody title = { __( 'View', 'image-to-text' ) } initialOpen = { false }>
					<TextControl
						label = { __( 'Text', 'image-to-text' ) }
						value = { attributes.text }
						onChange = { ( value ) => setAttributes( { text: value } ) }
					/>
					<ToggleControl
						label = { __( 'alt text', 'image-to-text' ) }
						checked = { attributes.alt }
						onChange = { ( value ) => setAttributes( { alt: value } ) }
					/>
					<TextControl
						label = { __( 'alt text', 'image-to-text' ) }
						value = { attributes.alt_text }
						onChange = { ( value ) => setAttributes( { alt_text: value } ) }
					/>
					<PanelColorSettings
						title = { __( 'Color Settings', 'image-to-text' ) }
						colorSettings = { [
							{
								value: attributes.back_color,
								onChange: ( colorValue ) => setAttributes( { back_color: colorValue } ),
								label: __( 'Back Color', 'image-to-text' ),
							},
							{
								value: attributes.font_color,
								onChange: ( colorValue ) => setAttributes( { font_color: colorValue } ),
								label: __( 'Font Color', 'image-to-text' ),
							},
						] }
					>
					</PanelColorSettings>
				</PanelBody>
				<PanelBody title = { __( 'Font', 'image-to-text' ) } initialOpen = { false }>
					<RangeControl
						label = { __( 'Font Size', 'image-to-text' ) }
						max = { 100 }
						min = { 3 }
						value = { attributes.font_size }
						onChange = { ( value ) => setAttributes( { font_size: value } ) }
					/>
					<SelectControl
						label = { __( 'Font file', 'image-to-text' ) }
						value = { attributes.font_file }
						options = { options_imagetotext }
						onChange = { ( value ) => { setAttributes( { font_file: value } ) } }
					/>
					<RangeControl
						label = { __( 'Font baseline adjust', 'image-to-text' ) }
						max = { 1.45 }
						min = { 1.20 }
						step = { 0.01 }
						value = { attributes.vt_mg }
						onChange = { ( value ) => setAttributes( { vt_mg: value } ) }
					/>
				</PanelBody>
			</InspectorControls>
		</div>
	);
}
