import type { Api } from '@/service/api';
import type { Notify } from '@/service/notify';
import type { Filters } from '@/service/filters';

declare module '@vue/runtime-core' {
    interface ComponentCustomProperties {
        $api: Api,
        $notify: Notify,
        $filters: Filters,
    }
}
