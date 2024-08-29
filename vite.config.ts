import { fileURLToPath, URL } from 'node:url';

import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import vueDevTools from 'vite-plugin-vue-devtools';
import legacy from '@vitejs/plugin-legacy';

// https://vitejs.dev/config/
export default defineConfig(({ mode }) => ({
    base: '/build/',
    plugins: [
        vue(),
        vueDevTools(),
        legacy({
            // https://browsersl.ist
            modernTargets: 'since 2020-01-01, not dead',
            modernPolyfills: true,
            renderLegacyChunks: false,
        }),
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./assets/src', import.meta.url)),
        },
    },
    build: {
        chunkSizeWarningLimit: 1024,
        outDir: './public/build',
        emptyOutDir: true,
        copyPublicDir: false,
        sourcemap: mode === 'development',
        rollupOptions: {
            input: './assets/admin.ts',
            output: {
                entryFileNames: '[name].js',
                assetFileNames: '[name].[ext]',
            },
        },
    },
    css: {
        preprocessorOptions: {
            scss: {
                api: 'modern-compiler',
            },
        },
    },
}));
