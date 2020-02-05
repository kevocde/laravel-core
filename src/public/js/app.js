/**
 * Inicializa los select2 del formulario de búsqueda rápido
 */
function loadSelect2SelectsOfForm(formFash) {
    $(`select[form="${formFash}"]`).each(function (idx, element) {
        $(element).select2()
    })
}

/**
 * Añade los respectivos eventos a los elementos asociados al formulario de búsqueda rápida
 */
function addEventsToFashForm(gridId, formFash) {
    $(`#${gridId} table thead`).find(`[form="${formFash}"]`).on('change focusout keyup', function (evt) {
        let submit = true
        if (evt.type == 'keyup') {
            let keycode = (event.keyCode ? event.keyCode : event.which)
            submit = (keycode == '13')
        }
        if (submit) $(`#${formFash}`).submit()
    })
}

/**
 * Carga en los form-group la clase required
 */
function loadRequiredSenialOfInputs() {
    $('select.form-control, input.form-control, checkbox.form-control, radio.form-control').each((idx, element) => {
        let parent = $(element).parent()
        if ($(element).prop('required') && parent.hasClass('form-group')) {
            parent.addClass('required')
        }
    })
}

/**
 * Función incializadora, esta carga configuraciones específicas
 */
function initSigaWeb() {
    try {
        // Configuración de select2
        $.fn.select2.defaults.set('theme', 'bootstrap4')
        // Carga de los labels de los inputs cuando son requeridos
        loadRequiredSenialOfInputs()
    } catch(err) {
        console.error(err)
    }
}

window.addEventListener('DOMContentLoaded', function () {
    initSigaWeb()
})