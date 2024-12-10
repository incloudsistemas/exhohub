import axios from 'axios';
import Swal from 'sweetalert2';

export class WebGlobalCustom {
    constructor() {
        this.displaySliderArrows();
        this.gdprAlert();
        this.displayWhatsappButton();
        this.customLink();
        this.openAjaxModal();
    }

    // Return base href
    getBaseHref() {
        return window.location.origin;
    }

    displaySliderArrows() {
        const slider = document.querySelector('#slider');

        if (!slider) {
            return;
        }

        const leftArrow = slider.querySelector('.slider-arrow-left');
        const rightArrow = slider.querySelector('.slider-arrow-right');

        if (leftArrow && rightArrow) {
            leftArrow.style.display = 'none';
            rightArrow.style.display = 'none';

            // Show hidden Arrows on hover
            slider.addEventListener('mouseover', function handleMouseOver() {
                leftArrow.style.display = 'block';
                rightArrow.style.display = 'block';
            });

            // Hide Arrows on mouse out
            slider.addEventListener('mouseout', function handleMouseOut() {
                leftArrow.style.display = 'none';
                rightArrow.style.display = 'none';
            });
        }
    }

    gdprAlert() {
        const gdprAlertElement = document.querySelector('#gdpr-alert');
        const acceptBtn = document.querySelector('#gdpr-accept');

        if (!gdprAlertElement || !acceptBtn) {
            // console.error('Elementos do GDPR não foram encontrados!');
            return;
        }

        const gdprAccepted = localStorage.getItem('gdprAccepted');
        // console.log('GDPR Accepted:', gdprAccepted);

        if (gdprAccepted === 'true') {
            // gdprAlertElement.style.display = 'none';
            gdprAlertElement.classList.add('d-none');
        } else {
            // gdprAlertElement.style.display = 'block';
            gdprAlertElement.classList.remove('d-none');
        }

        acceptBtn.addEventListener('click', function () {
            // console.log('Aceitando GDPR e salvando no Local Storage');
            localStorage.setItem('gdprAccepted', 'true');

            // gdprAlertElement.style.display = 'none';
            gdprAlertElement.classList.add('d-none');
        });
    }

    displayWhatsappButton() {
        const whatsappButton = document.querySelector('#whatsapp-button');

        if (!whatsappButton) {
            return;
        }

        setTimeout(function () {
            whatsappButton.style.display = 'block';
        }, 2000); // = 2 secs
    }

    customLink() {
        const links = document.querySelectorAll('.custom-link');

        links.forEach(element => {
            element.addEventListener('click', function (event) {
                event.preventDefault();

                const link = element.getAttribute('data-href');
                let target = element.getAttribute('data-target');

                if (link && link != '') {
                    if (!target) {
                        target = '_self';
                    }

                    window.open(link, target);
                }
            });
        });
    }

    openAjaxModal() {
        const ajaxModals = document.querySelectorAll('.ajax-modal');

        ajaxModals.forEach(ajaxModal => {
            ajaxModal.addEventListener('click', (event) => {
                event.preventDefault();

                const modalTarget = ajaxModal.getAttribute('data-bs-target');
                const modalEl = document.querySelector(modalTarget);

                if (!modalEl) {
                    return;
                }

                const ajaxRoute = ajaxModal.getAttribute('href') || ajaxModal.getAttribute('data-href');

                if (!ajaxRoute) {
                    return;
                }

                axios.get(ajaxRoute)
                    .then((response) => {
                        modalEl.querySelector('.modal-content').innerHTML = response.data;
                    }).catch((error) => {
                        this.showSystemErrorMessage(error);
                    }).then(() => {
                        // always executed
                    });
            });
        });
    }

    googleRecaptcha(form) {
        const gRecaptchaSite = form.querySelector('.g-recaptcha-site');
        const gRecaptchaResponse = form.querySelector('.g-recaptcha-response');

        if (gRecaptchaSite && gRecaptchaResponse) {
            var recaptchaSite = gRecaptchaSite.value;
            // console.log(recaptchaSite);

            grecaptcha.ready(function () {
                grecaptcha.execute('' + recaptchaSite + '', { action: 'homepage' })
                    .then(function (token) {
                        gRecaptchaResponse.value = token;
                    });
            });
        }
    }

    formRuleCheck(form) {
        const ruleCheck = form.querySelector('.rule-check');
        const submitButton = form.querySelector('[data-form-action="submit"]');

        if (!ruleCheck) {
            return;
        }

        submitButton.disabled = true;

        ruleCheck.addEventListener('change', function () {
            submitButton.disabled = !this.checked;
        });
    }

    submitButtonToggleIndicator(button) {
        const hasIndicator = button.getAttribute('data-indicator') === 'on';
        const indicatorLabel = button.querySelector('.indicator-label');
        const indicatorProgress = button.querySelector('.indicator-progress');

        if (hasIndicator) {
            indicatorLabel.style.display = 'none';
            indicatorProgress.style.display = 'block';
        } else {
            indicatorLabel.style.display = 'block';
            indicatorProgress.style.display = 'none';
        }
    }

