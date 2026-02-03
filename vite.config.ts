import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { resolve } from "path";

export default defineConfig({
    // Root level of our SPA folder
    root: resolve(__dirname, './assets'),
    plugins: [
        react(),
        // symfony()
    ],
    resolve: {
        alias: {
            '@': resolve(__dirname, 'assets'),
            '@app': resolve(__dirname, 'assets/app'),
            '@components': resolve(__dirname, 'assets/components'),
            '@lib': resolve(__dirname, 'assets/lib'),
            '@hooks': resolve(__dirname, 'assets/hooks'),
        }
    },
    build: {
        outDir: '../public/build',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: {
                app: resolve(__dirname, 'assets/main.tsx')
            }
        }
        // manifest: true,
    },
    server: {
        proxy: {
            '/api': 'http://127.0.0.1:8000'
        },
        origin: 'http://localhost:5173',
        port: 5173,
        strictPort: true,
    },
})
