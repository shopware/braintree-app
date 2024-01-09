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
    background: #D1D9E0;
    height: 23px;
    border: 1px solid #D1D9E0;
    line-height: 14px;
    font-size: 12px;

    &__indicator {
        width: 8px;
        height: 8px;
        border-radius: 100%;
        background: #52667A;
        display: inline-block;
    }

    &__text {
        color: #52667A;
    }

    &.success {
        background: #E7F9E9;
        border-color: #c3f1c8;

        .sw-status__indicator {
            background: #37D046;
        }

        .sw-status__text {
            color: #2AC437;
        }
    }
}
</style>
