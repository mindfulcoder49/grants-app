<template>
    <div class="grants-gov-search-container">
      
      <!-- Input field for the Grants.gov search 
      <input 
        v-model="searchTerm" 
        type="text" 
        placeholder="Search Grants.gov" 
        class="search-input"
      />
      -->
      <!-- Search button triggers the search 
      <button @click="searchGrantsGov" class="search-button">
        Search Grants.gov
      </button>
      -->

  
      <!-- Displaying the search results with paging -->
      <div v-if="paginatedResults.length" class="results-container">
        <h3 class="results-header">Search Results for "{{ getSearchTerm() }}"</h3>
        <div class="results-content">
            <div class="pagination">
            <button @click="prevPage" :disabled="currentPage === 1">Previous</button>
            <span>Page {{ currentPage }} of {{ totalPages }}</span>
            <button @click="nextPage" :disabled="currentPage === totalPages">Next</button>
            </div>
            <ul class="results-list">
            <li v-for="result in paginatedResults" :key="result.id" class="result-item">
                <h4>{{ result.title }}</h4>
                <p>{{ result.agency }} ({{ result.number }})</p>
                <p>Open Date: {{ result.openDate }} | Close Date: {{ result.closeDate }}</p>
                <p>Status: {{ result.oppStatus }} | ID: {{ result.id }}</p>
                <!-- Add link to grant details on grants.gov using result.id after https://www.grants.gov/search-results-detail/-->
                <a :href="'https://www.grants.gov/search-results-detail/' + result.id" target="_blank">View Details</a>
                <GrantsGovMoreInfo :resultID="result.id" @grant-details="emitGrantInfoLoaded" />
            </li>
            </ul>
        </div>
      </div>
    </div>
  </template>
  
  
  <script>
import GrantsGovMoreInfo from '@/Components/GrantsGovMoreInfo.vue';

export default {
  name: "GrantsGovSearch",
  data() {
    return {
      searchTerm: this.getSearchTerm(),
      results: [], // Stores all results from the API
      currentPage: 1, // Current page for pagination
      pageSize: 5, // Number of results per page
    };
  },
  async mounted() {
    // Fetch data from the Grants.gov API on component mount
    await this.searchGrantsGov();
  },
  components: {
    GrantsGovMoreInfo,
  },
  computed: {
    // Calculate the paginated results based on the current page and page size
    paginatedResults() {
      const start = (this.currentPage - 1) * this.pageSize;
      const end = start + this.pageSize;
      return this.results.slice(start, end);
    },
    // Calculate total number of pages based on results length
    totalPages() {
      return Math.ceil(this.results.length / this.pageSize);
    },
  },
  props: {
    companyDescription: String, // Prop to receive the initial search term
  },
  watch: {
    // Watch for changes to companyDescription prop
    companyDescription() {
      this.searchTerm = this.getSearchTerm();
      this.searchGrantsGov(); // Trigger search when companyDescription changes
    },
  },
  methods: {
    // For now, returns the initial search term without transformation
    getSearchTerm() {
        // Return the company description as the initial search term, but if it's blank or contains only whitespace, put in Artificial Intelligence
        if (this.companyDescription.trim() === '') {
            return 'Artificial Intelligence';
        }
        //replace all occurences of AI case insensitive as a word with Artificial Intelligence
        return this.companyDescription.replace(/\bAI\b/gi, 'Artificial Intelligence');
    },

    // Function to fetch data from the Grants.gov API
    async searchGrantsGov() {
      const payload = {
        keyword: this.searchTerm,
        cfda: null,
        agencies: null,
        dateRange: "",
        eligibilities: null,
        fundingCategories: null,
        fundingInstruments: null,
        oppStatuses: "forecasted|posted",
        rows: 5000,
        sortBy: "",
      };

      try {
        const response = await fetch(
          "https://apply07.grants.gov/grantsws/rest/opportunities/search",
          {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
            },
            body: JSON.stringify(payload),
          }
        );
        const data = await response.json();
        this.results = data.oppHits;
        this.currentPage = 1; // Reset to first page after search
      } catch (error) {
        console.error("Error fetching grants:", error);
      }
    },
    emitGrantInfoLoaded(grant) {
      // Re-emit the grant-info-loaded event up to Home.vue
      this.$emit('grant-details', grant);
    },
    // Navigate to the previous page
    prevPage() {
      if (this.currentPage > 1) {
        this.currentPage--;
      }
    },
    // Navigate to the next page
    nextPage() {
      if (this.currentPage < this.totalPages) {
        this.currentPage++;
      }
    },
  },
};
</script>

  
  <style scoped>
  /* Updated CSS for GrantsGovSearch Component */

/* General container styling */
.grants-gov-search-container {
  background-color: #f4f1eb; /* Light beige background */
}

/* Search input styling */
input[type="text"] {
  display: block;
  padding: 0.5rem;
  margin: auto;
  font-size: 1.5rem;
  border-radius: 1rem;
  width: 80%;
  max-width: 500px;
  border: 1px solid #c000;
  text-align: center;
  /* change the color of the placeholder text */
  color: #111;
}

/* change the color of the placeholder text */
input[type="text"]::placeholder {
    color: #76685b;
    font-style: italic;
}


/* Search button styling */

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

/* Results list styling */
.results-list {
  list-style: none;
  padding: 0;
}

/* Result item styling */
.result-item {
  margin-bottom: 5%;
  padding: 2%;
  border-bottom: 1px solid #ddd;
  border-radius: 5px;
  background-color: #fff; /* White background for contrast */
}

.result-item h4 {
  margin: 0;
  font-size: 1.125rem; /* Equivalent to 18px */
  font-weight: bold;
  margin-bottom: 0.5rem; /* Consistent spacing */
}

.result-item p {
  margin: 5px 0;
  font-size: 1rem; /* Equivalent to 16px */
  color: #333; /* Dark gray text */
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 20px;
}

.pagination button {
  min-width: 50px;
  color: white;
  border: none;
  cursor: pointer;
}

.pagination button:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}

.pagination span {
  font-size: 1.1rem;
}

a {
    padding-left: 1.25rem; /* equivalent to px-5 */
    padding-right: 1.25rem; /* equivalent to px-5 */
    margin-top: 1rem; /* equivalent to mt-4 */
    background: linear-gradient(to top, #B8CFD6, #B8CFD6); /* equivalent to bg-gradient-to-t from-atechGreen to-atechBlue-light */
    box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1); /* equivalent to shadow-lg */
    color: black; /* equivalent to text-black */
    font-size: 1.125rem; /* equivalent to text-lg */
    border-radius: 0.5rem; /* equivalent to rounded-lg */
    text-decoration: none; /* Remove underline for links */
    display: inline-block; /* Ensures padding and margin are respected */
}
  </style>
  