<template>
<mt-card
    class='sw-braintree-app-settings-general'
    :title='$t("settings.general.title")'
    :is-loading='loading'
>
    <div class='content'>
        <mt-switch
            class='sw-braintree-app-settings-general__threeDSecureEnforced'
            bordered
            :label='$t("settings.general.threeDSecureEnforced.label")'
            :checked='activeConfig.threeDSecureEnforced ?? rootConfig.threeDSecureEnforced ?? false'
            :inherited-value='rootConfig.threeDSecureEnforced ?? false'
            :is-inherited='isFieldInherited("threeDSecureEnforced")'
            :is-inheritance-field='!!salesChannelId'
            :help-text='$t("settings.general.threeDSecureEnforced.tooltip")'
            @change='activeConfig.threeDSecureEnforced = $event'
            @inheritance-remove='onRemoveInheritance("threeDSecureEnforced")'
            @inheritance-restore='onRestoreInheritance("threeDSecureEnforced")'
        />

        <mt-text-field
            class='sw-braintree-app-settings-general__shipsFromPostalCode'
            :label='$t("settings.general.shipsFromPostalCode.label")'
            :placeholder='$t("settings.general.shipsFromPostalCode.placeholder")'
            :inherited-value='rootConfig.shipsFromPostalCode ?? ""'
            :is-inherited='isFieldInherited("shipsFromPostalCode")'
            :is-inheritance-field='!!salesChannelId'
            :model-value='activeConfig.shipsFromPostalCode ?? rootConfig.shipsFromPostalCode ?? ""'
            @update:model-value='activeConfig.shipsFromPostalCode = $event'
            @inheritance-remove='onRemovePostalCodeInheritance()'
            @inheritance-restore='onRestoreInheritance("shipsFromPostalCode")'
        />
    </div>
</mt-card>
</template>

<script lang='ts'>
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import { MtCard, MtSwitch, MtTextField } from '@shopware-ag/meteor-component-library';
import { registerSaveHandler } from '@/resources/inject-keys';
import { DefaultConfigEntity } from '@/resources/entities';
import { inject } from 'vue';

export default defineComponent({
    name: 'sw-braintree-app-settings-general',

    components: { MtTextField, MtCard, MtSwitch },

    props: {
        salesChannelId: {
            type: String as PropType<string | null>,
            required: false,
            default: null,
        },
    },

    setup() {
        return {
            registerSaveHandler: inject(registerSaveHandler, () => {}),
        };
    },

    data(): {
        loading: boolean,
        config: Record<string, ConfigEntity>,
    } {
        return {
            loading: true,
            config: {},
        };
    },

    computed: {
        stringifySalesChannelId(){
            return String(this.salesChannelId);
        },

        activeConfig(): ConfigEntity {
            return this.config[this.stringifySalesChannelId] ?? {};
        },

        rootConfig(): ConfigEntity {
            return this.config['null'] ?? {};
        },
    },

    watch: {
        salesChannelId() {
            if (!this.config[this.stringifySalesChannelId])
                void this.getConfig(this.stringifySalesChannelId);
        },
    },

    created() {
        void this.getConfig('null');
        this.registerSaveHandler(this.updateConfig.bind(this));
    },

    methods: {
        async getConfig(salesChannelId: string): Promise<void> {
            this.loading = true;

            return this.$api.get<ConfigEntity>('/entity/by-sales-channel/config/' + salesChannelId)
                .then((config) => {
                    this.config[String(config.salesChannelId)] = config;
                    
                    // add new config parameters
                    if (this.config['null']?.threeDSecureEnforced === null)
                        this.config['null'].threeDSecureEnforced = false;

                    this.loading = false;
                })
                .catch((e) => this.$notify.error('fetch_settings', e));
        },

        async updateConfig(): Promise<void> {
            this.loading = true;
            
            try {
                await this.$api.patch<ConfigEntity>('/entity/config', Object.values(this.config));
                this.$notify.success('save_settings');

                // reset, but preserve the active config to avoid flickering of toggles due to default values
                this.config = {
                    null: this.rootConfig,
                    [this.stringifySalesChannelId]: this.activeConfig,
                };

                await Promise.all([this.getConfig('null'), this.getConfig(this.stringifySalesChannelId)]);
            } catch (e) {
                this.$notify.error('save_settings', e);
                this.loading = false;
            }
        },

        isFieldInherited(key: keyof ConfigEntity): boolean {
            if (this.salesChannelId === null)
                return false;

            return this.activeConfig[key] === null;
        },

        onRemovePostalCodeInheritance() {
            this.activeConfig['shipsFromPostalCode'] = this.config['null']?.['shipsFromPostalCode'] ?? '';
        },

        onRemoveInheritance(key: keyof ConfigEntity): void {
            // @ts-expect-error - TS does not know that the value of key is a valid assignment
            this.activeConfig[key] = this.config['null']?.[key] ?? DefaultConfigEntity(this.salesChannelId)[key];
        },

        onRestoreInheritance(key: keyof ConfigEntity): void {
            // @ts-expect-error - TS does not know that the value of key is a valid assignment
            this.activeConfig[key] = null;
        },
    },
});
</script>

<style lang='scss'>
.sw-braintree-app-settings-general {
    .content {
        display: flex;
        flex-direction: column;
        gap: var(--scale-size-24);
    }
}
</style>
