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

        <!-- Open Only checkbox -->
        <div class="flex flex-col items-center search-interface">
    <!-- Slider Toggle for Open Only or All Grants -->
    <div class="slider-container w-full flex items-center justify-center space-x-4">
      <label class="slider">
        <input type="checkbox" v-model="open_only" />
        <span class="slider-round">
          <span class="slider-text" v-if="open_only">Open Only<span class="slider-text-extra">: Searching grants accepting applications</span></span>
          <span class="slider-text" v-else>All Grants<span class="slider-text-extra">: including closed opportunities</span></span>
        </span>
      </label>
    </div>

        <div class="flex text-md font-bold text-blue-900 text-justify">
              Historical grant search is limited to the past two years. If you need more, let us know with the Help Us Improve button
        </div>

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
        default: 'SEARCH FOR GRANTS', // Default button text
      },
    },
    data() {
      return {
        search_type: 'centroid', // Default search type
        top_centroids: 200, // Default top centroids
        hamming_mode: 'cosine', // Default hamming mode
        centroid_async: true, // Default centroid async
        open_only: true, // Default open only
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
          open_only: this.open_only,
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
  padding: 15px 80px;
  font-size: 1rem;
  color: #fff;
  background-color: #004aad;
  border: none;
  border-radius: 2px;
  cursor: pointer;
  width: 100%;
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

/* Slider container styles */
.slider-container {
  margin: 0; /* Remove excess margins */
  display: flex;
  align-items: center; /* Center the slider vertically */
  justify-content: center; /* Center the slider horizontally */
  padding: 0 0 20px 0; /* Add some padding to give space around the slider */
}

/* Slider container styles */
.slider-container {
  margin: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 0 20px 0;
}

/* Slider styles */
.slider {
  position: relative;
  display: inline-block;
  width: 100%;
  height: 60px;
  cursor: pointer;
  transition: background-color 0.4s;
  margin-bottom: 1rem;
}

/* Hide default input */
.slider input {
  opacity: 0;
  width: 0;
  height: 0;
}

/* First pseudo-element for the initial gradient */
.slider-round::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  border-radius: 40px;
  background: radial-gradient(circle at 6% 50%, rgb(0, 0, 0) 25px, white 26px);
  transition: opacity .4s;
  opacity: 1; /* Fully visible by default */
  z-index: 1;
}

/* Second pseudo-element for the final gradient */
.slider-round::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  border-radius: 40px;
  background: radial-gradient(circle at 94% 50%, black 25px, rgb(48, 48, 48) 26px);
  transition: opacity .4s;
  opacity: 0; /* Hidden by default */
  z-index: 1;
}

/* Checked styles: fade out the first gradient and fade in the second */
.slider input:checked + .slider-round::before {
  opacity: 0;
}

.slider input:checked + .slider-round::after {
  opacity: 1;
}

/* Text color transition */
.slider input:checked + .slider-round {
  color: white;
  transition: color 0.2s;
}

/* Slider text */
.slider-text {
  font-size: 1rem;
  transition: color 0.2s;
  line-height: 1;
  z-index: 2;
}

/* remove slider text extra  when screen is less than 640px */
@media (max-width: 1024px) {
  .slider-text-extra {
    display: none;
  }
}
/* Keyframe animation to slide the gradient */
@keyframes slide-gradient {
  0% {
    background: radial-gradient(circle at 6% 50%, black 25px, white 26px);
  }
  7% {
    background: radial-gradient(circle at 12% 50%, black 25px, white 26px);
  }
  14% {
    background: radial-gradient(circle at 18% 50%, black 25px, white 26px);
  }
  21% {
    background: radial-gradient(circle at 24% 50%, black 25px, white 26px);
  }
  28% {
    background: radial-gradient(circle at 30% 50%, black 25px, white 26px);
  }
  35% {
    background: radial-gradient(circle at 36% 50%, black 25px, white 26px);
  }
  42% {
    background: radial-gradient(circle at 42% 50%, black 25px, white 26px);
  }
  50% {
    background: radial-gradient(circle at 50% 50%, black 25px, white 26px);
  }
  57% {
    background: radial-gradient(circle at 58% 50%, black 25px, white 26px);
  }
  64% {
    background: radial-gradient(circle at 64% 50%, black 25px, white 26px);
  }
  71% {
    background: radial-gradient(circle at 70% 50%, black 25px, white 26px);
  }
  78% {
    background: radial-gradient(circle at 76% 50%, black 25px, white 26px);
  }
  85% {
    background: radial-gradient(circle at 82% 50%, black 25px, white 26px);
  }
  92% {
    background: radial-gradient(circle at 88% 50%, black 25px, white 26px);
  }
  100% {
    background: radial-gradient(circle at 94% 50%, black 25px, white 26px);
  }
}

@keyframes slide-gradient-reverse {
  0% {
    background: radial-gradient(circle at 94% 50%, black 25px, white 26px);
  }
  7% {
    background: radial-gradient(circle at 88% 50%, black 25px, white 26px);
  }
  14% {
    background: radial-gradient(circle at 82% 50%, black 25px, white 26px);
  }
  21% {
    background: radial-gradient(circle at 76% 50%, black 25px, white 26px);
  }
  28% {
    background: radial-gradient(circle at 70% 50%, black 25px, white 26px);
  }
  35% {
    background: radial-gradient(circle at 64% 50%, black 25px, white 26px);
  }
  42% {
    background: radial-gradient(circle at 58% 50%, black 25px, white 26px);
  }
  50% {
    background: radial-gradient(circle at 50% 50%, black 25px, white 26px);
  }
  57% {
    background: radial-gradient(circle at 42% 50%, black 25px, white 26px);
  }
  64% {
    background: radial-gradient(circle at 36% 50%, black 25px, white 26px);
  }
  71% {
    background: radial-gradient(circle at 30% 50%, black 25px, white 26px);
  }
  78% {
    background: radial-gradient(circle at 24% 50%, black 25px, white 26px);
  }
  85% {
    background: radial-gradient(circle at 18% 50%, black 25px, white 26px);
  }
  92% {
    background: radial-gradient(circle at 12% 50%, black 25px, white 26px);
  }
  100% {
    background: radial-gradient(circle at 6% 50%, black 25px, white 26px);
  }
}



/* Slider round styles */
.slider-round {
  position: relative;
  width: 100%;
  height: 100%;
  border-radius: 40px;
  font-weight: 600;
  color: black;
  display: flex;
  align-items: center;
  justify-content: center;
  background: radial-gradient(circle at 6% 50%, black 25px, white 26px); /* Initial background */
  overflow: hidden;
  transition: color 0.2s;
}

/* Trigger the sliding animation when the checkbox is checked */
.slider input:checked + .slider-round {
  animation: slide-gradient 0.4s forwards;
  color: white; /* Change text color */
}

/* Apply the reverse animation when the checkbox is unchecked */
.slider input:not(:checked) + .slider-round {
  animation: slide-gradient-reverse 0.4s forwards;
  color: black;
}


/* Add any additional custom styling */


</style>