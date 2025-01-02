<template>
  <!-- Grant List Component -->
  <div ref="grantList">
    <!-- Pagination controls -->
    <div v-if="paginatedGrants.length > 0">
      <div class="pagination flex w-full">
        <!-- button to go to the first page -->
         <div class="button-group flex" >
        <button @click="currentPage = 1" :disabled="currentPage === 1">First</button>

        <button @click="prevPage" :disabled="currentPage === 1">Previous</button>
         </div>
        <!-- Make the display an editable input -->
         <div class="numbers-group flex" >
        <input type="number" v-model="currentPage" min="1" max="totalPages" class="text-center" style="width: 80px;">
        <span class="m-auto"> of </span><span class="m-auto">{{ totalPages }}</span>
          </div>
          <div class="button-group flex" >
        <button @click="nextPage" :disabled="currentPage === totalPages || grants.length === 0">Next</button>
        <!-- button to go to the last page -->
        <button @click="currentPage = totalPages" :disabled="currentPage === totalPages">Last</button>
          </div>
      </div>
      
      <div v-for="grant, index in paginatedGrants" :key="grant.id" class="grant-item border-b border-gray-300 pb-4 mb-4">



        <div v-if="grant.opportunityTitle" class="p-4 shadow rounded-lg">
          <!--
          <div class="bg-black text-white">
            <JsonTree :json="grant" />
          </div> -->

          


          <!-- Match Score (if available in API data)
          <h3 v-if="grant.similarity" class="text-xl font-semibold text-transparent bg-clip-text bg-gradient-to-r from-blue-800 via-green-700 drop-shadow-md">
            Match Score: {{ formatSimilarity(grant.similarity) }}
          </h3>

          <h3 class="text-xl font-bold mb-2">
            {{ grant.opportunityTitle }} - Ceiling: {{ formatCurrency(grant.awardCeiling) }}
          </h3>
          <p class="text-md text-gray-700 mb-2">{{ grant.synopsis.synopsisDesc }}</p>

          <div class="grid md:grid-cols-2 gap-4 text-md">
            <div>
              <strong>Opportunity ID:</strong> {{ grant.id }}<br>
              <strong>Opportunity Number:</strong> {{ grant.opportunityNumber }}<br>
              <strong>Post Date:</strong> {{ formatDate(grant.synopsis.postingDate) }}<br>
              <strong>Close Date:</strong> {{ formatDate(grant.synopsis.responseDate) }}<br>
              <strong>Agency Name:</strong> {{ grant.synopsis.agencyName }}<br>
              <strong>CFDAs:</strong><br>
              <p v-for="(cfda, index) in grant.cfdas" :key="index">
                {{ cfda.programTitle }}
              </p>
              <strong>Category of Funding Activity:</strong> {{ getOpportunityCategory(grant.opportunityCategory?.category) }}<br>
              <strong>Cost Sharing Requirement:</strong> {{ grant.synopsis.costSharingRequirement ? 'Yes' : 'No' }}<br>
              <strong>Version:</strong> {{ grant.revision }}
            </div>

            <div>
              <strong>Eligibility:</strong> {{ grant.synopsis.applicantEligibilityDesc || 'N/A' }}<br>
              <strong>Contact:</strong> {{ grant.synopsis.agencyContactEmail }}<br>
              <strong>Agency Contact:</strong> {{ grant.synopsis.agencyContactName }}<br>
              <strong>Agency Contact Phone:</strong> {{ grant.synopsis.agencyContactPhone }}<br>
              <strong>Agency Contact Email:</strong> {{ grant.synopsis.agencyContactEmail }}<br>
              <span v-if="grant.synopsis.additionalInformationUrl">
              <strong>Website:</strong>
              <a :href="grant.synopsis.additionalInformationUrl" target="_blank" class="text-blue-500 hover:underline">
                {{ grant.synopsis.additionalInformationUrl }}
              </a><br></span>
              <span v-if="grant.synopsis.fundingDescLinkUrl">
              <strong>Grant Funding Description:</strong>
              <a :href="grant.synopsis.fundingDescLinkUrl" target="_blank" class="text-blue-500 hover:underline">
                {{ grant.synopsis.fundingDescLinkUrl }}
              </a><br></span>
              <strong>Link to Grants.gov:</strong>
              <a :href="'https://www.grants.gov/search-results-detail/' + grant.id" target="_blank">View Details</a>
            </div>
          </div> -->
        </div> 





        <div v-else-if="grant.opportunity_title"> <!-- Native Grant -->
          <div class="grid grid-cols-[100px,1fr]">
            <div class="max-w-xs">
            <h3 class="text-xl font-semibold">
              {{ formatSimilarity(grant.similarity) }}
            </h3>
            <h3 class="text-m text-gray-400 my-2">
              Result {{ index + (currentPage - 1) * pageSize + 1 }}
            </h3>
          </div>
        <div>
          <h3 class="text-xl font-bold mb-2"> {{ grant.opportunity_title }} - Ceiling: {{ formatCurrency(grant.award_ceiling) }}</h3>
          <p class="text-md text-gray-700 mb-2" v-if="!isExpanded(grant.id)">
            {{ grant.description.length > 300 ? grant.description.slice(0, 300) + "..." : grant.description }}
          </p>


          <div v-if="isExpanded(grant.id)">
            <p class="text-md text-gray-700 mb-2">
                {{ grant.description }}
              </p>
              <strong>Eligibility:</strong> {{ getEligibilityDescription(grant.eligible_applicants) }}<br>
              <strong>Additional Eligibility Information:</strong> {{ stripHTML(grant.additional_information_on_eligibility) }}<br>
            <div class="grid md:grid-cols-2 gap-4 text-md" >

              <div>
                <strong>Opportunity ID:</strong> {{ grant.opportunity_id }}<br>
                <strong>Opportunity Number:</strong> {{ grant.opportunity_number }}<br>
                <strong>Post Date:</strong> {{ formatDate(grant.post_date) }}<br>
                <strong>Close Date:</strong> {{ formatDate(grant.close_date) }}<br>
                <strong>Agency Name:</strong> {{ grant.agency_name }}<br>
                <strong>CFDA Number:</strong> {{ grant.cfda_number }}<br>
                <strong>Category of Funding Activity:</strong> {{ getFundingActivity(grant.category_of_funding_activity) }}<br>
                <strong>Cost Sharing Requirement:</strong> {{ grant.cost_sharing_requirement ? 'Yes' : 'No' }}<br>
                <strong>Version:</strong> {{ grant.version }}
              </div>
              <div>
                <strong>Funding Instrument:</strong> {{ getFundingInstrument(grant.funding_instrument_type) }}<br>
                <strong>Category:</strong> {{ getOpportunityCategory(grant.opportunity_category) }}<br>
                <strong>Contact:</strong> {{ grant.grantor_contact_email }}<br>
                <strong>Website:</strong> 
                <a :href="grant.additional_information_url" target="_blank" class="text-blue-500 hover:underline">
                  {{ grant.additional_information_url }}
                </a><br>
                <strong>Link to Grants.gov: </strong>
                <a :href="'https://www.grants.gov/search-results-detail/' + grant.opportunity_id" target="_blank">View Details</a>
              </div>
            </div>
            <div class="w-full">

          </div>
        </div>
 
        <button @click="toggleExpand(grant.id)" class="toggle-button border-2 border-black p-2 rounded-lg cursor-pointer mr-10">
              {{ isExpanded(grant.id) ? 'Hide Details' : 'More Info' }}
            </button>
        <!-- Add to AI Conversation Button -->
        <button
          @click="toggleGrant(grant)"
          class="mt-4 border-black text-black border-2 p-2 rounded-lg cursor-pointer"
        >
          {{ isAdded(grant.id) ? 'Remove from AI Chatbot' : 'Add to AI Chatbot' }}
        </button>

        <p class="m-4">If you are logged in, clicking the "Add to AI Chatbot" button will also add this grant to your saved grants. 
          <a href="/login" @click="warnNavigation" class="text-blue-500 hover:underline">Login</a> to save grants.</p>
      </div>
    </div>
  </div>
