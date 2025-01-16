<template>
  <div class="min-h-screen   flex flex-col justify-center ">
    <div class="relative px-4 py-5 bg-white shadow-lg sm:rounded-3xl sm:p-16">

      <div class="max-w-7xl mx-auto">
        <h1 class="text-4xl font-extrabold text-gray-900 mb-8 text-center">Caja de la sucursal {{
          props.auth.user.sucursal_id }}</h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Main content (left column) -->
          <div class="lg:col-span-2 space-y-8">

            <!-- Mensaje de error -->
            <div v-if="error" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded" role="alert">
              <p class="font-bold">Error</p>
              <p>{{ error }}</p>
            </div>

            <!-- Filtro por día, semana o mes -->
            <div class="p-5 border rounded-xl shadow-md">
              <label for="filter" class="block text-sm font-medium text-gray-700 mb-2">Filtrar por:</label>
              <div class="flex items-center gap-4">
                <select
                  class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-cyan-500 focus:border-cyan-500 sm:text-sm rounded-md"
                  id="filter" v-model="selectedFilter">
                  <option value="day">Día</option>
                  <option value="week">Semana</option>
                  <option value="month">Mes</option>
                </select>
                <input
                  class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-cyan-500 focus:border-cyan-500 sm:text-sm rounded-md"
                  id="valueSelect"
                  :type="selectedFilter === 'day' ? 'date' : selectedFilter === 'week' ? 'week' : 'month'"
                  v-model="selectedValue" />
              </div>
              <div class="mt-4 flex justify-between">
                <button
                  class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-cyan-600 hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500"
                  @click="fetchFilteredData">
                  <RefreshCcwIcon class="h-5 w-5 mr-2" />
                  Aplicar Filtro
                </button>
                <button
                  class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-cyan-700 bg-cyan-100 hover:bg-cyan-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500"
                  @click="resetFilters">
                  <XIcon class="h-5 w-5 mr-2" />
                  Limpiar Filtro
                </button>
              </div>
            </div>

            <!-- Resumen financiero -->
            <div class=" border shadow-lg rounded-xl px-8 py-10 gap-4 flex flex-col">
              <div class="w-full flex justify-between">
                <h2 class="text-xl font-semibold mb-4 text-gray-900">Resumen Financiero {{
                  selectedValue.split('-')[2] + '/' + selectedValue.split('-')[1] + '/' + selectedValue.split('-')[0] }}</h2>
                <span>Disponible: $<span class="text-2xl text-green-800 ">{{ dineroDisponible }}</span></span>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div v-if="selectedFilter === 'day'">
                  <p class="text-sm text-gray-600">Dinero inicial:</p>
                  <p class="font-medium text-gray-900">${{ safeToFixed(initialCash) }}</p>
                </div>
                <div v-if="selectedFilter === 'day'">
                  <p class="text-sm text-gray-600">Dinero final:</p>
                  <p class="font-medium text-gray-900">${{ safeToFixed(finalCash) }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-600">Efectivo:</p>
                  <p class="font-medium text-gray-900">${{ safeToFixed(cashPayments) }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-600">Tarjetas:</p>
                  <p class="font-medium text-gray-900">${{ safeToFixed(cardPayments) }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-600">Total ventas:</p>
                  <p class="font-medium text-gray-900">${{ safeToFixed(Number(cashPayments) + Number(cardPayments)) }}
                  </p>
                </div>
              </div>
              <div v-if="isToday" class="space-y-6 p-4 flex  justify-end items-end border-t  border-gray-400 ">
                <div class="grid gap-3 grid-cols-2 ">
                  <button @click="openModal('initialCash')" 
                    :class="['inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white ',
                      !existentInitialCash ? 'opacity-100 cursor-not-allowed bg-gray-400 ' 
                      : 'bg-lime-600 hover:bg-lime-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500']"
                    :disabled="initialCash" >
                    <Plus class="size-5 mr-2" 
                    />
                    Asignar cantidad inicial
                  </button>
                  <button @click="openModal('finalCash')"
                  :class="['inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white ',
                      !existentFinalCash ? 'opacity-100 cursor-not-allowed bg-gray-400 ' 
                      : 'bg-lime-600 hover:bg-lime-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500']"
                    :disabled="finalCash" >
                    <Plus class="size-5 mr-2" />
                    Asignar cantidad final
                  </button>
                  <button @click="openModal('withdraw')"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <ArrowDownIcon class="size-5 mr-2" />
                    Sacar dinero
                  </button>
                  <button @click="openModal('deposit')"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <ArrowUpIcon class="size-5 mr-2" />
                    Ingresar dinero
                  </button>
                  <span></span>
                  <button @click="openModal('addGastos')"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <Plus class="size-5 mr-2" />
                    Registrar gasto
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Sidebar (right column) -->
          <div class="lg:col-span-1 space-y-8">
            <!-- Registros en caja -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
              <div class="px-6 py-4 bg-gradient-to-r from-green-600 to-lime-500">
                <h2 class="text-xl font-semibold text-white">Registros en Caja</h2>
              </div>
              <div class="p-6 max-h-80 overflow-y-auto">
                <ul class="space-y-4">
                  <li v-for="(registro, index) in registrosCaja" :key="index"
                    class="flex items-center justify-between border-b pb-2">
                    <div>
                      
                      <p class="capitalize text-gray-500">Tipo: <span class=" text-black font-medium">{{ registro.tipo }}</span> </p>
                      <p class="text-sm text-gray-500">Hora: {{ registro.updated_at.split('T')[1].split('.')[0] }}</p>
                    </div>
                    <span :class="registro.tipo !== 'salida' ? 'text-green-600' : 'text-red-600'">
                      {{ (registro.tipo === 'entrada' || registro.tipo === 'corte-entrada' || registro.tipo === 'corte-salida') ? '+' : '-' }}${{ safeToFixed(registro.cantidad) }}
                    </span>
                  </li>
                </ul>
              </div>
            </div>

            <!-- Ventas -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
              <div class="px-6 py-4 bg-gradient-to-r to-lime-500 from-green-600">
                <h2 class="text-xl font-semibold text-white">Ventas</h2>
              </div>
              <div class="p-6 max-h-80 overflow-y-auto">
                <ul class="space-y-4">
                  <li v-for="(venta, index) in ventas" :key="index"
                    class="flex items-center justify-between border-b pb-2">
                    <div>
                      <p class="font-medium">Venta #{{ venta.id }}</p>
                      <p class="text-sm text-gray-500">Día: {{ venta.updated_at.split('T')[0] }}</p>
                      <p class="text-sm text-gray-500">Hora: {{ venta.updated_at.split('T')[1].split('.')[0] }}</p>
                    </div>
                    <span class="text-blue-600">${{ safeToFixed(venta.total) }}</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- Mensaje de carga -->
        <div v-if="isLoading" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
          <div class="bg-white p-6 rounded-lg shadow-xl">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-cyan-500 mx-auto"></div>
            <p class="mt-4 text-center text-gray-700">Cargando...</p>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- Modal para ingresar o sacar dinero -->
  <ModalCantidad
    v-if="showModal"
    :is-visible="showModal"
    :modal-type="modalType"
    :modal-amount="modalAmount"
    :modal-motivo="modalMotivo"
    @close="closeModal"
    @save="saveModal"
  />
  
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import Swal from 'sweetalert2'
import { RefreshCcwIcon, ArrowUpIcon, ArrowDownIcon, XIcon, Plus } from 'lucide-vue-next';
import ModalCantidad from './Caja/ModalCantidad.vue';
import axios from 'axios';


const { props } = usePage()
const selectedFilter = ref('day')
const corte = ref(props?.corte)
const today = new Date()
const selectedValue = ref(today?.toISOString().split('T')[0])
const initialCash = ref(corte?.value?.saldo_inicial || 0)
const existentInitialCash = ref(corte?.value?.saldo_inicial ? 0 : 1)
const finalCash = ref(corte.value?.saldo_final || 0)
const existentFinalCash = ref(corte?.value?.saldo_final ? 0 : 1)
const cashPayments = ref(0)
const dineroDisponible = ref(corte?.value?.dinero_total || 0)
const cardPayments = ref(0)
const productsUsed = ref([])
const isLoading = ref(false)
const error = ref('')

// Modal state
const showModal = ref(false)
const modalType = ref('')
const modalAmount = ref(0)
const modalMotivo = ref('')


// Dummy data for registros en caja and ventas
const registrosCaja = ref(props.logscaja)

const ventas = ref(props.ventas)

const isToday = computed(() => {
  const today = new Date()
  const formattedToday = today.toISOString().split('T')[0]
  return selectedFilter.value === 'day' && selectedValue.value === formattedToday
})

const fetchFilteredData = () => {
  isLoading.value = true;
  error.value = '';

  axios
    .post('/corte-caja/filtro', {
      filter: selectedFilter.value,
      value: selectedValue.value,
    })
    .then((response) => {
      console.log(response.data);
      ventas.value = response.data.ventas;
      registrosCaja.value = response.data.logscaja;
      corte.value = response.data.corte;
      dineroDisponible.value = response?.data?.corte?.dinero_total || 0;
      calculatePayments();
      showToast("success", "Filtro actualizado correctamente");
    })
    .catch((error) => {
      console.error(error);
      showToast("error", error.response?.data?.error || "Error al obtener datos con este filtro");
      error.value = 'Ocurrió un error al obtener los datos. Inténtalo de nuevo.';
    })
    .finally(() => {
      isLoading.value = false;
    });
};

const resetFilters = () => {
  selectedFilter.value = 'day'
  selectedValue.value = new Date().toISOString().split('T')[0]
  fetchFilteredData()
}

const calculatePayments = () => {
  cashPayments.value = props.ventas.reduce((total, venta) => venta.metodo_pago === 'efectivo' ? Number(total) + Number(venta.total) : Number(total), 0)
  cardPayments.value = props.ventas.reduce((total, venta) => venta.metodo_pago === 'tarjeta' ? Number(total) + Number(venta.total) : Number(total), 0)
}

const showToast = (icon, title) => {
  Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer)
      toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
  }).fire({
    icon,
    title
  })
}


