/**
 * API client
 * Will throw the original fetch response if the status is not ok
 */
export class Api {
    readonly baseUrl: string;
    readonly prefix: string;

    constructor(baseUrl: string = document.location.origin, prefix: string = '/api') {
        this.baseUrl = baseUrl;
        this.prefix = prefix;
    }

    get<T = unknown>(path: string, options: RequestInit = {}): Promise<T> {
        return this.fetch<T>(path, {
            ...options,
            method: 'GET',
        });
    }

    post<T = unknown>(path: string, data: any = {}, options: RequestInit = {}): Promise<T> {
        return this.fetch<T>(path, {
            ...options,
            body: JSON.stringify(data),
            method: 'POST',
        });
    }

    put<T = unknown>(path: string, data: any = {}, options: RequestInit = {}): Promise<T> {
        return this.fetch<T>(path, {
            ...options,
            body: JSON.stringify(data),
            method: 'PATCH',
        });
    }

    patch<T = unknown>(path: string, data: any = {}, options: RequestInit = {}): Promise<T> {
        return this.fetch<T>(path, {
            ...options,
            body: JSON.stringify(data),
            method: 'PATCH',
        });
    }

    delete<T = unknown>(path: string, options: RequestInit = {}): Promise<T> {
        return this.fetch<T>(path, {
            ...options,
            method: 'DELETE',
        });
    }

    async fetch<T = unknown>(path: string, options: RequestInit = {}): Promise<T> {
        const url = new URL(`${this.prefix}${path}`, this.baseUrl);

        const addParams = new URLSearchParams(document.location.search);
        url.searchParams.forEach((value: string, key: string) => addParams.set(key, value));
        url.search = addParams.toString();

        const response = await fetch(url, {
            ...options,
        });

        if (!response.ok)
            throw response;

        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json'))
            throw new TypeError(`API response is not JSON: ${await response.text()}`);

        return (await response.json()) as T;
    }
}
