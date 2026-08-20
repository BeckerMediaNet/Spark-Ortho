class AcfColorPalette {
    constructor() {
        this.colors = app_localized?.themeJson.settings?.color?.palette;
        this.init();
    }

    init() {
        if (!this.colors) {
            console.error('No color palette defined');
            return;
        }

        acf.add_filter('color_picker_args', this.setupColorPickerArgs.bind(this));
    }

    setupColorPickerArgs(args, field) {
        let palette = [];
        let colorSet = [
            'Primary',
            'Secondary',
            'Success',
            'Danger',
            'Warning',
            'Info',
            'Light',
            'Dark',
        ];
        for (let key in this.colors) {
            if (colorSet.includes(this.colors[key].name)) {
                palette.push(this.colors[key].color);
            }
        }

        args.palettes = palette;
        return args;
    }
}

export default AcfColorPalette;