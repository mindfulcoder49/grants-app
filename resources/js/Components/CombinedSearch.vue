<template>
    <!-- Main Container -->
    <div class="combined-search flex flex-col items-center">
      <!-- Textarea with auto-grow functionality -->
      <textarea
        placeholder="Enter a description of your company"
        v-model="companyDescription"
        @input="adjustHeight"
        ref="inputField"
      />
      
      <div class="flex flex-col md:flex-row w-full sm:w-[70%] m-auto"> 
  
      <!-- Advanced Search Limiters -->
      <div class="advanced-search flex-col items-center pt-6 pr-10 w-full">
        <div class="search-interface">
          <h2 class="text-2xl font-semibold mb-4">Advanced Search Limiters</h2>
          <p class="text-justify text-gray-600">
            Add fields to filter the grants based on specific keywords or
            eligibility criteria.
          </p>
        </div>
  
        
  
        <div class="search-interface flex align-left w-full">
          <button
            v-if="fields.length < 10"
            @click="addField"
            class="px-3 py-2 bg-transparent text-black border border-black border-2 rounded-md hover:bg-gray-800 hover:text-white"
          >
            +Add Limiter&nbsp;
          </button>
        </div>
      </div>
  
      <!-- Search Options and Button -->
      <div class="search-options pt-8">
        <!-- Include All Toggle Switch -->
        <div class="switch-container">
          <!-- Toggle Switch -->
          <label class="switch">
            <input type="checkbox" v-model="include_all" />
            <span class="slider round"></span>
          </label>
  
          <!-- Toggle Text -->
          <div class="toggle-text">
            <span class="main-text">{{ include_all ? 'All Grants' : 'Open Only' }}</span>
            <span class="slider-text-extra">
              {{ include_all
                ? ': including closed opportunities'
                : ': Searching grants accepting applications' }}
            </span>
          </div>
        </div>
  
        <!-- Historical Grant Search Notice -->
        <div class="flex text-md text-justify">
          Historical grant search is limited to the past two years. If you need more, let us know with
          the Help Us Improve button.
        </div>
      </div>
    </div>
      <div
          v-for="(search, index) in fields"
          :key="index"
          class="search-row flex items-center w-full sm:w-[70%] m-auto"
        >
          <div class="flex items-center space-x-4 w-full">
            <!-- Remove Button -->
            <button
              @click="removeField(index)"
              class="bg-transparent text-black border border-black border-2 rounded-md hover:bg-gray-800 hover:text-white px-4 p-2 font-black"
            >
              X
            </button>
            <!-- Select for Field Name -->
            <select v-model="search.field" class="flex-grow border rounded">
              <option v-for="field in availableFields" :key="field" :value="field">
                {{ formatFieldName(field) }}
              </option>
            </select>
          </div>
  
          <!-- Field Value Input -->
          <div v-if="search.field === 'eligible_applicants'" class="border rounded w-full">
            <select v-model="search.value" class="w-full">
              <option v-for="(label, code) in applicantTypes" :key="code" :value="code">
                {{ code }} - {{ label }}
              </option>
            </select>
          </div>
  
          <div
            v-else-if="search.field === 'category_of_funding_activity'"
            class="border rounded w-full"
          >
            <select v-model="search.value" class="w-full">
              <option v-for="(label, code) in fundingActivityTypes" :key="code" :value="code">
                {{ code }} - {{ label }}
              </option>
            </select>
          </div>
  
          <div
            v-else-if="search.field === 'post_date' || search.field === 'close_date'"
            class="border rounded w-full"
          >
            <input type="date" v-model="search.value" class="w-full" />
          </div>
  
          <div v-else class="w-full border rounded">
            <input v-model="search.value" placeholder="Enter keyword" class="w-full" />
          </div>
        </div>
      <!-- Search Button -->
      <div class="w-full flex flex-col items-center">
        <button class="searchButton" @click="searchGrants">{{ buttonText }}</button>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    name: 'CombinedSearch',
    emits: ['search'], // Emit search event to parent
    props: {
      buttonText: {
        type: String,
        default: 'SEARCH GRANTS.GOV', // Default button text
      },
    },
    data() {
      return {
        // From SearchInput
        search_type: 'centroid', // Default search type
        top_centroids: 200, // Default top centroids
        hamming_mode: 'cosine', // Default hamming mode
        centroid_async: true, // Default centroid async
        companyDescription: '',
  
        // From SearchButton
        include_all: false, // Default include all
  
        // From AdvancedSearch
        fields: [],
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
          '25': 'Others',
        },
        fundingActivityTypes: {
          ACA: 'Affordable Care Act',
          AG: 'Agriculture',
          AR: 'Arts',
          BC: 'Business and Commerce',
          CD: 'Community Development',
          CP: 'Consumer Protection',
          DPR: 'Disaster Prevention and Relief',
          ED: 'Education',
          ELT: 'Employment, Labor and Training',
          EN: 'Energy',
          ENV: 'Environment',
          FN: 'Food and Nutrition',
          HL: 'Health',
          HO: 'Housing',
          HU: 'Humanities',
          ISS: 'Income Security and Social Services',
          IS: 'Information and Statistics',
          LJL: 'Law, Justice and Legal Services',
          NR: 'Natural Resources',
          RA: 'Recovery Act',
          RD: 'Regional Development',
          ST: 'Science and Technology',
          T: 'Transportation',
          O: 'Other',
        },
      };
    },
    methods: {
      // From SearchInput
      adjustHeight() {
        // Reset height to auto, then set height based on scrollHeight
        this.$refs.inputField.style.height = '5rem';
        this.$refs.inputField.style.height = `${this.$refs.inputField.scrollHeight}px`;
      },
  
      // From AdvancedSearch
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
        return this.fields.filter((field) => field.field !== '' && field.value !== '');
      },
      formatFieldName(field) {
        // Replace underscores with spaces and capitalize each word
        return field.replace(/_/g, ' ').replace(/\b\w/g, (char) => char.toUpperCase());
      },
  
      // From SearchButton
      searchGrants() {
        const searchPayload = {
          description: this.companyDescription,
          open_only: !this.include_all,
          search_type: this.search_type,
          top_centroids: this.top_centroids,
          hamming_mode: this.hamming_mode,
          centroid_async: this.centroid_async,
        };
  
        const advancedFields = this.getFields();
  
        if (advancedFields.length > 0) {
          searchPayload.advancedFields = advancedFields;
        }
  
        // Emit search event with data
        this.$emit('search', searchPayload);
      },
    },
    mounted() {
      this.fetchSearchFields();
    },
  };
  </script>
  
  <style scoped>
  /* Combined styles from all three components */
  
  /* Textarea Styles */
  textarea {
    display: block;
    width: 70%;
    padding: 0.5rem;
    margin: 0.5rem auto;
    font-size: 2rem;
    border-radius: 50px;
    border: 1px solid #c000;
    text-align: center;
    color: #111;
    resize: none;
    overflow: hidden;
    line-height: 1.5;
    height: 5rem;
  }
  
  textarea:focus {
    outline: none;
  }
  
  textarea::placeholder {
    color: #50473e;
    font-style: italic;
    padding-top: 0.1rem;
    font-weight: 600;
    font-size: 2rem;
  }
  
  @media (max-width: 640px) {
    textarea {
      font-size: 1.5rem;
      margin: 0.25rem 0;
      width: 100%;
      height: 3rem;
    }
    textarea::placeholder {
      font-size: 1.5rem;
    }
  }
  
  /* Switch Container */
  .switch-container {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
    width: 100%;
  }
  
  /* Switch (Toggle) */
  .switch {
    position: relative;
    display: inline-block;
    width: 52px;
    height: 28px;
    flex-shrink: 0;
  }
  
  .switch input {
    opacity: 0;
    width: 0;
    height: 0;
  }
  
  .slider {
    position: absolute;
    cursor: pointer;
    background-color: white;
    border-radius: 28px;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    transition: 0.4s;
  }
  
  .slider:before {
    position: absolute;
    content: '';
    height: 24px;
    width: 24px;
    left: 2px;
    bottom: 2px;
    background-color: #004aad;
    border-radius: 50%;
    transition: 0.4s;
  }
  
  input:checked + .slider {
    background-color: white;
  }
  
  input:checked + .slider:before {
    transform: translateX(24px);
  }
  
  .slider.round {
    border-radius: 28px;
  }
  
  .slider.round:before {
    border-radius: 50%;
  }
  
  /* Toggle Text */
  .toggle-text {
    margin-left: 1rem;
    display: flex;
  }
  
  .main-text {
    font-weight: bold;
    min-width: 80px;
    white-space: nowrap;
    font-size: 1.2rem;
  }
  
  .slider-text-extra {
    font-weight: bold;
    min-width: 250px;
    white-space: nowrap;
    font-size: 1.2rem;
  }
  
  @media (max-width: 1024px) {
    .slider-text-extra {
      display: none;
    }
  }
  
  /* Button Styles */
  button.searchButton {
    display: block;
    margin: 3% 0;
    padding: 15px 0;
    font-size: 1rem;
    color: #fff;
    background-color: #004aad;
    border: none;
    border-radius: 2px;
    cursor: pointer;
    width: 200px;
    font-weight: 700;
  }
  
  button:hover {
    background-color: #0056b3;
  }
  
  /* Advanced Search Styles */

  .advanced-search {
    min-width: 250px;
  }
  .search-row {
    flex-direction: column;
    margin: 1rem auto;
    border: 5px solid white;
    padding: 0 0.25rem;
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
  
  @media (max-width: 640px) {
    .search-row {
      flex-direction: column;
      margin-bottom: 2rem;
    }
  }
  </style>
  