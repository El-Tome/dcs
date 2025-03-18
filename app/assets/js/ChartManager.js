import Chart from 'chart.js/auto';

export default class ChartManager {
    constructor() {
        this.initCharts();
    }

    initCharts() {
        document.querySelectorAll("[id^='chart-data-']").forEach((dataContainer, index) => {
            const canvasId = `chart-${index + 1}`;
            const canvas = document.getElementById(canvasId);

            if (!canvas || !dataContainer) {
                console.error(`ChartManager: Canvas or data container not found for ${canvasId}`);
                return;
            }

            const ctx = canvas.getContext('2d');
            const labels = JSON.parse(dataContainer.dataset.labels || '[]');
            const values = JSON.parse(dataContainer.dataset.values || '[]');

            new Chart(ctx, {
                type: 'bar',  // Change en "line", "pie", etc.
                data: {
                    labels: labels, // Noms des applications
                    datasets: [{
                        label: 'Total des ventes (€)',
                        data: values, // Prix total des applications du client
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        });
    }
}
