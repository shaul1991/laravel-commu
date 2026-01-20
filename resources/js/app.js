import './bootstrap';
import './auth';
import './mermaid';
import * as Sentry from '@sentry/browser';

Sentry.init({
    dsn: import.meta.env.VITE_SENTRY_DSN,
    environment: import.meta.env.VITE_APP_ENV || 'local',
    tracesSampleRate: parseFloat(import.meta.env.VITE_SENTRY_TRACES_SAMPLE_RATE || '1.0'),
    replaysSessionSampleRate: 0.1,
    replaysOnErrorSampleRate: 1.0,
});

// Sentry test function (development only)
if (import.meta.env.DEV) {
    window.testSentry = () => {
        throw new Error('Sentry Frontend Test Error');
    };
}
