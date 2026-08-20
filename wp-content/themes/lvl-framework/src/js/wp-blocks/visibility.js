/**
 * Block Visibility
 *
 */
function addBlockVisibilityAttributes(settings, name) {
	if (typeof settings.attributes !== 'undefined') {
		settings.attributes = {
			...settings.attributes,
			hide: {
				type: 'object',
				properties: {
					desktop: {
						type: 'boolean',
						default: false,
					},
					tablet: {
						type: 'boolean',
						default: false,
					},
					mobile: {
						type: 'boolean',
						default: false,
					}
				}
			}
		};
	}

	return settings;
}

wp.hooks.addFilter(
	'blocks.registerBlockType',
	'lvl/block-visibility-attributes',
	addBlockVisibilityAttributes
);

/**
 * Create the visibility panel and options
 */

var blockVisibilityPanel = wp.compose.createHigherOrderComponent(function (BlockEdit) {

	return function (props) {
		var Fragment = wp.element.Fragment;
		var VisibilityControls = wp.components.PanelBody;
		var InspectorControls = wp.blockEditor.InspectorControls;
		var attributes = props.attributes;
		var setAttributes = props.setAttributes;
		var isSelected = props.isSelected;
		let hasHide = (attributes.hide?.desktop || attributes.hide?.tablet || attributes.hide?.mobile) ?? false;

		return React.createElement(
			Fragment,
			null,
			React.createElement(BlockEdit, props),
			isSelected && React.createElement(
				InspectorControls,
				null,
				React.createElement(VisibilityControls, {
					title: wp.i18n.__('Responsive Visibility', 'lvl'),
					initialOpen: hasHide,
					children: [
						levelVisibility(props, 'desktop'),
						levelVisibility(props, 'tablet'),
						levelVisibility(props, 'mobile'),
					]
				})
			)
		);
	};
}, 'blockVisibilityPanel');

wp.hooks.addFilter(
	'editor.BlockEdit',
	'lvl/visibility-controls',
	blockVisibilityPanel
);

const levelVisibility = function (props, level) {
	const hide = props.attributes.hide?.[level] ?? false;

	const onChange = (val) => {
		const newHide = {
			...(props.attributes.hide || {}),
			[level]: val
		};

		props.setAttributes({ hide: newHide });
	};

	return [
		React.createElement(wp.components.ToggleControl, {
			label: 'Hide on ' + level,
			hideLabelFromVision: false,
			checked: hide,
			value: hide,
			onChange: onChange,
			allowReset: true
		})
	];
}