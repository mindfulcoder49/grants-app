<template>

  
  <!-- Home Page Layout (content only, no header/footer) -->
  <h1 class="text-center text-3xl font-bold my-12">
    I am looking for grants
  </h1>
  
  <SearchInput v-model="companyDescription" />
  <SearchButton :companyDescription="companyDescription" @search="performSearch" :buttonText="buttonText" />

  <AdvancedSearch ref="advancedSearch" :initial-fields="advancedSearchFields" />


  <!-- Conditionally render content only after a search is performed -->
  <div v-if="searchPerformed">
    <!-- Tabs for selecting which search result to display
    <div class="tabs">
      <span v-if="loadingVectorSearch" class="loading-spinner"></span>
      <button 
        :class="{ active: activeTab === 'vectorSearch', inactive: activeTab !== 'vectorSearch'}" 
        class = "vector-search-button"
        @click="activeTab = 'vectorSearch'">
        Semantic Meaning Vector Search
      </button>
      <button 
        :class="{ active: activeTab === 'govgrants', inactive: activeTab !== 'govgrants' }"
        @click="activeTab = 'govgrants'">
        Keyword Search
      </button>

      

      
      

    </div>  -->
    
    <!-- Display GovGrants content when active 
    <div v-show="activeTab === 'govgrants'">
      <GrantsGovSearch 
      ref="govGrantsSearch"
      :addedGrants="selectedGrants.map(g => g.id)"  
      :companyDescription="companyDescription" 
      @add-to-ai-conversation="addSelectedGrant"
      @remove-from-ai-conversation="removeSelectedGrant"
               />
    </div> -->
    
    <!-- div for spinner and loading message-->

    
    
    <!-- Pass selectedGrants to AiAssistant -->
    <ai-assistant :grants="selectedGrants" :govgrants="govgrants" @remove-grant="removeSelectedGrant" />
    
    <!-- Display GrantList content when active -->
    <div v-show="activeTab === 'vectorSearch'" class="results-container ">
      <div v-if="grants != null" class="results-header">
        <div class="border-b border-gray-300 pb-2 mb-[60px]">
          <h3 class="text-2xl font-bold ">Relevant Grants</h3>
        </div>

        <!-- Sidebar -->
        <div class="sidebar rounded-md">
          <h2 class="sidebar-title">Filters</h2>

          <!-- Any Field Filter -->
          <div class="filter-section">
            <label for="opportunity-title">General Filter:</label>
            <input type="text" id="any-field" v-model="filters.anyField" placeholder="Search by any field">
          </div>

          <!-- Opportunity Title Filter -->
          <div class="filter-section">
            <label for="opportunity-title">Opportunity Title:</label>
            <input type="text" id="opportunity-title" v-model="filters.opportunityTitle" placeholder="Search by title">
          </div>

          <!-- Opportunity ID Filter -->
          <div class="filter-section">
            <label for="opportunity-id">Opportunity ID:</label>
            <input type="text" id="opportunity-id" v-model="filters.opportunityId" placeholder="Search by ID">
          </div>

          <!-- Opportunity Number Filter -->
          <div class="filter-section">
            <label for="opportunity-number">Opportunity Number:</label>
            <input type="text" id="opportunity-number" v-model="filters.opportunityNumber" placeholder="Search by number">
          </div>

          <!-- Post Date Range Filter -->
          <div class="filter-section">
            <label for="post-date-from">Post Date From:</label>
            <input type="date" id="post-date-from" v-model="filters.postDateFrom">
            <label for="post-date-to">To:</label>
            <input type="date" id="post-date-to" v-model="filters.postDateTo">
          </div>

          <!-- Close Date Range Filter -->
          <div class="filter-section">
            <label for="close-date-from">Close Date From:</label>
            <input type="date" id="close-date-from" v-model="filters.closeDateFrom">
            <label for="close-date-to">To:</label>
            <input type="date" id="close-date-to" v-model="filters.closeDateTo">
          </div>

          <!-- Agency Name Filter -->
          <div class="filter-section">
            <label for="agency-name">Agency Name:</label>
            <select id="agency-name" v-model="filters.agencyName">
              <option value="">All Agencies</option>
              <option v-for="agency in uniqueAgencies" :key="agency" :value="agency">{{ agency }}</option>
            </select>
          </div>

          <!-- CFDA Number Filter -->
          <div class="filter-section">
            <label for="cfda-number">CFDA Number:</label>
            <input type="text" id="cfda-number" v-model="filters.cfdaNumber" placeholder="Search by CFDA">
          </div>

          <!-- Category of Funding Activity Filter -->
          <div class="filter-section">
            <label for="funding-category">Category of Funding Activity:</label>
            <select id="funding-category" v-model="filters.categoryOfFundingActivity">
              <option value="">All Categories</option>
              <option v-for="[code, label] in Object.entries(fundingActivityTypes)" :key="code" :value="code">
                {{ label }}
              </option>
            </select>
          </div>

          <!-- Cost Sharing Requirement Filter -->
          <div class="filter-section">
            <label for="cost-sharing">Cost Sharing Requirement:</label>
            <select id="cost-sharing" v-model="filters.costSharing">
              <option value="">Any</option>
              <option value="yes">Yes</option>
              <option value="no">No</option>
            </select>
          </div>

          <!-- Version Filter -->
          <div class="filter-section">
            <label for="version">Version:</label>
            <input type="number" id="version" v-model="filters.version" placeholder="Search by version">
          </div>

          <!-- Eligibility Filter -->
          <div class="filter-section">
            <label for="eligibility">Eligibility:</label>
            <select id="eligibility" v-model="filters.eligibility">
              <option value="">All Eligibility Types</option>
              <option v-for="[code, label] in Object.entries(applicantTypes)" :key="code" :value="code">
                {{ label }}
              </option>
            </select>
          </div>

          <!-- Funding Instrument Filter -->
          <div class="filter-section">
            <label for="funding-instrument">Funding Instrument:</label>
            <select id="funding-instrument" v-model="filters.fundingInstrument">
              <option value="">All Instruments</option>
              <option v-for="instrument in uniqueFundingInstruments" :key="instrument" :value="instrument">{{ instrument }}</option>
            </select>
          </div>

          <!-- Category Filter -->
          <div class="filter-section">
            <label for="category">Category:</label>
            <select id="category" v-model="filters.opportunityCategory">
              <option value="">All Categories</option>
              <option v-for="category in uniqueOpportunityCategories" :key="category" :value="category">{{ category }}</option>
            </select>
          </div>

          <!-- Grantor Contact Filter -->
          <div class="filter-section">
            <label for="contact">Contact:</label>
            <input type="email" id="contact" v-model="filters.grantorContact" placeholder="Search by contact email">
          </div>

          <!-- Clear Filters Button -->
          <div class="filter-section">
            <button @click="clearFilters" class="clear-filters-button">Clear Filters</button>
          </div>
        </div>

      </div>
      <div class="results-content">
        <div v-if="loadingVectorSearch"  class="w-full flex justify-center">
          <p>Results still loading... &nbsp</p>
          <div class="loading-spinner"></div>
        </div>
        <!-- Pass selectedGrants to GrantList -->
        <GrantList
          :grants="filteredGrants"
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
import AdvancedSearch from '@/Components/AdvancedSearch.vue';

