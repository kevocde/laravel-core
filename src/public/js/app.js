jQuery.validator.setDefaults({
    errorElement: 'div',
    errorClass: 'invalid-feedback d-block',
    highlight: function (element, errorClass) {
        $(element).addClass('is-invalid')
    },
    unhighlight: function (element, errorClass, validClass) {
        $(element).removeClass('is-invalid')
        $(element).addClass('is-valid')
    }
})