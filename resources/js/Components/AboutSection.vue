<template>
    <!-- About Section Template -->
     <div class="about-container">
        <div class="about-header">About</div>
    <div class="about-section">
      <h2>People</h2>
      <ul><li v-for="person in people">{{ person.name }}</li></ul>
      <h2>Technology</h2>
      <ul><li v-for="technology in technologies">{{ technology.name }}</li></ul>
      <h2>AI Blueprint for MA</h2>
      <p>The goal of AI Blueprint for MA is to coordinate and implement practical efforts to attract, retain and grow AI talent for MA.  We are "doers"; i.e. our volunteer teams of 2-3 people "pick up a shovel" and execute a small task each month. This effort supports that State of MA AI Task Force and is a volunteer organization. </p>
    </div>
    </div>
  </template>
  
  <script>
  export default {
    name: 'AboutSection',
    data() {
      return {
        people: [{ name: 'Salil Darji' }, { name: 'Alex Alcivar' }],
        technologies: [{ name: 'ChatGPT' }, { name: 'Vue.js' }, { name: 'Inertia' }, { name: 'Laravel Breeze' }],
      };
    },
  };
  </script>
  