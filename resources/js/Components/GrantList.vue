<template>
  <!-- Grant List Component -->
  <div ref="grantList">
    <!-- Pagination controls -->
    <div v-if="paginatedGrants.length > 0">
      <div class="pagination">
        <!-- button to go to the first page -->
        <button @click="currentPage = 1" :disabled="currentPage === 1">First</button>

        <button @click="prevPage" :disabled="currentPage === 1">Previous</button>
        <!-- Make the display an editable input -->
        <input type="number" v-model="currentPage" min="1" max="totalPages" class="text-center" style="width: 80px;">
        <span class="mx-4"> of {{ totalPages }}</span>
        <button @click="nextPage" :disabled="currentPage === totalPages || grants.length === 0">Next</button>
        <!-- button to go to the last page -->
        <button @click="currentPage = totalPages" :disabled="currentPage === totalPages">Last</button>
      </div>
      
      <div v-for="grant, index in paginatedGrants" :key="grant.id" class="grant-item border-b border-gray-300 pb-4 mb-4">


        <div>
          <h3 class="text-xl font-bold mb-2">#{{ index + (currentPage - 1) * pageSize + 1 }}</h3>
        </div>
        <div v-if="grant.opportunityTitle" class="p-4 bg-white shadow rounded-lg">
          <!--
          <div class="bg-black text-white">
            <JsonTree :json="grant" />
          </div> -->

          


          <!-- Match Score (if available in API data) -->
          <h3 v-if="grant.similarity" class="text-xl font-semibold text-transparent bg-clip-text bg-gradient-to-r from-blue-800 via-green-700 drop-shadow-md">
            Match Score: {{ formatSimilarity(grant.similarity) }}
          </h3>

          <h3 class="text-xl font-bold mb-2">
            {{ grant.opportunityTitle }} - Ceiling: {{ formatCurrency(grant.awardCeiling) }}
          </h3>
          <p class="text-sm text-gray-700 mb-2">{{ grant.synopsis.synopsisDesc }}</p>

          <div class="grid md:grid-cols-2 gap-4 text-sm">
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
          </div>
        </div>





        <div v-else-if="grant.opportunity_title"> <!-- Native Grant -->
          <h3 class="text-xl font-semibold text-transparent bg-clip-text bg-gradient-to-r from-blue-800 via-green-700 drop-shadow-md">
          Match Score: {{ formatSimilarity(grant.similarity) }}
        </h3>
          <h3 class="text-xl font-bold mb-2">{{ grant.opportunity_title }} - Ceiling: {{ formatCurrency(grant.award_ceiling) }}</h3>
          <p class="text-sm text-gray-700 mb-2">{{ grant.description }}</p>
          <div class="grid md:grid-cols-2 gap-4 text-sm">
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
              <strong>Eligibility:</strong> {{ getEligibilityDescription(grant.eligible_applicants) }}<br>
              <strong>Additional Eligibility Information:</strong> {{ stripHTML(grant.additional_information_on_eligibility) }}<br>
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
        </div>

        <!-- Add to AI Conversation Button -->
        <button
          @click="toggleGrant(grant)"
          class="mt-4 bg-black text-white p-2 rounded-lg cursor-pointer"
        >
          {{ isAdded(grant.id) ? 'Remove from AI Chatbot' : 'Add to AI Chatbot' }}
        </button>

        <p class="m-4">If you are logged in, clicking the "Add to AI Chatbot" button will also add this grant to your saved grants. 
          <a href="/login" @click="warnNavigation" class="text-blue-500 hover:underline">Login</a> to save grants.</p>
      </div>

      <div class="pagination">
        <button @click="currentPage = 1" :disabled="currentPage === 1">First</button>
        <button @click="prevPage" :disabled="currentPage === 1">Previous</button>
        <span>Page {{ currentPage }} of {{ totalPages }}</span>
        <button @click="nextPage" :disabled="currentPage === totalPages || grants.length === 0">Next</button>
        <button @click="currentPage = totalPages" :disabled="currentPage === totalPages">Last</button>
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
      return date.toLocaleDateString( 'en-US', { month: 'short', day: 'numeric', year: 'numeric' });
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
