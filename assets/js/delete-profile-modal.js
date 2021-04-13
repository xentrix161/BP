(function () {
    var modal = $('.delete-modal');
    var form = $('#delete-user-form');
    var deleteBtn = $('.delete-acc');
    var submitBtn = $('#delete-submit');
    var resetBtn = $('#delete-reset');
    var messageContent = $('#delete-modal-message');

    deleteBtn.on('click', function (e) {
        e.preventDefault();
        modal.show();
    });

    resetBtn.on('click', function (e) {
        e.preventDefault();
        form.trigger('reset');
        modal.hide();
    });

    submitBtn.on('click', function (e) {
        e.preventDefault();

        var inputs = $('#delete-user-form :input');
        var values = {};
        inputs.each(function () {
            values[this.name] = $(this).val();
        });

        $.post('/user/user-account-delete', values)
            .done(function (response) {
                console.log(response);
                if (response.success === true) {
                    timerMessage(response.message, 1);
                } else {
                    if (!!response.message) {
                        timerMessage(response.message);
                    } else {
                        timerMessage('Niečo sa nepodarilo. Skúste znovu prosím.');
                    }
                }
            })
            .fail(function (xhr) {
                if (xhr.status === 500 || xhr.status === 0) {
                    timerMessage('Niečo sa nepodarilo. Skúste znovu prosím.');
                }
            });
    });

    function timerMessage(message, type) {
        form.hide();
        messageContent.show();
        messageContent.text(message);
        setTimeout(function () {
            form.trigger('reset');
            modal.hide();
            messageContent.text('');
            form.show();
            messageContent.hide();
            if (type === 1) {
                window.location.href = 'http://127.0.0.1:8000/homepage';
            } else {
            }
        }, 5000);
    }
})();