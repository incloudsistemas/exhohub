import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { viteStaticCopy } from 'vite-plugin-static-copy';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/web-assets/style.css',
                'resources/web-assets/css/font-icons.css',
                'resources/web-assets/css/components/bs-select.css',
                'resources/web-assets/css/swiper.css',
                'resources/css/web/custom.css',

                'resources/web-assets/js/components/bs-select.js',
                'resources/web-assets/js/components/selectsplitter.js',
                'resources/js/web/global-custom.js',
                'resources/js/web/form-validation.js',
                'resources/js/web/contact-us-form.js',
                'resources/js/web/business-lead-form.js',
                'resources/js/web/property-page.js',
                'resources/js/web/property-search.js',
            ],
            refresh: true,
        }),
        viteStaticCopy({
            targets: [
                {
                    src: 'resources/web-assets/favicon.ico',
                    dest: ''
                },
                {
                    src: 'resources/web-assets/images/**/*',
                    dest: 'web/images'
                },
                {
                    src: 'resources/web-assets/js/plugins.min.js',
                    dest: 'web/js'
                },
                {
                    src: 'resources/web-assets/js/functions.bundle.js',
                    dest: 'web/js'
                },
                {
                    src: 'resources/web-assets/js/components/selectsplitter.js',
                    dest: 'web/js'
                },
                {
                    src: 'resources/web-assets/js/components/bs-select.js',
                    dest: 'web/js'
                },
            ]
        })
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks: (id) => {
                    if (id.includes('input-mask')) {
                        return 'input-mask';
                    }
                },
            },
        },
    },
});
