declare module '*.vue' {
    import type { DefineComponent } from 'vue';

    const component: DefineComponent<object, object, any>;

    export default component;
}

// vite side effect
declare module 'vite/modulepreload-polyfill' {}
