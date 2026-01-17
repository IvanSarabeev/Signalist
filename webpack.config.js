const Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    .addEntry('app', './assets/app.js')
    .enableReactPreset()
    .enableTypeScriptLoader()
    .enablePostCssLoader((options) => {
        options.postcssOptions = {
            config: './postcss.config.js'
        };
    })
    // Additional Type Checking
    // .enableForkedTypeScriptTypesChecking()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .splitEntryChunks()
    .configureDevServerOptions(options => {
        options.hot = true;
        options.port = 8080;
        options.allowedHosts = 'all';
        // options.headers = { 'Access-Control-Allow-Origin': '*' };
    });

module.exports = Encore.getWebpackConfig();
