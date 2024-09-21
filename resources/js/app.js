import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import VueGtag from 'vue-gtag';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const vueApp = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(VueGtag, {
                config: { id: import.meta.env.VITE_GA_ID },
            });
    
        // Add global click tracking
        document.addEventListener('click', (event) => {
            const target = event.target;
    
            if (target.tagName === 'A' || target.tagName === 'BUTTON') {
                vueApp.config.globalProperties.$gtag('event', 'click', {
                    event_category: 'Interaction',
                    event_label: target.href || target.innerText,
                });
            }
        });
        vueApp.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
