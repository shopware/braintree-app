<template>
<a class='sw-internal-link' :href='href' @click='onClick'>
    <slot />
</a>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
    name: 'sw-internal-link',

    emits: ['click'],

    props: {
        href: {
            type: String,
            required: true,
        },
    },

    methods: {
        onClick() {
            this.$emit('click');
        },
    },
});
</script>

<style lang="scss" scoped>
.sw-internal-link {
    display: grid;
    grid-auto-flow: column;
    grid-column-gap: 6px;
    align-items: center;
    text-decoration: underline;
    color: #189eff;
    font-size: 14px;
    flex-shrink: 0;

    &:hover {
        color: #1596ff;
    }

    &:visited {
        color: #1596ff;
    }
}
</style>
