<!-- src/components/LineChart.vue -->
<template>
  <div class="w-full h-96">
    <canvas ref="canvasRef"></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const props = defineProps({
  chartData: Object
});

const canvasRef = ref(null);
let chartInstance = null;

const renderChart = () => {
  const ctx = canvasRef.value.getContext('2d');

  if (chartInstance) {
    chartInstance.destroy();
  }

  chartInstance = new Chart(ctx, {
    type: 'line',
    data: props.chartData,
    options: {
      responsive: true,
      maintainAspectRatio: false, // biar bisa pakai height dari parent
      animation: false, // tidak ada animasi
      plugins: {
        legend: {
          display: true
        },
        title: {
          display: false
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: (value) => 'Rp' + value.toLocaleString('id-ID')
          }
        }
      }
    }
  });
};

watch(() => props.chartData, () => {
  renderChart();
}, { deep: true });

onMounted(() => {
  renderChart();
});
</script>
