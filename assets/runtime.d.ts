import type { Store } from 'vuex';
import type { BraintreeState } from '@/store';
import type { Api } from '@/service/api';
import type { Notify } from '@/service/notify';

// Vue 3
// declare module '@vue/runtime-core' {
//     interface ComponentCustomProperties {
//         $store: Store<BraintreeState>,
//         $api: Api,
//     }
// }

// Vue 2
declare module 'vue/types/vue' {
    interface Vue {
        $store: Store<BraintreeState>,
        $api: Api,
        $notify: Notify,
    }
}
