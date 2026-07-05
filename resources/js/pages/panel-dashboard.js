import ApexCharts from 'apexcharts';

const el = document.querySelector('#week-chart');

if (el) {
    const labels = JSON.parse(el.dataset.labels || '[]');
    const values = JSON.parse(el.dataset.values || '[]');

    new ApexCharts(el, {
        chart: { type: 'area', height: 260, toolbar: { show: false } },
        series: [{ name: 'Randevu', data: values }],
        xaxis: { categories: labels },
        stroke: { curve: 'smooth', width: 3 },
        dataLabels: { enabled: false },
        colors: ['#7f56da'],
        fill: {
            type: 'gradient',
            gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05 },
        },
    }).render();
}