</div>

      <div class="pagination flex w-full justify-between py-5">
        <!-- button to go to the first page -->
         <div class="button-group flex" >
        <button @click="currentPage = 1" :disabled="currentPage === 1">First</button>

        <button @click="prevPage" :disabled="currentPage === 1">Previous</button>
         </div>
        <!-- Make the display an editable input -->
         <div class="numbers-group flex" >
        <input type="number" v-model="currentPage" min="1" max="totalPages" class="text-center" style="width: 80px;">
        <span class="m-auto"> of {{ totalPages }}</span>
          </div>
          <div class="button-group flex" >
        <button @click="nextPage" :disabled="currentPage === totalPages || grants.length === 0">Next</button>
        <!-- button to go to the last page -->
        <button @click="currentPage = totalPages" :disabled="currentPage === totalPages">Last</button>
          </div>
      </div>
    </div>

    <!-- If no grants available, show a message -->
    <div v-else>
      <p>No grants found. Results may still be loading.</p>
    </div>
  </div>
</template>

<script>
import JsonTree from '@/Components/JsonTree.vue';

export default {
  name: 'GrantList',
  components: {
    JsonTree,
  },
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
      expandedGrants: [],
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
    toggleExpand(grantId) {
      const index = this.expandedGrants.indexOf(grantId);
      if (index > -1) {
        // Grant is already expanded; collapse it
        this.expandedGrants.splice(index, 1);
      } else {
        // Grant is collapsed; expand it
        this.expandedGrants.push(grantId);
      }
    },
    isExpanded(grantId) {
      return this.expandedGrants.includes(grantId);
    },
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
        //scroll to top of grant list
        this.scrollToTop();
      }
    },
    nextPage() {
      if (this.currentPage < this.totalPages) {
        this.currentPage++;
        //scroll to top of grant list
        this.scrollToTop();
      } 
    },
    scrollToTop() {
      this.$refs.grantList.scrollIntoView({ behavior: 'smooth' });
    },
    formatDate(value) {
      if (!value) return '';
      const date = new Date(value);
      //dates liek Sept 4 1999
      return date.toUTCString().slice(0, 16);
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
    },
    // Method to convert funding instrument code to description
    getFundingInstrument(code) {
      const fundingInstrumentMap = {
        'G': 'Grant',
        'CA': 'Cooperative Agreement',
        'O': 'Other',
        'PC': 'Procurement Contract',
      };
      return fundingInstrumentMap[code] || 'N/A';
    },
    // Method to convert eligibility code to description
    getEligibilityDescription(code) {
      const eligibilityMap = {
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
      };
      return eligibilityMap[code] || 'N/A';
    },
    // Method to convert opportunity category to description
    getOpportunityCategory(code) {
      const opportunityCategoryMap = {
        'D': 'Discretionary',
        'M': 'Mandatory',
        'C': 'Continuation',
        'E': 'Earmark',
        'O': 'Other'
      };
      return opportunityCategoryMap[code] || 'N/A';
    },
    // Method to convert funding activity category to description
    getFundingActivity(code) {
      const fundingActivityMap = {
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
        'O': 'Other'
      };
      return fundingActivityMap[code] || 'N/A';
    },
    stripHTML(html) {
      const doc = new DOMParser().parseFromString(html, 'text/html');
      return doc.body.textContent || '';
    },
    warnNavigation() {
      if (!confirm('Navigating to Login/Register will clear your search and AI Chat. Continue?')) {
        event.preventDefault();
      }
    },
  }
};
</script>

