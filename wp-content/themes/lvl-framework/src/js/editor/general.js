// Remove default button styles
wp.domReady( () => {
    wp.blocks.unregisterBlockStyle('core/button', ['fill', 'outline']);
} );

(function(wp) {
    const addFilter = wp.hooks.addFilter;

    addFilter('editor.BlockListBlock', 'lvl/apply-default-styles', function(BlockListBlock) {
        return function(props) {
            if (!props.name.includes('lvl/')) {
                return wp.element.createElement(BlockListBlock, props);
            }

            const { attributes } = props;
            const defaultStyles = attributes?.defaultStyles || {};

            if (!attributes.style && defaultStyles) {
                wp.data.dispatch('core/block-editor').updateBlockAttributes(props.clientId, {
                    style: defaultStyles
                });
            }

            return wp.element.createElement(BlockListBlock, props);
        };
    });

})(window.wp);