import { CONFIG } from './config';

export default function initLegalDashboardCharts() {
  const chartEl = document.querySelector('[data-legal-contract-inflow-chart]');

  if (
    !chartEl ||
    chartEl.dataset.chartReady === 'true' ||
    !CONFIG.hasApexCharts ||
    typeof window.config === 'undefined'
  )
    return;

  let categories = [];
  let seriesData = [];

  try {
    categories = JSON.parse(chartEl.dataset.categories || '[]');
    seriesData = JSON.parse(chartEl.dataset.series || '[]');
  } catch {
    return;
  }

  const { cardColor, textMuted: labelColor, borderColor, primary: primaryColor } = window.config.colors;
  const primarySubtleColor =
    typeof window.Helpers !== 'undefined' ? window.Helpers.getCssVar('primary-bg-subtle') : primaryColor;

  try {
    new window.ApexCharts(chartEl, {
      chart: {
        type: 'area',
        height: 320,
        parentHeightOffset: 0,
        toolbar: { show: false },
        fontFamily: window.config.fontFamily
      },
      series: [{ name: 'العقود', data: seriesData }],
      dataLabels: { enabled: false },
      stroke: { curve: 'smooth', width: 3 },
      colors: [primaryColor],
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 0.35,
          opacityFrom: 0.45,
          opacityTo: 0.08,
          stops: [0, 90, 100],
          gradientToColors: [primarySubtleColor]
        }
      },
      grid: {
        borderColor,
        strokeDashArray: 6,
        padding: { top: -12, left: 0, right: 8, bottom: 0 }
      },
      markers: {
        size: 4,
        strokeWidth: 3,
        colors: [cardColor],
        strokeColors: primaryColor,
        hover: { size: 6 }
      },
      xaxis: {
        categories,
        axisBorder: { show: false },
        axisTicks: { show: false },
        labels: {
          style: {
            colors: labelColor,
            fontSize: '12px'
          }
        }
      },
      yaxis: {
        min: 0,
        tickAmount: 4,
        labels: {
          style: {
            colors: labelColor,
            fontSize: '12px'
          }
        }
      },
      tooltip: {
        shared: true,
        intersect: false,
        x: { show: true }
      },
      legend: { show: false },
      responsive: [
        {
          breakpoint: 992,
          options: {
            chart: { height: 280 }
          }
        },
        {
          breakpoint: 576,
          options: {
            chart: { height: 240 }
          }
        }
      ]
    }).render();

    chartEl.dataset.chartReady = 'true';
  } catch (err) {
    console.warn('[dashboard-chart] Failed to render chart', err);
  }
}
