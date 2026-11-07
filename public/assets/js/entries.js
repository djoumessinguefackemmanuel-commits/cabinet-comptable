
/* =========================================================
   CABINET COMPTABLE
   JavaScript des écritures comptables
   Fichier : public/assets/js/entries.js
   ========================================================= */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
    initializeEntryLines();
    initializeEntryCalculations();
    initializeEntryValidation();
    initializeEntryDeleteButtons();
});

/* 1. Initialisation des lignes d'écriture */
function initializeEntryLines() {
    const addButtons = document.querySelectorAll(
        '[data-entry-add-line]'
    );

    addButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const targetSelector = button.dataset.entryAddLine;
            const container = document.querySelector(targetSelector);

            if (!container) {
                return;
            }

            addEntryLine(container);
        });
    });
}

/* 2. Ajouter une nouvelle ligne */
function addEntryLine(container) {
    const template = document.querySelector(
        '#entry-line-template'
    );

    if (!template) {
        return;
    }

    const fragment = template.content.cloneNode(true);

    container.appendChild(fragment);

    const newLine = container.lastElementChild;

    if (newLine) {
        initializeLineEvents(newLine);
        updateEntryTotals();
    }
}

/* 3. Initialiser les événements d'une ligne */
function initializeLineEvents(line) {
    const inputs = line.querySelectorAll(
        '[data-entry-debit], [data-entry-credit]'
    );

    inputs.forEach(function (input) {
        input.addEventListener('input', updateEntryTotals);
    });

    const deleteButton = line.querySelector(
        '[data-entry-delete]'
    );

    if (deleteButton) {
        deleteButton.addEventListener('click', function () {
            line.remove();
            updateEntryTotals();
        });
    }
}

/* 4. Initialiser les calculs des lignes existantes */
function initializeEntryCalculations() {
    const containers = document.querySelectorAll(
        '[data-entry-lines]'
    );

    containers.forEach(function (container) {
        container.querySelectorAll('tr').forEach(function (line) {
            initializeLineEvents(line);
        });
    });

    updateEntryTotals();
}

/* 5. Calculer les totaux débit et crédit */
function updateEntryTotals() {
    let totalDebit = 0;
    let totalCredit = 0;

    document.querySelectorAll('[data-entry-debit]').forEach(function (input) {
        totalDebit += getNumericValue(input.value);
    });

    document.querySelectorAll('[data-entry-credit]').forEach(function (input) {
        totalCredit += getNumericValue(input.value);
    });

    updateEntryTotalElement('[data-entry-total-debit]', totalDebit);
    updateEntryTotalElement('[data-entry-total-credit]', totalCredit);

    const difference = totalDebit - totalCredit;

    updateEntryTotalElement(
        '[data-entry-difference]',
        Math.abs(difference)
    );

    updateEntryBalanceStatus(totalDebit, totalCredit);
}

/* 6. Convertir une valeur en nombre */
function getNumericValue(value) {
    const normalizedValue = String(value || '')
        .replace(/\s/g, '')
        .replace(',', '.');

    const number = Number(normalizedValue);

    return Number.isFinite(number) ? number : 0;
}

/* 7. Mettre à jour un élément de total */
function updateEntryTotalElement(selector, value) {
    const element = document.querySelector(selector);

    if (!element) {
        return;
    }

    element.textContent = new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    }).format(value);
}

/* 8. Vérifier l'équilibre de l'écriture */
function updateEntryBalanceStatus(totalDebit, totalCredit) {
    const statusElements = document.querySelectorAll(
        '[data-entry-balance-status]'
    );

    const balanced = Math.abs(totalDebit - totalCredit) < 0.01;

    statusElements.forEach(function (element) {
        element.textContent = balanced
            ? 'Écriture équilibrée'
            : 'Écriture non équilibrée';

        element.classList.toggle('text-success', balanced);
        element.classList.toggle('text-danger', !balanced);
    });
}

/* 9. Validation du formulaire */
function initializeEntryValidation() {
    const forms = document.querySelectorAll(
        '[data-entry-form]'
    );

    forms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const debit = calculateEntryTotal('[data-entry-debit]');
            const credit = calculateEntryTotal('[data-entry-credit]');

            if (Math.abs(debit - credit) >= 0.01) {
                event.preventDefault();

                if (typeof window.showMessage === 'function') {
                    window.showMessage(
                        'Les totaux débit et crédit doivent être égaux.',
                        'error'
                    );
                } else {
                    window.alert(
                        'Les totaux débit et crédit doivent être égaux.'
                    );
                }
            }
        });
    });
}

/* 10. Calculer le total d'un ensemble de champs */
function calculateEntryTotal(selector) {
    let total = 0;

    document.querySelectorAll(selector).forEach(function (input) {
        total += getNumericValue(input.value);
    });

    return total;
}

/* 11. Gestion des boutons de suppression */
function initializeEntryDeleteButtons() {
    document.querySelectorAll('[data-entry-delete]').forEach(function (button) {
        if (button.dataset.initialized === 'true') {
            return;
        }

        button.dataset.initialized = 'true';

        button.addEventListener('click', function () {
            const line = button.closest('tr');

            if (line) {
                line.remove();
                updateEntryTotals();
            }
        });
    });
}
