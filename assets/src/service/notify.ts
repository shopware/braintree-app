import * as sw from '@shopware-ag/meteor-admin-sdk';
import type { I18n, Messages } from '@/i18n';

export class Notify {
    constructor(
        private readonly i18n: I18n,
    ) {
    }

    async error(code: keyof Messages['errors'], response?: unknown) {
        if (!(response instanceof Response))
            throw response;

        if (response.ok)
            return;

        const clone = response.clone();
        const message = await response
            .json()
            // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
            .then((json) => String(json?.message || json))
            .catch(() => clone.text());

        void sw.notification.dispatch({
            variant: 'error',
            title: this.i18n.global.t('notification.error'),
            message: this.i18n.global.t(`errors.${String(code)}`, { message }),
        });
    }

    success(code: keyof Messages['success']) {
        void sw.notification.dispatch({
            variant: 'success',
            title: this.i18n.global.t('notification.success'),
            message: this.i18n.global.t(`success.${String(code)}`),
        });
    }
}
