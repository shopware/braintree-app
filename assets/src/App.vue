<template>
<sw-braintree-app-page-controller />
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import SwBraintreeAppPageController from './views/sw-braintree-app-page-controller.vue';

export default defineComponent({
    name: 'sw-braintree-app',

    components: {
        'sw-braintree-app-page-controller': SwBraintreeAppPageController,
    },
});
</script>
