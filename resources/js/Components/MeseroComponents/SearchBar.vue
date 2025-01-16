<template>
    <div class="search-bar">
      <input type="text" placeholder="Search" v-model="query" @input="onSearch" />
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        query: '',
      };
    },
    methods: {
      onSearch() {
        // Lógica de búsqueda
      },
    },
  };
  </script>
  