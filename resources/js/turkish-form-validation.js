/**
 * HTML5 form dogrulama mesajlarini Turkce gosterir.
 * data-msg-required, data-msg-email vb. ile alan bazli ozellestirilebilir.
 */
function turkishValidationMessage(field) {
    const validity = field.validity;

    if (validity.valid) {
        return '';
    }

    if (validity.valueMissing) {
        if (field.dataset.msgRequired) {
            return field.dataset.msgRequired;
        }
        if (field.type === 'checkbox' || field.type === 'radio') {
            return 'Devam etmek için bu kutuyu işaretlemeniz gerekir.';
        }
        if (field.tagName === 'SELECT') {
            return 'Lütfen listeden bir seçenek seçin.';
        }
        if (field.type === 'date') {
            return 'Lütfen bir tarih seçin.';
        }
        if (field.type === 'email') {
            return 'E-posta adresi girin.';
        }
        return 'Bu alan zorunludur.';
    }

    if (validity.typeMismatch) {
        if (field.dataset.msgEmail) {
            return field.dataset.msgEmail;
        }
        if (field.type === 'email') {
            return 'Geçerli bir e-posta adresi girin.';
        }
        return 'Geçersiz değer.';
    }

    if (validity.tooShort) {
        return `En az ${field.minLength} karakter girin.`;
    }

    if (validity.tooLong) {
        return `En fazla ${field.maxLength} karakter girebilirsiniz.`;
    }

    if (validity.rangeUnderflow) {
        return field.type === 'date' ? 'Seçilen tarih çok erken.' : 'Girilen değer çok küçük.';
    }

    if (validity.rangeOverflow) {
        return field.type === 'date' ? 'Seçilen tarih çok ileri.' : 'Girilen değer çok büyük.';
    }

    if (validity.patternMismatch) {
        return field.dataset.msgPattern || 'Geçersiz format.';
    }

    return 'Lütfen geçerli bir değer girin.';
}

function isValidatableField(field) {
    return field instanceof HTMLInputElement
        || field instanceof HTMLSelectElement
        || field instanceof HTMLTextAreaElement;
}

export function initTurkishFormValidation(root = document) {
    root.querySelectorAll('form').forEach(form => {
        form.addEventListener('invalid', event => {
            const field = event.target;
            if (! isValidatableField(field)) {
                return;
            }

            field.setCustomValidity(turkishValidationMessage(field));
        }, true);

        const clearCustomValidity = event => {
            const field = event.target;
            if (isValidatableField(field)) {
                field.setCustomValidity('');
            }
        };

        form.addEventListener('input', clearCustomValidity, true);
        form.addEventListener('change', clearCustomValidity, true);
    });
}
