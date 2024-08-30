<template>
    <!-- Input template with placeholder -->
    <input type="text" placeholder="Enter a description of your company" v-model="companyDescription" />
  </template>
  
  <script>
  export default {
    name: 'SearchInput',
    data() {
      return { companyDescription: '' };
    },
  };
  </script>
  

  <style scoped>
    /* Scoped CSS */
    input {
      width: 100%;
      padding: 0.5rem;
      margin: 0.5rem 0;
      font-size: 1rem;
      border-radius: 0.25rem;
    }

    /* Responsive Input and Button Styles */
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

    </style>