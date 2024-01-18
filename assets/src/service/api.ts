import type { AxiosInstance } from 'axios';
import axios, { type AxiosResponse, type AxiosRequestConfig } from 'axios';
import { requestInterceptor } from './interceptor';

export class Api {
    server: AxiosInstance;

    constructor() {
        this.server = axios.create({
            baseURL: `https://braintree.shopware.com/api`,
        });

        this.server.interceptors.request.use(requestInterceptor);
    }

    get<T = any>(url: string, config: AxiosRequestConfig<T> = {}): Promise<AxiosResponse<T>> {
        return this.server.get<T>(url, config);
    }

    post<T = any>(url: string, data: any = {}, config: AxiosRequestConfig<T> = {}): Promise<AxiosResponse<T>> {
        return this.server.post<T>(url, data, config);
    }

    put<T = any>(url: string, data: any = {}, config: AxiosRequestConfig<T> = {}): Promise<AxiosResponse<T>> {
        return this.server.put<T>(url, data, config);
    }

    patch<T = any>(url: string, data: any = {}, config: AxiosRequestConfig<T> = {}): Promise<AxiosResponse<T>> {
        return this.server.patch<T>(url, data, config);
    }

    delete<T = any>(url: string, config: AxiosRequestConfig<T> = {}): Promise<AxiosResponse<T>> {
        return this.server.delete<T>(url, config);
    }
}

