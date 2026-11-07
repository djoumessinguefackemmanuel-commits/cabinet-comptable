
/* =========================================================
   CABINET COMPTABLE
   JavaScript des tableaux de bord
   Fichier : public/assets/js/dashboard.js
   ========================================================= */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
    initializeDashboardFilters();
    initializeDashboardRefresh();
    initializeDashboardCounters();
    initializeDashboardDate();
});

/* 1. Gestion des filtres du tableau de bord */
function initializeDashboardFilters() {
    const filters = document.querySelectorAll(
        '[data-dashboard-filter]'
    );

    filters.forEach(function (filter) {
        filter.addEventListener('change', function () {
            const targetSelector = filter.dataset.dashboardFilter;
            const target = document.querySelector(targetSelector);

            if (!target) {
                return;
            }

            const selectedValue = filter.value;

            target.querySelectorAll('[data-filter-value]').forEach(function (item) {
                const itemValue = item.dataset.filterValue;

                item.hidden = (
                    selectedValue !== 'all' &&
                    itemValue !== selectedValue
                );
            });
        });
    });
}

/* 2. Actualisation visuelle du tableau de bord */
function initializeDashboardRefresh() {
    const refreshButtons = document.querySelectorAll(
        '[data-dashboard-refresh]'
    );

    refreshButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const originalText = button.textContent;

            button.disabled = true;
            button.textContent = 'Actualisation...';

            window.location.reload();

            window.setTimeout(function () {
                button.disabled = false;
                button.textContent = originalText;
            }, 1500);
        });
    });
}

/* 3. Animation des statistiques */
function initializeDashboardCounters() {
    const counters = document.querySelectorAll(
        '[data-counter]'
    );

    counters.forEach(function (counter) {
        const targetValue = Number(counter.dataset.counter);

        if (!Number.isFinite(targetValue)) {
            return;
        }

        const duration = 1000;
        const startTime = performance.now();

        function updateCounter(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);

            const currentValue = Math.floor(
                targetValue * progress
            );

            counter.textContent = new Intl.NumberFormat('fr-FR').format(
                currentValue
            );

            if (progress < 1) {
                requestAnimationFrame(updateCounter);
            }
        }

        requestAnimationFrame(updateCounter);
    });
}

/* 4. Affichage de la date du tableau de bord */
function initializeDashboardDate() {
    const dateElements = document.querySelectorAll(
        '[data-dashboard-date]'
    );

    const currentDate = new Date();

    const formattedDate = new Intl.DateTimeFormat('fr-FR', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    }).format(currentDate);

    dateElements.forEach(function (element) {
        element.textContent = formattedDate;
    });
}

/* 5. Mise à jour d'une statistique */
function updateDashboardStat(selector, value) {
    const element = document.querySelector(selector);

    if (!element) {
        return;
    }

    const numericValue = Number(value);

    if (!Number.isFinite(numericValue)) {
        return;
    }

    element.textContent = new Intl.NumberFormat('fr-FR').format(
        numericValue
    );
}

/* 6. Affichage d'un état vide */
function showDashboardEmptyState(container, message) {
    if (!container) {
        return;
    }

    container.innerHTML = '';

    const emptyState = document.createElement('div');
    emptyState.className = 'empty-state';

    const paragraph = document.createElement('p');
    paragraph.textContent = message || 'Aucune donnée disponible.';

    emptyState.appendChild(paragraph);
    container.appendChild(emptyState);
}

/* 7. Filtrage des activités récentes */
function filterDashboardActivities(searchValue) {
    const activities = document.querySelectorAll(
        '[data-dashboard-activity]'
    );

    const query = String(searchValue || '').trim().toLowerCase();

    activities.forEach(function (activity) {
        const content = activity.textContent.toLowerCase();

        activity.hidden = !content.includes(query);
    });
}

/* 8. Impression du tableau de bord */
function printDashboard() {
    window.print();
}
