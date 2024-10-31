<template>
  <!-- Textarea with auto-grow functionality -->
  <textarea
    placeholder="Enter a description of your company"
    v-model="companyDescription"
    @input="adjustHeight"
    ref="inputField"
  />
</template>

<script>
export default {
  name: 'SearchInput',
  data() {
    return {
      companyDescription: '',
    };
  },
  methods: {
    adjustHeight() {
      // Reset height to auto, then set height based on scrollHeight
      this.$refs.inputField.style.height = "auto";
      this.$refs.inputField.style.height = `${this.$refs.inputField.scrollHeight}px`;
    },
  },
};
</script>

<style scoped>
textarea {
  display: flex;
  width: 100%;
  padding: 0.5rem;
  margin: 0.5rem 0;
  font-size: 1.5rem;
  border-radius: 10px;
  border: 1px solid #c000;
  text-align: center;
  justify-content: center ;
  color: #111;
  display: block;
  resize: none; /* Disable manual resizing */
  overflow: hidden; /* Hide scrollbars */
  line-height: 1.5cap;
  padding: 10px; /* Adjust as necessary */
  line-height: 1.5; /* Adjust the line height */
}

textarea:focus {
  outline: none; /* Remove default focus styling */
}

/* Placeholder styling */
textarea::placeholder {
  color: #50473e;
  font-style: italic;
  padding-top: 1rem;
  font-weight: 600;
}
</style>
