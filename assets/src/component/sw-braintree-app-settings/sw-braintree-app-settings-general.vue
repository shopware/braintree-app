<template>
<mt-card
    class='sw-braintree-app-settings-general'
    :title='$t("settings.general.title")'
    :is-loading='loading'
>
    <div class='sw-braintree-app-settings-general__container'>
        <mt-switch
            class='sw-braintree-app-settings-general__threeDSecureEnforced'
            :label='$t("settings.general.threeDSecureEnforced.label")'
            :checked='activeConfig?.threeDSecureEnforced ?? undefined'
            :is-inherited='isThreeDSecureEnforcedInherited'
            :is-inheritance-field='!!salesChannelId'
            @change='onChanceThreeDSecureEnforcedInherited'
            @inheritance-remove='onRemoveInheritance'
            @inheritance-restore='onRestoreInheritance'
        />

        <mt-icon
            v-tooltip.top='$t("settings.general.threeDSecureToolTip")'
            style='position: relative; top: -1px'
            :color="'#189EFF'"
            name='solid-question-circle-s'
        />
    </div>
</mt-card>
</template>

<script lang='ts'>
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import { MtCard, MtSwitch, MtIcon } from '@shopware-ag/meteor-component-library';
import { registerSaveHandler } from '@/resources/inject-keys';
import { DefaultConfigEntity } from '@/resources/entities';
import { inject } from 'vue';

export default defineComponent({
    name: 'sw-braintree-app-settings-general',

    components: { MtCard, MtSwitch, MtIcon },

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
            return this.config[this.stringifySalesChannelId] ?? DefaultConfigEntity(this.salesChannelId);
        },

        isThreeDSecureEnforcedInherited(){
            if(this.salesChannelId === null)
                return false;

            return this.activeConfig.threeDSecureEnforced === null;
        },
    },

    watch: {
        salesChannelId(){
            void this.getConfig();
        },
    },

    created() {
        void this.getConfig();
        this.registerSaveHandler(this.updateConfig.bind(this));
    },

    methods: {
        async getConfig(): Promise<void> {
            if(this.config[this.stringifySalesChannelId]) return;

            return this.$api.get<ConfigEntity>('/entity/by-sales-channel/config/' + this.stringifySalesChannelId)
                .then((config) => {
                    this.config[this.stringifySalesChannelId] = config;
                    this.loading = false;
                    if (this.config['null']?.threeDSecureEnforced === null)
                        this.config['null'].threeDSecureEnforced = false;

                })
                .catch((e) => this.$notify.error('fetch_settings', e));
        },

        async updateConfig(): Promise<void> {
            return this.$api.patch<ConfigEntity>('/entity/config', Object.values(this.config))
                .then(() => {
                    this.config = {};
                    void this.getConfig();
                    this.$notify.success('save_settings');
                })
                .catch((e) => this.$notify.error('save_settings', e));
        },

        onChanceThreeDSecureEnforcedInherited(){
            this.activeConfig.threeDSecureEnforced = !this.activeConfig.threeDSecureEnforced;
        },

        onRemoveInheritance(){
            this.activeConfig.threeDSecureEnforced = this.config['null']?.threeDSecureEnforced ?? false;
        },

        onRestoreInheritance(){
            this.activeConfig.threeDSecureEnforced = null;
        },
    },
});
</script>

<style lang='scss'>
.sw-braintree-app-settings-general {
    &__container {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .mt-field--switch__container .mt-field--switch {
        margin: 0;
    }
}
</style>
