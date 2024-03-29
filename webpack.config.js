const Encore = require('@symfony/webpack-encore');
const publicPath = '/build/';

Encore
    .setOutputPath('public/build/')
    .setPublicPath(publicPath)
    .setManifestKeyPrefix('build/')
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(true)
    .addEntry('homepage', './assets/compose.js')
    .splitEntryChunks()
    .disableSingleRuntimeChunk()
    .enableVersioning(false)
    .enableStylusLoader();
const config = Encore.getWebpackConfig();
config.watchOptions = {ignored: null};
module.exports = config;