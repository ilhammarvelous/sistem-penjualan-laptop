<script setup>
import { Pie } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  ArcElement,
} from 'chart.js'

import ChartDataLabels from 'chartjs-plugin-datalabels'

ChartJS.register(Title, Tooltip, Legend, ArcElement, ChartDataLabels)

defineProps(['chartData'])

const options = {
  responsive: true,
  plugins: {
    legend: {
      position: 'top',
    },
    datalabels: {
      color: '#fff',
      formatter: (value, context) => {
        const sum = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
        const percentage = ((value / sum) * 100).toFixed(1) + '%';
        return percentage;
      },
      font: {
        weight: 'bold'
      }
    }
  }
}
</script>

<template>
  <Pie :data="chartData" :options="options" />
</template>