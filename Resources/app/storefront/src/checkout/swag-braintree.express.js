import Plugin from 'src/plugin-system/plugin.class';

const BASE_URL = 'https://swagbraintree.dev.localhost/api';

export default class SwagBraintreeExpress extends Plugin {
    init() {
        this._registerEvents();
    }

    /**
     * register needed events
     *
     * @private
     */
    _registerEvents() {
        this.el.addEventListener('click', this._onClick.bind(this));
    }

    /**
     * click event handler
     *
     * @private
     */
    _onClick() {
        const body = {appName: 'SwagBraintreeApp', 'foo': 'bar'};

        fetch(window.router['frontend.gateway.context'], {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(body)
        })
        .then(res => res.json())
        .then(response => {
            if (response.redirectUrl) {
                const currentUrl = new URL(window.location.href);
                const redirectBase = new URL(response.redirectUrl);

                // Merge redirect path + current path
                const basePath = redirectBase.pathname.replace(/\/$/, '');
                const fullPath = basePath + currentUrl.pathname;

                const newUrl = new URL(fullPath + currentUrl.search + currentUrl.hash, redirectBase.origin);

                window.location.href = newUrl.toString();
            }

            window.location.reload();
        });
    }
}