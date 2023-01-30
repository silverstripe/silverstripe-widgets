const Path = require('path');
const { JavascriptWebpackConfig, CssWebpackConfig } = require('@silverstripe/webpack-config');

const ENV = process.env.NODE_ENV;
const PATHS = {
  ROOT: Path.resolve(),
  SRC: Path.resolve('client/src'),
  DIST: Path.resolve('client/dist'),
};

const config = [
  // Main JS bundle
  new JavascriptWebpackConfig('js', PATHS, 'silverstripe/widgets')
    .setEntry({
        WidgetAreaEditor: `${PATHS.SRC}/js/WidgetAreaEditor.js`,
    })
    .getConfig(),
  // sass to css
  new CssWebpackConfig('css', PATHS)
    .setEntry({
        WidgetAreaEditor: `${PATHS.SRC}/styles/WidgetAreaEditor.scss`,
    })
    .getConfig(),
];

// Use WEBPACK_CHILD=js or WEBPACK_CHILD=css env var to run a single config
module.exports = (process.env.WEBPACK_CHILD)
  ? config.find((entry) => entry.name === process.env.WEBPACK_CHILD)
  : config;


