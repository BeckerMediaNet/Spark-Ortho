//
// PLUGIN SIDEBAR
(function (wp) {
    let registerPlugin = wp.plugins.registerPlugin;
    let PluginSidebar = wp.editor.PluginSidebar;
    let PluginSidebarMoreMenuItem = wp.editor.PluginSidebarMoreMenuItem;
    let PanelBody = wp.components.PanelBody;
    let ToggleControl = wp.components.ToggleControl;
    let withSelect = wp.data.withSelect;
    let withDispatch = wp.data.withDispatch;
    let compose = wp.compose.compose;

    let BlockHintsToggle = compose(
        withSelect(function (select) {
            return {
                isChecked: select('core/edit-post').isFeatureActive('blockHints'),
            };
        }),
        withDispatch(function (dispatch) {
            return {
                onChange: function (isChecked) {
                    dispatch('core/edit-post').toggleFeature('blockHints');
                    let blockEditorDiv = document.querySelector('.block-editor-block-list__layout');
                    if (blockEditorDiv) {
                        if (isChecked) {
                            blockEditorDiv.classList.add('lvl-block-hints');
                        } else {
                            blockEditorDiv.classList.remove('lvl-block-hints');
                        }
                    }
                },
            };
        }),
    )(function (props) {
        let blockEditorDiv = document.querySelector('.block-editor-block-list__layout');
        if (blockEditorDiv) {
            if (props.isChecked) {
                blockEditorDiv.classList.add('lvl-block-hints');
            } else {
                blockEditorDiv.classList.remove('lvl-block-hints');
            }
        }

        return wp.element.createElement(ToggleControl, {
            label: 'Show Block Labels',
            checked: props.isChecked,
            onChange: props.onChange,
        });
    });

    let BlockBorderToggle = compose(
        withSelect(function (select) {
            return {
                isChecked: select('core/edit-post').isFeatureActive('blockBorder'),
            };
        }),
        withDispatch(function (dispatch) {
            return {
                onChange: function (isChecked) {
                    dispatch('core/edit-post').toggleFeature('blockBorder');
                    let blockEditorDiv = document.querySelector('.block-editor-block-list__layout');
                    if (blockEditorDiv) {
                        if (isChecked) {
                            blockEditorDiv.classList.add('lvl-block-border');
                        } else {
                            blockEditorDiv.classList.remove('lvl-block-border');
                        }
                    }
                },
            };
        }),
    )(function (props) {
        let blockEditorDiv = document.querySelector('.block-editor-block-list__layout');
        if (blockEditorDiv) {
            if (props.isChecked) {
                blockEditorDiv.classList.add('lvl-block-border');
            } else {
                blockEditorDiv.classList.remove('lvl-block-border');
            }
        }

        return wp.element.createElement(ToggleControl, {
            label: 'Always on Block Outlines',
            checked: props.isChecked,
            onChange: props.onChange,
        });
    });

    function BlockHintsSidebar() {
        let blockEditorDiv = document.querySelector('.block-editor-block-list__layout');

        if (blockEditorDiv) {
            if (!blockEditorDiv.classList.contains('lvl-block-hints')) {
                if (wp.data?.select('core/edit-post')?.isFeatureActive('blockHints'))
                    blockEditorDiv.classList.add('lvl-block-hints');
            }

            if (!blockEditorDiv.classList.contains('lvl-block-border')) {
                if (wp.data?.select('core/edit-post')?.isFeatureActive('blockBorder'))
                    blockEditorDiv.classList.add('lvl-block-border');
            }
        }

        return wp.element.createElement(
            PluginSidebar,
            {
                name: 'block-hints-sidebar',
                title: 'Block Hints',
            },
            wp.element.createElement(
                PanelBody,
                {},
                wp.element.createElement(BlockHintsToggle, {}),
                wp.element.createElement(BlockBorderToggle, {})
            )
        );
    }

    registerPlugin('block-hints-plugin', {
        render: function () {
            return wp.element.createElement(
                wp.element.Fragment,
                {},
                wp.element.createElement(BlockHintsSidebar, {}),
                wp.element.createElement(PluginSidebarMoreMenuItem, {
                    target: 'block-hints-sidebar',
                }, 'Block Hints')
            );
        },
    });


})(window.wp);