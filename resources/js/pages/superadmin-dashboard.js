import ApexCharts from 'apexcharts';

const el = document.querySelector('#growth-chart');

if (el) {
    new ApexCharts(el, {
        chart: { type: 'line', height: 300, toolbar: { show: false } },
        series: [
            { name: 'Yeni İşletme', type: 'column', data: JSON.parse(el.dataset.businesses || '[]') },
            { name: 'Ciro (₺)', type: 'line', data: JSON.parse(el.dataset.revenue || '[]') },
        ],
        xaxis: { categories: JSON.parse(el.dataset.labels || '[]') },
        yaxis: [
            { title: { text: 'İşletme' } },
            { opposite: true, title: { text: 'Ciro' } },
        ],
        stroke: { width: [0, 3], curve: 'smooth' },
        colors: ['#7f56da', '#22c55e'],
        dataLabels: { enabled: false },
    }).render();
}
