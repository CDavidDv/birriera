<template>
  <div class="min-h-screen md:p-6 w-full p-0">
    <div class="w-full  mx-auto">
      <div class="flex justify-between w-full mx-auto items-center mb-6">
        <h1 class="text-3xl font-bold">Área de Mesas</h1>
        <div class="flex gap-2">
          <button
            class="px-2 rounded-lg text-lg bg-purple-500 hover:bg-purple-600 text-white transition-colors" 
            @click="startOrderDelivery"
          >
            Crear pedido para llevar
          </button>
          <button
            v-if="props.user.roles[0] === 'admin'" 
            @click="openSettingsModal"
            class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 transition-colors"
            title="Configurar mesas"
          >
            <Settings class="w-6 h-6"  />
          </button>
        </div>
      </div>
      
      <div class="flex flex-col lg:flex-row gap-6 w-full">
        <!-- Plano del Restaurante -->
        <div class="flex-1 bg-white rounded-lg shadow-md p-6 ">
          <h2 class="text-xl font-semibold mb-4">Plano del Restaurante</h2>
          <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            <button 
              v-for="table in tables" 
              :key="table?.id"
              @click="selectTable(table)"
              :class="[
                'p-4 rounded-lg flex flex-col justify-center items-center place-items-center  transition-colors',
                table.estado === 'libre' ? 'bg-green-400 hover:bg-green-200' : 
                table.estado === 'ocupada' ? 'bg-red-400 hover:bg-red-200' : 
                table.estado === 'espera_pago' ? 'bg-blue-400 hover:bg-blue-200' :
                table.estado === 'espera_entrega' ? 'bg-gray-400 hover:bg-gray-200' :
                table.estado === 'para_llevar' ? 'bg-orange-400 hover:bg-orange-200' :

                'bg-yellow-400 hover:bg-yellow-200'
              ]"
            >
              <component :is="getTableIcon(table.estado)" class="w-8 h-8 mx-auto mb-2" />
              
              <span class="font-medium text-2xl">{{ table?.nombre || `Mesa ${table.id}` }}</span>
              <span class="text-center flex justify-center items-center place-items-center text-lg font-bold text-gray-900 bg-gray-50 size-fit p-1 rounded-lg">{{ getStatusText(table.estado) }}</span>
            </button>
          </div>
        </div>

        <!-- Panel Lateral -->
        <div class="w-full lg:w-80">
          <div class="bg-white text-xl font-semibold rounded-lg shadow-md p-6 mb-6">
            <h2 class=" mb-4">Resumen de Mesas</h2>
            <div class="space-y-2">
              <div class="flex justify-between items-center">
                <span class="flex items-center">
                  <CircleIcon class=" text-green-500 bg-green-500 rounded-full mr-1 " />
                  Libres
                </span>
                <span class="font-medium">{{ freeTables }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="flex items-center">
                  <CircleIcon class="text-red-500 bg-red-500 rounded-full mr-1" />
                  Ocupadas
                </span>
                <span class="font-medium">{{ ocupadaTables }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="flex items-center">
                  <CircleIcon class=" text-yellow-500 bg-yellow-500 rounded-full mr-1" />
                  Con Pedido
                </span>
                <span class="font-medium">{{ tablesWithOrders }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="flex items-center">
                  <CircleIcon class=" text-blue-500 bg-blue-500 rounded-full mr-1" />
                  Pagando cuenta
                </span>
                <span class="font-medium">{{ espera_pago }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="flex items-center">
                  <CircleIcon class=" text-gray-500 bg-gray-500 rounded-full mr-1" />
                  Pendiente de entregar
                </span>
                <span class="font-medium">{{ MesasParaEntregar }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="flex items-center">
                  <CircleIcon class=" text-orange-500 bg-orange-500 rounded-full mr-1" />
                  Para llevar
                </span>
                <span class="font-medium">{{ MesasParaLlevar }}</span>
              </div>
            </div>
          </div>
          
          <!--modal para ver las opciones-->
          <div v-if="showTableOptions"  class="fixed inset-0 max-h-screen px-2 sm:px-0 z-50 bg-black bg-opacity-50 flex justify-center items-center">
            
            <div class="bg-white rounded-lg shadow-md p-6 sm:max-h-[80vh] max-h-[50vh]  overflow-y-auto">

              <div v-if="selectedTable && selectedTable.nombre" class="flex justify-between  items-center">
                <h2 class="text-xl font-semibold mb-4">{{ `Acciones ${selectedTable.nombre}` }}</h2>
                <button @click="showTableOptions = false" class="mb-6">
                  <XIcon />
                </button>
              </div>
              <div class="space-y-6">
                <button 
                  v-if="selectedTable && selectedTable.estado === 'libre'"
                  @click="startNewOrder"
                  :disabled="!selectedTable"
                  class="w-full py-5 px-20 bg-blue-500 text-white rounded-lg font-medium disabled:bg-gray-300 disabled:cursor-not-allowed hover:bg-blue-600 transition-colors"
                >
                  Iniciar Nuevo Pedido
                </button>
                <button 
                  v-if="selectedTable && selectedTable.estado !== 'libre'"
                  @click="continueOrder"
                  class="w-full py-5 text-2xl px-4 bg-green-500 text-white rounded-lg font-medium hover:bg-green-600 transition-colors"
                >
                  Añadir productos
                </button>
                <button 
                  v-if="selectedTable && selectedTable.estado !== 'libre'"
                  @click="substracItem"
                  class="w-full py-5 text-2xl px-4 bg-amber-400 text-white rounded-lg font-medium hover:bg-amber-500 transition-colors"
                >
                  Ver/Quitar productos
                </button>
                <button 
                  v-if="selectedTable && selectedTable.estado !== 'libre'"
                  @click="resumeTicket"
                  class="w-full py-5 text-2xl px-4 bg-slate-400 text-white rounded-lg font-medium hover:bg-slate-500 transition-colors"
                >
                  Resumen del pedido
                </button>
                <div v-if="selectedTable && selectedTable.estado !== 'libre'" class="space-y-2">
                  <button 
                    @click="openTableChangeModal"
                    :disabled="!selectedTable || selectedTable.estado === 'libre'"
                    class="w-full py-5 text-2xl px-4 bg-blue-500 text-white rounded-lg font-medium disabled:bg-gray-300 hover:bg-blue-600 transition-colors"
                  >
                    Cambiar a otra mesa
                  </button>
                  
                  <div class="w-full h-44 flex items-center" v-if="selectedTable && selectedTable.estado !== 'libre'">
                    <div class="w-full border"></div>
                  </div>
                  <button 
                    @click="enviaraCaja"
                    class="w-full py-5 text-2xl px-4 bg-yellow-500 text-white rounded-lg font-medium hover:bg-yellow-600 transition-colors"
                  >
                    Enviar a caja
                  </button>
                </div>
                <div class="w-full h-16 flex items-center" v-if="selectedTable && selectedTable.estado !== 'libre'">
                  <div class="w-full border"></div>
                </div>
                <button
                  v-if="selectedTable && selectedTable.estado !== 'libre'"
                  @click="cancelOrdenMesa"
                  class="w-full py-5 text-2xl px-4 bg-red-500 text-white rounded-lg font-medium hover:bg-red-600 transition-colors"
                >
                  Cancelar pedido
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <SectionOrdersDelivery
        :ordenesParaLlevar="ordenesParaLlevar"
        @add="agregarProductos"
        @subs="substracItemDeliver"
        @cancel="cancelOrden"
        :ordenParaLlevar="ordenParaLlevar"
      />
    </div>

    <!-- Modal para Cambiar de Mesa -->
    <ModalChangeMesa 
      v-if="showTableChangeModal"
      :showTableChangeModal="showTableChangeModal"
      :tables="tables"
      @change="changeTable"
      @close="closeTableChangeModal"
    />
    
    <ModalPedido
      v-if="showOrderModal"
      :orden="filtrarOrden()"
      :table="selectedTable"
      :reConsumo="reConsumo"
      :para_llevar="para_llevar"
      :delivery="delivery"
      @close="closeOrderModal"
      @submit="submitOrder" 
      :ordenParaLlevar="ordenParaLlevar"
    />

    <ModalResumeOrder
      v-if="showResumeOrderModal"
      :orden="filtrarOrden()"
      :table="selectedTable"
      @close="showResumeOrderModal = false"
    />

    <ModalQuitarProductos
      v-if="showSubstracOrderModal"
      :orden="filtrarOrdenPendiente()"
      :table="selectedTable"
      :reConsumo="reConsumo"
      :para_llevar="para_llevar"
      :delivery="delivery"
      @close="closeSubstracOrderModal"
      @submit="submitOrder" 
    />


    <ModalConfigMesas
      v-if="showSettingsModal"
      :tables="tables"
      @close="closeSettingsModal"
      @update="changeTable"
    />
    
    
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { CircleIcon, UtensilsCrossedIcon, UsersIcon, ClipboardListIcon, Settings, X, Trash2, CircleDotIcon, CircleDot, CircleCheckBig, Dot, DotIcon, Timer, XIcon } from 'lucide-vue-next';
import { router, usePage } from '@inertiajs/vue3';
import ModalConfigMesas from './ModalConfigMesas.vue';
import ModalPedido from './ModalPedido.vue';
import Swal from 'sweetalert2';
import PayIcon from '../Icons/PayIcon.vue';
import ShopingCarIcon from '../Icons/ShopingCarIcon.vue';
import SectionOrdersDelivery from './SectionOrdersDelivery.vue';
import ModalChangeMesa from './ModalChangeMesa.vue';
import ModalQuitarProductos from './ModalQuitarProductos.vue';

const { props } = usePage()

const tables = ref(
  props.mesas.sort((a, b) => a.posicion - b.posicion)
);

const ordenesParaLlevar = ref(
  props.ordenesParaLlevar || []
);

const showTableOptions = ref(false)
const showOrderModal = ref(false)
const showSubstracOrderModal = ref(false)
const ordenes = ref(props.ordenes || [])
const showResumeOrderModal = ref(false)

const filtrarOrden = () => {
  return ordenes.value.find((orden) => orden?.mesa_id === selectedTable?.value?.id ) 
  || ordenesParaLlevar.value.find((orden) => orden.id == ordenParaLlevar.value) || []
}


const filtrarOrdenPendiente = () => {
  return ordenes.value.find((orden) => orden?.mesa_id === selectedTable?.value?.id) || 
  ordenesParaLlevar.value.find((orden) => orden.id == ordenParaLlevar.value) || []
}

const selectedTable = ref(null)
const showSettingsModal = ref(false)
const showTableChangeModal = ref(false);

const reConsumo = ref(false)

const freeTables = computed(() => tables.value?.filter(table => table?.estado === 'libre').length)
const ocupadaTables = computed(() => tables.value?.filter(table => table?.estado === 'ocupada').length)
const tablesWithOrders = computed(() => tables.value?.filter(table => table?.estado === 'pendiente').length)
const espera_pago = computed(() => tables.value?.filter(table => table?.estado === 'espera_pago').length)
const MesasParaEntregar = computed(() => tables.value?.filter(table => table?.estado === 'espera_entrega').length)
const MesasParaLlevar = computed(() => tables.value?.filter(table => table?.estado === 'para_llevar').length)


const closeSubstracOrderModal = () => {
  showSubstracOrderModal.value = false
}

// Funciones para modales
const openTableChangeModal = () => {
  showTableChangeModal.value = true;
};

const closeTableChangeModal = () => {
  showTableChangeModal.value = false;
};

const changeTable = (newTable) => {
  if (selectedTable.value && newTable.estado === 'libre') {
    // Transferir estado y pedidos de la mesa actual a la nueva
    newTable.estado = selectedTable.value.estado;
    newTable.pedidos = selectedTable.value.pedidos || [];
    selectedTable.value.estado = 'libre'; // Liberar la mesa anterior
    selectedTable.value.pedidos = []; // Limpiar los pedidos
    

    router.put('/mesaUpdate', {
        mesa: selectedTable.value.id,
        nuevaMesa: newTable.id,
    },
    {
      onSuccess: (response) => {
        
      },
      onerror: (error) => {
        console.error(error);
      },
        preserveScroll: true,
        preserveState: false
      }
      
    )
    selectedTable.value = newTable; // Seleccionar la nueva mesa
    closeTableChangeModal();
  } else {
    //console.error('No se puede cambiar a esta mesa');
  }
};

const getTableIcon = (status) => {
  switch (status) {
    case 'libre': return UtensilsCrossedIcon
    case 'ocupada': return UsersIcon
    case 'pendiente': return ClipboardListIcon
    case 'espera_entrega': return Timer
    case 'espera_pago': return PayIcon
    case 'para_llevar': return ShopingCarIcon
    default: return CircleIcon
  }
}

const getStatusText = (status) => {
  switch (status) {
    case 'libre': return 'Libre'
    case 'ocupada': return 'Ocupada'
    case 'pendiente': return 'Pedido en curso'
    case 'espera_entrega': return 'Esperando entregar'
    case 'espera_pago': return 'Pagando cuenta'
    case 'para_llevar': return 'Para llevar'
    default: return 'Desconocido'
  }
}

const selectTable = (table) => {
  selectedTable.value = table
  showTableOptions.value = true
}   

const para_llevar = ref(false);
const delivery = ref(false);

const startOrderDelivery = () => {
  selectedTable.value = null
  reConsumo.value = false
  para_llevar.value = true
  delivery.value = true
  showOrderModal.value = true
}

const startNewOrder = () => {
  if (selectedTable.value) {
    para_llevar.value = false
    delivery.value = false
    reConsumo.value = false
    showOrderModal.value = true
  } else {
    console.error('No hay mesa seleccionada')
  }
}

const continueOrder = () => {
  if (selectedTable.value) {
    para_llevar.value = false
    reConsumo.value = true
    showOrderModal.value = true
  } else {
    console.error('No hay mesa seleccionada')
  }
}

const substracItem = () => {
  if (selectedTable.value) {
    para_llevar.value = false
    reConsumo.value = true
    showSubstracOrderModal.value = true
  } else {
    console.error('No hay mesa seleccionada')
  }
} 
const resumeTicket = () => {
  if (selectedTable.value) {
    para_llevar.value = false
    reConsumo.value = false
    showResumeOrderModal.value = true
  }
} 

const substracItemDeliver = (data) => {
  ordenParaLlevar.value = data
  selectedTable.value = null
  para_llevar.value = false
  reConsumo.value = true
  showSubstracOrderModal.value = true
}



const ordenParaLlevar = ref(null)

const agregarProductos = (id) => {
  ordenParaLlevar.value = id
  selectedTable.value = null
  para_llevar.value = true
  reConsumo.value = true
  showOrderModal.value = true
}

const cancelOrdenMesa = () => {
  if (selectedTable.value) {
    const id = selectedTable.value.id
    cancelOrden(id, true)
  } else {
    console.error('No hay mesa seleccionada')
  }
}

const cancelOrden = (id, mesa= false) => {
  Swal.fire({
    title: '¿Estás seguro?',
    text: 'Esta acción no se puede deshacer',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Sí, cancelar',
    cancelButtonText: 'No, mantener'
  }).then((result) => {
    if (result.isConfirmed) {
        router.post('/cancelOrden', {id , mesa},{
          onSuccess: (response) => {
            showTableOptions.value = false
          tables.value = response.props.mesas
        },
        onerror: (error) => {
          console.error(error);
          showToast('error', 'Error al cancelar la orden')
        },
          preserveScroll: true,
          preserveState: false
        }
      )
    }
  })
}

const closeOrderModal = () => {
  showOrderModal.value = false
}


const submitOrder = () => {
  if (selectedTable.value) {
    selectedTable.value.estado = 'pendiente'
    showOrderModal.value = false
    selectedTable.value = null
  }
}



const enviaraCaja = () => {
  if (selectedTable.value) {
    router.post('/enviaraCaja', {
      id: selectedTable.value.id
    },
    {
      onSuccess: (response) => {
        tables.value = response.props.mesas
      },
      onerror: (error) => {
        console.error(error);
        showToast('error', 'Error al enviar la mesa a caja')
      },
        preserveScroll: true,
        preserveState: false
      }
    )
  }
}

const paraLlevar = () => {
  if (selectedTable.value) {
    router.post('/paraLlevar', {
      id: selectedTable.value.id
    },
    {
      onSuccess: (response) => {
        tables.value = response.props.mesas
      },
      onerror: (error) => {
        console.error(error);
        showToast('error', 'Error al enviar la mesa para llevar')
      },
        preserveScroll: true,
        preserveState: false
      }
    )
  }
}

const updateTables = (mesas) => {
  tables.value = props.mesas
}

// Nuevas funciones para la gestión de mesas
const openSettingsModal = () => {
  showSettingsModal.value = true
}

const closeSettingsModal = (mesas) => {
  showSettingsModal.value = false
  tables.value = props.mesas
}

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 1500,
    timerProgressBar: true,
    didOpen: toast => {
        toast.onmouseenter = Swal.stopTimer;
        toast.onmouseleave = Swal.resumeTimer;
    },
});

function showToast(type, message) {
    Toast.fire({ icon: type, title: message });
}

import Echo from 'laravel-echo';

import Pusher from 'pusher-js';

import axios from 'axios';
import ModalResumeOrder from './ModalResumeOrder.vue';
window.Pusher = Pusher;

//pusher
// Asegúrate de salir de canales innecesarios antes de suscribirte a uno nuevo
const sucursalId = props.auth.user.sucursal_id;


window.Echo.leaveChannel(`pedidos_sucursal_${sucursalId}`);

// Escuchar en el canal privado de la sucursal actual
window.Echo.channel(`pedidos_sucursal_${sucursalId}`)
  .listen('.crear-pedido', (data) => {

    // Obtener el pedido desde el servidor
    axios.get(`/getPedido/${data.id}`)
      .then(response => {
        const pedido = response.data;


        // Actualizar el estado de las mesas
        if (pedido.mesa_id) {
            tables.value.forEach((mesa) => {
                if (mesa.id === pedido.mesa_id) {
                    mesa.estado = 'pendiente';
                }
            });
        }

        // Agregar el pedido si no existe en las órdenes
        const ordenExistente = ordenes.value.find((orden) => orden?.id === pedido.id);
        const ordenExistenteParaLlevar = ordenesParaLlevar.value.find((orden) => orden?.id === pedido.id);
        
        if (!data.mesa_id && !ordenExistenteParaLlevar) {
            ordenesParaLlevar.value.push(pedido);
        } else if (data.mesa_id && !ordenExistente) {
            ordenes.value.push(pedido);
            showToast('success', 'Nuevo pedido recibido');
        }
      })
      .catch(error => {
        console.error('Error al obtener el pedido:', error);
      });
  })
  .listen('.add-pedido', (data) => {

      // Realizar una solicitud para obtener los datos del pedido desde el servidor
      axios.get(`/getPedido/${data.id}`)
          .then(response => {
              const pedido = response.data;
              // Buscar si el pedido ya existe en las listas
              const ordenExistenteParaLlevar = ordenesParaLlevar.value.find((orden) => orden?.id === pedido.id);
              const ordenExistente = ordenes.value.find((orden) => orden?.id === pedido.id);

              if (!pedido.mesa_id) {
                  if (ordenExistenteParaLlevar) {
                      // Si ya existe en pedidos para llevar, actualizarlo
                      Object.assign(ordenExistenteParaLlevar, pedido);
                      showToast('success', 'Pedido para llevar actualizado');
                  } else {
                      // Si no existe, agregarlo a pedidos para llevar
                      ordenesParaLlevar.value.push(pedido);
                      showToast('success', 'Nuevo pedido para llevar añadido');
                  }
              } else {
                  if (ordenExistente) {
                      // Si ya existe en pedidos en mesa, actualizarlo
                      Object.assign(ordenExistente, pedido);
                      showToast('success', 'Pedido en mesa actualizado');
                  } else {
                      // Si no existe, agregarlo a pedidos en mesa
                      ordenes.value.push(pedido);
                      showToast('success', 'Nuevo pedido en mesa añadido');
                  }
              }
          })
          .catch(error => {
              // Manejo de errores en caso de que la solicitud falle
              console.error('Error al obtener el pedido:', error);
          });
  })
  .listen('.substrack-pedido', (data) => {

      axios.get(`/getPedido/${data.id}`)
      .then(response => {
              const pedido = response.data;


              // Buscar si el pedido ya existe en las listas
              const ordenExistenteParaLlevar = ordenesParaLlevar.value.find((orden) => orden?.id === pedido.id);
              const ordenExistente = ordenes.value.find((orden) => orden?.id === pedido.id);

              if (!pedido.mesa_id) {
                  if (ordenExistenteParaLlevar) {
                      // Si ya existe en pedidos para llevar, actualizarlo
                      Object.assign(ordenExistenteParaLlevar, pedido);
                      showToast('success', 'Pedido para llevar actualizado');
                  } else {
                      // Si no existe, agregarlo a pedidos para llevar
                      ordenesParaLlevar.value.push(pedido);
                      showToast('success', 'Nuevo pedido para llevar añadido');
                  }
              } else {
                  if (ordenExistente) {
                      // Si ya existe en pedidos en mesa, actualizarlo
                      Object.assign(ordenExistente, pedido);
                      showToast('success', 'Pedido en mesa actualizado');
                  } else {
                      // Si no existe, agregarlo a pedidos en mesa
                      ordenes.value.push(pedido);
                      showToast('success', 'Nuevo pedido en mesa añadido');
                  }
              }
          })
          .catch(error => {
              // Manejo de errores en caso de que la solicitud falle
              console.error('Error al obtener el pedido:', error);
          });
  })
  .listen('.entregar-pedido', (data) => {

        // Realizar una solicitud para obtener los datos del pedido desde el servidor
        axios.get(`/getPedido/${data.id}`)
      .then(response => {
              const pedido = response.data;

              if (pedido.mesa_id) {
                  tables.value.forEach((mesa) => {
                      if (mesa.id === pedido.mesa_id) {
                          mesa.estado = 'ocupada';
                      }
                  });
              }


              // Buscar si el pedido ya existe en las listas
              const ordenExistenteParaLlevar = ordenesParaLlevar.value.find((orden) => orden?.id === pedido.id);
              const ordenExistente = ordenes.value.find((orden) => orden?.id === pedido.id);

              if (!pedido.mesa_id) {
                  if (ordenExistenteParaLlevar) {
                      // Si ya existe en pedidos para llevar, actualizarlo
                      Object.assign(ordenExistenteParaLlevar, pedido);
                      showToast('success', 'Pedido para llevar actualizado');
                  } else {
                      // Si no existe, agregarlo a pedidos para llevar
                      ordenesParaLlevar.value.push(pedido);
                      showToast('success', 'Nuevo pedido para llevar añadido');
                  }
              } else {
                  if (ordenExistente) {
                      // Si ya existe en pedidos en mesa, actualizarlo
                      Object.assign(ordenExistente, pedido);
                      showToast('success', 'Pedido en mesa actualizado');
                  } else {
                      // Si no existe, agregarlo a pedidos en mesa
                      ordenes.value.push(pedido);
                      showToast('success', 'Nuevo pedido en mesa añadido');
                  }
              }
          })
          .catch(error => {
              // Manejo de errores en caso de que la solicitud falle
              console.error('Error al obtener el pedido:', error);
          });
  })
  .listen('.cancelar-pedido', (data) => {
    tables.value.forEach((mesa) => {
      if (mesa?.id === data?.pedido?.mesa_id) {
        mesa.estado = 'libre';
      }
    });

    if(data.mesa === null){
      //para llevar
      ordenesParaLlevar.value = ordenesParaLlevar.value.filter((orden) => orden?.id !== data.pedido.id);
      showToast('info', 'Pedido cancelado')
    }else{
      const ordenExistente = ordenes.value.find((orden) => orden?.id === data.pedido.id);
      if (ordenExistente) {
        ordenes.value = ordenes.value.filter((orden) => orden?.id !== data.pedido.id);
        showToast('info', 'Pedido cancelado')
      }
    }
    
  })
  .listen('.cocinar-pedido', (data) => {

      axios.get(`/getPedido/${data.id}`)
      .then(response => {
              const pedido = response.data;

              if (pedido.mesa_id) {
                  tables.value.forEach((mesa) => {
                      if (mesa.id === pedido.mesa_id) {
                          mesa.estado = 'espera_entrega';
                      }
                  });
              }
              // Buscar si el pedido ya existe en las listas
              const ordenExistenteParaLlevar = ordenesParaLlevar.value.find((orden) => orden?.id === pedido.id);
              const ordenExistente = ordenes.value.find((orden) => orden?.id === pedido.id);

              if (!pedido.mesa_id) {
                  if (ordenExistenteParaLlevar) {
                      // Si ya existe en pedidos para llevar, actualizarlo
                      Object.assign(ordenExistenteParaLlevar, pedido);
                      showToast('success', 'Pedido para llevar actualizado');
                  } else {
                      // Si no existe, agregarlo a pedidos para llevar
                      ordenesParaLlevar.value.push(pedido);
                      showToast('success', 'Nuevo pedido para llevar añadido');
                  }
              } else {
                  if (ordenExistente) {
                      // Si ya existe en pedidos en mesa, actualizarlo
                      Object.assign(ordenExistente, pedido);
                      showToast('success', 'Pedido en mesa actualizado');
                  } else {
                      // Si no existe, agregarlo a pedidos en mesa
                      ordenes.value.push(pedido);
                      showToast('success', 'Nuevo pedido en mesa añadido');
                  }
              }
          })
          .catch(error => {
              // Manejo de errores en caso de que la solicitud falle
              console.error('Error al obtener el pedido:', error);
          });
  })
  .listen('.update-mesas', (data) => {
      // data.mesaNew -> ID de la nueva mesa
      // data.mesaOld -> Objeto con información de la mesa anterior


      // Actualizar el estado de las mesas
      tables.value.forEach((mesa) => {
          if (mesa?.id === data.mesaNew?.id) {
              mesa.estado = data.mesaOld?.estado; // Asignar el estado de la mesa anterior
          }
          if (mesa?.id === data.mesaOld.id) {
              mesa.estado = 'libre'; // Liberar la mesa anterior
          }
      });

      // Buscar la orden asociada a la mesa anterior
      const ordenExistente = ordenes.value.find((orden) => orden?.mesa_id === data.mesaOld.id);

      if (ordenExistente) {
          // Actualizar la mesa_id de la orden a la nueva mesa
          ordenExistente.mesa_id = data.mesaNew.id;

          // Mostrar notificación de éxito
          showToast('success', `Pedido actualizado a la nueva mesa: ${data.mesaNew.nombre}`);
      } else {
          console.warn('No se encontró una orden para la mesa anterior:', data.mesaOld.id);
      }

  })
  .listen('.enviar-caja-pedido', (data) => {

    // Realizar una solicitud para obtener los datos del pedido desde el servidor
    axios.get(`/getPedido/${data.id}`)
    .then(response => {
          const pedido = response.data;

          // Buscar si el pedido ya existe en las listas
          const ordenExistenteParaLlevar = ordenesParaLlevar.value.find((orden) => orden?.id === pedido.id);
          const ordenExistente = ordenes.value.find((orden) => orden?.id === pedido.id);


          if (!pedido.mesa_id) {
              if (ordenExistenteParaLlevar) {
                  // Si ya existe en pedidos para llevar, actualizarlo
                  Object.assign(ordenExistenteParaLlevar, pedido);
                  showToast('success', 'Pedido para llevar actualizado');
              } else {
                  // Si no existe, agregarlo a pedidos para llevar
                  ordenesParaLlevar.value.push(pedido);
                  showToast('success', 'Nuevo pedido para llevar añadido');
              }
          } else {
              if (ordenExistente) {
                  // Si ya existe en pedidos en mesa, actualizarlo
                  Object.assign(ordenExistente, pedido);
                  showToast('success', 'Pedido en mesa actualizado');
                  if (pedido.mesa_id) {
                      tables.value.forEach((mesa) => {
                          if (mesa.id === pedido.mesa.id) {
                              mesa.estado = pedido.mesa.estado;
                          }
                      });
                  }
              } else {
                  if (pedido?.mesa_id) {
                    console.log('Pedido recibido: añadir', pedido);
                      tables.value.forEach((mesa) => {
                          if (mesa.id === pedido?.mesa?.id) {
                            mesa.estado = pedido?.mesa?.estado;
                          }
                      });
                  }
                  // Si no existe, agregarlo a pedidos en mesa
                  ordenes.value.push(pedido);
                  showToast('success', 'Nuevo pedido en mesa añadido');
              }
              
          }
      })
      .catch(error => {
          // Manejo de errores en caso de que la solicitud falle
          console.error('Error al obtener el pedido:', error);
      });
  })
  .listen('.para-llevar-pedido', (data) => {

    // Realizar una solicitud para obtener los datos del pedido desde el servidor
    axios.get(`/getPedido/${data.id}`)
    .then(response => {
          const pedido = response.data;

          // Buscar si el pedido ya existe en las listas
          const ordenExistenteParaLlevar = ordenesParaLlevar.value.find((orden) => orden?.id === pedido.id);
          const ordenExistente = ordenes.value.find((orden) => orden?.id === pedido.id);


          if (!pedido.mesa_id) {
              if (ordenExistenteParaLlevar) {
                  // Si ya existe en pedidos para llevar, actualizarlo
                  Object.assign(ordenExistenteParaLlevar, pedido);
                  showToast('success', 'Pedido para llevar actualizado');
              } else {
                  // Si no existe, agregarlo a pedidos para llevar
                  ordenesParaLlevar.value.push(pedido);
                  showToast('success', 'Nuevo pedido para llevar añadido');
              }
          } else {
              if (ordenExistente) {
                  // Si ya existe en pedidos en mesa, actualizarlo
                  Object.assign(ordenExistente, pedido);
                  showToast('success', 'Pedido en mesa actualizado');
                  if (pedido.mesa_id) {
                      tables.value.forEach((mesa) => {
                          if (mesa.id === pedido.mesa.id) {
                              mesa.estado = pedido.mesa.estado;
                          }
                      });
                  }
              } else {
                  if (pedido?.mesa_id) {
                    console.log('Pedido recibido: añadir', pedido);
                      tables.value.forEach((mesa) => {
                          if (mesa.id === pedido?.mesa?.id) {
                            mesa.estado = pedido?.mesa?.estado;
                          }
                      });
                  }
                  // Si no existe, agregarlo a pedidos en mesa
                  ordenes.value.push(pedido);
                  showToast('success', 'Nuevo pedido en mesa añadido');
              }
              
          }
      })
      .catch(error => {
          // Manejo de errores en caso de que la solicitud falle
          console.error('Error al obtener el pedido:', error);
      });
  })
  .listen('.finalizar-pedido', (data) => {

      // Realizar una solicitud para obtener los datos del pedido desde el servidor
      axios.get(`/getPedido/${data.id}`)
      .then(response => {
            const pedido = response.data;

            // Buscar si el pedido ya existe en las listas
            const ordenExistenteParaLlevar = ordenesParaLlevar.value.find((orden) => orden?.id === pedido.id);
            const ordenExistente = ordenes.value.find((orden) => orden?.id === pedido.id);


            if (!pedido.mesa_id) {
                if (ordenExistenteParaLlevar) {
                    // Si ya existe en pedidos para llevar, actualizarlo
                    Object.assign(ordenExistenteParaLlevar, pedido);
                    showToast('success', 'Pedido para llevar actualizado');
                } else {
                    // Si no existe, agregarlo a pedidos para llevar
                    ordenesParaLlevar.value.push(pedido);
                    showToast('success', 'Nuevo pedido para llevar añadido');
                }
            } else {
                if (ordenExistente) {
                    // Si ya existe en pedidos en mesa, actualizarlo
                    Object.assign(ordenExistente, pedido);
                    showToast('success', 'Pedido en mesa actualizado');
                    if (pedido.mesa_id) {
                        tables.value.forEach((mesa) => {
                            if (mesa.id === pedido.mesa.id) {
                                mesa.estado = pedido.mesa.estado;
                            }
                        });
                    }
                } else {
                    if (pedido?.mesa_id) {
                      console.log('Pedido recibido: añadir', pedido);
                        tables.value.forEach((mesa) => {
                            if (mesa.id === pedido?.mesa?.id) {
                              mesa.estado = pedido?.mesa?.estado;
                            }
                        });
                    }
                    // Si no existe, agregarlo a pedidos en mesa
                    ordenes.value.push(pedido);
                    showToast('success', 'Nuevo pedido en mesa añadido');
                }
                
            }
        })
        .catch(error => {
            // Manejo de errores en caso de que la solicitud falle
            console.error('Error al obtener el pedido:', error);
        });
  })
  .listen('.pagar-pedido', (data) => {
   // Realizar una solicitud para obtener los datos del pedido desde el servidor
   axios.get(`/getPedido/${data.id}`)
      .then(response => {
            const pedido = response.data;

            // Buscar si el pedido ya existe en las listas
            const ordenExistenteParaLlevar = ordenesParaLlevar.value.find((orden) => orden?.id === pedido.id);
            const ordenExistente = ordenes.value.find((orden) => orden?.id === pedido.id);


            if (!pedido.mesa_id) {
                if (ordenExistenteParaLlevar) {
                    // Si ya existe en pedidos para llevar, actualizarlo
                    Object.assign(ordenExistenteParaLlevar, pedido);
                    showToast('success', 'Pedido para llevar actualizado');
                } else {
                    // Si no existe, agregarlo a pedidos para llevar
                    ordenesParaLlevar.value.push(pedido);
                    showToast('success', 'Nuevo pedido para llevar añadido');
                }
            } else {
                if (ordenExistente) {
                    // Si ya existe en pedidos en mesa, actualizarlo
                    Object.assign(ordenExistente, pedido);
                    showToast('success', 'Pedido en mesa actualizado');
                    if (pedido.mesa_id) {
                        tables.value.forEach((mesa) => {
                            if (mesa.id === pedido.mesa.id) {
                                mesa.estado = pedido.mesa.estado;
                            }
                        });
                    }
                } else {
                    if (pedido?.mesa_id) {
                      console.log('Pedido recibido: añadir', pedido);
                        tables.value.forEach((mesa) => {
                            if (mesa.id === pedido?.mesa?.id) {
                              mesa.estado = pedido?.mesa?.estado;
                            }
                        });
                    }
                    // Si no existe, agregarlo a pedidos en mesa
                    ordenes.value.push(pedido);
                    showToast('success', 'Nuevo pedido en mesa añadido');
                }
                
            }
        })
        .catch(error => {
            // Manejo de errores en caso de que la solicitud falle
            console.error('Error al obtener el pedido:', error);
        });
  })


</script>
