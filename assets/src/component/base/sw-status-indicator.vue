<template>
<span class='sw-status' :class='status'>
    <span class='sw-status__indicator' />
    <span class='sw-status__text'>{{ statusText }}</span>
</span>
</template>

<script lang="ts">
import { defineComponent, type PropType } from 'vue';

export default defineComponent({
    name: 'sw-status-indicator',

    props: {
        status: {
            type: String as PropType<StatusIndicatorType>,
            required: false,
            default: undefined,
        },

        text: {
            type: String as PropType<string | null>,
            required: false,
            default: null,
        },
    },

    computed: {
        statusText(): string {
            return this.text ?? this.status ?? '';
        },
    },
});

</script>

<style lang="scss" scoped>
.sw-status {
    display: flex;
    gap: 6px;
    align-items: center;
    padding: 4px 12px;
    border-radius: 50px;
    background: var(--color-background-primary-default);
    height: 23px;
    border: 1px solid var(--color-border-primary-default);
    line-height: 14px;
    font-size: 12px;

    &__indicator {
        width: 8px;
        height: 8px;
        border-radius: 100%;
        background: var(--color-text-secondary-default);
        display: inline-block;
    }

    &__text {
        color: var(--color-text-secondary-default);
    }

    &.success {
        background: var(--color-green-50);
        border-color: var(--color-green-200);

        .sw-status__indicator {
            background: var(--color-green-500);
        }

        .sw-status__text {
            color: var(--color-green-500);
        }
    }

    &.warning {
        background: var(--color-orange-50);
        border-color: var(--color-orange-200);

        .sw-status__indicator {
            background: var(--color-orange-600);
        }

        .sw-status__text {
            color: var(--color-orange-600);
        }
    }

    &.danger {
        background: var(--color-red-50);
        border-color: var(--color-red-200);

        .sw-status__indicator {
            background: var(--color-red-500);
        }

        .sw-status__text {
            color: var(--color-red-500);
        }
    }
}
</style>
