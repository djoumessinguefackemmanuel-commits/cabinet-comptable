
/* =========================================================
   CABINET COMPTABLE
   JavaScript principal de l'application
   Fichier : public/assets/js/app.js
   ========================================================= */

'use strict';

/* 1. Initialisation générale */
document.addEventListener('DOMContentLoaded', function () {
    initializeSidebar();
    initializeAlerts();
    initializeDeleteConfirmation();
    initializeDropdowns();
    initializeTooltips();
    initializeCurrentYear();
});

/* 2. Gestion du menu latéral */
function initializeSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const overlay = document.querySelector('.sidebar-overlay');

    if (!sidebar || !menuToggle) {
        return;
    }

    function openSidebar() {
        sidebar.classList.add('active');

        if (overlay) {
            overlay.classList.add('active');
        }

        document.body.classList.add('sidebar-open');
        menuToggle.setAttribute('aria-expanded', 'true');
    }

    function closeSidebar() {
        sidebar.classList.remove('active');

        if (overlay) {
            overlay.classList.remove('active');
        }

        document.body.classList.remove('sidebar-open');
        menuToggle.setAttribute('aria-expanded', 'false');
    }

    menuToggle.addEventListener('click', function () {
        if (sidebar.classList.contains('active')) {
            closeSidebar();
        } else {
            openSidebar();
        }
    });

    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeSidebar();
        }
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 992) {
            closeSidebar();
        }
    });
}

/* 3. Fermeture des messages d'alerte */
function initializeAlerts() {
    const alerts = document.querySelectorAll(
        '.alert[data-auto-dismiss="true"]'
    );

    alerts.forEach(function (alert) {
        const duration = parseInt(
            alert.dataset.duration || '5000',
            10
        );

        if (Number.isNaN(duration) || duration <= 0) {
            return;
        }

        window.setTimeout(function () {
            alert.style.transition = 'opacity 0.3s ease';
            alert.style.opacity = '0';

            window.setTimeout(function () {
                alert.remove();
            }, 300);
        }, duration);
    });

    document.querySelectorAll('[data-dismiss-alert]').forEach(function (button) {
        button.addEventListener('click', function () {
            const alert = button.closest('.alert');

            if (alert) {
                alert.remove();
            }
        });
    });
}

/* 4. Confirmation avant suppression */
function initializeDeleteConfirmation() {
    const deleteButtons = document.querySelectorAll(
        '[data-confirm-delete]'
    );

    deleteButtons.forEach(function (button) {
        button.addEventListener('click', function (event) {
            const message = button.dataset.confirmMessage ||
                'Voulez-vous vraiment supprimer cet élément ?';

            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });
}

/* 5. Gestion simple des menus déroulants */
function initializeDropdowns() {
    const dropdownToggles = document.querySelectorAll(
        '[data-dropdown-toggle]'
    );

    dropdownToggles.forEach(function (toggle) {
        const targetSelector = toggle.dataset.dropdownToggle;
        const target = document.querySelector(targetSelector);

        if (!target) {
            return;
        }

        toggle.addEventListener('click', function (event) {
            event.preventDefault();

            target.classList.toggle('show');

            const isOpen = target.classList.contains('show');

            toggle.setAttribute('aria-expanded', String(isOpen));
        });
    });

    document.addEventListener('click', function (event) {
        dropdownToggles.forEach(function (toggle) {
            const target = document.querySelector(
                toggle.dataset.dropdownToggle
            );

            if (!target) {
                return;
            }

            if (
                !toggle.contains(event.target) &&
                !target.contains(event.target)
            ) {
                target.classList.remove('show');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    });
}

/* 6. Infobulles simples */
function initializeTooltips() {
    const elements = document.querySelectorAll('[data-tooltip]');

    elements.forEach(function (element) {
        element.setAttribute(
            'title',
            element.dataset.tooltip
        );
    });
}

/* 7. Mise à jour automatique de l'année */
function initializeCurrentYear() {
    const yearElements = document.querySelectorAll('[data-current-year]');

    yearElements.forEach(function (element) {
        element.textContent = new Date().getFullYear();
    });
}

/* 8. Fonction utilitaire pour afficher un message */
function showMessage(message, type = 'info') {
    const container = document.querySelector(
        '.notification-container'
    );

    if (!container) {
        window.alert(message);
        return;
    }

    const alert = document.createElement('div');

    alert.className = 'alert alert-' + type;
    alert.textContent = message;

    container.appendChild(alert);

    window.setTimeout(function () {
        alert.remove();
    }, 5000);
}

/* 9. Fonction utilitaire de formatage monétaire */
function formatMoney(amount, currency = 'FCFA') {
    const value = Number(amount);

    if (!Number.isFinite(value)) {
        return '0 ' + currency;
    }

    return new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    }).format(value) + ' ' + currency;
}

/* 10. Fonction utilitaire de formatage de date */
function formatDate(dateValue) {
    const date = new Date(dateValue);

    if (Number.isNaN(date.getTime())) {
        return '';
    }

    return new Intl.DateTimeFormat('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    }).format(date);
}
