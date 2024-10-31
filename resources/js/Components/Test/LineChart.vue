<template>
    <div>
      <line-chart :chart-data="chartData" :options="chartOptions" />
    </div>
  </template>
  
  <script>
  import { Line } from 'vue-chartjs';
  
  export default {
    name: 'LineChart',
    extends: Line,
    props: {
      chartData: Object,
    },
    data() {
      return {
        chartOptions: {
          responsive: true,
          maintainAspectRatio: false,
        },
      };
    },
    mounted() {
      if (this.chartData) {
        this.renderChart(this.chartData, this.chartOptions);
      }
    },
    watch: {
      chartData(newData) {
        if (newData) {
          this.renderChart(newData, this.chartOptions);
        }
      },
    },
  };
  </script>
  
  <style scoped>
  /* Add any necessary styles */
  </style>
  