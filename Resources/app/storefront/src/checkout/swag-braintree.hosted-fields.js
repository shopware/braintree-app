import Plugin from 'src/plugin-system/plugin.class';
import BraintreeClient from 'braintree-web/client';
import BraintreeHostedFields from 'braintree-web/hosted-fields';
import Braintree3DSecure from 'braintree-web/three-d-secure';
import BraintreeDataCollector from 'braintree-web/data-collector';
import DomAccess from 'src/helper/dom-access.helper';
import AppClient from '@friendsofshopware/storefront-sdk/service/app-client.service';
import PageLoadingIndicatorUtil from 'src/utility/loading-indicator/page-loading-indicator.util';
import ElementLoadingIndicatorUtil from 'src/utility/loading-indicator/element-loading-indicator.util';
import ButtonLoadingIndicatorUtil from 'src/utility/loading-indicator/button-loading-indicator.util';

const BASE_URL = 'http://localhost:8123/api';

/**
 * @typedef {module:braintree-web/client.Client} BraintreeClient
 *
 * @typedef {module:braintree-web/hosted-fields.HostedFields} BraintreeHostedFields
 * @typedef {module:braintree-web/hosted-fields.HostedFields~stateObject} BraintreeState
 * @typedef {module:braintree-web/hosted-fields.HostedFields~tokenizePayload} BraintreeTokenizedPayload
 *
 * @typedef {module:braintree-web/three-d-secure.ThreeDSecure} BraintreeThreeDSecure
 * @typedef {module:braintree-web/three-d-secure.ThreeDSecure~verifyPayload} BraintreeVerifedPayload
 *
 * @typedef {module:braintree-web/data-collector.dataCollector} BraintreeDataCollector
 * @typedef {module:braintree-web/data-collector.deviceData} BraintreeDeviceData
 */
export default class SwagBraintreeHostedFields extends Plugin {
    static options = {
        confirmOrderFormSelector: '#confirmOrderForm',
        confirmOrderButtonSelector: '#confirmOrderForm button[type=submit]',

        appShopId: null,
        placeholders: {},
        cardholderName: '',
        postalCode: '',

        currencyId: '',
        salesChannelId: '',
        noMerchantAccountId: '',

        numberFieldSelector: '#sw-braintree-payment-method__number',
        cvvFieldSelector: '#sw-braintree-payment-method__cvv',
        expirationDateFieldSelector: '#sw-braintree-payment-method__expirationDate',
        cardholderNameFieldSelector: '#sw-braintree-payment-method__cardholderName',
        postalCodeFieldSelector: '#sw-braintree-payment-method__postalCode',

        cartAmount: 0,
    }

    async init() {
        ElementLoadingIndicatorUtil.create(this.el);

        this._client = new AppClient('SwagBraintreeApp');

        const braintreeClient = await this.createClient();
        const braintreeHostedFields = await this.createHostedFields(braintreeClient);
        const braintree3DS = await this.create3DSecure(braintreeClient);
        const braintreeDataCollector = this.createDataCollector(braintreeClient); // can be resolved later

        braintreeHostedFields.on('blur', this.checkValidity.bind(this, braintreeHostedFields));
        DomAccess.querySelector(document, this.options.confirmOrderFormSelector)
            .addEventListener('submit', (event) => {
                this.onSubmitOrderConfirm(braintreeHostedFields, braintree3DS, braintreeDataCollector, event)
                    .catch(this.resetOnSubmitError.bind(this))
            });

        ElementLoadingIndicatorUtil.remove(this.el);
    }

    /**
     * @returns {Promise<BraintreeClient>} Client token of the merchant
     */
    async createClient() {
        const request = await this._client.post(`${BASE_URL}/client/token?shop-id=${this.options.appShopId}&currency-id=${this.options.currencyId}&sales-channel-id=${this.options.salesChannelId}`);

        if (!request.ok) throw new Error(await request.text());

        const authorization = (await request.json()).token;

        return BraintreeClient.create({ authorization });
    }

    /**
     * @param {BraintreeClient} client
     * @returns Promise<BraintreeHostedFields>
     */
    createHostedFields(client) {
        return BraintreeHostedFields.create({
            client,
            preventAutofill: false,
            styles: { input: { 'font-size': '14px' } },
            fields: {
                number: {
                    selector: this.options.numberFieldSelector,
                    placeholder: this.options.placeholders.number,
                },
                cvv: {
                    selector: this.options.cvvFieldSelector,
                    placeholder: this.options.placeholders.cvv,
                },
                expirationDate: {
                    selector: this.options.expirationDateFieldSelector,
                    placeholder: this.options.placeholders.expirationDate,
                },
                cardholderName: {
                    container: this.options.cardholderNameFieldSelector,
                    placeholder: this.options.placeholders.cardholderName,
                    prefill: this.options.cardholderName,
                },
                postalCode: {
                    selector: this.options.postalCodeFieldSelector,
                    placeholder: this.options.placeholders.postalCode,
                    prefill: this.options.postalCode,
                }
            },
        });
    }

