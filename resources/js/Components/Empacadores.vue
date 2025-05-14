<template>
  <div class="flex items-center justify-center p-4 w-full">
      <div class="rounded-xl max-h-[90vh] w-full max-w-7xl  overflow-auto">
          <div class="p-6 w-full">
                <div v-if="preprocessedOrders.length === 0" class="flex justify-center  flex-col items-center ">
                    <ClipboardListIcon class="w-16 h-16 text-gray-400 mb-4" />
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No hay pedidos pendientes</h3>
                    <p class="text-gray-500">Los pedidos para empacar aparecerán aquí</p>
                </div>
              <div class="gap-6  justify-center grid grid-cols-3 w-full">
                  <OrderCard
                      v-for="order in preprocessedOrders"
                      :key="order.id"
                      :order="order"
                      :empacadores="true"
                      @complete="completeOrder"
                  />
              </div>
          </div>
      </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import Swal from 'sweetalert2';
import { usePage, router } from '@inertiajs/vue3';
import OrderCard from './OrderCard.vue';

const { props } = usePage();
const ordenes = ref(props.ordenes);

console.log(props.ordenesPendientesEntrega)
console.log(props.ordenesPendientesParaLlevar)

const preprocessedOrders = computed(() =>
  ordenes.value
    .filter(order => order.productos && order.productos.length > 0 && order.productos.some(producto => producto.estado !== 'entregado'))
    .map(order => ({
      ...order,
      isUrgent: isOrderUrgent(order.created_at),
      groupedItems: groupItemsByPerson(order.productos),
    }))
);

function isOrderUrgent(createdAt) {
  const waitTime = Date.now() - new Date(createdAt).getTime();
  return waitTime > 15 * 60 * 1000;
}

function groupItemsByPerson(items) {
  return items.reduce((acc, item) => {
    // Filtrar solo los productos con estado "finalizado" o "espera_empacar"
    if (item.estado === 'finalizado' || item.estado === 'espera_empacar' || item.estado === 'espera_entrega' && item.tipo_servicio === 'para_llevar') {
      const personId = item.persona_id;
      if (!acc[personId]) {
        acc[personId] = [];
      }
      acc[personId].push(item);
    }
    return acc;
  }, {});
}

function completeOrder(orderId) {
  const order = ordenes.value.find((order) => order.id === orderId);
  
  router.post(
      '/completar_pedido',
      { id: orderId },
      {
          preserveScroll: true,
          preserveState: true,
          onSuccess: () => {
              ordenes.value = ordenes.value.filter(order => order.id !== orderId);
              showToast('success', 'Pedido entregado');
          },
          onError: () => {
              showToast('error', 'Error al completar el pedido');
          },
      }
  );
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
import { ClipboardListIcon } from 'lucide-vue-next';
window.Pusher = Pusher;

//pusher
// Asegúrate de salir de canales innecesarios antes de suscribirte a uno nuevo
const sucursalId = props.auth.user.sucursal_id;
//console.log(props.orders)

window.Echo.leaveChannel(`pedidos_sucursal_${sucursalId}`);

// Escuchar en el canal privado de la sucursal actual
window.Echo.channel(`pedidos_sucursal_${sucursalId}`)
    .listen('.cocinar-pedido-delivery', (data) => {
        console.log('Evento recibido:', data);
        axios.get(`/getPedido/${data.id}`)
        .then(response => {
            const pedido = response.data;
            console.log('Pedido recibido:', pedido);
            const ordenExistente = ordenes.value.find((orden) => orden?.id === pedido.id);
            
            if (!ordenExistente) {
                ordenes.value.push(pedido);
                showToast('success', 'Nuevo pedido recibido');
            }
        })
        .catch(error => {
            console.error('Error al obtener el pedido:', error);
        });
    })
    .listen('.empacar-pedido', (data) => {
        console.log('Procesar evento empacar-pedido:', data);
        axios.get(`/getPedido/${data.id}`)
        .then(response => {
            const pedido = response.data;
            console.log('Pedido recibido:', pedido);
            const ordenExistente = ordenes.value.find((orden) => orden?.id === pedido.id);
            
            if (!ordenExistente) {
                ordenes.value.push(pedido);
                showToast('success', 'Nuevo pedido para empacar');
            }
        })
        .catch(error => {
            console.error('Error al obtener el pedido:', error);
        });
    })
    .listen('.entregar-pedido', (data) => {
        console.log('Procesar evento entregar-pedido:', data);
        axios.get(`/getPedido/${data.id}`)
        .then(response => {
            const pedido = response.data;
            console.log('Pedido recibido:', pedido);
            const index = ordenes.value.findIndex((orden) => orden?.id === pedido.id);
            
            if (index !== -1) {
                ordenes.value.splice(index, 1);
                showToast('success', 'Pedido entregado correctamente');
            }
        })
        .catch(error => {
            console.error('Error al obtener el pedido:', error);
        });
    })
    .listen('.cancelar-pedido', (data) => {
        const order = data.pedido;
        const index = ordenes.value.findIndex((o) => o.id === order.id);
        if (index !== -1) {
            ordenes.value.splice(index, 1);
        }
        showToast('info', 'Pedido cancelado');
    })
    .listen('.terminar-pedido', (data) => {
        const index = ordenes.value.findIndex((o) => o.id === data.id);
        if (index !== -1) {
            ordenes.value.splice(index, 1);
        }
        showToast('info', 'Pedido terminado');
    });
</script>
