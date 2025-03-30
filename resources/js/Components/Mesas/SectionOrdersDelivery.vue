<!-- SectionOrdersDelivery.vue -->
<template>
  <div class="bg-white rounded-lg shadow-md p-6 mt-6">
    <h2 class="text-xl font-semibold mb-4">Pedidos para llevar</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div 
        v-for="order in ordenesParaLlevar" 
        :key="order.id" 
        class="bg-gray-100 p-4 rounded-lg shadow-sm"
      >
        <p v-if="order.nombre_cliente" class="text-gray-800 text-xl font-bold mb-2">Cliente: {{ order.nombre_cliente }}</p>
        <p v-else class="text-gray-800 font-bold mb-2 text-xl">Pedido ID: {{ order.id }}</p>
        <div class="flex justify-between items-center mb-2">
          <div class="flex flex-col gap-2">
            <button 
            @click="handleAdd(order.id)"
              class="text-xl bg-blue-500  text-white px-2 size-fit py-2  rounded-lg hover:bg-blue-600 transition-colors"
            >
              Agregar productos
            </button>
            <button 
              @click="handleSubstack(order.id)"
              class="text-xl bg-blue-500 w-full text-white px-2 size-fit py-2  rounded-lg hover:bg-blue-600 transition-colors"
              >
              Quitar productos
            </button>
          </div>
          <button 
            @click="handleCancel(order.id)"
            class="text-xl bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors"
          >
            Cancelar
          </button>
        </div>
      </div>
    </div>
    <div v-if="!ordenesParaLlevar?.length" class="text-center text-gray-600 mt-4">
      No hay pedidos para llevar
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  ordenesParaLlevar: {
    type: Array,
    required: true,
  },
});

const emit = defineEmits(['add', 'cancel', 'subs']);

const handleCancel = (orderId) => {
  emit('cancel', orderId);
};

const handleAdd = (orderId) => {
  emit('add', orderId);
};

const handleSubstack = (orderId) => {
  emit('subs', orderId);
};
</script>