    /**
     * @param {BraintreeClient} client
     * @returns Promise<BraintreeThreeDSecure>
     */
    create3DSecure(client) {
        return Braintree3DSecure.create({
            client,
            version: 2
        });
    }

    /**
     * @param {BraintreeClient} client
     * @returns Promise<BraintreeDataCollector>
     */
    createDataCollector(client) {
        return BraintreeDataCollector.create({ client });
    }

    /**
     * @param {BraintreeHostedFields} braintreeHostedFields
     * @param {BraintreeThreeDSecure} braintree3DS
     * @param {Promise<BraintreeDataCollector>} braintreeDataCollector
     * @param {SubmitEvent} event
     */
    async onSubmitOrderConfirm(braintreeHostedFields, braintree3DS, braintreeDataCollector, event) {
        event.preventDefault();

        if (!this.checkSubmitValidity(braintreeHostedFields)) return;

        PageLoadingIndicatorUtil.create();

        let payload = await this.tokenizeTransaction(braintreeHostedFields);

        payload = await this.validateWith3DSecure(braintree3DS, payload);

        const braintreeDeviceData = await (await braintreeDataCollector).getDeviceData();

        this.setPayloadForSubmit(payload.nonce, braintreeDeviceData, event.target);

        event.target.submit();
    }

    /**
     * Checks if the fields are valid or returns the first invalid field
     *
     * @param {BraintreeHostedFields} braintreeHostedFields Braintree hosted fields Instance
     * @param {BraintreeState?} state Braintree state
     * @returns {HTMLElement|undefined} First invalid element if exists
     */
    checkValidity(braintreeHostedFields, state) {
        if (!state) state = braintreeHostedFields.getState();

        let invalid = undefined;
        for (let field of ['cvv', 'number', 'expirationDate', 'cardholderName', 'postalCode']) {
            if (state.fields[field].isValid) {
                state.fields[field].container.classList.remove('is-invalid');
                braintreeHostedFields.setMessage({ field, message: '' }); // screen reader
                continue;
            }

            state.fields[field].container.classList.add('is-invalid');
            braintreeHostedFields.setMessage({ field, message: 'Invalid' }); // screen reader

            invalid ??= state.fields[field].container;
        }

        return invalid;
    }

    /**
     * Checks if the fields are valid to be submitted
     * Also highlights the first invalid field
     *
     * @param {BraintreeHostedFields} braintreeHostedFields Braintree hosted fields Instance
     * @returns {boolean} ready for submit
     */
    checkSubmitValidity(braintreeHostedFields) {
        const invalidField = this.checkValidity(braintreeHostedFields);
        if (!invalidField) return true;

        invalidField.focus();
        invalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
        this.resetOrderConfirmButton();

        return false;
    }

    /**
     * Tokenize the payment and set the nonce to a hidden field to be submitted
     *
     * @param {BraintreeHostedFields} braintreeHostedFields Braintree hosted fields Instance
     * @returns {Promise<BraintreeTokenizedPayload>} tokenized payload
     */
    tokenizeTransaction(braintreeHostedFields) {
        //const cardholderName = document.querySelector(this.options.cardholderNameFieldSelector)?.value;

        return braintreeHostedFields.tokenize({
            vault: true,
        });
    }

    /**
     * @param {string} braintreeNonce
     * @param {BraintreeDeviceData} braintreeDeviceData Collected device data
     * @param {HTMLElement} confirmOrderForm
     */
    setPayloadForSubmit(braintreeNonce, braintreeDeviceData, confirmOrderForm) {
        const createInput = (name, value) => {
            const input = document.createElement('input');
            input.setAttribute('type', 'hidden');
            input.setAttribute('name', name);
            input.setAttribute('value', value);
            confirmOrderForm.appendChild(input);
        };

        createInput('braintreeNonce', braintreeNonce);
        createInput('braintreeDeviceData', braintreeDeviceData);
    }

    /**
     * @param {BraintreeThreeDSecure} braintree3DS
     * @param {BraintreeTokenizedPayload} payload
     * @returns {Promise<BraintreeVerifedPayload>}
     */
    async validateWith3DSecure(braintree3DS, payload) {
        braintree3DS.on('lookup-complete', (_, next) => void next());

        return braintree3DS.verifyCard({
            amount: this.options.cartAmount.toString(),
            nonce: payload.nonce,
            bin: payload.details.bin,
            collectDeviceData: true,
        })
    }

    /**
     * Resets the order confirm button for a new submit
     */
    resetOrderConfirmButton() {
        const confirmOrderFormButton = DomAccess.querySelector(document, this.options.confirmOrderButtonSelector);
        (new ButtonLoadingIndicatorUtil(confirmOrderFormButton)).remove();
        confirmOrderFormButton.removeAttribute('disabled');
    }

    /**
     * @param {Error} error
     * @private
     */
    resetOnSubmitError(error) {
        if (!!error) console.error(error);

        this.resetOrderConfirmButton();
        PageLoadingIndicatorUtil.remove();
    }
}
