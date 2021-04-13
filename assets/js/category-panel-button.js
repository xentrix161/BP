(function () {
    var checkbox = $('#panel-open-close');

    checkbox.on('change', function (evt) {
        var self = $(this);
        window.localStorage.setItem('panel', self.is(':checked'));
    })

    if (window.localStorage.getItem('panel') === 'true') {
        checkbox.prop('checked', true);
    } else if (window.localStorage.getItem('panel') === 'false') {
        checkbox.prop('checked', false);
    }
})()