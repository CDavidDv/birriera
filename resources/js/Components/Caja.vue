<template>
  <div class="min-h-screen bg-gray-100 p-6">
    <div class="max-w-4xl mx-auto">
      <h1 class="text-3xl font-bold mb-6">Caja - Procesar Pagos</h1>

      <div v-if="ordenes.length > 0" class="mb-6">
        <label for="order-selector" class="block mb-2 font-medium">Seleccionar Orden:</label>
        <select
          id="order-selector"
          v-model="selectedOrderId"
          class="w-full p-2 border rounded"
        >
          <option
            v-for="order in ordenes"
            :key="order.id"
            :value="order.id"
          >
          {{ order?.mesa?.nombre ? order?.mesa?.nombre : 'Para llevar' }} - Orden #{{ order.id }} {{ order?.nombre_cliente ? `- ${order?.nombre_cliente}` : '' }}
          </option>
        </select>
      </div>

      <div v-if="currentOrder" class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
          <h2 v-if="currentOrder?.mesa" class="text-xl font-semibold">{{ currentOrder?.mesa?.nombre }}</h2>
          <h2 v-else class="text-xl font-semibold">Para llevar</h2>
          <span class="text-gray-500">Orden #{{ currentOrder.id }}</span>
        </div>

        <div class="border-t border-b py-4 mb-4">
          <h3 class="font-medium mb-2">Resumen del Pedido</h3>
          <div class="space-y-2">
            <div
              v-for="product in currentOrder.productos"
              :key="product.id"
              class="flex justify-between"
            >
              <span>{{ product.cantidad }}x {{ product.producto.nombre }}</span>
              <span>${{ (product.cantidad * parseFloat(product.producto.precio)) }}</span>
            </div>
          </div>
        </div>

        <div class="space-y-2 mb-4">
          <div class="flex justify-between">
            <span>Subtotal</span>
            <span>${{ subtotal }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span>Descuento</span>
            <input
              v-model.number="discount"
              type="number"
              min="0"
              :max="subtotal"
              class="w-20 px-2 py-1 border rounded"
              :class="{'border-red-500': discount > subtotal || discount < 0}"
              placeholder="$0.00"
            />
          </div>
          <div class="flex justify-between items-center">
            <span>Propina</span>
            <div class="flex items-center space-x-2">
              <button
                v-for="percentage in [10, 15, 20]"
                :key="percentage"
                @click="setTipPercentage(percentage)"
                :class="[
                  'px-2 py-1 rounded text-sm',
                  tipPercentage === percentage ? 'bg-blue-500 text-white' : 'bg-gray-200'
                ]"
              >
                {{ percentage }}%
              </button>
              <input
                v-model.number="customTip"
                type="number"
                min="0"
                class="w-20 px-2 py-1 border rounded"
                placeholder="Otro"
              />
            </div>
          </div>
        </div>

        <div class="flex justify-between items-center text-xl font-bold mb-6">
          <span>Total</span>
          <span>${{ total }}</span>
        </div>

        <div class="space-y-4">
          <h3 class="font-medium">Método de Pago</h3>
          <div class="flex space-x-4">
            <button
              v-for="method in paymentMethods"
              :key="method.id"
              @click="selectPaymentMethod(method.id)"
              :class="[
                'flex-1 py-2 px-4 rounded-lg font-medium transition-colors',
                selectedPaymentMethod === method.id ? 'bg-blue-500 text-white' : 'bg-gray-200 hover:bg-gray-300'
              ]"
            >
              <component :is="method.icon" class="w-5 h-5 inline-block mr-2" />
              {{ method.name }}
            </button>
          </div>

          <div v-if="selectedPaymentMethod === 'cash'" class="flex space-x-4 items-center justify-between">
            <label for="pago">Paga con:</label>
            <div>
              $
              <input
                v-model.number="cashReceived"
                type="number"
                min="0"
                class="border rounded-lg p-2 text-end"
                placeholder="0.00"
              />
            </div>
          </div>
          <div v-if="selectedPaymentMethod === 'cash'" class="flex justify-between items-center">
            <span>Cambio:</span>
            <span>${{ change || 0}}</span>
          </div>

          <button
            class="w-full px-3 py-2 bg-green-500 hover:bg-green-400 text-white rounded-xl"
            @click="procesarPago"
            :disabled="loading"
          >
            <span v-if="!loading">Completar</span>
            <span v-else>Cargando...</span>
          </button>
        </div>
      </div>

      <div v-else class="text-center text-gray-500">
        <p>No hay órdenes seleccionadas.</p>
      </div>
    </div>
  </div>
  <div>
    <!--Seccion imprimir tiquets recientes-->
    <div class="mt-8 bg-white p-6 w-full rounded-lg shadow-md">
      <h2 class="text-xl font-bold mb-4">Tickets Recientes</h2>

      <div v-if="recentTickets?.length > 0" class="space-y-4">
        <div
          v-for="ticket in recentTickets"
          :key="ticket.id"
          class="flex justify-between items-center border-b pb-2 space-x-3"
        >
          <div>
            <h3 class="font-medium">{{ ticket.mesa ? `Mesa: ${ticket?.mesa?.nombre}` : "Para llevar" }}</h3>
            <p v-if="ticket?.nombre_cliente" class="text-gray-500 text-sm">A nombre #{{ ticket?.nombre_cliente }}</p>
            <p v-else class="text-gray-500 text-sm">A nombre de: Sin nombre</p>
            <p class="text-gray-500 text-sm">Orden #{{ ticket?.id }}</p>
            <p class="text-gray-500 text-sm">Total: ${{ ticket.total }}</p>
          </div>
          <button
            class="px-3 py-1 bg-blue-500 hover:bg-blue-400 text-white rounded"
            @click="printTicket(ticket.id)"
          >
            Reimprimir
          </button>
        </div>
      </div>

      <div v-else class="text-center text-gray-500">
        <p>No hay tickets recientes.</p>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { CreditCardIcon, BanknoteIcon } from 'lucide-vue-next';