const openModal = (type) => {
  modalType.value = type
  modalAmount.value = 0
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  modalAmount.value = 0
}

const handleModalConfirm = () => {
  if (modalType.value === 'withdraw') {
    finalCash.value = Number(finalCash.value) - Number(modalAmount.value)
  } else {
    finalCash.value = Number(finalCash.value) + Number(modalAmount.value)
  }
  handleSaveFinalCash()
  closeModal()
}

onMounted(() => {
  calculatePayments()
})

const safeToFixed = (value) => {
  return parseFloat(value).toFixed(2)
}

const saveModal = (amount, motivo) => {
  if ( (modalType.value === 'withdraw' && amount > dineroDisponible.value) || (modalType.value === 'addGastos' && amount > dineroDisponible.value)){
    showToast("error", "No puedes sacar mas dinero del disponible")
    return
  }else{
    router.post('/corte-caja/guardar-gasto', {
      tipo: modalType.value,
      monto: amount,
      motivo: motivo
    }, {
      preserveScroll: true,
      onSuccess(e) {
        if (e.props.flash.error) {
          showToast("error", e.props.flash.error || "Error al guardar el gasto")
        } else {
          initialCash.value = e.props?.corte?.saldo_inicial || 0
          existentInitialCash.value = e.props?.corte?.saldo_inicial ? 0 : 1
          finalCash.value = e.props?.corte?.saldo_final || 0
          existentFinalCash.value = e.props?.corte?.saldo_final ? 0 : 1
          dineroDisponible.value = e.props?.corte?.dinero_total || 0
          showToast("success", "Gasto guardado correctamente")
        }
        //obtener Logs y ventas
        axios.get('/corte-caja/obtener-datos')
          .then(response => {
              console.log(response.data); // Asegúrate de imprimir response.data
              registrosCaja.value = response.data.logscaja;
              ventas.value = response.data.ventas;
          })
          .catch(error => {
              console.error('Error al obtener los datos:', error);
        });

      },
      onError(e) {
        showToast("error", e.error || "Error al guardar el gasto")
      }
    })
    closeModal();
  }  
};
</script>

<style scoped>
.table-striped tbody tr:nth-of-type(odd) {
  background-color: rgba(0, 0, 0, 0.05);
}
</style>
