<template>
    <!-- Button template -->
    <button @click="searchGrants">{{ buttonText }}</button>
  </template>
  
  <script>
  export default {
    name: 'SearchButton',
    props: {
      companyDescription: String, // Accept the description as a prop from parent
      buttonText: {
        type: String,
        default: 'SEARCH FOR GRANTS', // Default button text
      },
    },
    methods: {
      searchGrants() {
        // Emit search event with data directly from prop
        this.$emit('search', this.companyDescription);
      },
    },
  };
  </script>
  
  <style scoped>

button {
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

button:hover {
  background-color: #0056b3; /* Darker blue on hover */
}
</style>