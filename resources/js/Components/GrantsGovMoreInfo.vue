<template>
  <div class="grant-more-info-container">
    <!-- Main info always visible -->
    <p><strong>Award Ceiling:</strong> {{ grant.synopsis?.awardCeilingFormatted || 'N/A' }}</p>

    <!-- Toggle for showing more info -->
    <button @click="showMore = !showMore" class="more-info-button mt-4 bg-black text-white p-2 rounded-lg cursor-pointer mr-2">
      {{ showMore ? 'Less Info' : 'More Info' }}
    </button>

    <!-- Add to AI Conversation Button -->
    <button @click="toggleGrant(grant)" class="add-to-ai-chatbot-button mt-4 bg-black text-white p-2 rounded-lg cursor-pointer">
      {{ isAdded(grant.id) ? 'Remove from AI Chatbot' : 'Add to AI Chatbot' }}
    </button>
    <p class="m-4">If you are logged in, clicking the "Add to AI Chatbot" button will also add this grant to your saved grants. 
      <a href="/login" @click="warnNavigation" class="text-blue-500 hover:underline">Login</a> to save grants.</p>

    <!-- Additional info visible only if showMore is true -->
    <div v-show="showMore" class="more-info-content">
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
  </div>
</template>

<script>
export default {
  name: "GrantsGovMoreInfo",
  props: {
    resultID: {
      required: true,
    },
    addedGrants: {
      type: Array,
      required: true
    }
  },
  data() {
    return {
      showMore: false,
      grant: {
        synopsis: {
          estimatedFundingFormatted: '',
          awardCeilingFormatted: '',
          applicantEligibilityDesc: '',
          agencyContactDesc: '',
          responseDate: '',
          synopsisDesc: '',
        },
      },
    };
  },
  async mounted() {
    await this.fetchGrantDetails();
  },
  methods: {
    async fetchGrantDetails() {
      const payload = `oppId=${encodeURIComponent(this.resultID)}`;

      try {
        const response = await fetch(
          "https://apply07.grants.gov/grantsws/rest/opportunity/details",
          {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
            },
            body: payload,
          }
        );

        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        this.grant = {
          ...data,
          synopsis: {
            ...data.synopsis,
            synopsisDesc: this.decodeAndStripHtml(data.synopsis.synopsisDesc),
            applicantEligibilityDesc: this.decodeAndStripHtml(data.synopsis.applicantEligibilityDesc),
          },
        };

      } catch (error) {
        console.error("Error fetching grant details:", error);
      }
    },
    toggleGrant(grant) {
      if (this.isAdded(grant.id)) {
        this.$emit('remove-from-ai-conversation', grant.id);
      } else {
        this.$emit('add-to-ai-conversation', grant);
      }
    },
    isAdded(grantId) {
      return this.addedGrants.includes(grantId);
    },
    decodeAndStripHtml(text) {
      const tempElement = document.createElement('div');
      tempElement.innerHTML = text;
      const decodedText = tempElement.textContent || tempElement.innerText || '';
      return decodedText.replace(/<[^>]*>?/gm, ''); // Remove HTML tags
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
  },
};
</script>


  
<style scoped>
  /* Updated CSS for GrantsGovMoreInfo to match provided design */

/* General container styling */
.grant-more-info-container {
  margin-top: 10px;
  padding: 2%;
  background-color: #f4f1eb; /* Light beige background to match the site's theme */
  border-bottom: 1px solid #ddd;
  border-radius: 5px;
}

/* Button styling */



/* More info content box styling */
.more-info-content {
  margin-top: 10px;
  background-color: #f9f9f9; /* Slightly different background for contrast */
  padding: 10px;
  border-radius: 5px;
  font-size: 1rem; /* Match base font size */
  color: #333; /* Dark gray text for readability */
}

.more-info-content p {
    margin: .5rem 0;
}

/* Grant title styling */
.grant-title {
  font-size: 1.5rem; /* Larger for visual prominence */
  font-weight: bold;
  color: #000; /* Black text */
  margin-bottom: 0.5rem; /* Consistent spacing */
}

/* Grant number and agency name styling */
.grant-number,
.agency-name {
  font-size: 1rem; /* Match body text size */
  color: #333; /* Dark gray text */
  margin-top: 5px;
}

/* Grant item spacing and layout */
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



  </style>
  