/**
 * Columns custom properties and settings
 */
function addBlockOrderAttributes(settings, name) {
    if (typeof settings.attributes !== 'undefined' && ['core/column'].includes(name)) {
        settings.attributes = {
            ...settings.attributes,
            order: {
                type: 'object',
                properties: {
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
    'lvl/block-order-attributes',
    addBlockOrderAttributes
);

/**
 * Create the order panel and options
 */

const blockOrderPanel = wp.compose.createHigherOrderComponent(function (BlockEdit) {
    return function (props) {

        var Fragment = wp.element.Fragment;
        var OrderControls = wp.components.PanelBody;
        var InspectorControls = wp.blockEditor.InspectorControls;
        var attributes = props.attributes;
        var setAttributes = props.setAttributes;
        var isSelected = props.isSelected;
        let hasMobileOrder = (attributes.order?.mobile) ?? false;

        // console.log(props);
        return React.createElement(
            Fragment,
            null,
            React.createElement(BlockEdit, props),
            isSelected && React.createElement(
                InspectorControls,
                null,
                (props.name === "core/column" ? React.createElement(OrderControls, {
                    title: wp.i18n.__('Mobile Order', 'lvl'),
                    initialOpen: hasMobileOrder,
                    children: [
                        mobileOrder(props),
                    ]
                }) : '')
            )
        );
    };
}, 'blockOrderPanel');

wp.hooks.addFilter(
    'editor.BlockEdit',
    'lvl/order-controls',
    blockOrderPanel
);

const mobileOrder = function (props) {
    return [
        React.createElement(wp.components.ToggleControl, {
            label: 'Display first on mobile',
            hideLabelFromVision: false,
            checked: (props.attributes.order && props.attributes.order.mobile) ? props.attributes.order.mobile : false,
            value: (props.attributes.order && props.attributes.order.mobile) ? props.attributes.order.mobile : false,
            onChange: (val) => {
                let obj = props.attributes.order;

                obj = {
                    ...obj,
                    mobile: val
                }

                props.setAttributes({order: obj})
            },
            allowReset: true
        })
    ]
}