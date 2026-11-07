
/* =========================================================
   CABINET COMPTABLE
   JavaScript du module comptable
   Fichier : public/assets/js/accounting.js
   ========================================================= */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
    initializeAccountingSearch();
    initializeAccountingFilters();
    initializeAccountingSelectAll();
    initializeAccountingConfirmations();
    initializeAccountingRowActions();
});

/* 1. Recherche dans les tableaux comptables */
function initializeAccountingSearch() {
    const searchInputs = document.querySelectorAll(
        '[data-accounting-search]'
    );

    searchInputs.forEach(function (input) {
        input.addEventListener('input', function () {
            const tableSelector = input.dataset.accountingSearch;
            const table = document.querySelector(tableSelector);

            if (!table) {
                return;
            }

            const query = input.value.trim().toLowerCase();
            const rows = table.querySelectorAll('tbody tr');

            rows.forEach(function (row) {
                const content = row.textContent.toLowerCase();

                row.hidden = !content.includes(query);
            });
        });
    });
}

/* 2. Filtres des opérations comptables */
function initializeAccountingFilters() {
    const filters = document.querySelectorAll(
        '[data-accounting-filter]'
    );

    filters.forEach(function (filter) {
        filter.addEventListener('change', function () {
            const tableSelector = filter.dataset.accountingFilter;
            const table = document.querySelector(tableSelector);

            if (!table) {
                return;
            }

            const filterValue = filter.value;
            const filterColumn = filter.dataset.filterColumn;

            table.querySelectorAll('tbody tr').forEach(function (row) {
                if (filterValue === 'all' || filterValue === '') {
                    row.hidden = false;
                    return;
                }

                const cell = row.querySelector(
                    '[data-column="' + filterColumn + '"]'
                );

                if (!cell) {
                    row.hidden = true;
                    return;
                }

                row.hidden = cell.textContent.trim() !== filterValue;
            });
        });
    });
}

/* 3. Sélectionner ou désélectionner toutes les lignes */
function initializeAccountingSelectAll() {
    const selectAllInputs = document.querySelectorAll(
        '[data-select-all]'
    );

    selectAllInputs.forEach(function (selectAll) {
        selectAll.addEventListener('change', function () {
            const targetSelector = selectAll.dataset.selectAll;
            const container = document.querySelector(targetSelector);

            if (!container) {
                return;
            }

            container.querySelectorAll(
                'input[type="checkbox"][data-row-select]'
            ).forEach(function (checkbox) {
                checkbox.checked = selectAll.checked;
            });
        });
    });
}

/* 4. Confirmation des opérations sensibles */
function initializeAccountingConfirmations() {
    const buttons = document.querySelectorAll(
        '[data-accounting-confirm]'
    );

    buttons.forEach(function (button) {
        button.addEventListener('click', function (event) {
            const message = button.dataset.accountingConfirm ||
                'Voulez-vous confirmer cette opération ?';

            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });
}

/* 5. Actions sur les lignes comptables */
function initializeAccountingRowActions() {
    const actionButtons = document.querySelectorAll(
        '[data-accounting-action]'
    );

    actionButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const action = button.dataset.accountingAction;
            const row = button.closest('tr');

            if (!row) {
                return;
            }

            if (action === 'print') {
                window.print();
            }

            if (action === 'select') {
                const checkbox = row.querySelector(
                    'input[type="checkbox"][data-row-select]'
                );

                if (checkbox) {
                    checkbox.checked = !checkbox.checked;
                }
            }
        });
    });
}

/* 6. Récupérer les lignes sélectionnées */
function getSelectedAccountingRows(containerSelector) {
    const container = document.querySelector(containerSelector);

    if (!container) {
        return [];
    }

    return Array.from(
        container.querySelectorAll(
            'input[type="checkbox"][data-row-select]:checked'
        )
    );
}

/* 7. Formater un montant comptable */
function formatAccountingAmount(amount) {
    const value = Number(amount);

    if (!Number.isFinite(value)) {
        return '0 FCFA';
    }

    return new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    }).format(value) + ' FCFA';
}

/* 8. Calculer le total d'une liste de montants */
function calculateAccountingTotal(amounts) {
    if (!Array.isArray(amounts)) {
        return 0;
    }

    return amounts.reduce(function (total, amount) {
        const value = Number(amount);

        return total + (
            Number.isFinite(value) ? value : 0
        );
    }, 0);
}

/* 9. Vérifier l'équilibre débit/crédit */
function checkAccountingBalance(totalDebit, totalCredit) {
    const debit = Number(totalDebit);
    const credit = Number(totalCredit);

    if (!Number.isFinite(debit) || !Number.isFinite(credit)) {
        return false;
    }

    return Math.abs(debit - credit) < 0.01;
}

/* 10. Afficher un message de résultat */
function showAccountingMessage(message, type) {
    if (typeof window.showMessage === 'function') {
        window.showMessage(
            message,
            type || 'info'
        );
        return;
    }

    window.alert(message);
}
