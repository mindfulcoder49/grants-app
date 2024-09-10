<template>
  <div class="vector-test">
    <h2>Vector Test Suite</h2>
    
    <!-- Instructional Text for non-technical users -->
    <p class="instructions">
      This tool allows you to embed text into a vector space, where similar vectors (representing text) can be compared 
      based on their semantic similarity. You can insert, update, and delete vectors, as well as calculate the similarity 
      between two vectors. This makes it easier to understand how different pieces of text are related in meaning.
    </p>

    <!-- Embed Text -->
    <div>
      <h3>Embed Text</h3>
      <div class="quick-texts">
          <button @click="selectedText = 'Hello world!'">Hello world!</button>
          <button @click="selectedText = 'This is a test.'">This is a test.</button>
          <button @click="selectedText = 'I love AI.'">I love AI.</button>
      </div>
      <input v-model="selectedText" placeholder="Enter text to embed" />
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

      <!-- Search Results Table -->
      <table v-if="searchResults.length > 0">
        <thead>
          <tr>
            <th v-for="(value, key) in searchResults[0]" :key="key">{{ key }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(result, index) in searchResults" :key="index">
            <td v-for="(value, key) in result" :key="key">{{ truncateResult(value) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Retrieve and List All Vectors -->
    <div>
      <h3>List All Vectors (Max 10)</h3>
      <button @click="listAllVectors">List Vectors</button>
      <ul v-if="allVectors.length > 0">
        <li v-for="(vector, index) in allVectors" :key="index">
          {{vector.id}} : {{ truncateVector(vector.vector) }}
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      // Embeddings generated from the text
      embeddings: [],
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
      // Responses and results
      insertResponse: '',
      updateResponse: '',
      deleteResponse: '',
      cosineResponse: '',
      searchResponse: '',
      embedResponse: '',
      searchResults: [],
      allVectors: []
    };
  },
  mounted() {
    this.listAllVectors();
  },
  methods: {
    truncateVector(vector) {
      return `[${vector.slice(0, 1).join(', ')}... (${vector.length} dimensions)]`;
    },
    truncateResult(result) {
      //check if the result is an array and truncate if it is
        if (Array.isArray(result)) {
            return `[${result.slice(0, 1).join(', ')}... (${result.length} dimensions)]`;
        }

      return typeof result === 'string' && result.length > 15
        ? result.slice(0, 12) + '...'
        : result;
    },
    embedText() {
      axios.post('/api/vector/embed', { texts: [this.selectedText] })
        .then(response => {
          const embedding = response.data.embeddings[0];
          this.embeddings.push(embedding);
          this.embedResponse = `Embedding generated and saved.`;
        })
        .catch(error => {
          this.embedResponse = `Error: ${error.response.data.message}`;
        });
    },
    insertVector() {
      axios.post('/api/vector/insert', { vector: this.selectedInsertVector })
        .then(response => {
          this.insertResponse = `Inserted Vector ID: ${response.data.vector_id}`;
        })
        .catch(error => {
          this.insertResponse = `Error: ${error.response.data.message}`;
        });
    },
    updateVector() {
      axios.post(`/api/vector/update/${this.updateId}`, { vector: this.selectedUpdateVector })
        .then(response => {
          this.updateResponse = `Vector updated successfully.`;
        })
        .catch(error => {
          this.updateResponse = `Error: ${error.response.data.message}`;
        });
    },
    deleteVector() {
      axios.delete(`/api/vector/delete/${this.deleteId}`)
        .then(response => {
          this.deleteResponse = `Vector deleted successfully.`;
        })
        .catch(error => {
          this.deleteResponse = `Error: ${error.response.data.message}`;
        });
    },
    calculateCosineSimilarity() {
      axios.post('/api/vector/cosine-similarity', { vector1: this.selectedVector1, vector2: this.selectedVector2 })
        .then(response => {
          this.cosineResponse = `Cosine Similarity: ${response.data.cosine_similarity}`;
        })
        .catch(error => {
          this.cosineResponse = `Error: ${error.response.data.message}`;
        });
    },
    searchSimilarVectors() {
      axios.post('/api/vector/search', { vector: this.selectedSearchVector, topN: this.topN })
        .then(response => {
          this.searchResults = response.data.similar_vectors;
          this.searchResponse = `Search complete. Found ${this.searchResults.length} vectors.`;
        })
        .catch(error => {
          this.searchResponse = `Error: ${error.response.data.message}`;
        });
    },
    listAllVectors() {
      axios.get('/api/vector/list')
        .then(response => {
          this.allVectors = response.data.vectors;
          // Also add to embeddings
          this.embeddings = this.allVectors.map(vector => vector.vector);

        })
        .catch(error => {
          console.error(error);
        });
    }
  }
};
</script>

<style scoped>
.vector-test {
  padding: 20px;
}

.vector-test select,
.vector-test input {
  margin-right: 10px;
  margin-bottom: 10px;
  padding: 5px;
}

.vector-test button {
  margin-bottom: 10px;
  padding: 5px 10px;
}

.vector-test p {
  font-weight: bold;
  color: green;
}

.quick-texts {
  margin-top: 10px;
}

.instructions {
  margin-bottom: 20px;
  font-size: 14px;
  color: #333;
}
  /* black buttons with rounded corners and white text */
  .vector-test button {
        background-color: black;
        color: white;
        border-radius: 5px;
        padding: 5px 10px;
        cursor: pointer;
        margin: 5px;
    }

    .quick-texts button {
        background-color: white;
        color: black;
        border-radius: 5px;
        padding: 5px 10px;
        cursor: pointer;
        margin: 5px;
    }
/* Ensure mobile responsiveness */
@media screen and (max-width: 600px) {
  .vector-test {
    padding: 10px;
  }

  .vector-test select, .vector-test input {
    width: 100%;
    margin-bottom: 10px;
  }

  .vector-test button {
    width: 100%;
  }

  table, tbody, tr, th, td {
    display: block;
    width: 100%;
    overflow-x: auto;
  }
}
</style>
