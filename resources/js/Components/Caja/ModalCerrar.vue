<template>
    <div v-if="isVisible" class="fixed z-10 inset-0 overflow-y-auto" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Fondo opaco -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <!-- Espaciador -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <!-- Contenedor del Modal -->
        <div
          class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <!-- Contenido del Modal -->
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <!-- Icono -->
              <div
                :class="['mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full  sm:mx-0 sm:h-10 sm:w-10 bg-green-100'
                ]">

                <CashIcon class="h-6 w-6  text-black" />
              </div>
              <!-- Título y entrada -->
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg leading-6 font-medium pt-2 text-gray-900" id="modal-title">
                  Cerrar el corte
                </h3>
                <span class="text-sm text-gray-500 w-4/5 flex pt-3">{{ textoDescripcion }}</span>
                <div class="flex mt-2 items-center gap-4">
                  <InputLabel for="note" value="Cometario" class="w-1/3"/>
                  <div class="w-full">
                    <input
                    type="text"
                    v-model="note"
                    
                    :class="[
                        'block w-full h-20 border-gray-300 rounded-md shadow-sm sm:text-sm',
                        errors.note ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'focus:ring-cyan-500 focus:border-cyan-500'
                      ]"
                      placeholder="Ingrese un comentario"
                    />
                    <span v-if="errors.note" class="text-red-500 text-sm">{{ errors.note }}</span>
                  </div>
                </div>  
                
              </div>
            </div>
          </div>
          <!-- Botones -->
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button @click="confirm" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-cyan-600 text-white hover:bg-cyan-700 sm:ml-3 sm:w-auto sm:text-sm">
              Cerrar
            </button>
            <button @click="close" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
              Cancelar
            </button>
          </div>
        </div>
      </div>
    </div>
  </template>
  
<script setup>
import { ref } from 'vue';
import CashIcon from '../Icons/CashIcon.vue';
import InputLabel from '@/Components/InputLabel.vue';
  
  // Props
  const props = defineProps({
    isVisible: {
      type: Boolean,
      required: true,
    },
  });
  
  // Computed
  const tipoOperacion = ref('');
  const textoDescripcion = ref('');
  
  
  // Emit
  const emit = defineEmits(['close', 'save']);
  
  // Reactive data
  const amount = ref(0);
  const note = ref('');
  const errors = ref({
    amount: null,
    note: null,
  });

  // Emit handlers
  const close = () => emit('close');

  const confirm = () => {
    errors.value.amount = null;
    errors.value.note = null;


    if (note.value.trim() === '') {
      errors.value.note = 'El note no puede estar vacío';
    }

    if (errors.value.amount || errors.value.note) {
      return;
    }

    emit('save', note.value);
  };


  </script>
  