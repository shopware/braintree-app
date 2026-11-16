import { mergeTests } from '@shopware-ag/acceptance-test-suite';
import { OpenBraintreeSettings, type OpenBraintreeSettingsTask } from './ShopAdmin/Braintree/OpenBraintreeSettings';
import { SaveBraintreeSettings, type SaveBraintreeSettingsTask } from './ShopAdmin/Braintree/SaveBraintreeSettings';
import { SelectBraintreeSalesChannel, type SelectBraintreeSalesChannelTask } from './ShopAdmin/Braintree/SelectBraintreeSalesChannel';

export interface ShopAdminTasks {
    OpenBraintreeSettings: OpenBraintreeSettingsTask
    SaveBraintreeSettings: SaveBraintreeSettingsTask
    SelectBraintreeSalesChannel: SelectBraintreeSalesChannelTask
}

export const test = mergeTests(
    OpenBraintreeSettings,
    SaveBraintreeSettings,
    SelectBraintreeSalesChannel,
);
