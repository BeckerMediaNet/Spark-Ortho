import './wp-blocks/image';

(function (wp) {
    var registerFormatType = wp.richText.registerFormatType;
    var createElement = wp.element.createElement;
    var Fragment = wp.element.Fragment;
    var BlockControls = wp.blockEditor.BlockControls;
    var ToolbarGroup = wp.components.ToolbarGroup;
    var ToolbarButton = wp.components.ToolbarButton;
    var ColorPalette = wp.components.ColorPalette;
    var Popover = wp.components.Popover;
    var useState = wp.element.useState;
    var useSelect = wp.data.useSelect;
    var useDispatch = wp.data.useDispatch;

    function ColorSwatch({ color }) {
        return createElement('span', {
            style: {
                display: 'inline-block',
                width: '24px',
                height: '24px',
                background: color || 'transparent',
                border: '1px solid #ccc',
                borderRadius: '50%'
            }
        });
    }

    function AccentColorControl(props) {
        var [isColorPickerVisible, setColorPickerVisible] = useState(false);
        var [anchorRef, setAnchorRef] = useState(null);

        var colors = useSelect(function(select) {
            return select('core/block-editor').getSettings().colors;
        }, []);

        var selectedBlock = useSelect(function(select) {
            return select('core/block-editor').getSelectedBlock();
        }, []);

        var { updateBlockAttributes } = useDispatch('core/block-editor');

        var currentColor = selectedBlock && selectedBlock.attributes.className
            ? colors.find(c => selectedBlock.attributes.className.includes(`has-${c.slug}-accent-color`))?.color
            : null;

        function onToggle(event) {
            setColorPickerVisible(!isColorPickerVisible);
            setAnchorRef(event.currentTarget);
        }

        function onColorChange(color) {
            if (selectedBlock && selectedBlock.name === 'core/heading') {
                var colorObj = colors.find(c => c.color === color);
                var newClassName = '';
                if (colorObj) {
                    newClassName = 'has-accent has-' + colorObj.slug + '-accent-color';
                }
                updateBlockAttributes(selectedBlock.clientId, { className: newClassName });
            }
            setColorPickerVisible(false);
        }

        if (!selectedBlock || selectedBlock.name !== 'core/heading') {
            return null;
        }

        return createElement(
            Fragment,
            null,
            createElement(
                BlockControls,
                null,
                createElement(
                    ToolbarGroup,
                    null,
                    createElement(
                        ToolbarButton,
                        {
                            icon: createElement(ColorSwatch, { color: currentColor }),
                            title: 'Accent Color',
                            onClick: onToggle
                        }
                    )
                )
            ),
            isColorPickerVisible && createElement(
                Popover,
                {
                    onClose: function() { setColorPickerVisible(false); },
                    className: 'custom-accent-color-popover',
                    anchor: anchorRef,
                    position: "bottom center"
                },
                createElement(
                    'div',
                    { style: { padding: '12px' } },
                    createElement(ColorPalette, {
                        colors: colors,
                        value: currentColor,
                        onChange: onColorChange,
                        disableCustomColors: true,
                        clearable: true
                    })
                )
            )
        );
    }

    registerFormatType('custom/accent-color', {
        title: 'Accent Color',
        tagName: 'span',
        className: 'accent-color',
        edit: AccentColorControl
    });
})(window.wp);