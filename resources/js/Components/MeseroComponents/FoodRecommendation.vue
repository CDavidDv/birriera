<template>
    <div class="food-recommendation">
      <h2>What would you eat today?</h2>
      <div class="food-list">
        <div v-for="food in foodItems" :key="food.id" class="food-item">
          <img :src="food.image" :alt="food.name" />
          <p>{{ food.name }}</p>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    props: ['foodItems'],
  };
  </script>
  