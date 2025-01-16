<template>
    <div class="popular-food">
      <h2>Top meals this week</h2>
      <div class="food-list">
        <div v-for="food in popularItems" :key="food.id" class="food-item">
          <img :src="food.image" :alt="food.name" />
          <p>{{ food.name }}</p>
          <span class="rating">{{ food.rating }} ⭐</span>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    props: ['popularItems'],
  };
  </script>
  