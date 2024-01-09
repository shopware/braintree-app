import '@shopware-ag/entity-schema-types';
import type Entity from '@shopware-ag/admin-extension-sdk/es/data/_internals/Entity';

declare global {
    interface Entities extends EntitySchema.Entities {}

    interface ShopEntity {
        shopId: string,
        shopUrl: string,
        shopSecret: string,
        shopClientId: string | null,
        shopClientSecret: string | null,
        shopActive: boolean,
        braintreePublicKey: string,
        braintreePrivateKey: string,
        braintreeMerchantId: string,
        braintreeSandbox: boolean,
        currencyMappings: string[],
        configs: string[],
        createdAt: string,
        updatedAt: string | null,
    }

    interface ConfigEntity {
        id: string | null,
        shop: string,
        threeDSecureEnforced: boolean | null,
        salesChannelId: string | null,
        createdAt: string,
        updatedAt: string | null,
    }

    interface CurrencyMappingEntity {
        id: string | null,
        shop: string,
        salesChannelId: string | null,
        currencyId: string,
        currencyIso: string,
        merchantAccountId: string | null,
        createdAt: string,
        updatedAt: string | null,
    }

    interface BraintreeConnection {
        connectionStatus: MerchantAccountStatus,
        merchantAccount: MerchantAccount | null,
    }

    type MerchantAccountStatus = 'active' | 'disconnected' | 'pending' | 'suspended';
    type MerchantCardTypes = 'MAESTRO' | 'UK_MAESTRO' | 'MASTERCARD' | 'VISA';
    type MerchantPaymentMethods = MerchantCardTypes | 'PAYPAL_ACCOUNT' | 'LOCAL_PAYMENT';

    interface MerchantAccount {
        id: string,
        firstName: string,
        lastName: string,
        email: string,
        status: MerchantAccountStatus,
        subMerchantAccount: boolean,
        default: boolean,
        acceptedPaymentMethods: Array<MerchantPaymentMethods>,
        bankAccount: null, // unknown
        currencyIsoCode: string,
        type: 'MERCHANT_ACCOUNT', // unknown
        paypalAccount: null, // unknown
        threeDSecure: {
            v1: {
                cardTypes: Array<MerchantCardTypes>,
                enabled: boolean,
            },
            v2: {
                cardTypes: Array<MerchantCardTypes>,
                enabled: boolean,
            },
        },
    }

    type StatusIndicatorType = 'success' | 'warning' | 'danger' | undefined;

    type SalesChannelOption = {
        id: string,
        label: string,
        value: string,
    };

    type SalesChannel = {
        id: string,
        name: string,
        translated: {
            name: string,
        },
    } & typeof Entity;

    interface TabItem {
        label: string,
        name: string,
        hasError?: boolean,
        disabled?: boolean,
        badge?: 'positive' | 'critical' | 'warning' | 'info',
        onClick?: (name: string) => void,
        hidden?: boolean,
    }

    interface BraintreeAddress {
        id: string,
        company: string,
        countryCodeAlpha2: string,
        countryCodeAlpha3: string,
        countryCodeNumeric: string,
        countryName: string,
        extendedAddress: string,
        firstName: string,
        lastName: string,
        locality: string,
        postalCode: string,
        region: string,
        streetAddress: string,
    }

    interface BraintreeCustomer {
        id: string | null,
        company: string | null,
        email: string,
        fax: string | null,
        firstName: string,
        lastName: string,
        phone: string | null,
        website: string | null,
    }

    interface BraintreeStatusHistory {
        amount: string,
        status: string,
        timestamp: {
            date: string,
            timezone_type :number,
            timezone: string,
        },
    }

    interface BraintreeThreeDSecureInfo {
        cavv: string,
        dsTransactionId: string,
        eciFlag: string,
        enrolled: string,
        liabilityShiftPossible: boolean,
        liabilityShifted: boolean,
        status: string,
        threeDSecureVersion: string,
        xid: string,
    }

    interface BraintreeTransaction {
        id: string,
        amount: string,
        shippingAmount: string,
        taxAmount: string,
        currencyIsoCode: string,
        status: string,
        type: string,
        billingDetails: BraintreeAddress,
        shippingDetails: BraintreeAddress,
        customer: BraintreeCustomer,
        cvvResponseCode: string,
        statusHistory: BraintreeStatusHistory[],
        threeDSecureInfo: BraintreeThreeDSecureInfo,
        createdAt: string,
        updatedAt: string,
    }

    export interface BrainTableColumn {
        label: string,
        property: string,
    }

    export interface BrainTableItem {
        [key: string]: any,
    }

    interface Array<T> {
        unique: (onlyTruthy?: boolean) => Array<T>,
    }
}

export {};
