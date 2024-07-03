import { createI18n as createVueI18n } from 'vue-i18n';
import * as enGB from './en-GB.json';

export type I18n = ReturnType<typeof createI18n>;
export type Messages = typeof enGB;

export function createI18n(locale: { locale: string; fallbackLocale: string }) {
    return createVueI18n({
        locale: locale.locale,
        fallbackLocale: locale.fallbackLocale,
        messages: {
            'en-GB': enGB,
        },
        // meteor depends on it
        legacy: true,
    });
}
