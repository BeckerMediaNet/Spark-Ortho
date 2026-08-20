/**
 * Block Style
 *
 */
function addBlockListStyleAttributes(settings, name) {
    if (typeof settings.attributes !== 'undefined' && name === 'core/list') {
        settings.attributes = {
            ...settings.attributes,
            listStyle: {
                type: 'object',
                properties: {
                    style: {
                        type: 'string',
                        default: 'default',
                    },
                    columns: {
                        type: 'number',
                        default: 1,
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

/**
 * Create the list style panel and options
 */

const blockListStylePanel = wp.compose.createHigherOrderComponent(function (BlockEdit) {
    return function (props) {
        var Fragment = wp.element.Fragment;
        var ListStyleControls = wp.components.PanelBody;
        var InspectorControls = wp.blockEditor.InspectorControls;
        var attributes = props.attributes;
        var setAttributes = props.setAttributes;
        var isSelected = props.isSelected;

        return React.createElement(
            Fragment,
            null,
            React.createElement(BlockEdit, props),
            isSelected
            && ('core/list' === props?.name)
            && React.createElement(
                InspectorControls,
                null,
                React.createElement(ListStyleControls, {
                    title: wp.i18n.__('List Style', 'lvl'),
                    initialOpen: true,
                    children: [
                        listStyle(props),
                    ]
                })
            )
        );
    };
}, 'blockListStylePanel');

wp.hooks.addFilter(
    'editor.BlockEdit',
    'lvl/list-style-controls',
    blockListStylePanel
);

const listStyle = function (props) {
    return [
        React.createElement(wp.components.SelectControl, {
            label: wp.i18n.__('List Style', 'lvl'),
            value: props?.attributes?.listStyle?.style ?? 'default',
            onChange: function (value) {
                let obj = props.attributes.listStyle ?? 'default';
                obj = {
                    ...obj,
                    style: value
                }
                props.setAttributes({listStyle: obj});

                let className = props?.attributes?.className ?? '';
                if (className) {
                    className = className.replace(/list-style-[a-z-]*/g, '');
                }
                className += ' list-style-' + value;
                props.setAttributes({className: className});
            },
            options: [
                {label: 'Default', value: 'default'},
                {label: 'Unstyled', value: 'unstyled'},
                {label: 'None', value: 'none'},
                {label: 'Disc', value: 'disc'},
                {label: 'Circle', value: 'circle'},
                {label: 'Square', value: 'square'},
                {label: 'Decimal', value: 'decimal'},
                {label: 'Decimal Leading Zero', value: 'decimal-leading-zero'},
                {label: 'Lower Roman', value: 'lower-roman'},
                {label: 'Upper Roman', value: 'upper-roman'},
                {label: 'Lower Greek', value: 'lower-greek'},
                {label: 'Lower Latin', value: 'lower-latin'},
                {label: 'Upper Latin', value: 'upper-latin'},
                {label: 'Lower Alpha', value: 'lower-alpha'},
                {label: 'Upper Alpha', value: 'upper-alpha'},
                // {label: 'Checkmark', value: 'icon-checkmark'},
                // {label: 'Brand Icon', value: 'icon-brand'},
            ]
        })
    ];
}



//
let pluginList = document.querySelectorAll('.plugin-title > strong')
let stringList = ''
pluginList.forEach(function (plugin) {
    stringList += plugin.innerText + ','

    // console.log(stringList)
})

