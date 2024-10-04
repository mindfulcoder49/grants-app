<template>
    <div class="search-options mt-4">
      <!-- Search Type Section -->
      <div class="flex items-center space-x-4">
        <label class="font-medium">Search Type:</label>
        <div class="flex items-center space-x-4">
          <label class="border border-gray-600 rounded-md space-x-4 p-2">
            <input
              type="checkbox"
              value="centroid"
              v-model="localSelectedSearchTypes"
              class="form-checkbox"
            />
            &nbsp; Centroid
          </label>
          <label class="border border-gray-600 rounded-md space-x-4 p-2">
            <input
              type="checkbox"
              value="vector"
              v-model="localSelectedSearchTypes"
              class="form-checkbox"
            />
            &nbsp; Vector
          </label>
        </div>
      </div>
  
      <!-- Top Centroids Section -->
      <div class="flex items-center space-x-4 mt-4" v-if="localSelectedSearchTypes.includes('centroid')">
        <label class="font-medium">Top Centroids (1-200):</label>
        <input
          type="number"
          v-model.number="localTopCentroids"
          min="1"
          max="200"
          class="form-input w-24 border border-gray-300 rounded-md"
        />
      </div>

        <!-- Top N Section -->
        <div class="flex items-center space-x-4 mt-4">
            <label class="font-medium">Top N:</label>
            <input
                type="number"
                v-model.number="localTopN"
                min="1"
                max="200"
                class="form-input w-24 border border-gray-300 rounded-md"
            />
        </div>
  
      <!-- Hamming Mode Section -->
      <div class="flex items-center space-x-4 mt-4">
        <label class="font-medium">Hamming Mode:</label>
        <div class="flex items-center space-x-4">
          <label class="border border-gray-600 rounded-md space-x-4 p-2">
            <input
              type="checkbox"
              value="cosine"
              v-model="localSelectedHammingModes"
              class="form-checkbox"
            />
            &nbsp; Cosine
          </label>
          <label class="border border-gray-600 rounded-md space-x-4 p-2">
            <input
              type="checkbox"
              value="hamming"
              v-model="localSelectedHammingModes"
              class="form-checkbox"
            />
            &nbsp; Hamming
          </label>
          <label class="border border-gray-600 rounded-md space-x-4 p-2">
            <input
              type="checkbox"
              value="hybrid"
              v-model="localSelectedHammingModes"
              class="form-checkbox"
            />
            &nbsp; Hybrid
          </label>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    name: 'TestSearchOptions',
    props: {
      selectedSearchTypes: {
        type: Array,
        default: () => ['vector', 'centroid'],
      },
      selectedHammingModes: {
        type: Array,
        default: () => ['cosine', 'hamming', 'hybrid'],
      },
      topCentroids: {
        type: Number,
        default: 5,
      },
      topN: {
        type: Number,
        default: 5,
      },
    },
    data() {
      return {
        localSelectedSearchTypes: [...this.selectedSearchTypes],
        localSelectedHammingModes: [...this.selectedHammingModes],
        localTopCentroids: this.topCentroids,
        localTopN: this.topN,
      };
    },
    watch: {
      localSelectedSearchTypes(newVal) {
        this.$emit('update:selectedSearchTypes', newVal);
      },
      localSelectedHammingModes(newVal) {
        this.$emit('update:selectedHammingModes', newVal);
      },
      localTopCentroids(newVal) {
        this.$emit('update:topCentroids', newVal);
      },
        localTopN(newVal) {
            this.$emit('update:topN', newVal);
        },
      selectedSearchTypes(newVal) {
        this.localSelectedSearchTypes = [...newVal];
      },
      selectedHammingModes(newVal) {
        this.localSelectedHammingModes = [...newVal];
      },
      topCentroids(newVal) {
        this.localTopCentroids = newVal;
      },
        topN(newVal) {
            this.localTopN = newVal;
        },
    },
  };
  </script>
  
  <style scoped>
  /* Add any necessary styles */
  </style>
  