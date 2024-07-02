export type CurrencyFilter = (value: number, currency?: string) => string;
export type DateTimeFilter = (value: string, dateStyle?: 'medium' | 'full' | 'long' | 'short', timeStyle?: 'medium' | 'full' | 'long' | 'short') => string;

export type Filters = {
    toCurrency: CurrencyFilter,
    toDateTime: DateTimeFilter,
};

export function createFilters(locale: { locale: string; fallbackLocale: string }): Filters {
    return {
        toCurrency: (value, currency = 'USD') => {
            const formatter = new Intl.NumberFormat([locale.locale, locale.fallbackLocale], {
                style: 'currency',
                currency: currency,
            });

            return formatter.format(value);
        },

        toDateTime: (value, dateStyle, timeStyle) => {
            const formatter = new Intl.DateTimeFormat([locale.locale, locale.fallbackLocale], {
                dateStyle: dateStyle,
                timeStyle: timeStyle,
            });

            return formatter.format(new Date(value));
        },
    };
}
