<template>
<div class='sw-card-view__content'>
    <slot />
</div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
    name: 'sw-card-view-content',
});
</script>

<style lang="scss" scoped>
.sw-card-view__content {
    padding: var(--scale-size-16);
    padding-top: var(--scale-size-40);
}

@media screen and (min-width: 1360px) {
    .sw-card-view__content {
        padding: var(--scale-size-40);
    }
}
</style>
