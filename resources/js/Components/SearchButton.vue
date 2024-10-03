<template>
    <!-- Button template -->
    <button @click="searchGrants">{{ buttonText }}</button>

      <!-- Refined search_type and top_centroid inputs aligned with Tailwind CSS -->
      <div class="flex flex-col items-center space-y-6">
        <!-- Search Type Section -->
        <div class="flex items-center space-x-4">
          <label for="search_type" class="font-medium">Search Type:</label>
          <div id="search_type" class="flex items-center space-x-4">
            <label for="centroid"  class="border border-gray-600 rounded-md space-x-4 p-2">
              <input type="radio" id="centroid" name="search_type" value="centroid" v-model="search_type" class="form-radio">
              &nbsp  Centroid
            </label>
              <label for="vector" class="border border-gray-600 rounded-md space-x-4 p-2">
              <input type="radio" id="vector" name="search_type" value="vector" v-model="search_type" class="form-radio">
               &nbsp Vector
            </label>
          </div>
        </div>

        <!-- Top Centroids Section -->
        <div class="flex items-center space-x-4" v-show="search_type === 'centroid'">
          <label for="top_centroids" class="font-medium">Top Centroids (1-200):</label>
          <input type="number" id="top_centroids" name="top_centroids" v-model="top_centroids" min="1" max="200" class="form-input w-24 border border-gray-300 rounded-md">
        </div>
        <!-- Hamming mode section, three radio buttons, for hamming_mode, cosine, hamming, and hybrid-->
        <div class="flex items-center space-x-4" >
          <label for="hamming_mode" class="font-medium">Hamming Mode:</label>
          <div id="hamming_mode" class="flex items-center space-x-4">
            <label for="cosine"  class="border border-gray-600 rounded-md space-x-4 p-2">
              <input type="radio" id="cosine" name="hamming_mode" value="cosine" v-model="hamming_mode" class="form-radio
              ">
              &nbsp  Cosine
            </label>
              <label for="hamming" class="border border-gray-600 rounded-md space-x-4 p-2">
              <input type="radio" id="hamming" name="hamming_mode" value="hamming" v-model="hamming_mode" class="form-radio">
               &nbsp Hamming
            </label>
            <label for="hybrid" class="border border-gray-600 rounded-md space-x-4 p-2">
              <input type="radio" id="hybrid" name="hamming_mode" value="hybrid" v-model="hamming_mode" class="form-radio
              ">
              &nbsp  Hybrid
            </label>
          </div>
        </div>

      </div>


  </template>
  
  <script>
  export default {
    name: 'SearchButton',
    props: {
      companyDescription: String, // Accept the description as a prop from parent
      buttonText: {
        type: String,
        default: 'SEARCH FOR GRANTS', // Default button text
      },
    },
    data() {
      return {
        search_type: 'centroid', // Default search type
        top_centroids: 5, // Default top centroids
        hamming_mode: 'hybrid', // Default hamming mode
      };
    },
    methods: {
      searchGrants() {
        const searchPayload = {
          description: this.companyDescription,
          search_type: this.search_type,
          top_centroids: this.top_centroids,
          hamming_mode: this.hamming_mode,
        };

        // Emit search event with data directly from prop
        this.$emit('search', searchPayload);
      },
    },
  };
  </script>
  
  <style scoped>

button {
  display: block;
  margin: 3% auto;
  padding: 10px 20px;
  font-size: .8rem; /* Responsive font size */
  color: #fff; /* White text */
  background-color: #004aad; /* Blue button */
  border: none;
  border-radius: 2px;
  cursor: pointer;
  min-width: 200px; /* Max width for larger screens */
  font-weight: 700;
}

button:hover {
  background-color: #0056b3; /* Darker blue on hover */
}
</style>