
/* =========================================================
   CABINET COMPTABLE
   JavaScript des graphiques statistiques
   Fichier : public/assets/js/charts.js
   ========================================================= */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') {
        console.warn('Chart.js n’est pas encore chargé.');
        return;
    }

    initializeChartDefaults();
    initializeRevenueChart();
    initializeExpenseChart();
    initializeFinancialComparisonChart();
});

/* 1. Configuration générale des graphiques */
function initializeChartDefaults() {
    Chart.defaults.font.family = 'Arial, sans-serif';
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#64748b';

    Chart.defaults.plugins.legend.position = 'bottom';
    Chart.defaults.plugins.legend.labels.usePointStyle = true;
}

/* 2. Graphique des revenus */
function initializeRevenueChart() {
    const canvas = document.getElementById('revenueChart');

    if (!canvas) {
        return;
    }

    const labels = getChartData(canvas, 'labels', [
        'Janvier', 'Février', 'Mars', 'Avril',
        'Mai', 'Juin', 'Juillet', 'Août',
        'Septembre', 'Octobre', 'Novembre', 'Décembre'
    ]);

    const values = getChartData(canvas, 'values', []);

    new Chart(canvas, {
        type: 'line',

        data: {
            labels: labels,

            datasets: [{
                label: 'Revenus (FCFA)',
                data: values,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.12)',
                borderWidth: 3,
                fill: true,
                tension: 0.35,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            plugins: {
                legend: {
                    display: true
                }
            },

            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

/* 3. Graphique des dépenses */
function initializeExpenseChart() {
    const canvas = document.getElementById('expenseChart');

    if (!canvas) {
        return;
    }

    const labels = getChartData(canvas, 'labels', []);
    const values = getChartData(canvas, 'values', []);

    new Chart(canvas, {
        type: 'doughnut',

        data: {
            labels: labels,

            datasets: [{
                label: 'Dépenses',
                data: values,
                backgroundColor: [
                    '#2563eb',
                    '#16a34a',
                    '#f59e0b',
                    '#dc2626',
                    '#7c3aed',
                    '#0891b2'
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            plugins: {
                legend: {
                    position: 'bottom'
                }
            },

            cutout: '65%'
        }
    });
}

/* 4. Comparaison des revenus et dépenses */
function initializeFinancialComparisonChart() {
    const canvas = document.getElementById(
        'financialComparisonChart'
    );

    if (!canvas) {
        return;
    }

    const labels = getChartData(canvas, 'labels', []);

    const revenues = getChartData(
        canvas,
        'revenues',
        []
    );

    const expenses = getChartData(
        canvas,
        'expenses',
        []
    );

    new Chart(canvas, {
        type: 'bar',

        data: {
            labels: labels,

            datasets: [
                {
                    label: 'Revenus',
                    data: revenues,
                    backgroundColor: '#16a34a',
                    borderRadius: 5
                },
                {
                    label: 'Dépenses',
                    data: expenses,
                    backgroundColor: '#dc2626',
                    borderRadius: 5
                }
            ]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            plugins: {
                legend: {
                    position: 'bottom'
                }
            },

            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

/* 5. Récupérer les données d'un graphique */
function getChartData(canvas, attribute, defaultValue) {
    const rawValue = canvas.dataset[attribute];

    if (!rawValue) {
        return defaultValue;
    }

    try {
        const parsedValue = JSON.parse(rawValue);

        return Array.isArray(parsedValue)
            ? parsedValue
            : defaultValue;

    } catch (error) {
        console.error(
            'Données invalides pour le graphique :',
            attribute
        );

        return defaultValue;
    }
}

/* 6. Mettre à jour les données d'un graphique */
function updateChart(chart, labels, values) {
    if (!chart) {
        return;
    }

    chart.data.labels = labels;
    chart.data.datasets[0].data = values;

    chart.update();
}

/* 7. Télécharger un graphique */
function downloadChart(chart, filename) {
    if (!chart) {
        return;
    }

    const link = document.createElement('a');

    link.href = chart.toBase64Image();
    link.download = filename || 'graphique.png';

    link.click();
}