export default {
  name: 'HomeSection',
  components: { SearchInput, SearchButton, AiAssistant, GrantList, GrantsGovSearch, AdvancedSearch },
  data() {
    return {
      companyDescription: this.searchTerm || '',
      searchPerformed: false,
      govgrants: [],  // Store all the loaded government grant data here
      selectedGrants: [],  // Store selected grants here
      buttonText: 'SEARCH FOR GRANTS',
      activeTab: 'vectorSearch', // Default to GovGrants tab being active
      loadingVectorSearch: true, // Track if the vector search is loading
      grants: [],
      advancedSearchFields: [],
      filters: {
        anyField: '',
        opportunityTitle: '',
        opportunityId: '',
        opportunityNumber: '',
        postDateFrom: '',
        postDateTo: '',
        closeDateFrom: '',
        closeDateTo: '',
        agencyName: '',
        cfdaNumber: '',
        categoryOfFundingActivity: '',
        costSharing: '',
        version: null,
        eligibility: '',
        fundingInstrument: '',
        opportunityCategory: '',
        grantorContact: ''
      },
      applicantTypes: {
          '99': 'Unrestricted',
          '00': 'State governments',
          '01': 'County governments',
          '02': 'City or township governments',
          '04': 'Special district governments',
          '05': 'Independent school districts',
          '06': 'Public and State controlled institutions of higher education',
          '07': 'Native American tribal governments (Federally recognized)',
          '08': 'Public housing authorities/Indian housing authorities',
          '11': 'Native American tribal organizations (other than Federally recognized)',
          '12': 'Nonprofits with 501(c)(3) status',
          '13': 'Nonprofits without 501(c)(3) status',
          '20': 'Private institutions of higher education',
          '21': 'Individuals',
          '22': 'For-profit organizations other than small businesses',
          '23': 'Small businesses',
          '25': 'Others'
        },
        fundingActivityTypes: {
            'ACA': 'Affordable Care Act',
            'AG': 'Agriculture',
            'AR': 'Arts',
            'BC': 'Business and Commerce',
            'CD': 'Community Development',
            'CP': 'Consumer Protection',
            'DPR': 'Disaster Prevention and Relief',
            'ED': 'Education',
            'ELT': 'Employment, Labor and Training',
            'EN': 'Energy',
            'ENV': 'Environment',
            'FN': 'Food and Nutrition',
            'HL': 'Health',
            'HO': 'Housing',
            'HU': 'Humanities',
            'ISS': 'Income Security and Social Services',
            'IS': 'Information and Statistics',
            'LJL': 'Law, Justice and Legal Services',
            'NR': 'Natural Resources',
            'RA': 'Recovery Act',
            'RD': 'Regional Development',
            'ST': 'Science and Technology',
            'T': 'Transportation',
            'O': 'Other',
        },
    };
  },
  props: {
    searchTerm: {
      type: String,
      default: '',
    },
  },
  computed: {
    uniqueAgencies() {
    return [...new Set(this.grants.map(grant => grant.agency_name))];
    },
    uniqueCategories() {
      return [...new Set(this.grants.map(grant => grant.category_of_funding_activity))];
    },
    uniqueEligibilities() {
      return [...new Set(this.grants.flatMap(grant => grant.eligible_applicants))];
    },
    uniqueFundingInstruments() {
      return [...new Set(this.grants.map(grant => grant.funding_instrument_type))];
    },
    uniqueOpportunityCategories() {
      return [...new Set(this.grants.map(grant => grant.opportunity_category))];
    },
    filteredGrants() {
      return this.grants.filter(grant => {
        const postDate = new Date(grant.post_date);
        const postDateFrom = new Date(this.filters.postDateFrom);
        const postDateTo = new Date(this.filters.postDateTo);
        const closeDate = new Date(grant.close_date);
        const closeDateFrom = new Date(this.filters.closeDateFrom);
        const closeDateTo = new Date(this.filters.closeDateTo);

        // Check all individual filters
        const matchesFilters = 
          (this.filters.opportunityTitle ? grant.opportunity_title.toLowerCase().includes(this.filters.opportunityTitle.toLowerCase()) : true) &&
          (this.filters.opportunityId ? grant.opportunity_id === this.filters.opportunityId : true) &&
          (this.filters.opportunityNumber ? grant.opportunity_number === this.filters.opportunityNumber : true) &&
          (this.filters.postDateFrom ? postDate >= postDateFrom : true) &&
          (this.filters.postDateTo ? postDate <= postDateTo : true) &&
          (this.filters.closeDateFrom ? closeDate >= closeDateFrom : true) &&
          (this.filters.closeDateTo ? closeDate <= closeDateTo : true) &&
          (this.filters.agencyName ? grant.agency_name === this.filters.agencyName : true) &&
          (this.filters.cfdaNumber ? grant.cfda_number.includes(this.filters.cfdaNumber) : true) &&
          (this.filters.categoryOfFundingActivity ? grant.category_of_funding_activity === this.filters.categoryOfFundingActivity : true) &&
          (this.filters.costSharing ? (grant.cost_sharing_requirement ? 'yes' : 'no') === this.filters.costSharing : true) &&
          (this.filters.version ? grant.version === this.filters.version : true) &&
          (this.filters.eligibility ? grant.eligible_applicants.includes(this.filters.eligibility) : true) &&
          (this.filters.fundingInstrument ? grant.funding_instrument_type === this.filters.fundingInstrument : true) &&
          (this.filters.opportunityCategory ? grant.opportunity_category === this.filters.opportunityCategory : true) &&
          (this.filters.grantorContact ? grant.grantor_contact_email === this.filters.grantorContact : true);

        // Check the anyField filter
        const matchesAnyField = this.filters.anyField
          ? Object.values(grant).some(value => 
              value !== null && 
              value !== undefined && 
              value.toString().toLowerCase().includes(this.filters.anyField.toLowerCase())
            )
          : true;

        return matchesFilters && matchesAnyField;
      });
    },
  },  

  methods: {
  clearFilters() {
    this.filters = {
      opportunityTitle: '',
      opportunityId: '',
      opportunityNumber: '',
      postDateFrom: '',
      postDateTo: '',
      closeDateFrom: '',
      closeDateTo: '',
      agencyName: '',
      cfdaNumber: '',
      categoryOfFundingActivity: '',
      costSharing: '',
      version: null,
      eligibility: '',
      fundingInstrument: '',
      opportunityCategory: '',
      grantorContact: ''
    };
  },
  insertSortedGrant(grant) {
    // Insert grant in sorted order by matchScore
    let index = this.grants.findIndex(g => g.similarity < grant.similarity);
    if (index === -1) index = this.grants.length; // Insert at the end if no lower matchScore is found
    this.grants.splice(index, 0, grant);
  },

  async performSearch(searchPayload) {
    this.searchPerformed = true;
    this.loadingVectorSearch = true;
    this.grants = [];


    const advancedFields = this.$refs.advancedSearch.getFields();

    if (advancedFields.length > 0) {
      searchPayload.advancedFields = advancedFields;
    }

    try {
      const response = await fetch('/', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 
        },
        body: JSON.stringify(searchPayload),
      });

      const reader = response.body.getReader();
      const decoder = new TextDecoder("utf-8");
      let buffer = '';

      let { value, done } = await reader.read();
      while (!done) {
        buffer += decoder.decode(value, { stream: true });

        let lines = buffer.split('\n');
        buffer = lines.pop();

        for (const line of lines) {
          if (line.trim()) {
            try {
              const grant = JSON.parse(line);
              this.insertSortedGrant(grant); // Use sorted insertion
            } catch (err) {
              console.error('JSON parse error for line:', err);
            }
          }
        }
        ({ value, done } = await reader.read());
      }

      if (buffer.trim()) {
        try {
          const grant = JSON.parse(buffer);
          this.insertSortedGrant(grant); // Final sorted insertion
        } catch (err) {
          console.error('Final JSON parse error:', err);
        }
      }
    } finally {
      this.loadingVectorSearch = false;
    }
  },
  

  async addSelectedGrant(grant) {
    if (!this.selectedGrants.some(g => g.id === grant.id)) {
      this.selectedGrants.push(grant);

      try {
        const response = await axios.post('/saved-grants', {
          grant: JSON.stringify(grant),
        });
        console.log('Grant saved successfully:', response.data.message);
      } catch (error) {
        console.error('Failed to save grant:', error.response ? error.response.data : error.message);
      }
    }
  },

  removeSelectedGrant(grantId) {
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

body {
  font-family: 'Arial', sans-serif;
  margin: 0;
  padding: 0;
  background-color: #977022; /* Light beige background */
}
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

.sidebar {
  padding: 20px;
  border-radius: 5px;
  background-color: #fff;
  width: 100%
}

.sidebar-title {
  font-size: 1.5rem;
  font-weight: bold;
  margin-bottom: 20px;
}

.filter-section {
  margin-bottom: 20px;
}

.filter-section label {
  display: block;
  font-weight: bold;
  margin-bottom: 5px;
}

.filter-section select,
.filter-section input {
  width: 100%;
  padding: 5px;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 1rem;
}

.clear-filters-button {
  background-color: #ff4d4d;
  color: white;
  border: none;
  padding: 10px 15px;
  border-radius: 5px;
  cursor: pointer;
  width: 100%;
}

.clear-filters-button:hover {
  background-color: #e60000;
}


.logo {
  position: absolute;
  top: 2%;
  left: 2%;
  width: 10%; /* Responsive logo size */
  cursor: pointer;
}

.header-links {
  position: absolute;
  top: 2%;
  right: 2%;
  font-size: 1rem; /* 1rem = 16px for default readability */
  color: #555; /* Subtle dark gray */
}

h1 {
  text-align: center;
  font-size: 2.5rem; /* Equivalent to 40px */
  font-weight: bold;
  color: #000; /* Black text */
  margin-top: 20vh; /* Vertical center alignment */
}

/* Custom CSS for Results Page */
.results-container {
  display: flex;
  flex-direction: column; /* Mobile-first column layout */
  /* Padding only on sides and bottom */
  padding: 5% 5% 0;
}

@media (min-width: 768px) {
  .results-container {
    flex-direction: row; /* Row layout for desktop */
    justify-content: space-between;
  }
  .results-content {
    width: 60%;
  }
  .results-header {
    width: 35%;
    display: flex;
    flex-direction: column; /* Mobile-first column layout */
  }
}

.results-header {
  font-size: 1.5rem; /* Equivalent to 24px */
  font-weight: bold;
  color: #000;
  width: 100%;
  margin-bottom: 5%; /* Spacing for mobile */
}

@media (min-width: 768px) {
  .results-header {
    width: 35%;
    margin-bottom: 0; /* Remove spacing for desktop */
  }
}

.grant-list {
  width: 100%;
}

@media (min-width: 768px) {
  .grant-list {
    width: 100%;
  }
}

.grant-item {
  margin-bottom: 5%;
  padding: 2%;
  border-bottom: 1px solid #ddd;
}

.grant-item h3 {
  font-size: 1.125rem; /* Equivalent to 18px */
  font-weight: bold;
  margin-bottom: 0.5rem; /* Consistent spacing */
}

.grant-item p {
  font-size: 1rem; /* Equivalent to 16px */
  color: #333; /* Dark gray text */
}

/* Custom CSS for About Page */
.about-container {
  display: flex;
  flex-direction: column; /* Mobile-first column layout */
  padding: 5%;
}

.about-header {
  font-size: 1.5rem; /* Equivalent to 24px */
  font-weight: bold;
  color: #000;
  margin-bottom: 1.25rem; /* Spacing for mobile */
}

@media (min-width: 768px) {
  .about-container {
    flex-direction: row; /* Row layout for desktop */
    justify-content: space-between;
  }
  .about-section {
    width: 80%;
  }
}

.about-section {
  width: 100%;
  margin-bottom: 1.25rem; /* Spacing for mobile */
}

@media (min-width: 768px) {
  .about-section {
    width: 45%;
    margin-bottom: 0; /* Remove spacing for desktop */
  }
}

.about-section h2 {
  font-size: 1.5rem; /* Equivalent to 24px */
  font-weight: bold;
  margin-bottom: 1rem; /* Consistent spacing */
}

.about-section ul {
  list-style-type: none;
  padding: 0;
}

.about-section li {
  font-size: 1.125rem; /* Equivalent to 18px */
  color: #333;
  margin-bottom: 0.5rem; /* Consistent spacing */
}

.about-section p {
  font-size: 1rem; /* Equivalent to 16px */
  color: #333;
  margin-bottom: 0.75rem; /* Spacing for consistency */
}

a, p, div {
  word-wrap: break-word; /* For older browsers */
  word-break: break-word; /* Break long words/links */
  overflow-wrap: break-word; /* Modern solution */
}

</style>
