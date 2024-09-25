import type { Api } from '@/service/api';
import type { Notify } from '@/service/notify';
import type { Filters } from '@/service/filters';
import type { I18n } from '@/i18n';

declare module '@vue/runtime-core' {
    interface ComponentCustomProperties {
        $api: Api,
        $notify: Notify,
        $filters: Filters,
        $t: I18n['global']['t'],
    }
}
