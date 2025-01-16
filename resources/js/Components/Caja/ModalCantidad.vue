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
                :class="['mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full  sm:mx-0 sm:h-10 sm:w-10 ',
                      modalType === 'withdraw' ? 'bg-red-100' : modalType === 'deposit' ? 'bg-blue-100' : modalType === 'initialCash' ? 'bg-blue-100' : 'bg-green-100'
                ]">

                <CashIcon class="h-6 w-6  text-black" />
              </div>
              <!-- Título y entrada -->
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg leading-6 font-medium pt-2 text-gray-900" id="modal-title">
                  {{ tipoOperacion }}
                </h3>
                <span class="text-sm text-gray-500 w-4/5 flex pt-3">{{ textoDescripcion }}</span>
                <div class="flex mt-2 items-center gap-4" v-if="showMotivo()">
                  <InputLabel for="motivo" value="Motivo" class="w-1/3"/>
                  <div class="w-2/3">
                    <input
                    type="text"
                    v-model="motivo"
                    :class="[
                        'block w-full border-gray-300 rounded-md shadow-sm sm:text-sm',
                        errors.motivo ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'focus:ring-cyan-500 focus:border-cyan-500'
                      ]"
                      placeholder="Ingrese el motivo"
                    />
                    <span v-if="errors.motivo" class="text-red-500 text-sm">{{ errors.motivo }}</span>
                  </div>
                </div>
                
                <div class="flex mt-2 items-center gap-4">
                  <InputLabel for="amount" value="Cantidad" class="w-1/3"/>
                  <div class="w-2/3">
                    <input
                      id="amount"
                      type="number"
                      v-model="amount"
                      :class="[
                        'block w-full rounded-md shadow-sm sm:text-sm ',
                        errors.amount ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-cyan-500 focus:border-cyan-500'
                      ]"
                      placeholder="Ingrese la cantidad"
                      min="0"
                      step="0.01"
                    />
                    <span v-if="errors.amount" class="text-red-500 text-sm">{{ errors.amount }}</span>
                  </div>
                </div>
                  
                
              </div>
            </div>
          </div>
          <!-- Botones -->
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button @click="confirm" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-cyan-600 text-white hover:bg-cyan-700 sm:ml-3 sm:w-auto sm:text-sm">
              Confirmar
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
import InputError from '../InputError.vue';
  
  // Props
  const props = defineProps({
    modalType: {
      type: String,
      required: true,
      validator: (value) => ['deposit', 'withdraw', 'finalCash', 'initialCash', 'addGastos'].includes(value),
    },
    modalAmount: {
      type: Number,
      required: false,
      default: 0,
    },
    modalMotivo:{
      type: String,
      required: false,
      default: '',
    },
    isVisible: {
      type: Boolean,
      required: true,
    },
  });
  
  // Computed
  const tipoOperacion = ref('');
  const textoDescripcion = ref('');
  
  switch (props.modalType) {
    case 'deposit':
      tipoOperacion.value = 'Ingresar dinero';
      textoDescripcion.value = 'Ingrese la cantidad de dinero que desea ingresar a la caja.'
      break;
    case 'withdraw':
      tipoOperacion.value = 'Sacar dinero';
      textoDescripcion.value = 'Ingrese la cantidad de dinero que desea sacar de la caja.'
      break;
    case 'finalCash':
      tipoOperacion.value = 'Ingresar dinero final';
      textoDescripcion.value = 'Ingrese la cantidad de dinero que quedo en la caja al final del turno.'
      break;
    case 'initialCash':
      tipoOperacion.value = 'Ingresar dinero Inicial';
      textoDescripcion.value = 'Ingrese la cantidad de dinero con el que inicio el turno.'
      break;
    case 'addGastos':
      tipoOperacion.value = 'Registrar gasto';
      textoDescripcion.value = 'Ingrese la cantidad de dinero que gasto.'
      break;
    default:
      tipoOperacion.value = 'Operación';
      break;
  }
  // Emit
  const emit = defineEmits(['close', 'save']);
  
  // Reactive data
  const amount = ref(0);
  const motivo = ref('');
  const errors = ref({
    amount: null,
    motivo: null,
  });

  
  const showMotivo = () => {
    const type = props.modalType
    return type === 'addGastos' || type === 'withdraw' || type === 'deposit' 
  }
  // Emit handlers
  const close = () => emit('close');

  const confirm = () => {
    errors.value.amount = null;
    errors.value.motivo = null;

    if (amount.value <= 0) {
      errors.value.amount = 'La cantidad debe ser mayor a 0';
    }

    if (motivo.value.trim() === '' && showMotivo()) {
      errors.value.motivo = 'El motivo no puede estar vacío';
    }

    if (errors.value.amount || errors.value.motivo) {
      return;
    }

    emit('save', amount.value, motivo.value);
  };


  </script>
  