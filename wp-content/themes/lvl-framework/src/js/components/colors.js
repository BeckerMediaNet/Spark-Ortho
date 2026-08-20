const ColorManager = {
    theme: app_localized?.themeJson,

    getColorPaletteSet() {
        if (this.theme?.settings?.color?.palette && this.theme.settings?.color?.palette?.length > 0) {
            return this.theme.settings.color.palette;
        } else {
            const defaultColors = wp.data.select('core/editor').getEditorSettings().colors;
            return defaultColors.map((color) => ({
                color: color.color,
                name: color.name,
                slug: color.slug,
            }));
        }
    },

    getGradientsSet() {
        return this.theme?.settings?.color?.gradients || [];
    },

    colorPalette: [],
    gradients: [],

    init() {
        this.colorPalette = this.getColorPaletteSet();
        this.gradients = this.getGradientsSet();
    },

    getUniqueColors() {
        return this.colorPalette.filter((color, index, self) =>
            index === self.findIndex((c) => c.color === color.color)
        );
    },

    getUniqueGradients() {
        return this.gradients.filter((gradient, index, self) =>
            index === self.findIndex((g) => g.gradient === gradient.gradient)
        );
    },

    backgroundColors() {
        return [{ name: 'Background', colors: this.getUniqueColors() }];
    },

    gradientColors() {
        return [{ name: 'Background Patterns', gradients: this.getUniqueGradients() }];
    },

    accentColors() {
        return [{ name: 'Accent', colors: this.getUniqueColors() }];
    },

    getColorByColor(colorList, val) {
        const colorObj = colorList.find((group) =>
            group.colors.find((color) => color.color === val)
        );

        if (colorObj) {
            const color = colorObj.colors.find((color) => color.color === val);
            return { name: color.name, slug: color.slug, color: val };
        }

        return { name: 'Custom', slug: 'custom', color: val };
    },

    getColorBySlug(colorList, val) {
        const colorObj = colorList.find((group) =>
            group.colors.find((color) => color.slug === val)
        );

        if (colorObj) {
            const color = colorObj.colors.find((color) => color.slug === val);
            return { name: color.name, slug: color.slug, color: color.color };
        }

        return null;
    },

    getGradientByValue(gradientList, val) {
        const gradientObj = gradientList.find((group) =>
            group.gradients.find((gradient) => gradient.gradient === val)
        );

        if (gradientObj) {
            const gradient = gradientObj.gradients.find((gradient) => gradient.gradient === val);
            return { name: gradient.name, slug: gradient.slug, gradient: val };
        }

        return null;
    },

    isDark(color) {
        if (!color || typeof color !== 'string' || color.includes('url(')) return false;

        let r, g, b, a = 1;
        if (color.startsWith('#')) {
            const hex = color.slice(1);
            r = parseInt(hex.slice(0, 2), 16);
            g = parseInt(hex.slice(2, 4), 16);
            b = parseInt(hex.slice(4, 6), 16);
        } else {
            const match = color.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+(?:\.\d+)?))?\)$/);
            if (match) {
                [r, g, b, a] = [parseInt(match[1]), parseInt(match[2]), parseInt(match[3]), parseFloat(match[4] || 1)];
            } else {
                return false;
            }
        }

        r = Math.round((1 - a) * 255 + a * r);
        g = Math.round((1 - a) * 255 + a * g);
        b = Math.round((1 - a) * 255 + a * b);

        return ((r * 299 + g * 587 + b * 114) / 1000 < 125);
    }
};

ColorManager.init();

export const colorPalette = ColorManager.colorPalette;
export const gradients = ColorManager.gradients;
export const backgroundColors = ColorManager.backgroundColors();
export const gradientColors = ColorManager.gradientColors();
export const accentColors = ColorManager.accentColors();
export const getColorByColor = ColorManager.getColorByColor.bind(ColorManager);
export const getColorBySlug = ColorManager.getColorBySlug.bind(ColorManager);
export const getGradientByValue = ColorManager.getGradientByValue.bind(ColorManager);
export const isDark = ColorManager.isDark.bind(ColorManager);