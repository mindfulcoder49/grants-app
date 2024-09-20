<template>
  <!-- About Section Template -->
  <div class="about-container">
    <div class="about-header">Help Us Improve</div>
    <div class="about-section">
      <!-- Feedback Form -->
      <form class="feedback-form" @submit.prevent="submitFeedback">
        <div class="form-group">
          <label for="name">Name:</label>
          <input type="text" id="name" v-model="form.name" required />
        </div>

        <div class="form-group">
          <label for="email">Email:</label>
          <input type="email" id="email" v-model="form.email" required />
        </div>

        <div class="form-group">
          <label for="feedback">Feedback:</label>
          <textarea id="feedback" v-model="form.feedback" rows="5" required></textarea>
        </div>

        <button type="submit" class="submit-btn" :disabled="form.processing">Submit</button>
      </form>
      
      <div v-if="successMessage" class="success-message">{{ successMessage }}</div>
      <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({
  name: '',
  email: '',
  feedback: ''
});

const successMessage = ref('');
const errorMessage = ref('');

const submitFeedback = () => {
  form.post('/feedback', {
    onSuccess: () => {
      successMessage.value = 'Thank you for your feedback!';
      form.reset(); // Clear form fields after successful submission
    },
    onError: (errors) => {
      errorMessage.value = 'There was an issue submitting your feedback. Please try again.';
    }
  });
};
</script>

<style scoped>
/* Optional: Add some basic styles for the form */
.feedback-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.submit-btn {
  padding: 0.5rem 1rem;
  background-color: #007bff;
  color: white;
  border: none;
  cursor: pointer;
  border-radius: 5px;
}

.success-message {
  color: green;
  margin-top: 1rem;
}

.error-message {
  color: red;
  margin-top: 1rem;
}

/* inputs have rounded corners */
input[type="text"],
input[type="email"],
textarea {
  border-radius: 5px;
  padding: 0.5rem;
  border: 1px solid #ccc;
}
</style>
