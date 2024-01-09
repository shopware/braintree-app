declare module '*.vue' {
    import type { defineComponent } from 'vue';

    const component: ReturnType<typeof defineComponent>;
    export default component;
}

declare module '@shopware-ag/meteor-component-library/src/plugin/device-helper.plugin' {
    import type Vue from 'vue';

    export default class DeviceHelperPlugin {
        static install(vue: typeof Vue): void;
    }
}