import { router, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

// Datos que llegan desde el servidor
const { props } = usePage();
const ordenes = ref(props?.ordenes || []);
const selectedOrderId = ref(ordenes?.value?.length > 0 ? ordenes.value[0].id : null);
const recentTickets = ref(props?.ordenesRecientes)
console.log(props)
const currentOrder = computed(() => {
  return ordenes.value.find((order) => order.id === selectedOrderId.value) || null;
});

const paymentMethods = [
  { id: 'transfer', name: 'Transferencia', icon: BanknoteIcon },  
  { id: 'card', name: 'Tarjeta', icon: CreditCardIcon },
  { id: 'cash', name: 'Efectivo', icon: BanknoteIcon },
];

const selectedPaymentMethod = ref('cash');
const tipPercentage = ref(0);
const customTip = ref(null);
const discount = ref(0);
const cashReceived = ref(0);

// Cálculo del subtotal basado en los productos de la orden
const subtotal = computed(() => {
  return currentOrder.value
    ? currentOrder.value.productos.reduce(
        (sum, product) =>
          sum + product.cantidad * parseFloat(product.producto.precio),
        0
      )
    : 0;
});

const tipAmount = computed(() => {
  if (customTip.value !== null && customTip.value !== '') {
    return parseFloat(customTip.value);
  }
  return (subtotal.value * tipPercentage.value) / 100;
});

const total = computed(() => subtotal.value + tipAmount.value - discount.value);

const change = computed(() => {
  return cashReceived.value > total.value ? cashReceived.value - total.value : 0;
});

const setTipPercentage = (percentage) => {
  tipPercentage.value = percentage;
  customTip.value = null;
};

const resetTip = () => {
  customTip.value = null;
  tipPercentage.value = 0;
};

const selectPaymentMethod = (methodId) => {
  selectedPaymentMethod.value = methodId;
};

watch(discount, (value) => {
  if (value < 0) discount.value = 0;
  if (value > subtotal.value) discount.value = subtotal.value;
});

watch(customTip, (value) => {
  if (value < 0) customTip.value = 0;
});

const loading = ref(false);

const procesarPago = () => {
  if (selectedPaymentMethod.value === 'cash' && cashReceived.value < total.value) {
    Toast.fire({
      icon: 'error',
      title: 'El monto recibido no cubre el total de la orden.',
    });
    return;
  }

  loading.value = true;
  
  router.post('/terminar_pedido', {
    id: selectedOrderId.value,
    total: total.value,
    paymentMethod: selectedPaymentMethod.value,
    discount: discount.value,
    tip: tipAmount.value,
    cashReceived: selectedPaymentMethod.value === 'cash' ? cashReceived.value : null,
  },{
    onSuccess: (response) => {
      console.log('Pago procesado con éxito:', response);
      Toast.fire({
        icon: 'success',
        title: 'Pago procesado con éxito.',
      });
      ordenes.value = response.props.ordenes || [];
      recentTickets.value = response.props.ordenesRecientes || [];
    },
    onError: (error) => {
      console.error('Error al procesar el pago:', error);
      Toast.fire({
        icon: 'error',
        title: 'Error al procesar el pago.',
      });
      loading.value = false;
    },
    preserveScroll: true,
    preserveState: false

  });
    
};
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
  .listen('.cancelar-pedido', (data) => {
    const order = data.pedido
    const index = ordenes.value.findIndex((o) => o.id === order.id);
    if (index !== -1) {
      ordenes.value.splice(index, 1);
    }
    showToast('info', 'Pedido cancelado')
  })
  .listen('.enviar-caja-pedido', (data) => {
    // Realizar una solicitud para obtener los datos del pedido desde el servidor
    axios.get(`/getPedido/${data.id}`)
      .then(response => {
        const pedido = response.data;
        console.log(pedido);

        const ordenExistente = ordenes.value.find((orden) => orden?.id === pedido.id);

        if (ordenExistente) {
          // Si ya existe en pedidos en mesa, actualizarlo
          Object.assign(ordenExistente, pedido);
          showToast('success', 'Pedido en mesa actualizado');
        } else {
          // Si no existe, agregarlo a pedidos
          const wasEmpty = ordenes.value.length === 0; // Verificar si la lista estaba vacía
          ordenes.value.push(pedido);
          showToast('success', 'Nuevo pedido en mesa añadido');

          // Si estaba vacía, seleccionar automáticamente la nueva orden
          if (wasEmpty) {
            selectedOrderId.value = pedido.id;
          }
        }
      })
      .catch(error => {
        // Manejo de errores en caso de que la solicitud falle
        console.error('Error al obtener el pedido:', error);
      });
  })
  .listen('.pagar-pedido', (data) => {
    axios.get(`/getPedido/${data.id}`)
      .then(response => {
        const pedido = response.data;
        console.log(pedido);

        const ordenExistente = ordenes.value.find((orden) => orden?.id === pedido.id);
        printTicket(pedido.id)
        if (ordenExistente) {
          // Si ya existe en pedidos en mesa, actualizarlo
          Object.assign(ordenExistente, pedido);
          showToast('success', 'Pedido en mesa actualizado');
        } else {
          // Si no existe, agregarlo a pedidos
          const wasEmpty = ordenes.value.length === 0; // Verificar si la lista estaba vacía
          ordenes.value.push(pedido);
          showToast('success', 'Nuevo pedido en mesa añadido');

          // Si estaba vacía, seleccionar automáticamente la nueva orden
          if (wasEmpty) {
            selectedOrderId.value = pedido.id;
          }
        }
      })
      .catch(error => {
        // Manejo de errores en caso de que la solicitud falle
        console.error('Error al obtener el pedido:', error);
      });
  })


  const printTicket = (id) => {
  const order = ordenes.value.find(order => order.id === id) || props.ordenesRecientes.find(orden => orden.id === id); // Encuentra el pedido por su ID
  
  if (!order) {
    showToast('error', 'Pedido no encontrado');
    return;
  }
  const totalN = (order.productos.reduce((sum, product) => {
    const cantidad = parseFloat(product.cantidad) || 0; // Asegura que cantidad sea un número
    const precio = parseFloat(product.producto.precio) || 0; // Asegura que precio sea un número
      return sum + cantidad * precio;
  }, 0) 
  - (parseFloat(order.descuento) || 0) // Asegura que descuento sea un número
  + (parseFloat(order.propina) || 0)); // Asegura que propina sea un número


  fetch('https://print.test/print-ticket', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      order_id: order.id,
      mesa: order.mesa ? order?.mesa?.nombre : 'Para llevar', // Si tiene mesa, envíala, si no, 'Para llevar'
      nombre_cliente: order?.nombre_cliente || 'Sin nombre', // Nombre del cliente o 'Sin nombre' si no hay
      productos: order.productos.map(product => ({
        nombre: product.producto.nombre,
        cantidad: product.cantidad,
        precio: product.producto.precio,
        total: (product.cantidad * parseFloat(product.producto.precio)), // Total por producto
      })),
      totalN: (order.productos.reduce((sum, product) => sum + product.cantidad * parseFloat(product.producto.precio)) - order.descuento + order.propina), // Total con descuento y propina
      descuento: order.descuento || 0,
      propina: order.propina || 0,
      fecha: order.created_at, // Fecha del pedido
      // Puedes agregar más campos según sea necesario
      tipo: order.para_mesa ? 'Local' : 'Para llevar',
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showToast('success', 'Ticket impreso correctamente');
      } else {
        showToast('error', data.message || 'Error al imprimir el ticket');
      }
    })
    .catch((error) => {
      console.error('Error en la impresión del ticket:', error);
      showToast('error', 'Error al imprimir el ticket');
    });
};

</script>

