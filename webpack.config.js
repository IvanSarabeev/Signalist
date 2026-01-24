import { fileURLToPath } from 'node:url';
import { dirname, resolve } from 'node:path';
import Encore from '@symfony/webpack-encore';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

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
            config: './postcss.config.cjs'
        };
    })

    .addAliases({
        '@': resolve(__dirname, 'assets/'),
        '@app': resolve(__dirname, 'assets/app/'),
        '@components': resolve(__dirname, 'assets/components/'),
        '@lib': resolve(__dirname, 'assets/lib/'),
        '@hooks': resolve(__dirname, 'assets/hooks/')
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
                    '**/var/**',
                    '/node_modules'
                ]
            }
        };
    });

if (Encore.isProduction()) {
    Encore.cleanupOutputBeforeBuild();
}

export default Encore.getWebpackConfig();
