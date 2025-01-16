<template>
  <div class="min-h-screen w-full  p-4 md:p-6">
    <div v-if="orders.length > 0">
      <!-- Orders Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="order in orders" :key="order.id" 
          class="bg-white rounded-lg shadow-lg overflow-hidden">
          <!-- Order Header -->
          <div class="bg-gray-50 px-2 py-3 border-b">
            <div class="flex justify-between items-center">
              <div class="flex items-center space-x-3">
                <div class="flex flex-col">
                  <span class="text-lg font-bold text-gray-900">Orden #{{ order.id }}</span>
                  
                  <span v-if="order.para_mesa" class="bg-green-500 rounded-xl px-2 text-white text-sm">Pedido para mesa</span>
                  <span v-if="order.para_llevar" class="bg-yellow-500 rounded-xl px-2 text-white text-sm">Pedido para llevar</span>
                  
                </div>
                <span v-if="isOrderUrgent(order)"
                  class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">
                  Urgente
                </span>
              </div>
              <div class="flex items-center space-x-1">
                <ClockIcon class="w-4 h-4 text-gray-400" />
                <span class="text-sm text-gray-600">
                  {{ formatTime(order.created_at) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Grouped Items by Person -->
          <div class="divide-y">
            <div v-for="(personItems, personId) in groupItemsByPerson(order.productos)" 
              :key="personId" 
              class="p-4">
              <div class="flex items-center space-x-2 mb-3">
                <UserIcon class="w-5 h-5 text-gray-600" />
                <h3 class="font-medium text-gray-900 bg-green-200 px-2 rounded-xl">
                  Persona {{ personId }}
                </h3>
              </div>

              <!-- Person's Items -->
              <div class="space-y-3">
                <div v-for="item in filterItems(personItems)" :key="item.id"
                  class="flex items-start justify-between p-2 rounded-lg hover:bg-gray-50">
                  <div  v-if="item.estado !== 'entregado' ">
                    <div class="flex items-start space-x-3">
                      <span class="font-medium text-gray-900">{{ item.cantidad }}x</span>
                      <div class="flex flex-col">
                        <span class="text-gray-900">{{ item?.producto?.nombre }}</span>
                        <span v-if="item?.producto?.detalle" class="text-sm text-gray-500">
                          {{ item?.producto?.detalle }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Order Footer -->
          <div class="bg-gray-50 px-4 py-3 border-t">
            <div class="flex justify-around  w-full">
              <div class="flex justify-between space-x-4 ">
                <div class="flex items-center space-x-2">
                  <MapPinIcon class="w-4 h-4 text-gray-400" />
                  <span class="text-sm font-medium text-gray-700">
                    {{ order?.mesa?.nombre || 'Para llevar' }}
                  </span>
                </div>
                
                <div class="flex items-center text-end space-x-2 ">
                  <!-- <TimerIcon class="w-4 h-4 text-gray-400" />
                  <span class="text-sm text-gray-600">
                    {{ calculateEstimatedTime(order.productos) }} min
                  </span> -->
                </div>
              </div>
              
            </div>
            <div v-if="order.nombre_cliente" class="flex text-blue-500 w-full items-center space-x-2 justify-center">
              <AlertCircle class="size-3" />
              <span class=" font-bold ">A nombre de {{ order?.nombre_cliente }}</span>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="px-4 py-3 bg-gray-50 border-t">
            <button @click="completeOrder(order.id)"
              class="w-full bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-4 rounded-lg flex items-center justify-center space-x-2">
              <CheckIcon class="w-5 h-5" />
              <span>Completar Pedido</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="flex flex-col items-center justify-center p-8 bg-white rounded-lg shadow">
      <ClipboardListIcon class="w-16 h-16 text-gray-400 mb-4" />
      <h3 class="text-lg font-medium text-gray-900 mb-2">No hay pedidos pendientes</h3>
      <p class="text-gray-500">Los nuevos pedidos aparecerán aquí</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import {
  CheckIcon,
  ClockIcon,
  MapPinIcon,
  TimerIcon,
  ClipboardListIcon,
  UserIcon,
  AlertCircle
} from 'lucide-vue-next';
import { router, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const { props } = usePage();

const orders = computed(() => props.pedidos || []);
// Helper Functions
function isOrderUrgent(order) {
  if(order.reConsumo) return true;
  if(order.prioridad === 'urgente') return true;
}

function formatTime(timestamp) {
  return new Date(timestamp).toLocaleTimeString([], {
    hour: '2-digit',
    minute: '2-digit',
    hour12: true
  });
}

function groupItemsByPerson(items) {
  return items.reduce((acc, item) => {
    
    const personId = item.persona_id;
    
    if (!acc[personId]) {
      acc[personId] = [];
    }
    acc[personId].push(item);
    return acc;
  }, {});
}

function calculateEstimatedTime(items) {
  const baseTime = 10;
  const itemCount = items.reduce((total, item) => total + item.cantidad, 0);
  return baseTime + (itemCount * 2);
}

// Toast Configuration
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

// Actions
const completeOrder = (orderId) => {
  try {
    
    router.post('/enviaraEntregar', { id: orderId }, {
      preserveScroll: true,
      preserveState: false,
      onSuccess: (a) => {
        orders.value = props.pedidos || [];
        Toast.fire({
          icon: 'success',
          title: `Pedido #${orderId} completado exitosamente`
        });
        
        
      },
      onError: (error) => {
        Toast.fire({
          icon: 'error',
          title: 'Error al completar el pedido'
        });
        console.error('Error al completar el pedido:', error);
      }
    });
  } catch (error) {
    Toast.fire({
      icon: 'error',
      title: 'Error al completar el pedido'
    });
    console.error('Error al completar el pedido:', error);
  }
};

const filterItems = (items) => {
    return items.filter(item => (item.estado === 'pendiente'));
};


import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
import { data } from 'autoprefixer';
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

          // Agregar el pedido si no existe en las órdenes
          const ordenExistente = orders.value.find((orden) => orden?.id === pedido.id);
          
          if (!ordenExistente) {
              orders.value.push(pedido);
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
                
                const ordenExistente = orders.value.find((orden) => orden?.id === pedido.id);

                if (!pedido.mesa_id) {
                    if (ordenExistenteParaLlevar) {
                        // Si ya existe en pedidos para llevar, actualizarlo
                        Object.assign(ordenExistenteParaLlevar, pedido);
                        showToast('success', 'Pedido para llevar actualizado');
                    } else {
                        // Si no existe, agregarlo a pedidos para llevar
                        orders.value.push(pedido);
                        showToast('success', 'Nuevo pedido para llevar añadido');
                    }
                } else {
                    if (ordenExistente) {
                        // Si ya existe en pedidos en mesa, actualizarlo
                        Object.assign(ordenExistente, pedido);
                        showToast('success', 'Pedido en mesa actualizado');
                    } else {
                        // Si no existe, agregarlo a pedidos en mesa
                        orders.value.push(pedido);
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

                const ordenExistente = orders.value.find((orden) => orden?.id === pedido.id);

                if (!pedido.mesa_id) {
                    if (ordenExistenteParaLlevar) {
                        // Si ya existe en pedidos para llevar, actualizarlo
                        Object.assign(ordenExistenteParaLlevar, pedido);
                        showToast('success', 'Pedido para llevar actualizado');
                    } else {
                        // Si no existe, agregarlo a pedidos para llevar
                        orders.value.push(pedido);
                        showToast('success', 'Nuevo pedido para llevar añadido');
                    }
                } else {
                    if (ordenExistente) {
                        // Si ya existe en pedidos en mesa, actualizarlo
                        Object.assign(ordenExistente, pedido);
                        showToast('success', 'Pedido en mesa actualizado');
                    } else {
                        // Si no existe, agregarlo a pedidos en mesa
                        orders.value.push(pedido);
                        showToast('success', 'Nuevo pedido en mesa añadido');
                    }
                }
            })
            .catch(error => {
                // Manejo de errores en caso de que la solicitud falle
                console.error('Error al obtener el pedido:', error);
            });
    })

    
    .listen('.cocinar-pedido', (data) => {

        // Realizar una solicitud para obtener los datos del pedido desde el servidor
        axios.get(`/getPedido/${data.id}`)
            .then(response => {
                const pedido = response.data;

                // Buscar el índice del pedido en la lista
                const index = orders.value.findIndex((orden) => orden?.id === pedido.id);

                if (index !== -1) {
                    // Eliminar el pedido encontrado
                    orders.value.splice(index, 1);
                    showToast('success', 'Pedido completado correctamente');
                } else {
                    console.warn('Pedido no encontrado en la lista');
                }
            })
            .catch(error => {
                // Manejo de errores en caso de que la solicitud falle
                console.error('Error al obtener el pedido:', error);
            });
    })
    .listen('.cocinar-pedido-delivery', (data) => {

      // Realizar una solicitud para obtener los datos del pedido desde el servidor
      axios.get(`/getPedido/${data.id}`)
          .then(response => {
              const pedido = response.data;

              // Buscar el índice del pedido en la lista
              const index = orders.value.findIndex((orden) => orden?.id === pedido.id);

              if (index !== -1) {
                  // Eliminar el pedido encontrado
                  orders.value.splice(index, 1);
                  showToast('success', 'Pedido completado correctamente');
              } else {
                  console.warn('Pedido no encontrado en la lista');
              }
          })
          .catch(error => {
              // Manejo de errores en caso de que la solicitud falle
              console.error('Error al obtener el pedido:', error);
          });
      })
    .listen('.update-mesas', (data) => {
      
      const mesaNueva = data.mesaNew //id
      const mesaVieja = data.mesaOld //id

      const ordenExistente = orders.value.find((orden) => orden?.mesa_id === mesaVieja.id);

      if (ordenExistente) {
          // Actualizar la mesa_id de la orden a la nueva mesa
          ordenExistente.mesa = mesaNueva;

          // Mostrar notificación de éxito
          showToast('success', `Pedido actualizado a la nueva mesa: ${data.mesaNew.nombre}`);
      } else {
          console.warn('No se encontró una orden para la mesa anterior:', data.mesaOld.id);
      }
            
    })
    .listen('.para-llevar-pedido', (data) => {

       
      axios.get(`/getPedido/${data.id}`)
        .then(response => {
                const pedido = response.data;

                const ordenExistente = orders.value.find((orden) => orden?.id === pedido.id);

                if (!pedido.mesa_id) {
                    if (ordenExistenteParaLlevar) {
                        // Si ya existe en pedidos para llevar, actualizarlo
                        Object.assign(ordenExistenteParaLlevar, pedido);
                        showToast('success', 'Pedido para llevar actualizado');
                    } else {
                        // Si no existe, agregarlo a pedidos para llevar
                        orders.value.push(pedido);
                        showToast('success', 'Nuevo pedido para llevar añadido');
                    }
                } else {
                    if (ordenExistente) {
                        // Si ya existe en pedidos en mesa, actualizarlo
                        Object.assign(ordenExistente, pedido);
                        showToast('success', 'Pedido en mesa actualizado');
                    } else {
                        // Si no existe, agregarlo a pedidos en mesa
                        orders.value.push(pedido);
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
      
      const mesaNueva = data.mesaNew //id
      const mesaVieja = data.mesaOld //id

      const ordenExistente = orders.value.find((orden) => orden?.mesa_id === mesaVieja.id);

      if (ordenExistente) {
          // Actualizar la mesa_id de la orden a la nueva mesa
          ordenExistente.mesa = mesaNueva;

          // Mostrar notificación de éxito
          showToast('success', `Pedido actualizado a la nueva mesa: ${data.mesaNew.nombre}`);
      } else {
          console.warn('No se encontró una orden para la mesa anterior:', data.mesaOld.id);
      }
            
    })
    .listen('.cancelar-pedido', (data) => {
      const order = data.pedido
      const index = orders.value.findIndex((o) => o.id === order.id);
      if (index !== -1) {
        orders.value.splice(index, 1);
      }
      showToast('info', 'Pedido cancelado')
    })

</script>