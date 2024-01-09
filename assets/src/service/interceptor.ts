import store from '../store';
import type { InternalAxiosRequestConfig } from 'axios';

export interface RequestConfig {
    'location-id': string,
    'shop-id': string,
    'shop-url': string,
    privileges: string,
    timestamp: string,
    'sw-version': string,
    'sw-context-language': string,
    'sw-user-language': string,
    'shopware-shop-signature': string,
}

// eslint-disable-next-line max-len
export function requestInterceptor<T = RequestConfig>(config: InternalAxiosRequestConfig<T>): InternalAxiosRequestConfig<T> {
    config.headers.Accept = 'application/json';

    if (config.method === 'post' || config.method === 'put' || config.method === 'patch')
        config.headers['Content-Type'] = 'application/json';


    config.params ??= {};
    store.state.context?.forEach((value: string, key: string) => {
        // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
        config.params[key] = value;
    });

    return config;
}
