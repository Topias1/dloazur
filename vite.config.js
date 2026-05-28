import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                // UI-SPEC §Typography locks to 2 weights/family.
                // Fredoka carries the brand voice (display); Inter is body.
                bunny('Fredoka', { weights: [600, 700] }),
                bunny('Inter', { weights: [400, 600] }),
            ],
        }),
        tailwindcss(),
        VitePWA({
            registerType: 'prompt',
            buildBase: '/build/',
            includeAssets: ['icons/*.png'],
            manifest: {
                name: 'Dlo Azur · Métier',
                short_name: 'Dlo Azur',
                description: 'Saisie de passages offline-first — Dlo Azur Piscines',
                theme_color: '#0080ff',
                background_color: '#fdfcf9',
                display: 'standalone',
                orientation: 'portrait',
                start_url: '/admin/passages/create',
                icons: [
                    { src: '/icons/pwa-192x192.png', sizes: '192x192', type: 'image/png' },
                    { src: '/icons/pwa-512x512.png', sizes: '512x512', type: 'image/png', purpose: 'any maskable' },
                ],
            },
            workbox: {
                navigateFallback: '/offline',
                navigateFallbackDenylist: [/^\/admin\//, /^\/portail\//, /^\/api\//],
                globPatterns: ['**/*.{js,css,woff2}'],
                runtimeCaching: [
                    {
                        urlPattern: /\/build\/assets\//,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'vite-assets',
                            expiration: { maxAgeSeconds: 365 * 24 * 60 * 60, maxEntries: 60 },
                        },
                    },
                    {
                        urlPattern: ({ url }) => url.pathname === '/offline',
                        handler: 'CacheFirst',
                        options: { cacheName: 'offline-fallback' },
                    },
                    {
                        urlPattern: /\/api\/passages/,
                        handler: 'NetworkOnly',
                        method: 'POST',
                        options: {
                            backgroundSync: {
                                name: 'passages-queue',
                                options: { maxRetentionTime: 24 * 60 },
                            },
                        },
                    },
                    {
                        urlPattern: /\/api\/passages\/.+\/photos/,
                        handler: 'NetworkOnly',
                        method: 'POST',
                        options: {
                            backgroundSync: {
                                name: 'photos-queue',
                                options: { maxRetentionTime: 24 * 60 },
                            },
                        },
                    },
                ],
            },
            devOptions: {
                enabled: false, // SW généré uniquement en build prod (Pitfall 9 — tests offline via build)
            },
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
