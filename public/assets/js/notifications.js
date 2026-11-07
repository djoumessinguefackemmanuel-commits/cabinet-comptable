
/* =========================================================
   CABINET COMPTABLE
   JavaScript de gestion des notifications
   Fichier : public/assets/js/notifications.js
   ========================================================= */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
    initializeNotificationRead();
    initializeNotificationDelete();
    initializeNotificationReadAll();
    initializeNotificationFilters();
    updateNotificationCounter();
});

/* 1. Marquer une notification comme lue */
function initializeNotificationRead() {
    document.querySelectorAll('[data-notification-read]').forEach(function (button) {
        button.addEventListener('click', function () {
            const notification = button.closest('[data-notification-item]');

            if (!notification) {
                return;
            }

            notification.classList.remove('unread');
            notification.classList.add('read');

            notification.dataset.notificationStatus = 'read';

            button.disabled = true;
            button.textContent = 'Lue';

            updateNotificationCounter();
        });
    });
}

/* 2. Supprimer visuellement une notification */
function initializeNotificationDelete() {
    document.querySelectorAll('[data-notification-delete]').forEach(function (button) {
        button.addEventListener('click', function () {
            const notification = button.closest('[data-notification-item]');

            if (!notification) {
                return;
            }

            if (!window.confirm('Voulez-vous supprimer cette notification ?')) {
                return;
            }

            notification.remove();
            updateNotificationCounter();
        });
    });
}

/* 3. Marquer toutes les notifications comme lues */
function initializeNotificationReadAll() {
    const buttons = document.querySelectorAll(
        '[data-notification-read-all]'
    );

    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            document.querySelectorAll('[data-notification-item]').forEach(function (item) {
                item.classList.remove('unread');
                item.classList.add('read');

                item.dataset.notificationStatus = 'read';

                const readButton = item.querySelector(
                    '[data-notification-read]'
                );

                if (readButton) {
                    readButton.disabled = true;
                    readButton.textContent = 'Lue';
                }
            });

            updateNotificationCounter();
        });
    });
}

/* 4. Filtrer les notifications */
function initializeNotificationFilters() {
    document.querySelectorAll('[data-notification-filter]').forEach(function (filter) {
        filter.addEventListener('change', function () {
            const selectedValue = filter.value;

            document.querySelectorAll('[data-notification-item]').forEach(function (item) {
                const status = item.dataset.notificationStatus ||
                    (item.classList.contains('unread') ? 'unread' : 'read');

                item.hidden = (
                    selectedValue !== 'all' &&
                    selectedValue !== status
                );
            });
        });
    });
}

/* 5. Mettre à jour le compteur de notifications non lues */
function updateNotificationCounter() {
    const unreadCount = document.querySelectorAll(
        '[data-notification-item].unread'
    ).length;

    document.querySelectorAll('[data-notification-counter]').forEach(function (counter) {
        counter.textContent = unreadCount;

        counter.hidden = unreadCount === 0;
    });
}

/* 6. Afficher une notification dans la page */
function addNotification(title, message, type) {
    const container = document.querySelector(
        '[data-notifications-container]'
    );

    if (!container) {
        return;
    }

    const item = document.createElement('article');

    item.className = 'notification-item unread';
    item.dataset.notificationItem = '';
    item.dataset.notificationStatus = 'unread';

    const heading = document.createElement('h4');
    heading.textContent = title;

    const content = document.createElement('p');
    content.textContent = message;

    const typeLabel = document.createElement('span');
    typeLabel.className = 'notification-type';
    typeLabel.textContent = type || 'Information';

    item.appendChild(heading);
    item.appendChild(content);
    item.appendChild(typeLabel);

    container.prepend(item);

    updateNotificationCounter();
}

/* 7. Effacer toutes les notifications visibles */
function clearNotifications() {
    if (!window.confirm('Voulez-vous supprimer toutes les notifications ?')) {
        return;
    }

    document.querySelectorAll('[data-notification-item]').forEach(function (item) {
        item.remove();
    });

    updateNotificationCounter();
}
