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

    <!-- Additional info visible only if showMore is true -->
    <div v-show="showMore" class="more-info-content">
      <p><strong>Estimated Funding:</strong> {{ grant.synopsis?.estimatedFundingFormatted || 'N/A' }}</p>
      <p><strong>Agency Contact:</strong> {{ grant.synopsis?.agencyContactDesc || 'N/A' }}</p>
      <p><strong>Closing Date:</strong> {{ grant.synopsis?.responseDate || 'N/A' }}</p>
      <p><strong>Synopsis:</strong> {{ grant.synopsis?.synopsisDesc || 'N/A' }}</p>
      <p><strong>Applicant Eligibility:</strong> {{ grant.synopsis?.applicantEligibilityDesc || 'N/A' }}</p>
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
  