import type { InjectionKey } from 'vue';

export type RegisterSaveHandler = (handler: () => (void | Promise<unknown>)) => void;
export const registerSaveHandler = Symbol('registerSaveHandler') as InjectionKey<RegisterSaveHandler>;
