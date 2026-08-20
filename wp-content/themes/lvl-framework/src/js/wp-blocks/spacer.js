function addSpacerAttributes(settings, name) {
	if (typeof settings.attributes !== 'undefined') {

		if (name === 'core/spacer') {

			settings.attributes = Object.assign(settings.attributes, {
				flex_fill : {
					type: 'boolean',
					default: false
				},
			});
		}
	}

	return settings;
}

wp.hooks.addFilter(
	'blocks.registerBlockType',
    'lvl/block-extra-attributes',
	addSpacerAttributes
);

/**
 * Create the SpacerExtras panel and options
 */

var blockSpacerExtrasPanel = wp.compose.createHigherOrderComponent(function (BlockEdit) {

	return function (props) {
		var Fragment = wp.element.Fragment;
		var OutlineControls = wp.components.PanelBody;
		var InspectorControls = wp.blockEditor.InspectorControls;
		var attributes = props.attributes;
		var setAttributes = props.setAttributes;
		var isSelected = props.isSelected;
		// console.log(props)

		return React.createElement(
			Fragment,
			null,
			React.createElement(BlockEdit, props),
			isSelected && (props.name === 'core/spacer') && React.createElement(
				InspectorControls,
				null,
				React.createElement(OutlineControls, {
					title: wp.i18n.__('Extras', 'lvl'),
					initialOpen: true,
					children: [
						flexFillToggle(props),
					],
					// onChange: (val) => {
					// 	setAttributes({ flex_fill: val })
					// }
				})
			)
		);
	};
}, 'blockVisibilityPanel');

wp.hooks.addFilter(
	'editor.BlockEdit',
    'lvl/extra-controls',
	blockSpacerExtrasPanel
);

const flexFillToggle = function (props) {
	return [
		React.createElement(wp.components.ToggleControl, {
			label: 'Fill the remaining space',
			help: 'This will make the spacer fill the remaining space in the container if the container supports it. May not show accurately in Editor',
			hideLabelFromVision: false,
			checked: (props.attributes.flex_fill)? props.attributes.flex_fill : false,
			value: (props.attributes.flex_fill)? props.attributes.flex_fill : false,
			onChange: (val) => {
				props.setAttributes({ flex_fill: val })
				// add remove class "flex-fill" with regex
				let regex = /flex-fill/g;
				let className = props?.attributes?.className?? '';
				if (className) {
					className = className.replace(regex, '').trim();
					// remove double spaces
					className = className.replace(/\s{2,}/g, ' ');
				}
				if (val) {
					className += ' flex-fill';
				}
				props.setAttributes({ className: className });

			}
		})
	]
}