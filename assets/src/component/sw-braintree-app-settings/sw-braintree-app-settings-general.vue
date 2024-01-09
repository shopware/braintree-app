<template>
<sw-card
    class='sw-braintree-app-settings-general'
    :title='$tc("settings.general.title")'
    :is-loading='loading'
>
    <div class='sw-braintree-app-settings-general__container'>
        <!-- eslint-disable vue/attribute-hyphenation -->
        <sw-switch
            class='sw-braintree-app-settings-general__threeDSecureEnforced'
            :label='$tc("settings.general.threeDSecureEnforced.label")'
            :checked='activeConfig?.threeDSecureEnforced'
            :isInherited='isThreeDSecureEnforcedInherited'
            :isInheritanceField='!!salesChannelId'
            @change='onChanceThreeDSecureEnforcedInherited'
            @inheritance-remove='onRemoveInheritance'
            @inheritance-restore='onRestoreInheritance'
        />

        <sw-icon
            v-tooltip.top='$tc("settings.general.threeDSecureToolTip")'
            style='position: relative; top: -1px'
            :color="'#189EFF'"
            name='solid-question-circle-s'
        />
    </div>
</sw-card>
</template>

<script lang='ts'>
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import { SwCard, SwSwitch, SwIcon } from '@shopware-ag/meteor-component-library';
import { registerSaveHandler } from '@/resources/inject-keys';
import { DefaultConfigEntity } from '@/resources/entities';

export default defineComponent({
    name: 'sw-braintree-app-settings-general',

    inject: {
        registerSaveHandler: {
            from: registerSaveHandler,
            default: () => {},
        },
    },
    components: { SwCard, SwSwitch, SwIcon },

    props: {
        salesChannelId: {
            type: String as PropType<string | null>,
            required: false,
            default: null,
        },
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
        // @ts-expect-error - TODO: Fix this
        // eslint-disable-next-line @typescript-eslint/no-unsafe-call
        this.registerSaveHandler(this.updateConfig.bind(this));
    },

    methods: {
        async getConfig(): Promise<void> {
            if(this.config[this.stringifySalesChannelId]) return;

            return this.$api.get<ConfigEntity>('/entity/by-sales-channel/config/' + this.stringifySalesChannelId)
                .then((response) => {
                    this.$set(this.config, this.stringifySalesChannelId, response.data);
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
            this.config[this.stringifySalesChannelId].threeDSecureEnforced = !this.config[this.stringifySalesChannelId].threeDSecureEnforced;
        },

        onRemoveInheritance(){
            this.$set(this.config[this.stringifySalesChannelId], 'threeDSecureEnforced', this.config['null']?.threeDSecureEnforced ?? false);
        },

        onRestoreInheritance(){
            this.$set(this.config[this.stringifySalesChannelId], 'threeDSecureEnforced', null);
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

    .sw-field--switch__container .sw-field--switch {
        margin: 0;
    }
}
</style>
