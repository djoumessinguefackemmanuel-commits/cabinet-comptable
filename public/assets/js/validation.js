
/* =========================================================
   CABINET COMPTABLE
   JavaScript de validation des formulaires
   Fichier : public/assets/js/validation.js
   ========================================================= */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
    initializeValidationForms();
    initializeRequiredFields();
    initializeEmailValidation();
    initializePhoneValidation();
    initializeAmountValidation();
    initializePasswordValidation();
});

/* 1. Initialisation des formulaires */
function initializeValidationForms() {
    const forms = document.querySelectorAll('[data-validate-form]');

    forms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();

                const invalidField = form.querySelector(':invalid');

                if (invalidField) {
                    invalidField.focus();
                }
            }

            form.classList.add('was-validated');
        });
    });
}

/* 2. Vérification des champs obligatoires */
function initializeRequiredFields() {
    const fields = document.querySelectorAll('[data-required-field]');

    fields.forEach(function (field) {
        field.addEventListener('blur', function () {
            validateRequiredField(field);
        });
    });
}

function validateRequiredField(field) {
    const valid = field.value.trim() !== '';

    field.setCustomValidity(
        valid ? '' : 'Ce champ est obligatoire.'
    );

    return valid;
}

/* 3. Vérification des adresses e-mail */
function initializeEmailValidation() {
    const fields = document.querySelectorAll('[data-validate-email]');

    fields.forEach(function (field) {
        field.addEventListener('blur', function () {
            const value = field.value.trim();

            const valid = value === '' || isValidEmail(value);

            field.setCustomValidity(
                valid ? '' : 'Veuillez saisir une adresse e-mail valide.'
            );
        });
    });
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

/* 4. Vérification des téléphones camerounais */
function initializePhoneValidation() {
    const fields = document.querySelectorAll('[data-validate-phone]');

    fields.forEach(function (field) {
        field.addEventListener('blur', function () {
            const value = field.value.trim();

            const valid = value === '' || isValidPhone(value);

            field.setCustomValidity(
                valid ? '' : 'Veuillez saisir un numéro camerounais valide.'
            );
        });
    });
}

function isValidPhone(phone) {
    const cleaned = String(phone).replace(/[\s.-]/g, '');

    return /^(\+237|237)?6[0-9]{8}$/.test(cleaned);
}

/* 5. Vérification des montants */
function initializeAmountValidation() {
    const fields = document.querySelectorAll('[data-validate-amount]');

    fields.forEach(function (field) {
        field.addEventListener('input', function () {
            validateAmount(field);
        });
    });
}

function validateAmount(field) {
    const value = Number(String(field.value).replace(',', '.'));

    const valid = (
        field.value.trim() === '' ||
        (Number.isFinite(value) && value >= 0)
    );

    field.setCustomValidity(
        valid ? '' : 'Veuillez saisir un montant positif valide.'
    );

    return valid;
}

/* 6. Vérification des mots de passe */
function initializePasswordValidation() {
    const passwordFields = document.querySelectorAll(
        '[data-password-field]'
    );

    passwordFields.forEach(function (field) {
        field.addEventListener('input', function () {
            validatePasswordConfirmation(field.form);
        });
    });
}

function validatePasswordConfirmation(form) {
    if (!form) {
        return true;
    }

    const password = form.querySelector('[name="password"]');
    const confirmation = form.querySelector(
        '[name="password_confirmation"]'
    );

    if (!password || !confirmation) {
        return true;
    }

    const valid = password.value === confirmation.value;

    confirmation.setCustomValidity(
        valid ? '' : 'Les mots de passe ne correspondent pas.'
    );

    return valid;
}

/* 7. Afficher les erreurs de validation */
function displayValidationError(field, message) {
    if (!field) {
        return;
    }

    field.classList.add('is-invalid');

    let errorElement = field.parentElement.querySelector(
        '.validation-error'
    );

    if (!errorElement) {
        errorElement = document.createElement('div');
        errorElement.className = 'validation-error text-danger small';

        field.parentElement.appendChild(errorElement);
    }

    errorElement.textContent = message;
}

/* 8. Effacer les erreurs */
function clearValidationError(field) {
    if (!field) {
        return;
    }

    field.classList.remove('is-invalid');

    const errorElement = field.parentElement.querySelector(
        '.validation-error'
    );

    if (errorElement) {
        errorElement.remove();
    }
}

/* 9. Vérifier la longueur minimale */
function validateMinimumLength(value, minimum) {
    return String(value).trim().length >= minimum;
}

/* 10. Vérifier qu'une valeur est numérique */
function validateNumericValue(value) {
    return (
        value !== '' &&
        Number.isFinite(Number(String(value).replace(',', '.')))
    );
}

/* 11. Réinitialiser les erreurs d'un formulaire */
function resetFormValidation(form) {
    if (!form) {
        return;
    }

    form.classList.remove('was-validated');

    form.querySelectorAll('input, select, textarea').forEach(function (field) {
        field.setCustomValidity('');
        field.classList.remove('is-invalid', 'is-valid');
    });

    form.querySelectorAll('.validation-error').forEach(function (error) {
        error.remove();
    });
}
