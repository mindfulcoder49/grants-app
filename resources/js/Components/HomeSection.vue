<template>
  <div v-if="searchPerformed">
    <!-- Pass selectedGrants to AiAssistant -->
    <ai-assistant :grants="selectedGrants" :govgrants="govgrants" @remove-grant="removeSelectedGrant" />
  </div>
  
  <!-- Home Page Layout (content only, no header/footer) -->
  <h1 class="text-center text-3xl font-bold my-12">
    I am looking for grants
  </h1>
  
  <SearchInput v-model="companyDescription" />
  <SearchButton :companyDescription="companyDescription" @search="performSearch" :buttonText="buttonText" />

  <!-- Conditionally render content only after a search is performed -->
  <div v-if="searchPerformed">
    <!-- Tabs for selecting which search result to display -->
    <div class="tabs">
      <button 
        :class="{ active: activeTab === 'govgrants', inactive: activeTab !== 'govgrants' }"
        @click="activeTab = 'govgrants'">
        Keyword Search
      </button>

      
      <button 
        :class="{ active: activeTab === 'vectorSearch', inactive: activeTab !== 'vectorSearch'}" 
        class = "vector-search-button"
        @click="activeTab = 'vectorSearch'">
        Semantic Meaning Vector Search
      </button>
      <span v-if="loadingVectorSearch" class="loading-spinner"></span>
      

    </div>
    
    <!-- Display GovGrants content when active -->
    <div v-show="activeTab === 'govgrants'">
      <GrantsGovSearch 
      ref="govGrantsSearch"
      :addedGrants="selectedGrants.map(g => g.id)"  
      :companyDescription="companyDescription" 
      @add-to-ai-conversation="addSelectedGrant"
      @remove-from-ai-conversation="removeSelectedGrant"
               />
    </div>
    
    <!-- Display GrantList content when active -->
    <div v-show="activeTab === 'vectorSearch'" class="results-container">
      <div v-if="$page.props.grants != null" class="results-header">
        Relevant Grants
      </div>
      <div class="results-content">
        <!-- Pass selectedGrants to GrantList -->
        <GrantList
          :grants="$page.props.grants"
          :addedGrants="selectedGrants.map(g => g.id)"  
          class="grant-list"
          @add-to-ai-conversation="addSelectedGrant"
          @remove-from-ai-conversation="removeSelectedGrant"
        />
      </div>
    </div>
  </div>
</template>
<script>
import SearchInput from '@/Components/SearchInput.vue';
import SearchButton from '@/Components/SearchButton.vue';
import AiAssistant from '@/Components/AiAssistant.vue';
import GrantList from '@/Components/GrantList.vue';
import GrantsGovSearch from '@/Components/GrantsGovSearch.vue';

export default {
  name: 'HomeSection',
  components: { SearchInput, SearchButton, AiAssistant, GrantList, GrantsGovSearch },
  data() {
    return {
      companyDescription: this.searchTerm || '',
      searchPerformed: false,
      govgrants: [],  // Store all the loaded government grant data here
      selectedGrants: [],  // Store selected grants here
      buttonText: 'SEARCH FOR GRANTS',
      activeTab: 'govgrants', // Default to GovGrants tab being active
      loadingVectorSearch: true, // Track if the vector search is loading
    };
  },
  props: {
    searchTerm: {
      type: String,
      default: '',
    },
    grants: {
      type: Array,
      default: () => [],
    },
  },
  methods: {
    performSearch(description) {
      this.searchPerformed = true;
      this.buttonText = 'SEARCHING...';
      this.loadingVectorSearch = true;  // Start loading vector search

      // Trigger both searches in parallel
      this.$inertia.post('/', { description }, {
        preserveScroll: true,
        onSuccess: () => {
          this.buttonText = 'SEARCH FOR GRANTS';  
          this.loadingVectorSearch = false;  
        },
        onError: () => {
          this.loadingVectorSearch = false; 
        }
      });

      // Trigger native search at the same time
      this.$nextTick(() => {
        if (this.$refs.govGrantsSearch) {
          this.$refs.govGrantsSearch.searchGrantsGov();
        }
      });
    },
    async addSelectedGrant(grant) {
      // Check if the grant is already selected
      
        this.selectedGrants.push(grant);

        // Make an API call to save the grant info if the user is authenticated
        try {
          const response = await axios.post('/saved-grants', {
            grant: JSON.stringify(grant),
          });
          console.log('Grant saved successfully:', response.data.message);
        } catch (error) {
          console.error('Failed to save grant:', error.response ? error.response.data : error.message);
        }
      
    },
    removeSelectedGrant(grantId) {
      // Remove the grant by its ID from the selectedGrants array
      this.selectedGrants = this.selectedGrants.filter(g => g.id !== grantId);
    },
  },
  watch: {
    grants(newGrants) {
      if (newGrants.length > 0) {
        this.searchPerformed = true;
        this.buttonText = 'SEARCH FOR GRANTS';  // Reset the button text when grants are updated
      }
    }
  },
  mounted() {
  },
};
</script>

<style scoped>
.tabs {
  display: flex;
  justify-content: center;
  margin-top: 20px;
  width: 100%;
  border-bottom: 1px solid black; /* Border under the entire tab section */
}

.tabs button {
  padding: 10px 20px;
  font-size: 1rem;
  color: black;
  background-color: #f4f1eb;
  border: 1px solid black; /* Border on all sides */
  border-bottom: 0px; /* No bottom border */
  border-radius: 5px 5px 0 0; /* Rounded top corners */
  cursor: pointer;
  margin: 0 5px;
  position: relative; /* To stack properly */
  top: 1px; /* Move the tab up by 1px to align with the content area */
}

.tabs button.inactive {
  border-bottom: 1px solid transparent; /* Make the bottom border "invisible" */
  background-color: #99d6ff; /* Match the background with the content area */
  top: 0; /* Align the active tab with the content */
}

.results-container  {
  border-top: none; /* Remove top border to align with the active tab */
}

.loading-spinner {
  border: 4px solid #f3f3f3;
  border-top: 4px solid #3498db;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  animation: spin 2s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

</style>
