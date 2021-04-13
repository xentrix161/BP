(function () {
    var form = $('.rate-wrapper form');
    var input = form.find('input');

    form.submit(function (evt) {
        evt.preventDefault();
        var self = evt.currentTarget;
        var url = self.action;
        var data = input.serializeArray();
        data[0]['ajax'] = true;
        $.post(url, {data: data}, function (response) {
            if (response.success === true) {
                var rateInfo = $('.rate-info');
                var rateValue = rateInfo.find('span:first-child');
                var countValue = rateInfo.find('span:last-child');
                rateValue.text(response.rating);
                var text = declension(response.count, ['Hodnotilo', 'Hodnotil', 'Hodnotili']) + ' ' + response.count + ' ' + declension(response.count, ['používateľov', 'používateľ', 'používatelia']);
                countValue.text(text);

                rateInfo.show();
                form.hide();
            }
        });
    });

    input.on('change', function () {
        var data = input.serializeArray();
        if (window.confirm('Naozaj si želáte ohodnotiť tento tovar ' + data[0].value + ' z 5 hviezdičiek?')) {
            form.submit()
        }
    })

    /**
     * @description Funkcia na skloňovanie slov na základe vstupného čísla.
     * @param {number} value
     * @param {string[]} declensionArray
     * @returns {string}
     */
    function declension(value, declensionArray) {
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
})()