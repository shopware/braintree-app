import * as sw from '@shopware-ag/meteor-admin-sdk';
import { type AxiosError, isAxiosError } from 'axios';
import type VueI18n from 'vue-i18n';
import type Messages from '../i18n/en-GB.json';

export class Notify {
    constructor(
        private readonly i18n: VueI18n,
    ) {
    }

    error(code: keyof typeof Messages.errors, error?: any) {
        if (!!error && !isAxiosError(error))
            throw error;

        void sw.notification.dispatch({
            variant: 'error',
            title: this.i18n.tc('notification.error'),
            message: this.i18n.tc(`errors.${String(code)}`, 0, {
                error: (error as AxiosError | undefined)?.message,
            }),
        });
    }

    success(code: keyof typeof Messages.success) {
        void sw.notification.dispatch({
            variant: 'success',
            title: this.i18n.tc('notification.success'),
            message: this.i18n.tc(`success.${String(code)}`),
        });
    }
}
