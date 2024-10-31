<template>
    <div class="advanced-search container mx-auto flex flex-col items-center justify-center p-6">
      <h2 class="text-2xl font-semibold mb-4 text-center">Advanced Search Limiters</h2>
      <p class="text-center text-gray-600 mb-4">Add fields to refine your vector search by filtering the grants based on 
        specific keywords or eligibility criteria.</p>
      
      <div v-for="(search, index) in fields" :key="index" class="search-row flex items-center space-x-4 w-full">

        <div class="flex items-center space-x-4 w-full">
                <!-- Remove Button -->
        <button @click="removeField(index)" class="text-red-900 hover:text-red-700 transition w-auto border rounded px-4 p-2 bg-red-300">
          X
        </button>
        <!-- Select for Field Name -->
        <select v-model="search.field" class="flex-grow p-2 border rounded">
          <option v-for="field in availableFields" :key="field" :value="field">
            {{ formatFieldName(field) }}
          </option>
        </select>
    </div>
        
        <!-- Show Applicant Type Dropdown if Field is 'eligible_applicants', otherwise show Keyword Input -->
        <div v-if="search.field === 'eligible_applicants'" class="p-2 border rounded w-full">
          <select v-model="search.value" class="w-full">
            <option v-for="(label, code) in applicantTypes" :key="code" :value="code">
              {{ code }} - {{ label }}
            </option>
          </select>
        </div>

        <!-- Show Funding Activity Type Dropdown if Field is 'funding_activity_type', otherwise show Keyword Input -->
        <div v-else-if="search.field === 'category_of_funding_activity'" class="p-2 border
        rounded w-full">
          <select v-model="search.value" class="w-full">
            <option v-for="(label, code) in fundingActivityTypes" :key="code" :value="code">
              {{ code }} - {{ label }}
            </option>
          </select>
        </div>
        
        <div v-else class="w-full p-2 border rounded">
          <input v-model="search.value" placeholder="Enter keyword" class="w-full" />
        </div>
        

      </div>
      <button 
        v-if="fields.length < 10" 
        @click="addField" 
        class="mb-4 px-4 py-2 bg-black text-white rounded hover:bg-blue-600 transition"
      >
        + Add Field
      </button>
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
  </style>
  