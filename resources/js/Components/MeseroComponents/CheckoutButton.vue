<template>
    <div class="checkout">
      <button @click="checkout">Checkout - {{ total }}</button>
    </div>
  </template>
  
  <script>
  export default {
    props: ['total'],
    methods: {
      checkout() {
        // Lógica de checkout
      },
    },
  };
  </script>
  