    // InputMask --- official docs reference: https://github.com/RobinHerbots/Inputmask
    initMasks() {
        // Date
        Inputmask({
            'alias': 'datetime',
            'inputFormat': 'dd/mm/yyyy',
            'placeholder': '__/__/____'
        }).mask('.date_ptbr_mask');

        // DateTime
        Inputmask({
            'alias': 'datetime',
            'inputFormat': 'dd/mm/yyyy HH:MM',
            'placeholder': '__/__/____ __:__'
        }).mask('.datetime_ptbr_mask');

        // Time
        Inputmask({
            'alias': 'datetime',
            'inputFormat': 'HH:MM',
            'placeholder': '__:__'
        }).mask('.time_mask');

        // Year
        Inputmask({
            'alias': 'datetime',
            'inputFormat': 'yyyy',
            'placeholder': '____'
        }).mask('.year_mask');

        // CEP
        Inputmask({
            'mask': '99999-999',
            'placeholder': '_____-___'
        }).mask('.zipcode_ptbr_mask');

        // CPF
        Inputmask({
            'mask': '999.999.999-99',
            'placeholder': '___.___.___-__'
        }).mask('.cpf_mask');

        // CNPJ
        Inputmask({
            'mask': "99.999.999/9999-99",
            'placeholder': '__.___.___/____-__'
        }).mask('.cnpj_mask');

        // LICENSE PLATE
        Inputmask({
            'mask': 'AAA-9999',
            'definitions': {
                'A': {
                    'validator': '[A-Za-z]',
                    'cardinality': 1
                }
            },
            'placeholder': '___-____'
        }).mask('.license_plate_ptbr_mask');

        // CURRENCY
        Inputmask({
            'alias': 'currency',
            'numericInput': true,
            'groupSeparator': '.',
            'autoGroup': true,
            'digits': 2,
            'radixPoint': ',',
            'radixFocus': true,
            // 'autoUnmask': true,
            // 'removeMaskOnSubmit': true,
            'rightAlign': false,
            'prefix': 'R$ ',
            'placeholder': '0'
        }).mask('.currency_ptbr_mask');

        // PERCENT
        Inputmask({
            'alias': 'percentage',
            'digits': 2,
            'radixPoint': ',',
            'digitsOptional': false,
            'rightAlign': false,
            'placeholder': '0'
        }).mask('.percent_ptbr_mask');

        // FLOAT
        Inputmask({
            'alias': 'numeric',
            'numericInput': true,
            'radixPoint': ',',
            'autoGroup': true,
            'groupSeparator': '.',
            'digits': 2,
            'digitsOptional': false,
            'allowMinus': true,
            'rightAlign': false,
            'placeholder': '0'
        }).mask('.float_ptbr_mask');

        // DDD + Phone
        Inputmask({
            'mask': ['(99) 9999-9999', '(99) 99999-9999'],
            'keepStatic': true
        }).mask('.phone_ptbr_mask');
    }

    // Clear all form inputs
    clearForm(form) {
        for (var i = 0; i < form.elements.length; i++) {
            form.elements[i].classList.remove("is-valid");

            if (form.elements[i].type === "text" || form.elements[i].type === "email" || form.elements[i].type === "textarea" || form.elements[i].type === "number" || form.elements[i].type === "password") {
                form.elements[i].value = "";
            } else if (form.elements[i].type === "radio" || form.elements[i].type === "checkbox") {
                form.elements[i].checked = false;
            } else if (form.elements[i].tagName === "SELECT") {
                form.elements[i].selectedIndex = 0;
            } else if (form.elements[i].type === "file") {
                form.elements[i].value = null;
            }
        }

        // Clear select2 plugins
        const select2Fields = form.querySelectorAll('[data-control="select2"]');
        select2Fields.forEach(select2 => {
            $(select2).val(null).trigger('change');
        });
    }

    showValidatorErrorMessage() {
        // Show popup warning. For more info check the plugin's official documentation: https://sweetalert2.github.io/
        Swal.fire({
            // title: "",
            text: "Desculpe, algumas informações foram inseridas incorretamente. Por favor verifique os dados preenchidos e tente novamente.",
            icon: "warning",
            buttonsStyling: false,
            confirmButtonText: "Ok, entendi!",
            customClass: {
                confirmButton: "button button-primary button-small button-rounded"
            }
        });
    }

    showFormErrorMessage(response) {
        console.log(response);

        Swal.fire({
            title: "Desculpe, alguns erros foram identificados ao enviar a requisição, por favor tente novamente.",
            text: response.message,
            icon: "error",
            buttonsStyling: false,
            confirmButtonText: "Ok, entendi!",
            customClass: {
                confirmButton: "button button-primary button-small button-rounded"
            }
        });
    }

    showSystemErrorMessage(error) {
        console.log(error);

        if (error.response) {
            // let dataMessage = error.response.data.message;
            let dataMessage = "";
            let dataErrors = error.response.data.errors;

            for (const errorsKey in dataErrors) {
                if (!dataErrors.hasOwnProperty(errorsKey)) continue;

                dataMessage += "\r\n" + dataErrors[errorsKey];
            }

            Swal.fire({
                title: "Desculpe, alguns erros foram identificados ao enviar a requisição, por favor tente novamente.",
                text: dataMessage,
                icon: "error",
                buttonsStyling: false,
                confirmButtonText: "Ok, entendi!",
                customClass: {
                    confirmButton: "button button-primary button-small button-rounded"
                }
            });
        } else {
            Swal.fire({
                title: "Desculpe, algum erro ocorreu ao enviar a requisição, por favor tente novamente.",
                icon: "error",
                buttonsStyling: false,
                confirmButtonText: "Ok, entendi!",
                customClass: {
                    confirmButton: "button button-primary button-small button-rounded"
                }
            });
        }
    }
}

export const webCustom = new WebGlobalCustom();
