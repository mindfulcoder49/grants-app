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
      <div class="flex flex-col items-center space-y-6">
        <div class="flex items-center space-x-4">
          <label for="search_type" class="font-medium">Search Options:</label>
          <div id="search_type" class="flex items-center space-x-4">
            <label class="border border-gray-600 rounded-md space-x-4 p-2">
              <input type="checkbox" name="search_type" value="centroid" v-model="centroidSearch" class="form-checkbox">
              &nbsp Centroid
            </label>
            <label class="border border-gray-600 rounded-md space-x-4 p-2">
              <input type="checkbox" name="search_type" value="vector" v-model="vectorSearch" class="form-checkbox">
              &nbsp Vector
            </label>
          </div>
        </div>
  
        <!-- Top Centroids Input -->
        <div class="flex items-center space-x-4" v-if="centroidSearch">
          <label for="top_centroids" class="font-medium">Top Centroids (1-200):</label>
          <input type="number" id="top_centroids" v-model="top_centroids" min="1" max="200" class="form-input w-24 border border-gray-300 rounded-md">
        </div>
      </div>
  
      <!-- Search Button -->
      <button @click="performSearches" :disabled="loading" class="search-button mt-6">
        {{ loading ? 'Running Searches...' : 'Run Search' }}
      </button>
  
      <!-- Loading Indicator -->
      <div v-if="loading" class="loading-indicator mt-4">
        <p>Loading, please wait...</p>
      </div>
  
      <!-- Results Display -->
      <div v-if="results.length" class="results-display mt-8">
        <h2 class="text-xl font-bold mb-4">Search Results</h2>
        <ul>
          <li v-for="result in results" :key="result.term">
            <strong>{{ result.term }}</strong> - Centroids Searched: {{ result.centroidsSearched }} - Time: {{ (result.timeTaken/1000).toFixed(2) }} seconds
            <div class="grants-list">
              Grants: 
              <div class="scrolling-row">
                <!-- add numbering to the grants -->
                <div v-for="grant, index in result.grants" :key="grant.id" class="flex flex-col">
                    <span class="font-bold flex-column">{{ index + 1 }}</span>
                    <span class="font-bold flex-column">{{ grant.id }}</span>
                    <span>{{ (grant.similarity * 100).toFixed(2) }}%</span>
                    <span 
                        class="color-square"
                        :style="{ backgroundColor: '#' + grant.id.toString().padEnd(6, '0') }"
                    >&nbsp;</span>
                </div>

              </div>
            </div>
          </li>
        </ul>
        <div class="comparison-results" v-if="vectorResult">
          <h3 class="font-bold">Full Vector Search Result</h3>
          <div class="scrolling-row">
            <span>{{ vectorResult }}</span>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  
  export default {
    name: 'SearchCentroidTest',
    data() {
      return {
        searchTerms: 'Artificial Intelligence', // User input for search terms
        centroidSearch: true, // Centroid search checkbox
        vectorSearch: false, // Vector search checkbox
        top_centroids: 5, // Default top centroids
        loading: false,
        results: [], // Store search results
        vectorResult: null, // Full vector search result for comparison
      };
    },
    methods: {
      async performSearches() {
        this.loading = true;
        this.results = [];
        this.vectorResult = null;
  
        const terms = this.searchTerms
          .split(',')
          .map((term) => term.trim())
          .filter((term) => term);
  
        if (!terms.length) {
          alert('Please enter at least one search term.');
          this.loading = false;
          return;
        }
  
        try {
          for (const term of terms) {
            let topResult = null;
  
            if (this.centroidSearch) {
              // Iterate through centroids from 1 to top_centroids
              for (let i = 1; i <= this.top_centroids; i++) {
                const payload = {
                  description: term,
                  search_type: 'centroid',
                  hamming_mode: 'cosine', // Always cosine
                  top_centroids: i,
                  testMode: true, // Test mode to return only the top result
                };
  
                const startTime = performance.now();
                const response = await axios.post('/search', payload);
                const endTime = performance.now();
  
                const timeTaken = endTime - startTime;
                const currentTopResult = response.data.grants[0]?.id;
  
                topResult = currentTopResult;
  
                // Store the result for this centroid search
                this.results.push({
                  term,
                  centroidsSearched: i,
                  timeTaken,
                  grants: response.data.grants.map(grant => ({
                    id: grant.id,
                    similarity: grant.similarity, // Store grant similarity score
                  })),
                });
              }
            }
  
            if (this.vectorSearch) {
              // Perform a full vector search for comparison
              const vectorPayload = {
                description: term,
                search_type: 'vector',
                hamming_mode: 'cosine', // Always cosine
                testMode: true, // Test mode to return only the top result
              };
              const vectorStartTime = performance.now();
              const vectorResponse = await axios.post('/search', vectorPayload);
              const vectorEndTime = performance.now();
              const vectorTimeTaken = vectorEndTime - vectorStartTime;
              
              this.results.push({
                term,
                centroidsSearched: 'Vector Search',
                timeTaken: vectorTimeTaken,
                grants: vectorResponse.data.grants.map(grant => ({
                  id: grant.id,
                  similarity: grant.similarity, // Store grant similarity score
                })),
              });
            }
          }
        } catch (error) {
          console.error('Search failed', error);
          alert('Search failed. Please try again.');
        }
  
        this.loading = false;
      },
    },
  };
  </script>
  
  <style scoped>
  .search-analyzer {
    margin: 0 auto;
    padding: 20px;
  }
  .search-button {
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
  .loading-indicator {
    text-align: center;
  }
  .results-display {
    margin-top: 20px;
  }
  .grants-list {
    margin-top: 10px;
  }
  .scrolling-row {
    display: flex;
    overflow-x: auto;
    white-space: nowrap;
  }
  .scrolling-row span {
    padding: 0 10px;
    background-color: #e3f2fd;
    border-radius: 5px;
    margin-right: 5px;
    white-space: nowrap;
  }
  .comparison-results {
    margin-top: 20px;
  }
  </style>
  