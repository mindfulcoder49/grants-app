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
  width: 80%;
  max-width: 500px;
  padding: 0.5rem;
  margin: 0.5rem auto;
  font-size: 1.5rem;
  border-radius: 1rem;
  border: 1px solid #c000;
  text-align: center;
  color: #111;
  display: block;
  resize: none; /* Disable manual resizing */
  overflow: hidden; /* Hide scrollbars */
  line-height: 1.5cap;
}

/* Placeholder styling */
textarea::placeholder {
  color: #76685b;
  font-style: italic;
}
</style>
