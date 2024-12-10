import Inputmask from 'inputmask';
import { webCustom } from "./global-custom.js";

class PropertySearch {
    constructor() {
        this.form = document.querySelector('#property-search-form');

        if (!this.form) {
            // console.log('Form not found');
            return;
        }

        this.initForm();
        this.handleFieldsByRoleChange();
        this.handleRadioButtonsChange();
    }

    initForm() {
        webCustom.initMasks();
    }

    handleFieldsByRoleChange() {
        const enterpriseSections = this.form.querySelectorAll('[data-element="enterprise-section"]');

        const displayEnterpriseSectionByRole = (role) => {
            switch (role) {
                case 'lancamentos':
                    enterpriseSections.forEach((section) => {
                        section.style.display = 'block';
                    });

                    break;
                default:
                    enterpriseSections.forEach((section) => {
                        section.style.display = 'none';
                    });
                    break;
            }
        }

        const roleInput = this.form.querySelector('[name="role"]');

        // on load
        displayEnterpriseSectionByRole(roleInput.value);

        // on change
        roleInput.addEventListener('change', () => {
            displayEnterpriseSectionByRole(roleInput.value);
        });
    }

    handleRadioButtonsChange() {
        // Handle radio buttons
        const radioButtons = this.form.querySelectorAll('.btn-group .btn-check');

        radioButtons.forEach(function (button) {
            // button.setAttribute('data-checked', false);
            button.setAttribute('data-checked', button.checked);

            button.addEventListener('click', function (e) {
                var wasChecked = (button.getAttribute('data-checked') === 'true');

                radioButtons.forEach(function (b) {
                    b.setAttribute('data-checked', 'false');
                });

                if (wasChecked) {
                    button.checked = false;
                } else {
                    button.checked = true;
                    button.setAttribute('data-checked', 'true');
                }
            });
        });
    }
}

const propertySearch = new PropertySearch();
