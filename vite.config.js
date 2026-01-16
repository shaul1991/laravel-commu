import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { sentryVitePlugin } from '@sentry/vite-plugin';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    return {
        build: {
            sourcemap: true,
        },
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js'],
                refresh: true,
            }),
            tailwindcss(),
            env.SENTRY_AUTH_TOKEN && sentryVitePlugin({
                org: env.SENTRY_ORG,
                project: env.SENTRY_PROJECT,
                authToken: env.SENTRY_AUTH_TOKEN,
                url: env.SENTRY_URL,
                sourcemaps: {
                    filesToDeleteAfterUpload: ['./public/build/**/*.map'],
                },
            }),
        ].filter(Boolean),
        server: {
            watch: {
                ignored: ['**/storage/framework/views/**'],
            },
        },
    };
});
