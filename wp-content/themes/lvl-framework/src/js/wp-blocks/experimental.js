function lvlModifyBlockSupport(blockNames, filterName, supports) {
    wp.hooks.addFilter(
        'blocks.registerBlockType',
        filterName,
        function(settings, name) {
            if (!blockNames.includes(name)) {
                return settings;
            }

            return Object.assign({}, settings, {
                supports: Object.assign({}, settings.supports, supports),
            });
        }
    );
}

// Example usage:
lvlModifyBlockSupport(
    ['core/column', 'core/media-text', 'core/separator'],
    'lvl/modify-group-block',
    {
        __experimentalBorder: {
            color: true,
            radius: true,
            style: true,
            width: true
        }
    }
);

lvlModifyBlockSupport(
    ['core/group'],
    'lvl/modify-block-shadow-support',
    {
        shadow: true
    }
);