<style scoped>
.grant-item {
  margin-bottom: 5%;
  padding: 2%;
  border-bottom: 1px solid #ddd;
  border-radius: 5px;
}


.pagination button:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}

/* General container styling */
.grants-gov-search-container {
  background-color: #f4f1eb; /* Light beige background */
}

.pagination input[type="number"] {
  width: 3rem;
  height: 1cap;
  text-align: center;
  justify-content: center;
}

.pagination {
  display: flex;
  justify-content: space-between
}

.pagination div {
  display: flex;
  align-items: center;
  
}

/* Search button styling */
.pagination button {
  display: block;
  margin: 3% 3%;
  padding: 5px;
  font-size: 1rem; /* Responsive font size */
  color: black; /* White text */
  border: 2px solid black; /* Blue border */
  border-radius: 5px;
  cursor: pointer; 
  font-weight: 700;
}

.pagination button:hover {
  background-color: #0056b3; /* Darker blue on hover */
  color: white; /* White text on hover */
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


.pagination button:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}

.pagination span {
  font-size: 1.1rem;
  font-weight: bold;
  width: 3rem;
}

a {
    padding-left: .5rem; /* equivalent to px-5 */
    color: #08278d;
    text-decoration: underline;
}

/* make the pagination divs flex col on small screens */
@media (max-width: 640px) {
  .pagination .numbers-group {
    flex-direction: column;
  }
}

/* set pagination button width and height based on screen size */
@media (min-width: 640px) {
  .pagination button {
    width: 100px;
    height: 50px;
  }
}

@media (max-width: 640px) {
  .pagination button {
    width: 75px;
    height: 100px;
  }
}
</style>
