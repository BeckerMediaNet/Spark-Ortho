// https://laravel-mix.com/docs/6.0/api
let mix = require("laravel-mix");
require("laravel-mix-clean");
require("laravel-mix-webp");

const path = require("path");
const glob = require("glob");
const { existsSync } = require("node:fs");

mix.options({
  processCssUrls: false,
  cssNano: {
    cssDeclarationSorter: false,
  },
});

// e.g. npm run dev --assets=images OR npm run images
let assets = false;

if (process.env.npm_config_assets) {
  assets = process.env.npm_config_assets;
}
// OR
if (process.env.npm_config_argv) {
  let argv = JSON.parse(process.env.npm_config_argv)?.original;
  if (argv) {
    if (argv.includes("images")) assets = "images";
    else if (argv.includes("fonts")) assets = "fonts";
    else if (argv.includes("webp")) assets = "webp";
  }
}

// if not looking to compile assets then process other tasks
if (!assets || mix.inProduction()) {
  /**
   * COMPILE BLOCK SCRIPTS
   **/
  const blockScripts = glob.sync("blocks/**/src/*.js");
  blockScripts.forEach((file) => {
    let dir = path.join(path.dirname(file), "../");
    let name = path.basename(file, ".js");
    mix.js(`${file}`, `${dir}/dist/${name}.min.js`);
  });

  /**
   * COMPILE BLOCK STYLES
   **/
  const blockStyles = glob.sync("blocks/**/src/*.scss");
  blockStyles.forEach((file) => {
    let dir = path.join(path.dirname(file), "../");
    let name = path.basename(file, ".scss");
    mix.sass(`${file}`, `${dir}/dist/${name}.min.css`);
  });

  /**
   * APPEND THEME VARIABLES TO SASS
   **/
  const fs = require("fs");

  function getThemeVariables() {
    const theme = JSON.parse(fs.readFileSync("theme.json", "utf8"));

    const palette = theme.settings.color.palette;

    let colors = "";
    let paletteMap = "$palette: (\n";
    palette.forEach((color) => {
      paletteMap += `  brand-${color.slug}: ${color.color},\n`;
      colors += `$brand-${color.slug}: ${color.color};\n`;
    });
    paletteMap += ");\n";

    let wpPaletteMap = "$wpPalette: (\n";
    palette.forEach((color) => {
      wpPaletteMap += `  ${color.slug}: ${color.color},\n`;
    });
    wpPaletteMap += ");\n";

    const headings = Object.entries(theme.styles.elements)
      .filter(([key]) => /^h[1-6]$/.test(key))
      .map(([key, value]) => ({ [key]: value }));
    let headingsMap = "$headings: (\n";
    headings.forEach((heading) => {
      const key = Object.keys(heading);
      const { typography } = heading[key];
      const { color } = heading[key];
      headingsMap += ` ("${key}", ${typography.fontFamily}, ${typography.fontSize}, ${typography.fontWeight}, ${typography.lineHeight}, ${color.text}),\n`;
    });
    headingsMap += ");\n";

    return paletteMap + wpPaletteMap + colors + headingsMap;
  }

  mix.webpackConfig({
    stats: {
      children: false,
    },
    module: {
      rules: [
        {
          test: /\.scss$/,
          loader: "sass-loader",
          options: {
            additionalData: getThemeVariables(),
          },
        },
      ],
    },
  });

  mix
    // .sourceMaps() // UNCOMMENT FOR DEV IF YOU NEED SOURCEMAPS **DO NOT USE IN PRODUCTION**
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
    .js("src/js/app.js", "dist/js/app.min.js")
    .js("src/js/bootstrap.js", "dist/js/bootstrap.min.js")

    // FRONTEND
    .sass("src/scss/style.scss", "dist/css/app.min.css")
    .sass("src/scss/bootstrap.scss", "dist/css/bootstrap.min.css")

    // ADMIN
    .sass("src/scss/login.scss", "dist/admin/css/login.min.css")

    // FOR WATCHING
    .js("src/js/editor.js", "dist/js/editor.min.js")
    .sass("src/scss/editor.scss", "dist/admin/css/editor.min.css")
    .sass("src/scss/editor/bs.scss", "dist/admin/css/editor-bs.min.css")
    .sass("src/scss/editor/theme.scss", "dist/admin/css/editor-theme.min.css")

    .sass("src/scss/admin.scss", "dist/admin/css/admin.min.css");

  // if src/scss/fonts.scss exists then compile it
  if (existsSync("src/scss/fonts.scss")) {
    mix.sass("src/scss/fonts.scss", "dist/css/fonts.min.css");
  }
}

// ASSETS
// FONTS
if (assets === "fonts" || mix.inProduction()) {
  // check if directory exists
  if (glob.sync("src/fonts").length) {
    mix
      .clean({
        // dry: true,
        cleanOnceBeforeBuildPatterns: ["dist/fonts/*"],
      })
      .copy("src/fonts", "dist/fonts");
  }
}

// PRODUCTION ONLY
if (assets === "webp" || mix.inProduction()) {
  // check if directory exists
  if (glob.sync("src/img").length) {
    mix.ImageWebp({
      from: "src/img",
      to: "dist/img",
      imageminWebpOptions: {
        quality: 80,
      },
    });
  }
}
