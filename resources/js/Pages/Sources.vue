<template>
  <!-- About Section Template -->
  <div class="about-container">
    <div class="about-header">Sources</div>
    <div class="about-section">
      <p>
        <strong>Here is a list of sources for AI-focused grants.</strong> Please note that the search engine currently only provides results from
        <a href="https://www.grants.gov/">grants.gov</a>, which features federal grants. However, this list can be used to manually find grants for your project.
      </p>
      <p>
        <strong>If you have any sources you would like to add to this list, please use the feedback form.</strong> Your contributions not only help others, but also enhance the efficiency of the process.
      </p>
      <ul>
        <li v-for="source in sources" :key="source.name">
          <a :href="source.url" target="_blank">{{ source.name }}</a>
          <div v-if="getResultDetails(source.name)" class="source-details">
            <h4>{{ getResultDetails(source.name).title }}</h4>
            <p><strong>Description:</strong> {{ getResultDetails(source.name).description }}</p>
            <p><strong>Amount:</strong> {{ getResultDetails(source.name).amount }}</p>
            <p><strong>Deadline:</strong> {{ getResultDetails(source.name).deadline }}</p>
            <p><strong>Purpose:</strong> {{ getResultDetails(source.name).purpose }}</p>
            <p><strong>Eligibility:</strong> {{ getResultDetails(source.name).eligibility }}</p>
            <p><strong>Application Process:</strong> {{ getResultDetails(source.name).application_process }}</p>
            <p><strong>Contact:</strong> <a :href="'mailto:' + getResultDetails(source.name).contact">{{ getResultDetails(source.name).contact }}</a></p>
            <p><strong>Website:</strong> <a :href="getResultDetails(source.name).website" target="_blank">{{ getResultDetails(source.name).website }}</a></p>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Sources',
  data() {
    return {
      sources: [
        { name: 'Grants.gov', url: 'https://www.grants.gov/' },
        { name: 'NSF SBIR - AI Focused Grants', url: 'https://seedfund.nsf.gov/topics/artificial-intelligence/' },
        { name: 'Meta Llama 3.1 Impact Grants', url: 'https://llama.meta.com/llama-impact-grants/' },
        { name: 'Climate Change AI Innovation Grants 2024', url: 'https://www.climatechange.ai/grants' },
        { name: 'Cooperative AI Foundation Research Grants', url: 'https://www.cooperativeai.com/' },
        { name: 'MassTech Collaborative Grants', url: 'https://masstech.org/procurements-and-grants#grants' },
        { name: 'Acorn Innovation Grants', url: 'https://www.mass-ventures.com/mvcapital/acorn' },
      ],
      results: [
        {
          id: 1,
          title: 'NSF SBIR - AI Focused Grants',
          description: 'Funding for AI projects that aim to address national priorities, with a focus on commercialization potential.',
          amount: 'Up to $2 million',
          deadline: '2024-11-22',
          match: 95,
          purpose: 'To support small businesses engaging in scientific research and development that has the potential for commercialization.',
          eligibility: 'Small businesses with fewer than 500 employees, at least 50% owned by U.S. citizens or permanent residents.',
          application_process: 'Initial Project Pitch followed by Full Proposal if selected.',
          contact: 'nsf-sbir@nsf.gov',
          website: 'https://seedfund.nsf.gov/topics/artificial-intelligence/',
        },
        {
          id: 2,
          title: 'Meta Llama 3.1 Impact Grants',
          description: 'Grants for leveraging Llama 3.1 models to address community challenges.',
          amount: 'Up to $500,000',
          deadline: '2024-11-22',
          match: 85,
          purpose: 'To foster the use of Llama 3.1 models in impactful community projects across various sectors such as education, public services, and economic development.',
          eligibility: 'Non-profits, academic institutions, and businesses with innovative AI application ideas.',
          application_process: 'Submission of a detailed proposal through the Meta platform.',
          contact: 'LlamaImpactEvent@meta.com',
          website: 'https://llama.meta.com/llama-impact-grants/',
        },
        {
          id: 3,
          title: 'Climate Change AI Innovation Grants 2024',
          description: 'Funding for AI projects aimed at climate change mitigation and adaptation.',
          amount: 'Up to $150,000',
          deadline: '2024-09-15',
          match: 80,
          purpose: 'To support innovative AI projects that address climate change, particularly those that develop new datasets or computational models.',
          eligibility: 'Researchers affiliated with accredited universities in OECD Member Countries.',
          application_process: 'Proposals submitted via the CMT submission portal.',
          contact: 'grants@climatechange.ai',
          website: 'https://www.climatechange.ai/calls/innovation_grants_2024',
        },
        {
            id: 4,
            title: 'Cooperative AI Foundation Research Grants',
            description: 'The Cooperative AI Foundation is seeking proposals for research projects in Cooperative AI. This call is open throughout 2024 with quarterly deadlines.',
            amount: 'Minimum GBP 10,000; no fixed upper limit',
            deadline: '2024-10-06',
            match: 90,
            purpose: 'To support research that enhances the cooperative intelligence of advanced AI systems for societal benefit.',
            eligibility: 'Open to anyone; applications from outside computer science are welcomed.',
            application_process: 'Submit applications via SurveyMonkey Apply. Proposals should include a project plan and budget.',
            contact: 'info@cooperativeai.com',
            website: 'https://www.cooperativeai.com/'
        },
        {
            id: 5,
            title: 'Grants.gov',
            description: 'A comprehensive database for federal grant opportunities across various fields, including AI-focused projects.',
            amount: 'Varies by grant',
            deadline: 'Varies by grant',
            match: 95,
            purpose: 'To provide access to federal grants for individuals and organizations seeking funding for research and development projects.',
            eligibility: 'Eligibility varies by individual grant requirements.',
            application_process: 'Search and apply for grants directly through the Grants.gov portal. Follow the specific application guidelines for each grant.',
            contact: 'support@grants.gov',
            website: 'https://www.grants.gov/'
        },
        {
          id: 6,
          title: 'MassTech Collaborative Grants',
          description: 'Various grants for innovation, technology, broadband, and cyber-focused projects in Massachusetts.',
          amount: 'Varies by grant',
          deadline: 'Varies by grant',
          match: 85,
          purpose: 'To support a variety of research, scientific, technical, and management consulting services for Massachusetts projects.',
          eligibility: 'Open to qualified individuals, firms, and organizations.',
          application_process: 'Review current NOFOs and submit proposals via email as directed.',
          contact: 'proposals@masstech.org',
          website: 'https://masstech.org/procurements-and-grants#grants'
        },
        {
          id: 7,
          title: 'Acorn Innovation Grants',
          description: 'Grants supporting Principal Investigators at Massachusetts research institutions for developing and validating early-stage technologies.',
          amount: '$32,500 per award (up to 10 awards)',
          deadline: '2024-10-11',
          match: 90,
          purpose: 'To fund small projects that demonstrate the viability of early-stage technologies, helping researchers secure further funding for commercialization.',
          eligibility: 'Principal Investigators at Massachusetts research institutions (universities and medical centers) with disclosed technology.',
          application_process: 'Submit applications via the MassVentures portal after reviewing the RFP.',
          contact: 'info@massventures.com',
          website: 'https://www.mass-ventures.com/mvcapital/acorn'
        }
      ],
    };
  },
  methods: {
    getResultDetails(sourceName) {
      return this.results.find(result => result.title === sourceName) || null;
    },
  },
};
</script>

<style scoped>
a {
  color: #0070f3;
  text-decoration: none;
}

a:hover {
  text-decoration: underline;
}

.source-details {
  margin-top: 10px;
  background-color: #f9f9f9;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 5px;
  margin-left: 20px;
}
</style>
