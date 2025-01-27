import 'vite/modulepreload-polyfill';
import '@shopware-ag/meteor-component-library/styles.css';
import './css/shopware-reboot.scss';
import './src/main';

Array.prototype.unique = function <T> (onlyTruthy?: boolean): Array<T> {
    return this.filter((v, idx, a) => a.indexOf(v) === idx || (onlyTruthy === true && !!v));
};
