export const tabs = ['swBraintreeAppSettingsGeneral', 'swBraintreeAppSettingsCurrency'];

export type Tabs = typeof tabs[number];

export const settingsTabHandler = {
    set(item: Tabs): void {
        window.localStorage.setItem('sw-braintree-app-settings-active-tab', item);
    },

    get(): Tabs {
        let item = window.localStorage.getItem('sw-braintree-app-settings-active-tab') as null | Tabs;

        if (!item || !tabs.includes(item)) 
            item = 'swBraintreeAppSettingsGeneral';

        this.set(item);

        return item;
    },

    clear(): void {
        window.localStorage.removeItem('sw-braintree-app-settings-active-tab');
    },
};