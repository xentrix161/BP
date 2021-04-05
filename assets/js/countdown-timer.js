export default class CountdownTimer {
    constructor() {
        /** @type {null | Node | Element} */
        this.timerEl = null;
        /** @type {null | number} */
        this.time = null;
        /** @type {null | number} */
        this.timeout = null;

        /** @type {string} */
        this.redirectPath = '';

        this.collector();
    }

    getUrl(el) {
        let redirectPath = el.getAttribute('data-redirect');

        if (!redirectPath) {
            return window.domain + '/homepage';
        } else if (redirectPath.indexOf('/') === 0) {
            return window.domain + redirectPath;
        }
        return redirectPath;
    }

    /**
     * @returns {void}
     * @private
     */
    collector() {
        this.timerEl = document.querySelector('[data-redirect-time]');

        if (!this.timerEl) {
            this.timerEl = document.querySelector('[data-redirect]');

            if (!this.timerEl) {
                return;
            }
        }
        this.setCounterData();

        this.countdown(() => {
            window.location.href = this.redirectPath;
        });
    }

    setCounterData() {
        /** @type {string} */
        let timeValue = this.timerEl.getAttribute('data-redirect-time');
        if (this.isNumeric(timeValue)) {
            this.time = parseInt(timeValue);
            this.redirectPath = this.getUrl(this.timerEl);
        } else {
            const url = this.getUrl(this.timerEl);
            if (!url) {
                window.location.href = window.domain + '/homepage';
            } else {
                window.location.href = url;
            }
        }
    }

    /**
     * @description Funkcia vracia TRUE ak je hodnota zadaná v parametri numerická.
     * @param {string | number} value
     * @returns {boolean}
     * @private
     */
    isNumeric(value) {
        return !isNaN(parseInt(value));
    }

    countdown(callback) {
        if (typeof callback === 'function' && this.isNumeric(this.time)) {
            this.setNewCountdownValue(this.time);
            this.timeout = setInterval(() => {
                if (this.time <= 0) {
                    clearInterval(this.timeout);
                    callback();
                } else {
                    this.time--;
                    this.setNewCountdownValue(this.time);
                }
            }, 1000);
        }
    }

    setNewCountdownValue(value) {
        this.timerEl.innerText = value + ' ' + this.declension(value, ['sekúnd', 'sekunda', 'sekundy']);
    }

    /**
     * @description Funkcia na skloňovanie slov na základe vstupného čísla.
     * @param {number} value
     * @param {string[]} declensionArray
     * @returns {string}
     */
    declension(value, declensionArray) {
        value = isNaN(+value) ? 0 : value;
        if (value === 1) {
            console.log(value, 1);
            return declensionArray[1];
        } else if (value >= 2 && value <= 4) {
            console.log(value, 2);
            return declensionArray[2];
        }
        console.log(value, 0);
        return declensionArray[0];
    }
}