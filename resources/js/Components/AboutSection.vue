<template>
    <!-- About Section Template -->
     <div class="about-container">
        <div class="about-header">About</div>
    <div class="about-section">
      <p><strong>This innovative mini-project</strong> that aims to bridge the gap between Massachusetts-based teams with potential grant opportunities they may not be aware of. The grant funding landscape presents a paradoxical blend of transparency and opacity. While information regarding grants is readily accessible and publicly disseminated, it is often challenging to identify grant opportunities. The allocation of grants frequently favors researchers who possess insider knowledge, creating an uneven playing field.
        </p><p>
          <strong>Recognizing the challenges</strong> in navigating the vast landscape of grant resources, the presents a minimum viable product (MVP) in the form of a vector search engine based on grants.gov. This search engine is designed to provide more relevant and tailored results, enabling users to discover suitable grant opportunities with greater ease and efficiency. Their project holds significant promise in empowering organizations and teams to access critical funding and support for their initiatives.
</p>
      <h2>People</h2>
      <ul>
        <h3 class="text-lg font-semibold my-2">
        Creators</h3>
        <li v-for="person in people">
          <a class="text-blue-600 flex" 
          :href="person.linkedInURL">{{ person.name }} 
          <svg class="ml-1 bg-blue-700 text-white rounded-sm" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
            <path fill-rule="evenodd" d="M12.51 8.796v1.697a3.738 3.738 0 0 1 3.288-1.684c3.455 0 4.202 2.16 4.202 4.97V19.5h-3.2v-5.072c0-1.21-.244-2.766-2.128-2.766-1.827 0-2.139 1.317-2.139 2.676V19.5h-3.19V8.796h3.168ZM7.2 6.106a1.61 1.61 0 0 1-.988 1.483 1.595 1.595 0 0 1-1.743-.348A1.607 1.607 0 0 1 5.6 4.5a1.601 1.601 0 0 1 1.6 1.606Z" clip-rule="evenodd"/>
            <path d="M7.2 8.809H4V19.5h3.2V8.809Z"/>
          </svg>
</a>
        </li>
        <h3 class="text-lg font-semibold my-2">Contributors</h3>
        <li v-for="contributor in contributors">{{ contributor.name }}</li>
      </ul>
      <h2>Technology</h2>
      <ul><li v-for="technology in technologies">{{ technology.name }}</li></ul>
      <h2>AI Blueprint for MA</h2>
      <p>The goal of AI Blueprint for MA is to coordinate and implement practical efforts to attract, retain and grow AI talent for MA.  We are "doers"; i.e. our volunteer teams of 2-3 people "pick up a shovel" and execute a small task each month. This effort supports that State of MA AI Task Force and is a volunteer organization. </p>
    </div>
    </div>
  </template>
  
  <script>
  /* contributors:
  Catherine Havasi
John J. Stamatopolous
Julie Chavez
Pam Boiros
Ram Katamaraja
Sharna Sattiraju
Thomas Schlapp
Walter Rivera
*/
  export default {
    name: 'AboutSection',
    data() {
      return {
        people: [{ name: 'Salil Darji', linkedInURL: 'https://www.linkedin.com/in/mindfulcoder49/' }, { name: 'Alex Alcivar', linkedInURL: 'https://www.linkedin.com/in/saldarji/' }],
        contributors: [{ name: 'Catherine Havasi' }, { name: 'John J. Stamatopolous' }, { name: 'Julie Chavez' }, { name: 'Pam Boiros' }, { name: 'Ram Katamaraja' }, { name: 'Sharna Sattiraju' }, { name: 'Thomas Schlapp' }, { name: 'Walter Rivera' }],
        technologies: [{ name: 'GPT-4o-mini' }, { name: 'OpenAI small embeddings'},  { name: 'Vue.js' }, { name: 'Inertia' }, { name: 'Laravel Breeze' }],
      };
    },
  };
  </script>
  