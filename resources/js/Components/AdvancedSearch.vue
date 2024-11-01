<template>
    <div class="advanced-search flex-col items-center justify-center pt-6 w-full">
        <div class="search-interface" >
      <h2 class="text-2xl font-semibold mb-4">Advanced Search Limiters</h2>
      <p class="text-justify text-gray-600">Add fields to refine your vector search by filtering the grants based on 
        specific keywords or eligibility criteria.</p>
      </div>
      
      <div v-for="(search, index) in fields" :key="index" class="search-row flex items-center w-full">

        <div class="flex items-center space-x-4 w-full">
                <!-- Remove Button -->
        <button @click="removeField(index)" class="text-white hover:text-red-700 transition w-auto border rounded px-4 p-2 bg-black font-black">
          X
        </button>
        <!-- Select for Field Name -->
        <select v-model="search.field" class="flex-grow border rounded">
          <option v-for="field in availableFields" :key="field" :value="field">
            {{ formatFieldName(field) }}
          </option>
        </select>
    </div>
        
        <!-- Show Applicant Type Dropdown if Field is 'eligible_applicants', otherwise show Keyword Input -->
        <div v-if="search.field === 'eligible_applicants'" class=" border rounded w-full">
          <select v-model="search.value" class="w-full">
            <option v-for="(label, code) in applicantTypes" :key="code" :value="code">
              {{ code }} - {{ label }}
            </option>
          </select>
        </div>

        <!-- Show Funding Activity Type Dropdown if Field is 'funding_activity_type', otherwise show Keyword Input -->
        <div v-else-if="search.field === 'category_of_funding_activity'" class="border
        rounded w-full">
          <select v-model="search.value" class="w-full">
            <option v-for="(label, code) in fundingActivityTypes" :key="code" :value="code">
              {{ code }} - {{ label }}
            </option>
          </select>
        </div>
        <!-- use a date input field for post_date and close_date -->
        <div v-else-if="search.field === 'post_date' || search.field === 'close_date'" class=" border
        rounded w-full">
          <input type="date" v-model="search.value" class="w-full" />
        </div>
        
        <div v-else class="w-full border rounded">
          <input v-model="search.value" placeholder="Enter keyword" class="w-full" />
        </div>
        

      </div>
      <div class="search-interface flex align-left w-full">
      <button 
        v-if="fields.length < 10" 
        @click="addField" 
        class="px-4 py-2 bg-black text-white rounded hover:bg-gray-800"
      >
        + Add Limiter
      </button>
    </div>
    </div>
  </template>
  
  <script>
  export default {
    props: {
      initialFields: {
        type: Array,
        default: () => [{ field: '', value: '' }]
      },
    },
    data() {
      return {
        fields: [...this.initialFields],
        availableFields: [],
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
        }
      };
    },
    methods: {
      addField() {
        if (this.fields.length < 10) {
          this.fields.push({ field: '', value: '' });
        }
      },
      removeField(index) {
        this.fields.splice(index, 1);
      },
      async fetchSearchFields() {
        const response = await fetch('/api/search-fields');
        this.availableFields = await response.json();
      },
      getFields() {
        // Strip out null fields
        this.fields = this.fields.filter(field => field.field !== '' && field.value !== '');
        return this.fields; // Provide a way for the parent to get fields
      },
      formatFieldName(field) {
        // Replace underscores with spaces and capitalize each word
        return field.replace(/_/g, ' ').replace(/\b\w/g, (char) => char.toUpperCase());
      }
    },
    mounted() {
      this.fetchSearchFields();
    },
  };
  </script>
  
  <style scoped>
  /* Adjust for mobile responsiveness */
  @media (max-width: 640px) {
    .search-row {
      flex-direction: column;
      margin-bottom: 2rem;
    }
  }

  .search-row {
    flex-direction: column;
    margin: 1rem auto;
    border: 5px solid white;
    padding: 0 .25rem;
    border-radius: 5px;
  }

  .search-row div {
    margin: 0.5rem 0;
  }

  .search-interface {
  width: 100%;
  padding: 1rem 0;
  border-radius: 1rem;
  background-color: #f4f1eb;
} 
  </style>
  