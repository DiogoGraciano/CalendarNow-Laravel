import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.tsx',
                // Theme: default
                'resources/views/themes/default/css/theme.css',
                'resources/views/themes/default/js/theme.ts',
                // Theme: modern
                'resources/views/themes/modern/css/theme.css',
                'resources/views/themes/modern/js/theme.ts',
                // Marketplace
                'resources/css/marketplace.css',
                'resources/js/marketplace.ts',
            ],
            ssr: 'resources/js/ssr.tsx',
            refresh: true,
        }),
        react({
            babel: {
                plugins: ['babel-plugin-react-compiler'],
            },
        }),
        tailwindcss(),
        wayfinder({
            formVariants: true,
        }),
    ],
    esbuild: {
        jsx: 'automatic',
    },
});
