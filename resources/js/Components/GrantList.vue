<template>
  <!-- Grant List Component -->
  <div>
    <!-- Pagination controls -->


    <!-- Check if there are grants to display -->
    <div v-if="paginatedGrants.length > 0">
      <div class="pagination">
      <button @click="prevPage" :disabled="currentPage === 1">Previous</button>
      <span>Page {{ currentPage }} of {{ totalPages }}</span>
      <button @click="nextPage" :disabled="currentPage === totalPages || grants.length === 0">Next</button>
    </div>
      <div v-for="grant in paginatedGrants" :key="grant.id" class="grant-item border-b border-gray-300 pb-4 mb-4">
        <h3 class="text-xl font-semibold text-transparent bg-clip-text bg-gradient-to-r from-blue-800 via-green-700 drop-shadow-md">
          Match Score: {{ formatSimilarity(grant.similarity) }}
        </h3>

        <h3 class="text-xl font-bold mb-2">{{ grant.opportunity_title }} - Ceiling: {{ formatCurrency(grant.award_ceiling) }}</h3>
        <p class="text-sm text-gray-700 mb-2">{{ grant.description }}</p>
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div>
            <strong>Opportunity ID:</strong> {{ grant.opportunity_id }}<br>
            <strong>Opportunity Number:</strong> {{ grant.opportunity_number }}<br>
            <strong>Post Date:</strong> {{ formatDate(grant.post_date) }}<br>
            <strong>Close Date:</strong> {{ formatDate(grant.close_date) }}<br>
            <strong>Agency Name:</strong> {{ grant.agency_name }}<br>
            <strong>CFDA Number:</strong> {{ grant.cfda_number }}<br>
            <strong>Category of Funding Activity:</strong> {{ grant.category_of_funding_activity }}<br>
            <strong>Cost Sharing Requirement:</strong> {{ grant.cost_sharing_requirement ? 'Yes' : 'No' }}<br>
            <strong>Version:</strong> {{ grant.version }}
          </div>
          <div>
            <strong>Eligibility:</strong> {{ grant.eligible_applicants }}<br>
            <strong>Funding Instrument:</strong> {{ grant.funding_instrument_type }}<br>
            <strong>Category:</strong> {{ grant.opportunity_category }}<br>
            <strong>Contact:</strong> {{ grant.grantor_contact_email }}<br>
            <strong>Website:</strong> 
            <a :href="grant.additional_information_url" target="_blank" class="text-blue-500 hover:underline">
              {{ grant.additional_information_url }}
            </a>
            <strong>Link to Grants.gov: </strong>
            <!-- Add link to grant details on grants.gov using result.id after https://www.grants.gov/search-results-detail/-->
            <a :href="'https://www.grants.gov/search-results-detail/' + grant.opportunity_id" target="_blank">View Details</a>
          </div>
        </div>

        <!-- Add to AI Conversation Button -->
        <button
          @click="toggleGrant(grant)"
          class="mt-4 bg-black text-white p-2 rounded-lg cursor-pointer"
        >
          {{ isAdded(grant.id) ? 'Remove from AI Chatbot' : 'Add to AI Chatbot' }}
        </button>
      </div>
      <div class="pagination">
      <button @click="prevPage" :disabled="currentPage === 1">Previous</button>
      <span>Page {{ currentPage }} of {{ totalPages }}</span>
      <button @click="nextPage" :disabled="currentPage === totalPages || grants.length === 0">Next</button>
    </div>
    </div>

    <!-- If no grants available, show a message -->
    <div v-else>
      <p>No grants found. Results may still be loading.</p>
    </div>

  </div>
</template>

<script>
export default {
  name: 'GrantList',
  props: {
    grants: {
      type: Array,
      required: true,
      default: () => [],
    },
    addedGrants: {
      type: Array,
      required: true
    }
  },
  data() {
    return {
      currentPage: 1,
      pageSize: 5, // Number of grants per page
    };
  },
  computed: {
    // Calculate paginated grants
    paginatedGrants() {
      const start = (this.currentPage - 1) * this.pageSize;
      const end = start + this.pageSize;
      return this.grants.slice(start, end);
    },
    totalPages() {
      const length = this.grants.length || 0;
      return Math.ceil(length / this.pageSize);
    },
  },
  methods: {
    toggleGrant(grant) {
      if (this.isAdded(grant.id)) {
        // If already added, remove the grant
        this.$emit('remove-from-ai-conversation', grant.id);
      } else {
        // If not added, add the grant
        this.$emit('add-to-ai-conversation', grant);
      }
    },
    isAdded(grantId) {
      return this.addedGrants.includes(grantId);
    },
    prevPage() {
      if (this.currentPage > 1) {
        this.currentPage--;
      }
    },
    nextPage() {
      if (this.currentPage < this.totalPages) {
        this.currentPage++;
      }
    },
    formatDate(value) {
      if (!value) return '';
      const date = new Date(value);
      return date.toLocaleDateString('en-US');
    },
    formatCurrency(value) {
      if (!value) return '$0';
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
      }).format(value);
    },
    formatSimilarity(value) {
      if (!value) return '0%';
      return `${(value * 100).toFixed(2)}%`;
    }
  }
};
</script>

<style scoped>
.grant-item {
  margin-bottom: 5%;
  padding: 2%;
  border-bottom: 1px solid #ddd;
  border-radius: 5px;
  background-color: #fff; /* White background for contrast */
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 20px;
}



.pagination button:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}



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

.pagination button {
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

.pagination button:hover {
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
