import { mergeTests } from '@shopware-ag/acceptance-test-suite';
import { OpenBraintreeSettings, type OpenBraintreeSettingsTask } from './ShopAdmin/Braintree/OpenBraintreeSettings';
import { SaveBraintreeSettings, type SaveBraintreeSettingsTask } from './ShopAdmin/Braintree/SaveBraintreeSettings';
import { SelectBraintreeSalesChannel, type SelectBraintreeSalesChannelTask } from './ShopAdmin/Braintree/SelectBraintreeSalesChannel';
import { ReadOrderTransactionState, type ReadOrderTransactionStateTask } from './ShopAdmin/Order/ReadOrderTransactionState';

export interface ShopAdminTasks {
    OpenBraintreeSettings: OpenBraintreeSettingsTask
    ReadOrderTransactionState: ReadOrderTransactionStateTask
    SaveBraintreeSettings: SaveBraintreeSettingsTask
    SelectBraintreeSalesChannel: SelectBraintreeSalesChannelTask
}

export const test = mergeTests(
    OpenBraintreeSettings,
    ReadOrderTransactionState,
    SaveBraintreeSettings,
    SelectBraintreeSalesChannel,
);
