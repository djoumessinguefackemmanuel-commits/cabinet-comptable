
/* =========================================================
   CABINET COMPTABLE
   JavaScript de gestion des entreprises
   Fichier : public/assets/js/companies.js
   ========================================================= */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
    initializeCompanySearch();
    initializeCompanyFilters();
    initializeCompanyForms();
    initializeCompanyDelete();
    initializeCompanyPhoneFields();
});

/* 1. Recherche d'une entreprise */
function initializeCompanySearch() {
    const searchInputs = document.querySelectorAll(
        '[data-company-search]'
    );

    searchInputs.forEach(function (input) {
        input.addEventListener('input', function () {
            const targetSelector = input.dataset.companySearch;
            const container = document.querySelector(targetSelector);

            if (!container) {
                return;
            }

            const query = input.value.trim().toLowerCase();

            container.querySelectorAll('[data-company-item]').forEach(function (item) {
                const content = item.textContent.toLowerCase();

                item.hidden = !content.includes(query);
            });
        });
    });
}

/* 2. Filtrer les entreprises */
function initializeCompanyFilters() {
    const filters = document.querySelectorAll(
        '[data-company-filter]'
    );

    filters.forEach(function (filter) {
        filter.addEventListener('change', function () {
            const targetSelector = filter.dataset.companyFilter;
            const container = document.querySelector(targetSelector);

            if (!container) {
                return;
            }

            const selectedValue = filter.value;

            container.querySelectorAll('[data-company-item]').forEach(function (item) {
                const itemValue = item.dataset.companyStatus;

                item.hidden = (
                    selectedValue !== 'all' &&
                    itemValue !== selectedValue
                );
            });
        });
    });
}

/* 3. Validation des formulaires entreprise */
function initializeCompanyForms() {
    const forms = document.querySelectorAll(
        '[data-company-form]'
    );

    forms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const name = form.querySelector('[name="name"]');
            const email = form.querySelector('[name="email"]');
            const phone = form.querySelector('[name="phone"]');

            if (name && name.value.trim() === '') {
                event.preventDefault();
                showCompanyError('Le nom de l’entreprise est obligatoire.');
                name.focus();
                return;
            }

            if (email && email.value.trim() !== '' && !isValidCompanyEmail(email.value)) {
                event.preventDefault();
                showCompanyError('Veuillez saisir une adresse e-mail valide.');
                email.focus();
                return;
            }

            if (phone && phone.value.trim() !== '' && !isValidCompanyPhone(phone.value)) {
                event.preventDefault();
                showCompanyError('Veuillez vérifier le numéro de téléphone.');
                phone.focus();
            }
        });
    });
}

/* 4. Vérifier le format d'un e-mail */
function isValidCompanyEmail(email) {
    const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    return pattern.test(String(email).trim());
}

/* 5. Vérifier le numéro de téléphone */
function isValidCompanyPhone(phone) {
    const cleanedPhone = String(phone).replace(/[\s.-]/g, '');

    return /^(\+237)?6[0-9]{8}$/.test(cleanedPhone);
}

/* 6. Confirmation de suppression */
function initializeCompanyDelete() {
    const deleteButtons = document.querySelectorAll(
        '[data-company-delete]'
    );

    deleteButtons.forEach(function (button) {
        button.addEventListener('click', function (event) {
            const message = button.dataset.confirmMessage ||
                'Voulez-vous vraiment supprimer cette entreprise ?';

            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });
}

/* 7. Vérification des champs téléphone */
function initializeCompanyPhoneFields() {
    const phoneFields = document.querySelectorAll(
        '[data-company-phone]'
    );

    phoneFields.forEach(function (field) {
        field.addEventListener('blur', function () {
            if (
                field.value.trim() !== '' &&
                !isValidCompanyPhone(field.value)
            ) {
                field.setCustomValidity(
                    'Saisissez un numéro camerounais valide.'
                );
            } else {
                field.setCustomValidity('');
            }
        });
    });
}

/* 8. Afficher un message d'erreur */
function showCompanyError(message) {
    if (typeof window.showMessage === 'function') {
        window.showMessage(message, 'error');
        return;
    }

    window.alert(message);
}

/* 9. Réinitialiser les filtres */
function resetCompanyFilters() {
    document.querySelectorAll('[data-company-filter]').forEach(function (filter) {
        filter.value = 'all';
        filter.dispatchEvent(new Event('change'));
    });

    document.querySelectorAll('[data-company-search]').forEach(function (input) {
        input.value = '';
        input.dispatchEvent(new Event('input'));
    });
}
