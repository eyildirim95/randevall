import ApexCharts from 'apexcharts';

const daily = document.querySelector('#daily-chart');

if (daily) {
    new ApexCharts(daily, {
        chart: { type: 'line', height: 320, toolbar: { show: false } },
        series: [
            { name: 'Randevu', type: 'column', data: JSON.parse(daily.dataset.appointments || '[]') },
            { name: 'Gelir', type: 'line', data: JSON.parse(daily.dataset.income || '[]') },
        ],
        xaxis: { categories: JSON.parse(daily.dataset.labels || '[]'), labels: { rotate: -45 } },
        yaxis: [
            { title: { text: 'Randevu' } },
            { opposite: true, title: { text: 'Gelir' } },
        ],
        stroke: { width: [0, 3], curve: 'smooth' },
        colors: ['#7f56da', '#22c55e'],
        dataLabels: { enabled: false },
    }).render();
}

const service = document.querySelector('#service-chart');

if (service) {
    const labels = JSON.parse(service.dataset.labels || '[]');
    const values = JSON.parse(service.dataset.values || '[]');

    new ApexCharts(service, {
        chart: { type: 'donut', height: 280 },
        series: values,
        labels: labels,
        legend: { position: 'bottom' },
        colors: ['#7f56da', '#22c55e', '#f59e0b', '#3b82f6', '#ef4444', '#14b8a6', '#8b5cf6', '#f97316'],
    }).render();
}
