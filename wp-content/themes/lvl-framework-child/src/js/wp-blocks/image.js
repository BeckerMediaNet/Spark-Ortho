const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { InspectorAdvancedControls } = wp.blockEditor;
const { ToggleControl, Notice } = wp.components;
const { Fragment } = wp.element;

// Register fetchPriorityHigh attribute on core/image
addFilter(
    'blocks.registerBlockType',
    'lvl-child/image-fetch-priority',
    (settings, name) => {
        if (name !== 'core/image') return settings;

        return {
            ...settings,
            attributes: {
                ...settings.attributes,
                fetchPriorityHigh: {
                    type: 'boolean',
                    default: false,
                },
            },
        };
    }
);

// Add toggle to the Advanced tab of the Image block
const withFetchPriorityControl = createHigherOrderComponent((BlockEdit) => {
    return (props) => {
        if (props.name !== 'core/image') {
            return wp.element.createElement(BlockEdit, props);
        }

        const { attributes, setAttributes } = props;
        const { fetchPriorityHigh } = attributes;

        return wp.element.createElement(
            Fragment,
            null,
            wp.element.createElement(BlockEdit, props),
            wp.element.createElement(
                InspectorAdvancedControls,
                null,
                wp.element.createElement(ToggleControl, {
                    label: 'Fetch Priority: High',
                    checked: !!fetchPriorityHigh,
                    onChange: (value) => setAttributes({ fetchPriorityHigh: value }),
                    help: 'Improves LCP by telling the browser to load this image sooner. Use for above-the-fold images — especially hero images.',
                })
            )
        );
    };
}, 'withFetchPriorityControl');

addFilter(
    'editor.BlockEdit',
    'lvl-child/image-fetch-priority-control',
    withFetchPriorityControl
);
