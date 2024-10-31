<template>
    <div class="results-display mt-8">
      <!-- Statistics -->
      <div class="statistics mb-8">
        <h2 class="text-xl font-bold mb-4">Performance Statistics</h2>
        <ul>
          <li>Mean Time Taken: {{ statistics.meanTime.toFixed(2) }} ms</li>
          <li>Median Time Taken: {{ statistics.medianTime.toFixed(2) }} ms</li>
          <li>Standard Deviation: {{ statistics.stdDevTime.toFixed(2) }} ms</li>
        </ul>
      </div>
  
      <!-- Overlap Analysis -->
      <OverlapAnalysis
        :overlaps="overlaps"
        @show-grants="showGrants"
      />
  
      <!-- Time Taken Chart -->
      <div class="graphs mb-8">
        <h2 class="text-xl font-bold mb-4">Time Taken for Each Method</h2>
         <!-- <LineChart v-if="chartData" :chart-data="chartData" /> -->
      </div>
  
      <!-- Grants Modal -->
      <GrantsModal
        v-if="showGrantModal"
        :grant-ids="currentGrants"
        @close="closeGrantModal"
      />
    </div>
  </template>
  
  <script>
  import OverlapAnalysis from './OverlapAnalysis.vue';
  import LineChart from './LineChart.vue';
  import GrantsModal from './GrantsModal.vue';
  
  export default {
    name: 'TestResultsDisplay',
    components: {
      OverlapAnalysis,
      LineChart,
      GrantsModal,
    },
    props: {
      statistics: Object,
      overlaps: Array,
      chartData: Object,
    },
    data() {
      return {
        showGrantModal: false,
        currentGrants: [],
      };
    },
    methods: {
      showGrants(grantIds) {
        this.currentGrants = grantIds;
        this.showGrantModal = true;
      },
      closeGrantModal() {
        this.showGrantModal = false;
        this.currentGrants = [];
      },
    },
  };
  </script>
  
  <style scoped>
  /* Add any necessary styles */
  </style>
  