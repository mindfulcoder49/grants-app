<template>
  <!-- About Section Template -->
  <div class="about-container">
    <div class="about-header">Help Us Improve</div>
    <div class="about-section">
      <!-- Feedback Form -->
      <form class="feedback-form" @submit.prevent="submitFeedback" enctype="multipart/form-data">
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

        <div class="form-group">
          <label for="image">Screenshots of a problem can be very helpful! Please attach an Image (optional):</label>
          <input type="file" id="image" @change="handleFileUpload" />
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

// Initialize form data
const form = useForm({
  name: '',
  email: '',
  feedback: '',
  image: null // For the image file
});

const successMessage = ref('');
const errorMessage = ref('');

// Handle file selection for image upload
const handleFileUpload = (event) => {
  const file = event.target.files[0];
  form.image = file; // Attach the selected image to the form data
};

const submitFeedback = () => {
  // Use FormData to allow file uploads
  form.post('/feedback', {
    forceFormData: true, // Necessary to handle file uploads in Inertia.js
    onSuccess: () => {
      successMessage.value = 'Thank you for your feedback!';
      form.reset(); // Clear form fields after successful submission
      // manually remove and readd the image input
      document.getElementById('image').value = '';
    },
    onError: () => {
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
