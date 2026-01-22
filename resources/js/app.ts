import './bootstrap';
import '../css/app.css';
import i18n from '@/Core/plugins/i18n';

import { createApp, h, DefineComponent, defineComponent } from 'vue';
import { createInertiaApp, Link } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
import { createPinia } from "pinia";
import { initKtIcon } from "@/Core/plugins/keenthemes";
import { initInlineSvg } from "@/Core/plugins/inline-svg";
import { initApexCharts } from "@/Core/plugins/apexcharts";
import { initVeeValidate } from "@/Core/plugins/vee-validate";
import { Tooltip } from "bootstrap";
import { createRouter, createWebHistory } from 'vue-router';

// Create a dummy router to satisfy theme components
const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/:pathMatch(.*)*', component: { render: () => null } }
    ]
});

// A shim for router-link that uses Inertia's Link
const RouterLinkShim = defineComponent({
    name: 'RouterLink',
    props: ['to'],
    setup(props, { slots }) {
        return () => h(Link, { href: props.to }, slots);
    },
});

let appName = import.meta.env.VITE_APP_NAME || 'Muhuri-central-admin';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob<DefineComponent>('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(i18n)
            .use(createPinia())
            .use(router);

        // Initialize Theme Plugins
        initKtIcon(app);
        initInlineSvg(app);
        initApexCharts(app);
        initVeeValidate();

        // Shims/Aliases
        app.component('router-link', RouterLinkShim);
        app.directive("tooltip", (el) => {
            new Tooltip(el);
        });

        app.mount(el);
    },
    progress: {
        color: '#ffd400',
    },
});
