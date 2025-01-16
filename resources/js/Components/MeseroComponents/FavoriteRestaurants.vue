<template>
    <div class="favorite-restaurants">
      <h2>Your favourite restaurants</h2>
      <div v-for="restaurant in favoriteRestaurants" :key="restaurant.id" class="restaurant-item">
        <img :src="restaurant.image" :alt="restaurant.name" />
        <h3>{{ restaurant.name }}</h3>
        <p>{{ restaurant.address }}</p>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    props: ['favoriteRestaurants'],
  };
  </script>
  