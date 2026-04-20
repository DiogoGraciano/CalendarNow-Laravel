import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { I18nextProvider } from 'react-i18next';
import { Toaster } from 'react-hot-toast';
import { initializeTheme } from './hooks/use-appearance';
import i18n from './lib/i18n';
import { configureEcho } from '@laravel/echo-react';

configureEcho({
    broadcaster: 'reverb',
});

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.tsx`,
            import.meta.glob('./pages/**/*.tsx'),
        ),
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(
            <StrictMode>
                <I18nextProvider i18n={i18n}>
                    <App {...props} />
                    <Toaster
                        position="top-right"
                        reverseOrder={false}
                        toastOptions={{
                            duration: 4000,
                            style: {
                                background: 'var(--background)',
                                color: 'var(--foreground)',
                                border: '1px solid var(--border)',
                            },
                        }}
                    />
                </I18nextProvider>
            </StrictMode>,
        );
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on load...
initializeTheme();
