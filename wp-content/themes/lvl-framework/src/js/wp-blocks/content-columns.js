/**
 * Block Style
 *
 */
function addBlockListStyleAttributes(settings, name) {
    if (typeof settings.attributes !== 'undefined' && (name === 'core/list' || name === 'core/column')) {
        settings.attributes = {
            ...settings.attributes,
            contentOptions: {
                type: 'object',
                properties: {
                    columns: {
                        type: 'number',
                        default: 1,
                    }
                }
            },
            lvlClassList: {
                type: 'object',
                properties: {
                    contentColumns: {
                        type: 'string',
                        default: 'content-columns-1'
                    }
                }
            }
        };
    }

    return settings;
}

wp.hooks.addFilter(
    'blocks.registerBlockType',
    'lvl/block-list-style-attributes',
    addBlockListStyleAttributes
);

const blockListColumnPanel = wp.compose.createHigherOrderComponent(function (BlockEdit) {
    return function (props) {
        var Fragment = wp.element.Fragment;
        var ListStyleControls = wp.components.PanelBody;
        var InspectorControls = wp.blockEditor.InspectorControls;
        var attributes = props.attributes;
        var setAttributes = props.setAttributes;
        var isSelected = props.isSelected;
        let hasContentColumns = (attributes.contentOptions?.columns > 1) ?? false;

        return React.createElement(
            Fragment,
            null,
            React.createElement(BlockEdit, props),
            isSelected
            && ('core/list' === props?.name || 'core/column' === props?.name)
            && React.createElement(                InspectorControls,
                null,
                React.createElement(ListStyleControls, {
                    title: wp.i18n.__('Content Columns', 'lvl'),
                    initialOpen: hasContentColumns,
                    children: [
                        listColumn(props),
                    ]
                })
            )
        );
    };
});

wp.hooks.addFilter(
    'editor.BlockEdit',
    'lvl/list-column-controls',
    blockListColumnPanel
);

const listColumn = function (props) {
    return [
        React.createElement(wp.components.RangeControl, {
            label: wp.i18n.__('Content Columns', 'lvl'),
            value: props?.attributes?.contentOptions?.columns ?? '1',
            onChange: function (value) {
                let obj = props.attributes.contentOptions ?? '1';
                obj = {
                    ...obj,
                    columns: value
                }
                props.setAttributes({contentOptions: obj});

                let className = props?.attributes?.className ?? '';
                if (className) {
                    className = className.replace(/content-columns-[0-9]/g, '');
                }
                className += ' content-columns-' + value;
                props.setAttributes({className: className.trim()});

                props.setAttributes({style: {'--content-columns': value}});
                // props.setAttributes({lvlClassList: {contentColumns: 'content-columns-' + value}});
                // console.log(props.attributes);
            },
            min: 1,
            max: 6,
            step: 1,
            marks: true,
            initialPosition: 1,
            resetFallbackValue: 1,
            allowReset: true
        })
    ];
}