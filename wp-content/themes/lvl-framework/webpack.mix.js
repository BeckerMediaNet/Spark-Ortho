// https://laravel-mix.com/docs/6.0/api
let mix = require('laravel-mix');
require('laravel-mix-clean');
require('laravel-mix-webp')

const path = require('path');
const glob = require('glob');

mix.options({
    processCssUrls: false,
})

// e.g. npm run dev --assets=images OR npm run images
let assets = false;

if (process.env.npm_config_assets) {
    assets = process.env.npm_config_assets;
}
// OR
if (process.env.npm_config_argv) {
    let argv = JSON.parse(process.env.npm_config_argv)?.original;
    if (argv) {
        if (argv.includes('images'))
            assets = 'images';
        else if (argv.includes('fonts'))
            assets = 'fonts';
        else if (argv.includes('webp'))
            assets = 'webp';
    }
}

// if not looking to compile assets then process other tasks
if (!assets || mix.inProduction()) {
    /**
     * COMPILE BLOCK SCRIPTS
     **/
    const blockScripts = glob.sync('blocks/**/src/*.js');
    blockScripts.forEach(file => {
        let dir = path.join(path.dirname(file), '../');
        let name = path.basename(file, '.js');
        mix.js(`${file}`, `${dir}/dist/${name}.min.js`);
    });

    /**
     * COMPILE BLOCK STYLES
     **/
    const blockStyles = glob.sync('blocks/**/src/*.scss');
    blockStyles.forEach(file => {
        let dir = path.join(path.dirname(file), '../');
        let name = path.basename(file, '.scss');
        mix.sass(`${file}`, `${dir}/dist/${name}.min.css`);
    });

    mix
        .sourceMaps() // UNCOMMENT FOR DEV IF YOU NEED SOURCEMAPS **DO NOT USE IN PRODUCTION**
        // .webpackConfig(
        // 	{
        // 		devtool:'inline-source-map',
        // 		stats: {
        // 			children: false,
        // 		}
        // 	},
        // )

        // jQuery IF NEEDED **Typically better to use WP registered jquery**
        // .autoload({
        // 	jquery: ['$', 'window.jQuery', 'jQuery'],
        // })

        // JS
        .js('src/js/app.js', 'dist/js/app.min.js')
        .js('src/js/bootstrap.js', 'dist/js/bootstrap.min.js')
        .js('src/js/acf.js', 'dist/admin/js/acf.min.js')
        .js('src/js/forms.js', 'dist/js/forms.min.js')
        .js('src/js/admin.js', 'dist/admin/js/admin.min.js')

        // COMPILE JS LIBRARIES INTO THEIR OWN FILES THAT CAN HOUSE CUSTOM CODE
        .js('src/js/libraries/swiper.js', 'dist/js/swiper.min.js')
        .sass('src/scss/libraries/swiper.scss', 'dist/css/swiper.min.css')
        .js('src/js/libraries/swiffy.js', 'dist/js/swiffy.min.js')
        .js('src/js/libraries/countup.js', 'dist/js/countup.min.js')
        .js('src/js/libraries/aos.js', 'dist/js/aos.min.js')

        // FRONTEND
        .sass('src/scss/style.scss', 'dist/css/app.min.css')
        .sass('src/scss/bootstrap.scss', 'dist/css/bootstrap.min.css')
        .sass('src/scss/wordpress.scss', 'dist/css/wp.min.css')
        .sass('src/scss/frontend.scss', 'dist/css/frontend.min.css')
        // ADMIN
        .sass('src/scss/login.scss', 'dist/admin/css/login.min.css')
        .sass('src/scss/admin.scss', 'dist/admin/css/admin.min.css')

        // FOR WATCHING
        .js('src/js/editor.js', 'dist/js/editor.min.js')
        .sass('src/scss/editor.scss', 'dist/admin/css/editor.min.css')
}

// ASSETS
// FONTS
if (assets === 'fonts' || mix.inProduction()) {
    if (glob.sync('src/fonts').length) {
    mix
        .clean({
            // dry: true,
            cleanOnceBeforeBuildPatterns: [
                'dist/fonts/*'
            ]
        })
        .copy('src/fonts', 'dist/fonts');
}
}
//IMAGES
// if (assets === 'images' || mix.inProduction()) {
//     mix
//         .clean({
//             // dry: true,
//             cleanOnceBeforeBuildPatterns: [
//                 'dist/img/*'
//             ]
//         })
//         .copy('src/img', 'dist/img');
// }

// PRODUCTION ONLY
if (assets === 'webp' || mix.inProduction()) {
    if(glob.sync('src/img').length) {
        mix.ImageWebp({
            from: 'src/img',
            to: 'dist/img',
            imageminWebpOptions: {
                quality: 80
            }
        });
    }
}