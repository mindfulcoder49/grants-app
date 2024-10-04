<template>
    <div class="search-analyzer">
      <!-- Search Input -->
      <div class="search-input">
      <label for="searchTerms" class="font-medium">Search Terms (comma-separated):</label>
      <textarea
        id="searchTerms"
        v-model="searchTerms"
        placeholder="Enter search terms separated by commas"
        class="form-textarea mt-1 block w-full"
        rows="3"
      ></textarea>
    </div>
  
      <!-- Search Options -->
      <TestSearchOptions
        v-model:selected-search-types="selectedSearchTypes"
        v-model:selected-hamming-modes="selectedHammingModes"
        v-model:top-centroids="topCentroids"
        v-model:top-n="topN"
        />

  
      <!-- Search Button -->
      <button @click="performAllSearches" :disabled="loading" class="search-button mt-6">
        {{ loading ? 'Running Searches...' : 'Run All Searches' }}
      </button>
  
      <!-- Loading Indicator -->
      <div v-if="loading" class="loading-indicator mt-4">
        <p>Loading, please wait...</p>
      </div>
  
      <!-- Results Display -->
      <TestResultsDisplay
        v-if="!loading && results.length"
        :statistics="statistics"
        :overlaps="overlaps"
        :chart-data="chartData"
      />
  
      <!-- Data Download -->
      <button @click="downloadData" v-if="!loading && results.length" class="download-button">
        Download Data
      </button>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  import TestSearchOptions from '@/Components/Test/TestSearchOptions.vue';
  import TestResultsDisplay from '@/Components/Test/TestResultsDisplay.vue';
  
  export default {
    name: 'SearchTest',
    components: {
      TestSearchOptions,
      TestResultsDisplay,
    },
    data() {
      return {
        searchTerms: '', // User input for multiple search terms
        selectedSearchTypes: ['vector', 'centroid'], // Default selections
        selectedHammingModes: ['cosine', 'hamming', 'hybrid'],
        topCentroids: 5,
        topN: 5,
        results: [], // Store all results
        loading: false, // Loading state
        chartData: null,
        overlaps: [], // Store overlap analysis
        statistics: null, // Store calculated statistics
      };
    },
    methods: {
      async performAllSearches() {
        this.loading = true;
        this.results = [];
        this.overlaps = [];
        this.statistics = null;
        this.chartData = null;
        console.log(this.searchTerms);
        const terms = this.searchTerms
          .split(',')
          .map((term) => term.trim())
          .filter((term) => term);
  
        if (!terms.length) {
            
          alert('Please enter at least one search term.');
          this.loading = false;
          return;
        }
  
        if (!this.selectedSearchTypes.length) {
          alert('Please select at least one search type.');
          this.loading = false;
          return;
        }
  
        if (!this.selectedHammingModes.length) {
          alert('Please select at least one hamming mode.');
          this.loading = false;
          return;
        }
  
        for (const term of terms) {
          for (const searchType of this.selectedSearchTypes) {
            for (const hammingMode of this.selectedHammingModes) {
              // Prepare payload
              const payload = {
                description: term,
                search_type: searchType,
                hamming_mode: hammingMode,
                top_centroids: this.topCentroids,
                testMode: true,
                topN: this.topN,
              };
  
              // Measure time
              const startTime = performance.now();
              try {
                const response = await axios.post('/', payload);
                const endTime = performance.now();
                const timeTaken = endTime - startTime;
  
                // Collect results
                this.results.push({
                  term,
                  searchType,
                  hammingMode,
                  timeTaken,
                  grants: response.data.grants,
                });
              } catch (error) {
                console.error('Search failed', error);
                alert(`Search failed for term "${term}" with ${searchType} and ${hammingMode}.`);
              }
            }
          }
        }
        // Process results for display
        this.processResults();
        this.loading = false;
      },
      processResults() {
        //this.prepareChartData();
        this.analyzeOverlaps();
        this.calculateStatistics();
      },
      prepareChartData() {
        const labels = [];
        const data = [];
  
        this.results.forEach((result) => {
          labels.push(`${result.term} | ${result.searchType} | ${result.hammingMode}`);
          data.push(result.timeTaken);
        });
  
        this.chartData = {
          labels,
          datasets: [
            {
              label: 'Time Taken (ms)',
              backgroundColor: '#004aad',
              borderColor: '#004aad',
              fill: false,
              data,
            },
          ],
        };
      },
      analyzeOverlaps() {
  const grantMaps = {};

  // Create a map for each term, search type, and hamming mode combination
  this.results.forEach((result) => {
    const key = `${result.term}`;
    if (!grantMaps[key]) {
      grantMaps[key] = {};
    }
    const subKey = `${result.searchType}_${result.hammingMode}`;
    
    // Store grant IDs along with their index and timeTaken
    grantMaps[key][subKey] = {
      grants: result.grants.map((grant, index) => ({
        id: grant.id,
        index: index, // Capture the position of the grant in the result
      })),
      timeTaken: result.timeTaken, // Store time taken for this search
    };
  });

  this.overlaps = [];

  // Calculate overlap between methods for each term
  Object.keys(grantMaps).forEach((term) => {
    const methods = grantMaps[term];
    const methodKeys = Object.keys(methods);
    
    for (let i = 0; i < methodKeys.length; i++) {
      for (let j = i + 1; j < methodKeys.length; j++) {
        const methodA = methodKeys[i];
        const methodB = methodKeys[j];
        
        // Extract grants and indexes for both methods
        const grantsA = methods[methodA].grants;
        const grantsB = methods[methodB].grants;
        
        // Extract time taken for both methods
        const timeTakenA = methods[methodA].timeTaken;
        const timeTakenB = methods[methodB].timeTaken;

        // Convert grantsA and grantsB to maps for easier lookup by ID
        const grantsAMap = new Map(grantsA.map((grant) => [grant.id, grant.index]));
        const grantsBMap = new Map(grantsB.map((grant) => [grant.id, grant.index]));

        // Calculate intersection and union
        const intersection = [...grantsAMap.keys()].filter((id) => grantsBMap.has(id));
        const union = new Set([...grantsAMap.keys(), ...grantsBMap.keys()]);

        const overlapPercentage = (intersection.length / union.size) * 100;

        // Calculate position differences for intersecting IDs
        const positionDifferences = intersection.map((id) => Math.abs(grantsAMap.get(id) - grantsBMap.get(id)));
        const avgPositionDifference = positionDifferences.length > 0
          ? positionDifferences.reduce((a, b) => a + b, 0) / positionDifferences.length
          : 0;

        // Store unique grants for each method
        const uniqueToA = grantsA.filter((grant) => !grantsBMap.has(grant.id)).map((grant) => grant.id);
        const uniqueToB = grantsB.filter((grant) => !grantsAMap.has(grant.id)).map((grant) => grant.id);

        // Add overlap result to the list
        this.overlaps.push({
          term,
          methodA,
          methodB,
          overlapPercentage,
          avgPositionDifference, // New: Average position difference
          uniqueToA,
          uniqueToB,
          timeTakenA, // Add time taken for method A
          timeTakenB, // Add time taken for method B
        });
      }
    }
  });
},


      calculateStatistics() {
        const times = this.results.map((result) => result.timeTaken);
        const meanTime = this.mean(times);
        const medianTime = this.median(times);
        const stdDevTime = this.standardDeviation(times);
  
        this.statistics = {
          meanTime,
          medianTime,
          stdDevTime,
        };
      },
      mean(values) {
        return values.reduce((a, b) => a + b, 0) / values.length;
      },
      median(values) {
        const sorted = [...values].sort((a, b) => a - b);
        const half = Math.floor(sorted.length / 2);
        if (sorted.length % 2) return sorted[half];
        return (sorted[half - 1] + sorted[half]) / 2.0;
      },
      standardDeviation(values) {
        const avg = this.mean(values);
        const squareDiffs = values.map((value) => {
          const diff = value - avg;
          return diff * diff;
        });
        const avgSquareDiff = this.mean(squareDiffs);
        return Math.sqrt(avgSquareDiff);
      },
      downloadData() {
        const dataStr = JSON.stringify(this.results, null, 2);
        const blob = new Blob([dataStr], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
  
        // Create a temporary link to trigger download
        const link = document.createElement('a');
        link.href = url;
        link.download = 'search_results.json';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      },
    },
  };
  </script>
  
  <style scoped>
  /* Include the styles from your original component */
  .search-analyzer {
    margin: 0 auto;
    padding: 20px;
  }
  
  .search-button,
  .download-button {
    display: block;
    margin: 20px auto;
    padding: 10px 20px;
    font-size: 1rem;
    color: #fff;
    background-color: #004aad;
    border: none;
    border-radius: 2px;
    cursor: pointer;
    min-width: 200px;
    font-weight: 700;
  }
  
  .search-button:disabled {
    background-color: #888;
    cursor: not-allowed;
  }
  
  .search-button:hover:enabled,
  .download-button:hover {
    background-color: #0056b3;
  }
  
  .loading-indicator {
    text-align: center;
  }
  </style>
  