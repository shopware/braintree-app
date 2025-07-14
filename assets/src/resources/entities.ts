export const DefaultConfigEntity = (salesChannelId?: string | null): ConfigEntity => ({
    id: null,
    shop: '',
    threeDSecureEnforced: false,
    submitForSettlement: true,
    shipsFromPostalCode: null,
    salesChannelId: salesChannelId ?? null,
    createdAt: (new Date()).toISOString(),
    updatedAt: null,
});

export const DefaultCurrencyMappingEntity = (salesChannelId?: string | null, merchantAccount?: string | null): CurrencyMappingEntity => ({
    id: null,
    shop: '',
    salesChannelId: salesChannelId ?? null,
    currencyId: '',
    currencyIso: '',
    merchantAccountId: merchantAccount ?? null,
    createdAt: (new Date()).toISOString(),
    updatedAt: null,
});
