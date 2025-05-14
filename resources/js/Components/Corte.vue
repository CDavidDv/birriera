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
              <label for="filter" class="block text-lg font-medium text-gray-700 mb-2">Filtrar por:</label>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-4">
                  <div class="flex items-center gap-4">
                    <select
                      class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-cyan-500 focus:border-cyan-500 sm:text-lg rounded-md"
                      id="filter" v-model="selectedFilter">
                      <option value="day">Día</option>
                      <option value="week">Semana</option>
                      <option value="month">Mes</option>
                      <option value="custom">Rango Personalizado</option>
                    </select>
                  </div>

                  <div v-if="selectedFilter !== 'custom'" class="flex items-center gap-4">
                    <input
                      class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-cyan-500 focus:border-cyan-500 sm:text-lg rounded-md"
                      id="valueSelect"
                      :type="selectedFilter === 'day' ? 'date' : selectedFilter === 'week' ? 'week' : 'month'"
                      v-model="selectedValue" />
                  </div>

                  <div v-else class="grid grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Fecha Inicio</label>
                      <input
                        type="date"
                        class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-cyan-500 focus:border-cyan-500 sm:text-lg rounded-md"
                        v-model="fechaInicio" />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Fecha Fin</label>
                      <input
                        type="date"
                        class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-cyan-500 focus:border-cyan-500 sm:text-lg rounded-md"
                        v-model="fechaFin" />
                    </div>
                  </div>
                </div>

                <div class="flex flex-col justify-between">
                  <div class="flex justify-between mt-4">
                    <button
                      class="inline-flex items-center px-4 py-2 border border-transparent text-lg font-medium rounded-md text-white bg-cyan-600 hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500"
                      @click="fetchFilteredData">
                      <RefreshCcwIcon class="h-5 w-5 mr-2" />
                      Aplicar Filtros
                    </button>
                    <button
                      class="inline-flex items-center px-4 py-2 border border-transparent text-lg font-medium rounded-md text-cyan-700 bg-cyan-100 hover:bg-cyan-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500"
                      @click="resetFilters">
                      <XIcon class="h-5 w-5 mr-2" />
                      Limpiar Filtros
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Resumen financiero -->
            <div class="border shadow-lg rounded-xl px-8 py-10 gap-4 flex flex-col">
              <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Resumen Financiero {{
                  selectedValue.split('-')[2] + '/' + selectedValue.split('-')[1] + '/' + selectedValue.split('-')[0] }}</h2>
                <span>Disponible: $<span class="text-2xl text-green-800">{{ dineroDisponible }}</span></span>
              </div>
              <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div v-if="selectedFilter === 'day'">
                  <p class="text-lg text-gray-600">Dinero al iniciar la caja:</p>
                  <p class="font-medium text-gray-900">${{ safeToFixed(initialCash) }}</p>
                </div>
                <div v-if="selectedFilter === 'day'">
                  <p class="text-lg text-gray-600">Dinero al cerrar la caja:</p>
                  <p class="font-medium text-gray-900">${{ safeToFixed(finalCash) }}</p>
                </div>
                <div>
                  <p class="text-lg text-gray-600">Ventas en Efectivo:</p>
                  <p class="font-medium text-gray-900">${{ safeToFixed(cashPayments - initialCash) }}</p>
                </div>
                <div>
                  <p class="text-lg text-gray-600">Ventas con Tarjeta:</p>
                  <p class="font-medium text-gray-900">${{ safeToFixed(cardPayments - finalCash) }}</p>
                </div>
                <div>
                  <p class="text-lg text-gray-600">Total Ventas:</p>
                  <p class="font-medium text-gray-900">${{ safeToFixed(Number(cashPayments) + Number(cardPayments) - Number(initialCash) - Number(finalCash)) }}</p>
                </div>
                <div>
                  <p class="text-lg text-gray-600">Total Entradas:</p>
                  <p class="font-medium text-gray-900">${{ safeToFixed(totalEntradas) }}</p>
                </div>
                <div>
                  <p class="text-lg text-gray-600">Total Salidas:</p>
                  <p class="font-medium text-gray-900">${{ safeToFixed(totalSalidas) }}</p>
                </div>
                <div>
                  <p class="text-lg text-gray-600">Total Gastos:</p>
                  <p class="font-medium text-gray-900">${{ safeToFixed(totalGastos) }}</p>
                </div>
                <div>
                  <p class="text-lg text-gray-600">Otros Ingresos:</p>
                  <p class="font-medium text-gray-900">${{ safeToFixed(corte?.otros_ingresos || 0) }}</p>
                </div>
                <div>
                  <p class="text-lg text-gray-600">Venta Real:</p>
                  <p class="font-medium text-gray-900">${{ safeToFixed(ventaReal) }}</p>
                </div>
                <div>
                  <p class="text-lg text-gray-600">Efectivo Entregado:</p>
                  <p class="font-medium text-gray-900">${{ safeToFixed(corte?.efectivo_entregado || 0) }}</p>
                </div>
                <div>
                  <p class="text-lg text-gray-600">Saldo Siguiente Corte:</p>
                  <p class="font-medium text-gray-900">${{ safeToFixed(corte?.saldo_siguiente_corte || 0) }}</p>
                </div>
                <div>
                  <p class="text-lg text-gray-600">Diferencia:</p>
                  <p class="font-medium text-gray-900">${{ safeToFixed(corte?.difference || 0) }}</p>
                </div>
              </div>

              <!-- Información adicional del corte -->
              <div v-if="corte" class="mt-4 p-4 bg-gray-50 rounded-lg">
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <p class="text-sm text-gray-600">Hora de Apertura:</p>
                    <p class="font-medium">{{ corte.created_at ? new Date(corte.created_at).toLocaleString() : 'No registrada' }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-600">Hora de Cierre:</p>
                    <p v-if="corte.saldo_final" class="font-medium">{{ corte.updated_at ? new Date(corte.updated_at).toLocaleString() : 'No registrada' }}</p>
                    <p v-else class="font-medium">No registrada</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-600">Estado:</p>
                    <p class="font-medium">{{ corte.status ? 'Abierto' : 'Cerrado' }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-600">Nota:</p>
                    <p class="font-medium">{{ corte.note || 'Sin nota' }}</p>
                  </div>
                </div>
              </div>

              <div v-if="isToday" class="space-y-6 p-4 flex justify-end items-end border-t border-gray-400">
                <div class="grid gap-3 grid-cols-2 ">
                  <button @click="openModal('initialCash')" 
                    :class="['inline-flex items-center px-4 py-2 border border-transparent text-lg font-medium rounded-md text-white ',
                      initialCash ? 'opacity-100 cursor-not-allowed bg-gray-400 ' 
                      : 'bg-lime-600 hover:bg-lime-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500']"
                    :disabled="initialCash" >
                    <Plus class="size-5 mr-2" 
                    />
                    Asignar cantidad inicial
                  </button>
                  <button @click="openModal('finalCash')"
                          :class="[
                            'inline-flex items-center px-4 py-2 border border-transparent text-lg font-medium rounded-md text-white',
                            finalCash
                              ? 'opacity-50 cursor-not-allowed bg-gray-400' 
                              : 'bg-lime-600 hover:bg-lime-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500'
                          ]"
                          :disabled="finalCash"
                  >
                    <Plus class="size-5 mr-2" />
                    Asignar cantidad final
                  </button>
                  <button @click="openModal('withdraw')"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-lg font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <ArrowDownIcon class="size-5 mr-2" />
                    Sacar dinero
                  </button>
                  <button @click="openModal('deposit')"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-lg font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <ArrowUpIcon class="size-5 mr-2" />
                    Ingresar dinero
                  </button>
                  <span></span>
                  <button @click="openModal('addGastos')"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-lg font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <Plus class="size-5 mr-2" />
                    Registrar gasto
                  </button>
                </div>
              </div>
            </div>

            <!-- Lista de cortes del día -->
            <div v-if="selectedFilter === 'day'" class="mt-8">
              <h3 class="text-xl font-semibold mb-4">Cortes del Día</h3>
              <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                      <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hora</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo Inicial</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ventas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gastos</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo Final</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diferencia</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                      <tr v-for="corte in cortesDelDia" :key="corte.id">
                        <!--horario en mexico-->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                          {{ new Date(corte?.created_at).toLocaleString('es-MX', { timeZone: 'America/Mexico_City' }) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <span :class="[
                            'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                            corte.status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                          ]">
                            {{ corte?.status ? 'Abierto' : 'Cerrado' }}
                          </span>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                          ${{ safeToFixed(corte?.saldo_inicial) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                          ${{ safeToFixed(corte?.ventas_total) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                          ${{ safeToFixed(corte?.gastos) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                          ${{ safeToFixed(corte?.saldo_final) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm" :class="corte?.difference >= 0 ? 'text-green-600' : 'text-red-600'">
                          ${{ safeToFixed(corte?.difference) }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <div class="flex gap-2 justify-between">
              <button @click="showModalCerrar = true"
              class="inline-flex items-center px-4 py-2 border border-transparent text-lg font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
              :class="{ 'opacity-50 cursor-not-allowed': !isToday || !corte }"
              :disabled="!isToday || !corte"
              >
              <FolderClosed class="size-5 mr-2" />
              Cerrar corte
            </button>
            <div class="flex gap-2">
                  <button @click="generarReportePDF"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-lg font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <FileText class="size-5 mr-2" />
                    Generar PDF
                  </button>
                  <button @click="generarReporteExcel"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-lg font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <FileSpreadsheet class="size-5 mr-2" />
                    Generar Excel
                  </button>
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
                      <p class="text-lg text-gray-500">Hora: {{ registro.updated_at.split('T')[1].split('.')[0] }}</p>
                    </div>
                    <span :class="['text-lg', ['entrada', 'corte-entrada'].includes(registro.tipo) ? 'text-green-600' : 'text-red-600']">
                      {{ ['entrada', 'corte-entrada'].includes(registro.tipo) ? '+' : '-' }}${{ safeToFixed(registro.cantidad) }}
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
                      <p class="text-lg text-gray-500">Día: {{ venta.updated_at.split('T')[0] }}</p>
                      <p class="text-lg text-gray-500">Hora: {{ venta.updated_at.split('T')[1].split('.')[0] }}</p>
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

  <ModalCerrar 
    v-if="showModalCerrar"
    :is-visible="showModalCerrar"
    @close="showModalCerrar = false"
    @save="cerrarCorte"
  />

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
import { RefreshCcwIcon, ArrowUpIcon, ArrowDownIcon, XIcon, Plus, FolderClosed, FileText, FileSpreadsheet } from 'lucide-vue-next';
import ModalCantidad from './Caja/ModalCantidad.vue';
import axios from 'axios';
import ModalCerrar from './Caja/ModalCerrar.vue';
import { jsPDF } from 'jspdf';
import autoTable from 'jspdf-autotable';
import * as XLSX from 'xlsx';


const { props } = usePage()
const selectedFilter = ref('day')
const corte = ref(props?.corte)
const today = new Date()
const selectedValue = ref(today?.toISOString().split('T')[0])
const initialCash = ref(props?.corte?.saldo_inicial || 0)
const finalCash = ref(props?.corte?.saldo_final || 0)
const cashPayments = ref(0)
const cardPayments = ref(0)
const dineroDisponible = ref(0)
const registrosCaja = ref([])
const ventas = ref([])
const isLoading = ref(false)
const error = ref('')

// Modal state
const showModal = ref(false)
const modalType = ref('')
const modalAmount = ref(0)
const modalMotivo = ref('')

// Nuevas variables reactivas para los filtros
const fechaInicio = ref('')
const fechaFin = ref('')

// Variables computadas para los totales
const totalEntradas = computed(() => {
  return registrosCaja.value
    .filter(registro => ['entrada', 'corte-entrada'].includes(registro.tipo))
    .reduce((total, registro) => total + Number(registro.cantidad), 0);
});

const totalSalidas = computed(() => {
  return registrosCaja.value
    .filter(registro => ['salida', 'corte-salida'].includes(registro.tipo))
    .reduce((total, registro) => total + Number(registro.cantidad), 0);
});

const totalGastos = computed(() => {
  return registrosCaja.value
    .filter(registro => registro.tipo === 'gasto')
    .reduce((total, registro) => total + Number(registro.cantidad), 0);
});

const ventaReal = computed(() => {
  return Number(cashPayments.value) + Number(cardPayments.value) - totalGastos.value - Number(initialCash.value) ;
});

// Función para inicializar los datos
const initializeData = () => {
  if (props.corte) {
    initialCash.value = props.corte.saldo_inicial || 0;
    finalCash.value = props.corte.saldo_final || 0;
    cashPayments.value = props.corte.dinero_en_efectivo || 0;
    cardPayments.value = props.corte.dinero_tarjeta || 0;
    dineroDisponible.value = props.corte.dinero_total || 0;
  }
  
  if (props.ventas) {
    ventas.value = props.ventas;
  }
  
  if (props.logscaja) {
    registrosCaja.value = props.logscaja;
  }
};

// Llamar a initializeData cuando el componente se monta
onMounted(() => {
  initializeData();
});

// Agregar nueva variable reactiva para los cortes del día
const cortesDelDia = ref(props.cortesDelDia)

// Modificar fetchFilteredData para incluir los cortes del día
const fetchFilteredData = () => {
  isLoading.value = true;
  error.value = '';

  const params = {
    filter: selectedFilter.value,
    value: selectedFilter.value === 'custom' ? null : selectedValue.value,
    fecha_inicio: selectedFilter.value === 'custom' ? fechaInicio.value : null,
    fecha_fin: selectedFilter.value === 'custom' ? fechaFin.value : null
  };

  axios
    .post('/corte-caja/filtro', params)
    .then((response) => {
      ventas.value = response.data.ventas;
      registrosCaja.value = response.data.logscaja;
      corte.value = response.data.corte;
      cortesDelDia.value = response.data.cortes || [];
      
      // Actualizar los valores del resumen financiero
      if (response.data.corte) {
        initialCash.value = response.data.corte.saldo_inicial || 0;
        finalCash.value = response.data.corte.saldo_final || 0;
        cashPayments.value = response.data.corte.dinero_en_efectivo || 0;
        cardPayments.value = response.data.corte.dinero_tarjeta || 0;
        dineroDisponible.value = response.data.corte.dinero_total || 0;
      } else {
        // Si no hay corte, calcular los totales de las ventas
        cashPayments.value = ventas.value
          .filter(venta => venta.metodo_pago === 'cash')
          .reduce((total, venta) => total + Number(venta.total), 0);
        
        cardPayments.value = ventas.value
          .filter(venta => venta.metodo_pago === 'card')
          .reduce((total, venta) => total + Number(venta.total), 0);
      }
      
      showToast("success", "Filtros aplicados correctamente");
    })
    .catch((error) => {
      console.error(error);
      showToast("error", error.response?.data?.error || "Error al obtener datos con estos filtros");
      error.value = 'Ocurrió un error al obtener los datos. Inténtalo de nuevo.';
    })
    .finally(() => {
      isLoading.value = false;
    });
};

const resetFilters = () => {
  selectedFilter.value = 'day';
  selectedValue.value = new Date().toISOString().split('T')[0];
  fechaInicio.value = '';
  fechaFin.value = '';
  
  // Reiniciar los valores del resumen financiero
  initialCash.value = 0;
  finalCash.value = 0;
  cashPayments.value = 0;
  cardPayments.value = 0;
  dineroDisponible.value = 0;
  registrosCaja.value = [];
  ventas.value = [];
  
  fetchFilteredData();
};


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
          finalCash.value = e.props?.corte?.saldo_final || 0
          dineroDisponible.value = e.props?.corte?.dinero_total || 0
          ventas.value = e.props?.ventas
          registrosCaja.value = e.props?.logscaja
          corte.value = e.props?.corte
          cortesDelDia.value = e.props?.cortes


          showToast("success", "Gasto guardado correctamente")
        }
        //obtener Logs y ventas
        actualizarDatos()

      },
      onError(e) {
        showToast("error", e.error || "Error al guardar el gasto")
      }
    })
    closeModal();
  }  
};


const actualizarDatos = () => {
  axios.get('/corte-caja/obtener-datos')
    .then(response => {
      initialCash.value = response.data.corte?.saldo_inicial || 0
      finalCash.value = response.data.corte?.saldo_final || 0
      cashPayments.value = response.data.corte?.dinero_en_efectivo || 0
      cardPayments.value = response.data.corte?.dinero_tarjeta || 0
      dineroDisponible.value = response.data.corte?.dinero_total || 0
    })  
    .catch(error => {
        console.error('Error al obtener los datos:', error);
  });
  
}

const showModalCerrar = ref(false)

const cerrarCorte = (nota) => {
  router.post('/corte-caja/cerrar-corte', {
    corte_id: corte.value.id,
    nota: nota,
  }, {
    preserveScroll: true,
    onSuccess(e) {
      if (e.props.flash.error) {
        showToast("error", e.props.flash.error || "Error al cerrar el corte")
      } else {
        showToast("success", "Corte cerrado correctamente")
        showModalCerrar.value = false
        corte.value = e.props?.corte
        ventas.value = e.props?.ventas
        registrosCaja.value = e.props?.logscaja
      }
      //obtener Logs y ventas
      actualizarDatos()
    },
    onError(e) {
      showToast("error", e.error || "Error al cerrar el corte")
    }
  })
}

const generarReportePDF = () => {
  try {
    isLoading.value = true;
    
    // Crear nuevo documento PDF con orientación vertical
    const doc = new jsPDF({
      orientation: 'portrait',
      unit: 'mm',
      format: 'a4'
    });
    
    // Configurar el documento
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(20);
    doc.text('Reporte de Corte de Caja', doc.internal.pageSize.width / 2, 20, { align: 'center' });
    
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(12);
    doc.text(`Sucursal: ${props.auth.user.sucursal_id}`, 20, 30);
    doc.text(`Fecha: ${selectedValue.value}`, 20, 40);
    
    // Agregar resumen financiero
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(14);
    doc.text('Resumen Financiero', 20, 50);
    
    const resumenData = [
      ['Concepto', 'Monto'],
      ['Saldo Inicial', `$${safeToFixed(initialCash.value)}`],
      ['Ventas en Efectivo', `$${safeToFixed(cashPayments.value - initialCash.value)}`],
      ['Ventas con Tarjeta', `$${safeToFixed(cardPayments.value - finalCash.value)}`],
      ['Total Ventas', `$${safeToFixed(Number(cashPayments.value) + Number(cardPayments.value) - Number(initialCash.value) - Number(finalCash.value))}`],
      ['Dinero Disponible', `$${safeToFixed(dineroDisponible.value)}`]
    ];
    
    autoTable(doc, {
      startY: 55,
      head: [resumenData[0]],
      body: resumenData.slice(1),
      theme: 'grid',
      headStyles: { 
        fillColor: [41, 128, 185],
        textColor: [255, 255, 255],
        fontStyle: 'bold'
      },
      styles: {
        fontSize: 10,
        cellPadding: 3
      },
      margin: { left: 20 }
    });
    
    // Agregar registros de caja
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(14);
    doc.text('Registros de Caja', 20, doc.lastAutoTable.finalY + 20);
    
    const registrosData = registrosCaja.value.map(registro => [
      registro.created_at.split('T')[0],
      registro.created_at.split('T')[1].split('.')[0],
      registro.tipo,
      registro.descripcion || '-',
      `$${safeToFixed(registro.cantidad)}`
    ]);
    
    autoTable(doc, {
      startY: doc.lastAutoTable.finalY + 25,
      head: [['Fecha', 'Hora', 'Tipo', 'Descripción', 'Monto']],
      body: registrosData,
      theme: 'grid',
      headStyles: { 
        fillColor: [41, 128, 185],
        textColor: [255, 255, 255],
        fontStyle: 'bold'
      },
      styles: {
        fontSize: 10,
        cellPadding: 3
      },
      margin: { left: 20 },
      columnStyles: {
        0: { cellWidth: 25 },
        1: { cellWidth: 20 },
        2: { cellWidth: 25 },
        3: { cellWidth: 50 },
        4: { cellWidth: 25 }
      }
    });
    
    // Agregar ventas
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(14);
    doc.text('Ventas', 20, doc.lastAutoTable.finalY + 20);
    
    const ventasData = ventas.value.map(venta => [
      venta.id,
      venta.created_at.split('T')[0],
      venta.created_at.split('T')[1].split('.')[0],
      venta.mesa ? venta.mesa.nombre : 'Para llevar',
      venta.metodo_pago === 'cash' ? 'Efectivo' : 
      venta.metodo_pago === 'card' ? 'Tarjeta' : 
      venta.metodo_pago === 'transfer' ? 'Transferencia' : venta.metodo_pago,
      `$${safeToFixed(venta.total)}`
    ]);
    
    autoTable(doc, {
      startY: doc.lastAutoTable.finalY + 25,
      head: [['ID', 'Fecha', 'Hora', 'Mesa', 'Método de Pago', 'Total']],
      body: ventasData,
      theme: 'grid',
      headStyles: { 
        fillColor: [41, 128, 185],
        textColor: [255, 255, 255],
        fontStyle: 'bold'
      },
      styles: {
        fontSize: 10,
        cellPadding: 3
      },
      margin: { left: 20 },
      columnStyles: {
        0: { cellWidth: 15 },
        1: { cellWidth: 25 },
        2: { cellWidth: 20 },
        3: { cellWidth: 30 },
        4: { cellWidth: 30 },
        5: { cellWidth: 25 }
      }
    });
    
    // Agregar pie de página
    const pageCount = doc.internal.getNumberOfPages();
    for (let i = 1; i <= pageCount; i++) {
      doc.setPage(i);
      doc.setFont('helvetica', 'italic');
      doc.setFontSize(8);
      doc.text(
        `Página ${i} de ${pageCount}`,
        doc.internal.pageSize.width / 2,
        doc.internal.pageSize.height - 10,
        { align: 'center' }
      );
    }
    
    // Guardar el PDF
    doc.save(`corte-caja-${selectedValue.value}.pdf`);
    showToast('success', 'Reporte PDF generado correctamente');
  } catch (error) {
    console.error('Error al generar el PDF:', error);
    showToast('error', 'Error al generar el reporte PDF');
  } finally {
    isLoading.value = false;
  }
};

const generarReporteExcel = () => {
  try {
    isLoading.value = true;
    
    // Preparar datos para Excel
    const ventasData = ventas.value.map(venta => ({
      'ID': venta.id,
      'Fecha': venta.created_at.split('T')[0],
      'Hora': venta.created_at.split('T')[1].split('.')[0],
      'Mesa': venta.mesa ? venta.mesa.nombre : 'Para llevar',
      'Cliente': venta.nombre_cliente || 'Sin nombre',
      'Método de Pago': venta.metodo_pago === 'cash' ? 'Efectivo' : 
                       venta.metodo_pago === 'card' ? 'Tarjeta' : 
                       venta.metodo_pago === 'transfer' ? 'Transferencia' : venta.metodo_pago,
      'Descuento': venta.descuento || 0,
      'Propina': venta.propina || 0,
      'Total': venta.total
    }));
    
    // Crear hoja de cálculo
    const ws = XLSX.utils.json_to_sheet(ventasData);
    
    // Ajustar ancho de columnas
    const wscols = [
      {wch: 6},  // ID
      {wch: 12}, // Fecha
      {wch: 10}, // Hora
      {wch: 15}, // Mesa
      {wch: 20}, // Cliente
      {wch: 15}, // Método de Pago
      {wch: 12}, // Descuento
      {wch: 12}, // Propina
      {wch: 12}  // Total
    ];
    ws['!cols'] = wscols;
    
    // Crear libro
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Ventas');
    
    // Generar archivo Excel
    XLSX.writeFile(wb, `ventas-${selectedValue.value}.xlsx`);
    showToast('success', 'Reporte Excel generado correctamente');
  } catch (error) {
    console.error('Error al generar el Excel:', error);
    showToast('error', 'Error al generar el reporte Excel');
  } finally {
    isLoading.value = false;
  }
};

// Agregar las variables necesarias
const existentInitialCash = ref(props?.corte?.saldo_inicial ? 0 : 1)
const existentFinalCash = ref(props?.corte?.saldo_final ? 0 : 1)
const isToday = computed(() => {
  const today = new Date()
  const formattedToday = today.toISOString().split('T')[0]
  return selectedFilter.value === 'day' && selectedValue.value === formattedToday
})

</script>

<style scoped>
.table-striped tbody tr:nth-of-type(odd) {
  background-color: rgba(0, 0, 0, 0.05);
}
</style>
