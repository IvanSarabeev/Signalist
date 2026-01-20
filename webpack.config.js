const Encore = require('@symfony/webpack-encore');
const path = require("node:path");

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}


const isDevelopmentMode = process.env.NODE_ENV === "dev" ? "development" : "production";

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')

    .addEntry('app', './assets/index.tsx')

    .enableReactPreset()
    .enableTypeScriptLoader()
    .enableForkedTypeScriptTypesChecking()

    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(!isDevelopmentMode)

    .enableSingleRuntimeChunk()
    .splitEntryChunks()

    .enablePostCssLoader(options => {
        options.postcssOptions = {
            config: './postcss.config.js'
        };
    })

    .addAliases({
        '@': path.resolve(__dirname, 'assets/'),
        '@app': path.resolve(__dirname, 'assets/app/'),
        '@components': path.resolve(__dirname, 'assets/components/'),
        '@lib': path.resolve(__dirname, 'assets/lib/'),
        '@hooks': path.resolve(__dirname, 'assets/hooks/')
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
