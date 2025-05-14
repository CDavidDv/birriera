<template>
  <div class="min-h-screen w-full p-4 md:p-6">
    <div v-if="orders.length > 0">
      <!-- Orders Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="order in orders" :key="order.id" class="bg-white rounded-lg shadow-lg overflow-hidden">
          <!-- Order Header -->
          <div class="bg-gray-50 px-2 py-3 border-b">
            <div class="flex justify-between items-center">
              <div class="flex items-center space-x-3">
                <div class="flex flex-col">
                  <span class="text-lg font-bold text-gray-900">Orden #{{ order.id }}</span>
                  <span v-if="order.para_mesa" class="bg-green-500 rounded-xl px-2 text-white text-lg">Pedido para mesa</span>
                  <span v-if="order.para_llevar" class="bg-yellow-500 rounded-xl px-2 text-white text-lg">Pedido para llevar</span>
                </div>
                <span v-if="isOrderUrgent(order)" class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">
                  Urgente
                </span>
              </div>
              <div class="flex items-center space-x-1">
                <ClockIcon class="w-4 h-4 text-gray-400" />
                <span class="text-lg text-gray-600">{{ formatTime(order.created_at) }}</span>
              </div>
            </div>
          </div>

          <!-- Grouped Items by Person -->
          <div class="divide-y">
            <div v-for="(personItems, personId) in groupItemsByPerson(order.productos)" :key="personId" class="p-4">
              <div class="flex items-center space-x-2 mb-3">
                <UserIcon class="w-5 h-5 text-gray-600" />
                <h3 class="font-medium text-gray-900 bg-green-200 px-2 rounded-xl">Persona {{ personId }}</h3>
              </div>

              <!-- Person's Items -->
              <div class="space-y-3">
                <div v-for="item in filterItems(personItems)" :key="item.id" class="flex items-start justify-between p-2 rounded-lg hover:bg-gray-50">
                  <div v-if="item.estado !== 'entregado'">
                    <div class="flex items-start space-x-3">
                      <span class="font-medium text-gray-900 text-2xl">{{ item.cantidad }}x</span>
                      <div class="flex flex-col">
                        <span class="text-gray-900 text-2xl">{{ item?.producto?.nombre }}</span>
                        <span v-if="item?.producto?.detalle" class="text-lg text-gray-500">{{ item?.producto?.detalle }}</span>
                        <!--si es para llevar, mostrar para llevar color naranja, si es para comer, mostrar para comer color verde-->
                        <span v-if="item?.tipo_servicio" :class="{
                          'text-lg text-gray-500 bg-green-200 px-2 rounded-xl': item?.tipo_servicio === 'para_comer',
                          'text-lg text-gray-500 bg-orange-200 px-2 rounded-xl': item?.tipo_servicio === 'para_llevar'
                        }">{{ item?.tipo_servicio === 'para_llevar' ? 'Para llevar' : 'Para comer' }}</span>

                        <div v-if="item?.personalizacion" class="capitalize flex gap-2 my-1" >
                          <span v-for="observacion in item?.personalizacion.split(', ')" :key="observacion" :class="{
                            'text-white px-2 py-1 rounded-lg': true,
                            'bg-green-500': observacion.includes('con todo'),
                            'bg-orange-500': !observacion.includes('con todo')
                          }">
                            {{ observacion }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Order Footer -->
          <div class="bg-gray-50 px-4 py-3 border-t">
            <div class="flex justify-around w-full">
              <div class="flex justify-between space-x-4">
                <div class="flex items-center space-x-2">
                  <MapPinIcon class="size-7 text-gray-400" />
                  <span class="text-3xl font-medium text-gray-700">{{ order?.mesa?.nombre || 'Para llevar' }}</span>
                </div>
              </div>
            </div>
            <div v-if="order.nombre_cliente" class="flex text-blue-500 w-full items-center space-x-2 justify-center">
              <AlertCircle class="size-7" />
              <span class="font-semibold text-lg">A nombre de <span class="font-bold text-black text-2xl">{{ order?.nombre_cliente }}</span></span>
            </div>
            <div v-if="order.observaciones" class="flex text-blue-500 w-full items-center space-x-2 justify-center">
              <AlertCircle class="size-7" />
              <span class="font-semibold text-lg">Observaciones: <span class="font-bold text-black text-2xl">{{ order?.observaciones }}</span></span>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="px-4 py-3 bg-gray-50 border-t">
            <button @click="printComanda(order.id)" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg flex items-center justify-center space-x-2 mb-2">
              <PrinterIcon class="w-5 h-5" />
              <span>Imprimir Comanda</span>
            </button>
            <button @click="completeOrder(order.id)" class="w-full bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-4 rounded-lg flex items-center justify-center space-x-2">
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
import { computed, ref } from 'vue';
import {
  CheckIcon,
  ClockIcon,
  MapPinIcon,
  TimerIcon,
  ClipboardListIcon,
  UserIcon,
  AlertCircle,
  PrinterIcon
} from 'lucide-vue-next';
import { router, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const { props } = usePage();
const localOrders = ref(props.pedidos || []);

const orders = computed({
  get: () => {
    return sortOrdersByPriority(localOrders.value);
  },
  set: (newValue) => {
    localOrders.value = sortOrdersByPriority(newValue);
  }
});

// Función para ordenar pedidos por prioridad
function sortOrdersByPriority(ordersArray) {
  return [...ordersArray].sort((a, b) => {
    // Definir el orden de prioridad
    const priorityOrder = {
      'urgente_old': 0,    // Pedidos urgentes viejos
      'urgente_new': 1,    // Nuevos pedidos urgentes
      'normal_old': 2,     // Pedidos normales viejos
      'normal_new': 3      // Nuevos pedidos normales
    };

    // Determinar la categoría de cada pedido
    const getPriorityCategory = (order) => {
      const isUrgent = order.prioridad === 'urgente';
      const isOld = isOrderUrgent(order.created_at);
      
      if (isUrgent && isOld) return 'urgente_old';
      if (isUrgent && !isOld) return 'urgente_new';
      if (!isUrgent && isOld) return 'normal_old';
      return 'normal_new';
    };

    const priorityA = priorityOrder[getPriorityCategory(a)];
    const priorityB = priorityOrder[getPriorityCategory(b)];
    
    return priorityA - priorityB;
  });
}

// Helper Functions
function isOrderUrgent(order) {
  if (order.reConsumo) return true;
  if (order.prioridad === 'urgente') return true;
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
      onSuccess: () => {
        const index = localOrders.value.findIndex((o) => o.id === orderId);
        if (index !== -1) {
          localOrders.value.splice(index, 1);
        }
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

const printComanda = (orderId) => {
  const order = orders.value.find(order => order.id === orderId);

  if (!order) {
    showToast('error', 'Pedido no encontrado');
    return;
  }

  const totalN = (order.productos.reduce((sum, product) => {
    const cantidad = parseFloat(product.cantidad) || 0;
    const precio = parseFloat(product.producto.precio) || 0;
    return sum + cantidad * precio;
  }, 0)
  - (parseFloat(order.descuento) || 0)
  + (parseFloat(order.propina) || 0));

  fetch('https://print.test/print-ticket', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      order_id: order.id,
      mesa: order.mesa ? order?.mesa?.nombre : 'Para llevar',
      nombre_cliente: order?.nombre_cliente || 'Sin nombre',
      productos: order.productos.map(product => ({
        nombre: product.producto.nombre,
        cantidad: product.cantidad,
        precio: product.producto.precio,
        total: (product.cantidad * parseFloat(product.producto.precio)),
      })),
      total: totalN,
      descuento: order.descuento || 0,
      propina: order.propina || 0,
      fecha: order.created_at,
      tipo: order.para_mesa ? 'Local' : 'Para llevar',
      comanda: true // Indicar que es una comanda
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showToast('success', 'Comanda impresa correctamente');
      } else {
        showToast('error', data.message || 'Error al imprimir la comanda');
      }
    })
    .catch((error) => {
      console.error('Error en la impresión de la comanda:', error);
      showToast('error', 'Error al imprimir la comanda');
    });
};

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
window.Pusher = Pusher;

// Pusher setup
const sucursalId = props.auth.user.sucursal_id;
window.Echo.leaveChannel(`pedidos_sucursal_${sucursalId}`);

// Listen to the private channel of the current branch
window.Echo.channel(`pedidos_sucursal_${sucursalId}`)
  .listen('.crear-pedido', (data) => {
    axios.get(`/getPedido/${data.id}`)
      .then(response => {
        const pedido = response.data;
        const ordenExistente = localOrders.value.find((orden) => orden?.id === pedido.id);
        if (!ordenExistente) {
          localOrders.value.push(pedido);
          showToast('success', 'Nuevo pedido recibido');
          sortOrdersByPriority(localOrders.value);
        }
      })
      .catch(error => {
        console.error('Error al obtener el pedido:', error);
      });
  })
  .listen('.add-pedido', (data) => {
    axios.get(`/getPedido/${data.id}`)
      .then(response => {
        const pedido = response.data;
        const ordenExistente = localOrders.value.find((orden) => orden?.id === pedido.id);
        if (!pedido.mesa_id) {
          if (ordenExistenteParaLlevar) {
            Object.assign(ordenExistenteParaLlevar, pedido);
            showToast('success', 'Pedido para llevar actualizado');
            sortOrdersByPriority(localOrders.value);
          } else {
            localOrders.value.push(pedido);
            showToast('success', 'Nuevo pedido para llevar añadido');
            sortOrdersByPriority(localOrders.value);
          }
        } else {
          if (ordenExistente) {
            Object.assign(ordenExistente, pedido);
            showToast('success', 'Pedido en mesa actualizado');
          } else {
            localOrders.value.push(pedido);
            showToast('success', 'Nuevo pedido en mesa añadido');
          }
        }
      })
      .catch(error => {
        console.error('Error al obtener el pedido:', error);
      });
  })
  .listen('.substrack-pedido', (data) => {
    axios.get(`/getPedido/${data.id}`)
      .then(response => {
        const pedido = response.data;
        const ordenExistente = localOrders.value.find((orden) => orden?.id === pedido.id);
        if (!pedido.mesa_id) {
          if (ordenExistenteParaLlevar) {
            Object.assign(ordenExistenteParaLlevar, pedido);
            showToast('success', 'Pedido para llevar actualizado');
          } else {
            localOrders.value.push(pedido);
            showToast('success', 'Nuevo pedido para llevar añadido');
          }
        } else {
          if (ordenExistente) {
            Object.assign(ordenExistente, pedido);
            showToast('success', 'Pedido en mesa actualizado');
          } else {
            localOrders.value.push(pedido);
            showToast('success', 'Nuevo pedido en mesa añadido');
          }
        }
      })
      .catch(error => {
        console.error('Error al obtener el pedido:', error);
      });
  })
  .listen('.cocinar-pedido', (data) => {
    axios.get(`/getPedido/${data.id}`)
      .then(response => {
        const pedido = response.data;
        const index = localOrders.value.findIndex((orden) => orden?.id === pedido.id);
        if (index !== -1) {
          localOrders.value.splice(index, 1);
          showToast('success', 'Pedido completado correctamente');
        } else {
          console.warn('Pedido no encontrado en la lista');
        }
      })
      .catch(error => {
        console.error('Error al obtener el pedido:', error);
      });
  })
  .listen('.cocinar-pedido-delivery', (data) => {
    axios.get(`/getPedido/${data.id}`)
      .then(response => {
        const pedido = response.data;
        const index = localOrders.value.findIndex((orden) => orden?.id === pedido.id);
        if (index !== -1) {
          localOrders.value.splice(index, 1);
          showToast('success', 'Pedido completado correctamente');
        } else {
          console.warn('Pedido no encontrado en la lista');
        }
      })
      .catch(error => {
        console.error('Error al obtener el pedido:', error);
      });
  })
  .listen('.update-mesas', (data) => {
    const mesaNueva = data.mesaNew;
    const mesaVieja = data.mesaOld;
    const ordenExistente = localOrders.value.find((orden) => orden?.mesa_id === mesaVieja.id);
    if (ordenExistente) {
      ordenExistente.mesa = mesaNueva;
      showToast('success', `Pedido actualizado a la nueva mesa: ${data.mesaNew.nombre}`);
    } else {
      console.warn('No se encontró una orden para la mesa anterior:', data.mesaOld.id);
    }
  })
  .listen('.para-llevar-pedido', (data) => {
    axios.get(`/getPedido/${data.id}`)
      .then(response => {
        const pedido = response.data;
        const ordenExistente = localOrders.value.find((orden) => orden?.id === pedido.id);
        if (!pedido.mesa_id) {
          if (ordenExistenteParaLlevar) {
            Object.assign(ordenExistenteParaLlevar, pedido);
            showToast('success', 'Pedido para llevar actualizado');
          } else {
            localOrders.value.push(pedido);
            showToast('success', 'Nuevo pedido para llevar añadido');
          }
        } else {
          if (ordenExistente) {
            Object.assign(ordenExistente, pedido);
            showToast('success', 'Pedido en mesa actualizado');
          } else {
            localOrders.value.push(pedido);
            showToast('success', 'Nuevo pedido en mesa añadido');
          }
        }
      })
      .catch(error => {
        console.error('Error al obtener el pedido:', error);
      });
  })
  .listen('.update-mesas', (data) => {
    const mesaNueva = data.mesaNew;
    const mesaVieja = data.mesaOld;
    const ordenExistente = localOrders.value.find((orden) => orden?.mesa_id === mesaVieja.id);
    if (ordenExistente) {
      ordenExistente.mesa = mesaNueva;
      showToast('success', `Pedido actualizado a la nueva mesa: ${data.mesaNew.nombre}`);
    } else {
      console.warn('No se encontró una orden para la mesa anterior:', data.mesaOld.id);
    }
  })
  .listen('.cancelar-pedido', (data) => {
    const order = data.pedido;
    const index = localOrders.value.findIndex((o) => o.id === order.id);
    if (index !== -1) {
      localOrders.value.splice(index, 1);
    }
    showToast('info', 'Pedido cancelado');
  });
</script>
