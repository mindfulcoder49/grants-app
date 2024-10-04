<template>
    <div class="modal-overlay">
      <div class="modal-content">
        <h3 class="text-lg font-bold mb-4">Grants</h3>
        <ul>
          <li v-for="grant in grantIds" :key="grant">{{ grant }}</li>
        </ul>
        <button @click="$emit('close')" class="mt-4">Close</button>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    name: 'GrantsModal',
    props: {
      grantIds: {
        type: Array,
        default: () => [],
      },
    },
  };
  </script>
  
  <style scoped>
  .modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
  }
  
  .modal-content {
    background-color: #fff;
    padding: 20px;
    border-radius: 5px;
    max-height: 80vh;
    overflow-y: auto;
  }
  
  .modal-content button {
    background-color: #004aad;
    color: #fff;
    padding: 8px 16px;
    border: none;
    border-radius: 2px;
    cursor: pointer;
  }
  
  .modal-content button:hover {
    background-color: #0056b3;
  }
  </style>
  