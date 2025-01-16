<template>
    <div class="order-summary">
      <h2>My Order</h2>
      <div v-for="order in orders" :key="order.id" class="order-item">
        <p>{{ order.name }} - {{ order.quantity }}</p>
        <span>{{ order.price }}</span>
      </div>
      <div class="total">
        <p>Total: {{ total }}</p>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    props: ['orders', 'total'],
  };
  </script>
  