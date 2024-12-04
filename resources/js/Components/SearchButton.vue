<template>
    <!-- Button template -->


      <!-- Refined search_type and top_centroid inputs aligned with Tailwind CSS -->
      
        <!-- Search Type Section 
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
        </div> -->

        <!-- Top Centroids Section 
        <div class="flex items-center space-x-4" v-show="search_type === 'centroid'">
          <label for="top_centroids" class="font-medium">Top Centroids (1-200):</label>
          <input type="number" id="top_centroids" name="top_centroids" v-model="top_centroids" min="1" max="200" class="form-input w-24 border border-gray-300 rounded-md">
        </div>
        -->

        <!-- Hamming mode section, three radio buttons, for hamming_mode, cosine, hamming, and hybrid
        <div class="flex items-center space-x-4" >
          <label for="hamming_mode" class="font-medium">Search Mode: (Distance Comparison)</label>
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
        -->

        <div class="w-[30%]"> 
        <div class="switch-container">
            <!-- Toggle Switch on the left -->
            <label class="switch">
              <input type="checkbox" v-model="include_all" />
              <span class="slider round"></span>
            </label>

            <!-- Text on the right -->
            <div class="toggle-text">
              <span class="main-text">{{ include_all ? 'All Grants' : 'Open Only'  }}</span>
              <span class="slider-text-extra">
                {{ include_all ? ': including closed opportunities' : ': Searching grants accepting applications' }}
              </span>
            </div>
          </div>

  <!-- Historical grant search notice -->
  <div class="flex text-md text-justify">
    Historical grant search is limited to the past two years. If you need more, let us know with the Help Us Improve button
  </div>
</div>
  <!-- Search Button -->
   <div class="w-full flex flex-col items-center">
  <button @click="searchGrants">{{ buttonText }}</button>
  </div>
</template>



  
<script>
export default {
  name: 'SearchButton',
  emits: ['search'], // Emit search event to parent
  props: {
    companyDescription: String, // Accept the description as a prop from parent
    buttonText: {
      type: String,
      default: 'SEARCH GRANTS.GOV', // Default button text
    },
  },
  data() {
    return {
      search_type: 'centroid', // Default search type
      top_centroids: 200, // Default top centroids
      hamming_mode: 'cosine', // Default hamming mode
      centroid_async: true, // Default centroid async
      open_only: true, // Default open only
      include_all: false, // Default include all
    };
  },
  methods: {
    searchGrants() {
      const searchPayload = {
        description: this.companyDescription,
        search_type: this.search_type,
        top_centroids: this.top_centroids,
        hamming_mode: this.hamming_mode,
        centroid_async: this.centroid_async,
        open_only: !this.include_all,
      };

      // Emit search event with data directly from prop
      this.$emit('search', searchPayload);
    },
  },
};
</script>

  
<style scoped>
/* Button styles remain unchanged */
button {
  display: block;
  margin: 3% 0;
  padding: 15px 0;
  font-size: 1rem;
  color: #fff;
  background-color: #004aad;
  border: none;
  border-radius: 2px;
  cursor: pointer;
  width: 200px;
  font-weight: 700;
}

button:hover {
  background-color: #0056b3;
}

.search-interface {
  width: 100%;
  padding: 0 0 1rem 0;
  margin: 0 0;
  border-radius: 1rem;
  background-color: #f4f1eb;
}

/* Switch Container */
.switch-container {
  display: flex;
  align-items: center;
  margin-bottom: 1rem;
  /* Ensure the container doesn't shift */
  width: 100%;
}

/* Switch (Toggle) */
.switch {
  position: relative;
  display: inline-block;
  width: 52px;
  height: 28px;
  flex-shrink: 0; /* Prevent the switch from shrinking */
}

/* Hide default checkbox */
.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

/* Slider */
.slider {
  position: absolute;
  cursor: pointer;
  background-color: white;
  border-radius: 28px;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  transition: 0.4s;
}

.slider:before {
  position: absolute;
  content: '';
  height: 24px;
  width: 24px;
  left: 2px;
  bottom: 2px;
  background-color: #004aad;
  border-radius: 50%;
  transition: 0.4s;
}

input:checked + .slider {
  background-color: white;
}

input:checked + .slider:before {
  transform: translateX(24px);
}

.slider.round {
  border-radius: 28px;
}

.slider.round:before {
  border-radius: 50%;
}

/* Toggle Text */
.toggle-text {
  margin-left: 1rem;
  display: flex;
}

/* Main text styling */
.main-text {
  font-weight: bold;
  /* Set a fixed width to prevent shifting */
  min-width: 80px;
  /* Keep the text on one line */
  white-space: nowrap;
  font-size: 1.2rem;
}

/* Additional text styling */
.slider-text-extra {
  font-weight:bold;
  /* Adjust to prevent shifting */
  min-width: 250px;
  /* Keep the text on one line */
  white-space: nowrap;
  font-size: 1.2rem;
}

/* Responsive adjustments for extra text */
@media (max-width: 1024px) {
  .slider-text-extra {
    display: none;
  }
}
</style>

