<template>
<div v-if='loading' class='sw-braintree-app-table__loading'>
    Loading...
</div>
<div v-else-if='!loading && items.length > 0'>
    <table class='sw-braintree-app-table'>
        <tr class='sw-braintree-app-table__headers'>
            <th v-for='column in columns' :key='column.property' class='sw-braintree-app-table__header'>
                <slot :name='"column-" + column.property' :column='column'>
                    {{ column.label }}
                </slot>
            </th>
        </tr>

        <tr v-for='(item, c_idx) in items' :key='c_idx' class='sw-braintree-app-table__columns'>
            <td v-for='column in columns' :key='column.property + c_idx' class='sw-braintree-app-table__column'>
                <slot
                    :name='"cell-" + column.property.replace(".", "-")'
                    :row='deepFind(item, column.property)'
                    :column='column'
                    :item='item'
                >
                    {{ deepFind(item, column.property) }}
                </slot>
            </td>
        </tr>
    </table>
</div>
<div v-else class='sw-braintree-app-table__empty'>
    Empty
</div>
</template>

<script lang="ts">
import { type PropType, defineComponent } from 'vue';
// import { SwPagination } from '@shopware-ag/meteor-component-library';

export default defineComponent({
    name: 'sw-braintree-app-table',
    components: {
        // SwPagination,
    },

    emits: ['update:page', 'update:per-page'],

    props: {
        columns: {
            type: Array as PropType<BrainTableColumn[]>,
            required: true,
        },
        items: {
            type: Array as PropType<BrainTableItem[]>,
            required: true,
        },
        loading: {
            type: Boolean,
            required: false,
            default: false,
        },
    },

    methods: {
        deepFind(obj: unknown, path: string): unknown {
            let current = obj;
            for (path of path.split('.')) {
                // @ts-expect-error is unknown typed
                current = current[path];

                if (current === undefined)
                    return undefined;
            }

            return current;
        },
    },
});
</script>

<style lang="scss">
.sw-braintree-app-table {
    width: 100%;
    font-size: 14px;
    border-spacing: 0;

    tr:not(:first-child):nth-child(odd) {
        background-color: #F0F2F5;
    }

    &__headers {
        height: 64px;
        font-weight: 700;
        line-height: 16px;
    }

    &__header, &__column {
        border-right: 1px solid #D1D9E0;

        &:last-child {
            border-right: 0;
        }
    }

    &__header {
        border-top: 0;
        padding: 20px;
        border-bottom: 1px solid #D1D9E0;
        text-align: left;
    }

    &__column {
        padding: 10px 20px;
    }

    &__empty, &__loading {
        display: grid;
        width: 100%;
        height: 100px;
        place-items: center;
    }
}
</style>
