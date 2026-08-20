import {backgroundColors, getColorBySlug, isDark} from '../components/colors';

const BlockManager = {
    previousBlockAttributes: {},
    colorCache: new Map(),
    debounceTimeout: null,

    subscribeToBlockChanges() {
        wp.data.subscribe(() => {
            clearTimeout(this.debounceTimeout);
            this.debounceTimeout = setTimeout(() => {

                const selectedBlock = wp.data.select('core/block-editor').getSelectedBlock();
                if (!selectedBlock) return;

                const {attributes: newAttributes, clientId} = selectedBlock;
                const oldAttributes = this.previousBlockAttributes[clientId];
                const colorSlugOrHex = newAttributes.backgroundColor || newAttributes.style?.color?.background;

                if (oldAttributes && colorSlugOrHex === (oldAttributes.backgroundColor || oldAttributes.style?.color?.background)) return;

                const backgroundColor = this.getCachedColor(colorSlugOrHex);
                const theme = backgroundColor?.color ? (this.isDarkCached(backgroundColor.color) ? 'dark' : 'light') : '';
                const bs = {...newAttributes.bs, theme};

                wp.data.dispatch('core/block-editor').updateBlockAttributes(clientId, {bs});
                this.setDataBsThemeAttribute(selectedBlock);
                this.previousBlockAttributes[clientId] = newAttributes;
            }, 100);
        });
    },

    getCachedColor(colorSlugOrHex) {
        if (!colorSlugOrHex) return null;
        const key = colorSlugOrHex.startsWith('#') ? `hex${colorSlugOrHex}` : colorSlugOrHex;
        if (!this.colorCache.has(key)) {
            const color = colorSlugOrHex.startsWith('#') ? {color: colorSlugOrHex} : getColorBySlug(backgroundColors, colorSlugOrHex);
            this.colorCache.set(key, color);
        }
        return this.colorCache.get(key);
    },

    isDarkCached(color) {
        if (!color) return false;
        if (!this.colorCache.has(color)) {
            this.colorCache.set(color, isDark(color));
        }
        return this.colorCache.get(color);
    },

    setDataBsThemeAttribute(block) {
        const {clientId, attributes} = block;
        const colorSlugOrHex = attributes.backgroundColor || attributes.style?.color?.background;
        const backgroundColor = this.getCachedColor(colorSlugOrHex);

        const applyTheme = (blockElement) => {
            if (backgroundColor?.color) {
                const theme = this.isDarkCached(backgroundColor.color) ? 'dark' : 'light';
                blockElement.setAttribute('data-bs-theme', theme);
            } else {
                blockElement.removeAttribute('data-bs-theme');
            }
        };

        const blockElement = document.querySelector(`[data-block="${clientId}"]`);
        if (blockElement) {
            applyTheme(blockElement);
        } else {
            const observer = new MutationObserver((mutations, obs) => {
                const blockElement = document.querySelector(`[data-block="${clientId}"]`);
                if (blockElement) {
                    applyTheme(blockElement);
                    obs.disconnect();
                }
            });
            observer.observe(document.body, {childList: true, subtree: true});
        }
    },

    traverseBlocks(blocks) {
        blocks.forEach(block => {
            this.setDataBsThemeAttribute(block);
            this.previousBlockAttributes[block.clientId] = block.attributes;
            if (block.innerBlocks?.length) this.traverseBlocks(block.innerBlocks);
        });
    },

    applyDataAttributes() {
        const blocks = wp.data.select('core/block-editor').getBlocks();
        this.traverseBlocks(blocks);
    },

    init() {
        this.subscribeToBlockChanges();

        const unsubscribe = wp.data.subscribe(() => {
            const blocks = wp.data.select('core/block-editor').getBlocks();
            if (blocks.length > 0) {
                this.applyDataAttributes();
                unsubscribe();
            }
        });
    }
};

wp.hooks.addFilter(
    'blocks.registerBlockType',
    'lvl/block-bs-attributes',
    (settings, name) => {
        return {
            ...settings,
            attributes: {
                ...settings.attributes,
                bs: {
                    type: 'object',
                    default: {}
                }
            }
        };
    }
);


BlockManager.init();
