<template >
    <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-4">Selecciona una nueva mesa</h2>
        <div class="grid grid-cols-3 md:grid-cols-4 gap-4">
          <button 
            v-for="table in tables" 
            :key="table.id" 
            @click="changeTable(table)" 
            :disabled="table.estatus !== 'libre'"
            class="p-4 rounded-lg bg-gray-200 hover:bg-gray-300 transition-colors"
          >
            {{ table.nombre || `Mesa ${table.id}` }}
          </button>
        </div>
        <button 
          @click="closeTableChangeModal"
          class="mt-4 w-full py-2 px-4 bg-red-500 text-white rounded-lg"
        >
          Cancelar
        </button>
      </div>
    </div>
  </template>


<script setup>
const showTableChangeModal = ref(false);

const openTableChangeModal = () => {
  showTableChangeModal.value = true;
};

const closeTableChangeModal = () => {
  showTableChangeModal.value = false;
};

</script>  