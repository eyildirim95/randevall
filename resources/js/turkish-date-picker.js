import flatpickr from 'flatpickr/dist/flatpickr';
import { Turkish } from 'flatpickr/dist/l10n/tr.js';

flatpickr.localize(Turkish);

function buildOptions(input) {
    const options = {
        locale: Turkish,
        altInput: true,
        altFormat: 'd.m.Y',
        dateFormat: 'Y-m-d',
        disableMobile: true,
        allowInput: false,
        monthSelectorType: 'dropdown',
        altInputClass: input.className || 'form-control',
    };

    if (input.min) {
        options.minDate = input.min;
    }

    if (input.max) {
        options.maxDate = input.max;
    }

    if (input.value) {
        options.defaultDate = input.value;
    }

    return options;
}

export function initTurkishDatePickers(root = document) {
    root.querySelectorAll('input[type="date"]:not([data-no-datepicker])').forEach(input => {
        if (input._flatpickr) {
            return;
        }

        flatpickr(input, buildOptions(input));
    });
}
