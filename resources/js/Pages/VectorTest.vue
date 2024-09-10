<template>
    <div class="vector-test">
      <h2>Vector Test Suite</h2>
  
      <!-- Embed Text -->
      <div>
        <h3>Embed Text</h3>
        <select v-model="selectedText">
          <option v-for="text in testTexts" :key="text" :value="text">{{ text }}</option>
        </select>
        <button @click="embedText">Embed Text</button>
        <p>{{ embedResponse }}</p>
      </div>
  
      <!-- Insert Vector -->
      <div>
        <h3>Insert Vector</h3>
        <select v-model="selectedInsertVector">
          <option v-for="(vector, index) in embeddings" :key="index" :value="vector">
            {{ truncateVector(vector) }}
          </option>
        </select>
        <button @click="insertVector">Insert Vector</button>
        <p>{{ insertResponse }}</p>
      </div>
  
      <!-- Update Vector -->
      <div>
        <h3>Update Vector</h3>
        <input v-model="updateId" placeholder="Enter vector ID" />
        <select v-model="selectedUpdateVector">
          <option v-for="(vector, index) in embeddings" :key="index" :value="vector">
            {{ truncateVector(vector) }}
          </option>
        </select>
        <button @click="updateVector">Update Vector</button>
        <p>{{ updateResponse }}</p>
      </div>
  
      <!-- Delete Vector -->
      <div>
        <h3>Delete Vector</h3>
        <input v-model="deleteId" placeholder="Enter vector ID" />
        <button @click="deleteVector">Delete Vector</button>
        <p>{{ deleteResponse }}</p>
      </div>
  
      <!-- Cosine Similarity -->
      <div>
        <h3>Calculate Cosine Similarity</h3>
        <select v-model="selectedVector1">
          <option v-for="(vector, index) in embeddings" :key="index" :value="vector">
            {{ truncateVector(vector) }}
          </option>
        </select>
        <select v-model="selectedVector2">
          <option v-for="(vector, index) in embeddings" :key="index" :value="vector">
            {{ truncateVector(vector) }}
          </option>
        </select>
        <button @click="calculateCosineSimilarity">Calculate Cosine Similarity</button>
        <p>{{ cosineResponse }}</p>
      </div>
  
      <!-- Search for Similar Vectors -->
      <div>
        <h3>Search for Similar Vectors</h3>
        <select v-model="selectedSearchVector">
          <option v-for="(vector, index) in embeddings" :key="index" :value="vector">
            {{ truncateVector(vector) }}
          </option>
        </select>
        <input v-model="topN" placeholder="Enter top N results" />
        <button @click="searchSimilarVectors">Search</button>
        <p>{{ searchResponse }}</p>
      </div>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  
  export default {
    data() {
      return {
        // Test texts for generating embeddings
        testTexts: ["Hello world!", "This is a test.", "I love AI."],
        // Embeddings generated from the text
        embeddings: [],
        // Selected vectors and text for testing
        selectedInsertVector: null,
        selectedUpdateVector: null,
        selectedVector1: null,
        selectedVector2: null,
        selectedSearchVector: null,
        selectedText: "Hello world!",
        // User input values
        updateId: '',
        deleteId: '',
        topN: 5,
        // Responses
        insertResponse: '',
        updateResponse: '',
        deleteResponse: '',
        cosineResponse: '',
        searchResponse: '',
        embedResponse: ''
      };
    },
    methods: {
      // Function to truncate vectors for display purposes
      truncateVector(vector) {
        return `[${vector.slice(0, 3).join(', ')}... (${vector.length} dimensions)]`;
      },
      embedText() {
        axios
          .post('/api/vector/embed', { texts: [this.selectedText] })
          .then(response => {
            const embedding = response.data.embeddings[0];
            this.embeddings.push(embedding); // Save full embedding for use in operations
            this.embedResponse = `Embedding generated and saved.`;
          })
          .catch(error => {
            this.embedResponse = `Error: ${error.response.data.message}`;
          });
      },
      insertVector() {
        axios
          .post('/api/vector/insert', { vector: this.selectedInsertVector })
          .then(response => {
            this.insertResponse = `Inserted Vector ID: ${response.data.vector_id}`;
          })
          .catch(error => {
            this.insertResponse = `Error: ${error.response.data.message}`;
          });
      },
      updateVector() {
        axios
          .post(`/api/vector/update/${this.updateId}`, { vector: this.selectedUpdateVector })
          .then(response => {
            this.updateResponse = `Vector updated successfully.`;
          })
          .catch(error => {
            this.updateResponse = `Error: ${error.response.data.message}`;
          });
      },
      deleteVector() {
        axios
          .delete(`/api/vector/delete/${this.deleteId}`)
          .then(response => {
            this.deleteResponse = `Vector deleted successfully.`;
          })
          .catch(error => {
            this.deleteResponse = `Error: ${error.response.data.message}`;
          });
      },
      calculateCosineSimilarity() {
        axios
          .post('/api/vector/cosine-similarity', { vector1: this.selectedVector1, vector2: this.selectedVector2 })
          .then(response => {
            this.cosineResponse = `Cosine Similarity: ${response.data.cosine_similarity}`;
          })
          .catch(error => {
            this.cosineResponse = `Error: ${error.response.data.message}`;
          });
      },
      searchSimilarVectors() {
        axios
          .post('/api/vector/search', { vector: this.selectedSearchVector, topN: this.topN })
          .then(response => {
            this.searchResponse = `Similar Vectors: ${JSON.stringify(response.data.similar_vectors)}`;
          })
          .catch(error => {
            this.searchResponse = `Error: ${error.response.data.message}`;
          });
      }
    }
  };
  </script>
  
  <style scoped>
  .vector-test {
    padding: 20px;
  }
  
  .vector-test select, .vector-test input {
    margin-right: 10px;
  }
  
  .vector-test button {
    margin-bottom: 10px;
  }
  
  .vector-test p {
    font-weight: bold;
    color: green;
  }

  /* black buttons with rounded corners and white text */
    .vector-test button {
        background-color: black;
        color: white;
        border-radius: 5px;
        padding: 5px 10px;
        cursor: pointer;
    }
  </style>
  