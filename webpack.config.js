const path = require("path");

module.exports = {
    mode: 'development',
    entry: './assets/app/index.tsx',
    output: {
        path: path.resolve(__dirname, 'public/build'),
        filename: 'app.js',
        publicPath: '/build/'
    },
    resolve: {
        extensions: ['.ts', '.tsx', '.js', '.jsx']
    },
    module: {
        rules: [
            {
                test: /\.(ts|tsx)$/,
                use: 'ts-loader',
                exclude: /node_modules/,
            },
            {
                test: /\.css$/,
                use: ['style-loader', 'css-loader', 'postcss-loader'],
            },
        ]
    },
    devtool: 'source-map',
    devServer: {
        hot: true,
        port: 3000,
        static: {
            directory: path.resolve(__dirname, 'public')
        },
        proxy: [
            {
                context: ['/'],
                target: 'https://localhost:8000',
                changeOrigin: true
            }
        ]
    }
}
