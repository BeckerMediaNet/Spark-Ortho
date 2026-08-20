// wp editor panel for "Pin to top"
// "sticky" is a boolean attribute that can be added to any block


// create a panel to include all the options for the sticky attribute and container fluid attribute

function addBlockSectionWrapperAttribute(settings, name) {
    if (name !== 'lvl/section-wrapper') {
        return settings;
    }

    settings.attributes = {
        ...settings.attributes,
        sticky: {
            type: 'boolean',
            default: false,
        },
        containerFluid: {
            type: 'boolean',
            default: false,
        },
        stretchedBackground: {
            type: 'boolean',
            default: false,
        },
    }

    return settings;
}

wp.hooks.addFilter(
    'blocks.registerBlockType',
    'lvl/block-section-wrapper-attribute',
    addBlockSectionWrapperAttribute
);

const blockSectionWrapperPanel = wp.compose.createHigherOrderComponent(function (BlockEdit) {

    return function (props) {
        var Fragment = wp.element.Fragment;
        var StickyControls = wp.components.PanelBody;
        var InspectorControls = wp.blockEditor.InspectorControls;
        var attributes = props.attributes;
        var setAttributes = props.setAttributes;
        var isSelected = props.isSelected;

        let isSticky = attributes?.sticky ?? false;
        let isContainerFluid = attributes?.containerFluid ?? false;
        let isStretchedBackground = attributes?.stretchedBackground ?? false;

        return React.createElement(
            Fragment,
            null,
            React.createElement(BlockEdit, props),
            isSelected && React.createElement(
                InspectorControls,
                null,
                (props.name === "lvl/section-wrapper" ?
                    React.createElement(StickyControls, {
                            title: wp.i18n.__('More Options'),
                            initialOpen: isSticky || isContainerFluid || isStretchedBackground,
                        },
                        React.createElement(wp.components.ToggleControl, {
                            label: wp.i18n.__('Stick to Top'),
                            help: isSticky
                                ? wp.i18n.__('This will stick to the top of its container when scrolling.')
                                : wp.i18n.__('This will not stick to the top of its container when scrolling.')
                            ,
                            checked: attributes?.sticky,
                            onChange: function onChange(value) {
                                props.setAttributes({sticky: value});

                                let className = attributes?.className ?? '';
                                if (className) {
                                    className = className.replace(/sticky-top-most/g, '');
                                }
                                if (value)
                                    className += ' sticky-top-most';

                                props.setAttributes({className: className});
                            }
                        }),
                        React.createElement(wp.components.ToggleControl, {
                            label: wp.i18n.__('Container Fluid'),
                            help: isContainerFluid
                                ? wp.i18n.__('This will make the section full width.')
                                : wp.i18n.__('This will make the section contained width.')
                            ,
                            checked: attributes?.containerFluid,
                            onChange: function onChange(value) {
                                props.setAttributes({containerFluid: value});
                            }
                        }),
                        React.createElement(wp.components.ToggleControl, {
                            label: wp.i18n.__('Stretch Background (Nested Sections Only)'),
                            help: isStretchedBackground
                                ? wp.i18n.__('This will stretch the background to the edge of the screen when nested within other blocks.')
                                : wp.i18n.__('This will not stretch the background to the edge of the screen when nested within other blocks.')
                            ,
                            checked: attributes?.stretchedBackground,
                            onChange: function onChange(value) {
                                props.setAttributes({stretchedBackground: value});
                            }
                        })
                    ) : '')
            )
        );
    };
});

wp.hooks.addFilter(
    'editor.BlockEdit',
    'lvl/section-wrapper-controls',
    blockSectionWrapperPanel
);
