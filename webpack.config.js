const Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')

    .addEntry('app', './assets/app.js')

    .enableReactPreset()
    .enableTypeScriptLoader()
    .enableForkedTypeScriptTypesChecking()

    .enableSingleRuntimeChunk()
    .splitEntryChunks()

    .enablePostCssLoader(options => {
        options.postcssOptions = {
            config: './postcss.config.js'
        };
    })

    // .addRule({
    //     test: /\.svg$/i,
    //     issuer: /\.[jt]sx?$/, // JS/TS files only
    //     use: ['@svgr/webpack'],
    // })

    // .copyFiles({
    //     from: './assets/public',
    //     to: '[path][name].[ext]'
    // })

    .configureDevServerOptions(options => {
        options.hot = false;
        options.port = 8080;
        options.static = false;
        options.client = {
            overlay: false
        }
        options.liveReload = false;
        options.historyApiFallback = false;
        options.watchFiles = {
            paths: ['assets/**/*'],
            options: {
                ignored: [
                    '**/node_modules/**',
                    '**/public/build/**',
                    '**/var/**'
                ]
            }
        };
    });

if (Encore.isProduction()) {
    Encore.cleanupOutputBeforeBuild();
}

module.exports = Encore.getWebpackConfig();
