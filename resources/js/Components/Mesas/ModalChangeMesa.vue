<template>
    <div v-if="showTableChangeModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
      <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-4">Selecciona una nueva mesa</h2>
        
        <div class="grid grid-cols-3 gap-4">
          <button 
            v-for="table in tables" 
            :key="table.id"
            @click="handleChange(table)"
            :disabled="table.estado !== 'libre'"
            :class="[
              'p-4 rounded-lg transition-colors',
              table.estado === 'libre' ? 'bg-green-400 hover:bg-green-200' : 'bg-gray-200'
            ]"
          >
            {{ table.nombre || `Mesa ${table.id}` }}
          </button>
        </div>
        <button 
          @click="handleClose"
          class="mt-4 w-full py-2 px-4 bg-red-500 text-white rounded-lg"
        >
          Cancelar
        </button>
      </div>
    </div>
</template>
<script setup>
defineProps({
  tables: {
    type: Array,
    required: true,
  },
  showTableChangeModal: {
    type: Boolean,
    required: true,
  },
});

const emit = defineEmits(['change', 'close']);

const handleChange = (table) => {
  emit('change', table);
};

const handleClose = () => {
  emit('close');
};
</script>