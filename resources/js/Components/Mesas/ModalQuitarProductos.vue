<template>
    <div  class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
      <div class="bg-gray-50 rounded-xl max-h-[90vh] w-full max-w-7xl overflow-auto">
        <div class="p-6">
          <div class="flex w-full">
            <!-- Invoice -->
            <div class="w-full ">
              <div class="bg-white rounded-xl p-6">
                <div class="flex justify-between items-center mb-6">
                  <h2 class="text-xl font-bold">Orden <span v-if="para_llevar">para llevar</span></h2>
                  <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">
                    <XIcon class="w-6 h-6" />
                  </button>
                </div>
  
                <!-- Selección de Persona -->
                <div class="mb-4">
                  <label class="block text-gray-700 font-medium mb-2">Seleccionar Persona:</label>
                  <span class="text-gray-400 text-sm font-bold">Deslizar a la derecha para eliminar un item completo</span>
                </div>
  
                <!-- Listado de pedidos por persona -->
                <div class="space-y-4 mb-6 shadow-lg p-2 overflow-hidden" v-for="person in people" :key="person.id">
                  <div @click="selectedPerson = person.id" class="flex mb-2 gap-2 bg-blue-50 mx-2 text-xl transition-all w-full"
                    :class="{ 'font-bold border-b-green-600 w-full border-b-2 pb-1 bg-green-300 rounded-lg justify-center animate-pulse text-2xl': selectedPerson === person.id }">
                    <h3>
                      Persona {{ person.id }}
                    </h3>
                    <p v-if="selectedPerson === person.id" class="size-3 rounded-full my-auto bg-green-500"></p>
                  </div>
  
                  <div v-for="item in person.cartItems" :key="item.id" class="relative rounded-2xl overflow-hidden">
                    <!-- Fondo rojo con icono de basura -->
                    <div class="absolute inset-0 bg-red-500 flex items-center justify-start pl-6">
                      <TrashIcon class="w-6 h-6 text-white" />
                    </div>
                    
                    <!-- Contenido del item -->
                    <div class="touch-container bg-white w-full relative z-10"
                      @touchstart="startSwipe" 
                      @touchmove="moveSwipe" 
                      @touchend="endSwipe(item.id, $event)">
                      <div class="flex gap-3 w-full">
                        <img :src="item.imagen !== 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAFAAAABQCAYAAACOEfKtAAAAAXNSR0IArs4c6QAAAohJREFUeF7t1dunAlEYBfBvIpGSrtJVL5H+//+i10Qv6Sq6kHroIh3fxz7mjHSqZZOsIWZq1jT7N2vvCXq93k24vS0QEPBtOwsSEPMjIOhHQAKiAmCeayABQQEwzgYSEBQA42wgAUEBMM4GEhAUAONsIAFBATDOBhIQFADjbCABQQEwzgYSEBQA42wgAUEBMM4GEhAUAONsIAFBATDOBhIQFADjbCABQQEwzgYSEBQA42wgAUEBMO69gZVKRcrlsgRBIMfjUfr9vt1ys9mUQqFg+/v9XobD4UtDuZdPp9PSarUkHo/L9XqV6XQqm83mpeu+erJXQB2QDnS73cpisZButyuHw8E+1WpV5vO5nM/nP+c8M4B8Pn83n0qlLK4PQ/9Xj90De+a675zjFVBvqNPpyG63M0C3r99nMhkZDAZ2z+12W06nk4zHYzvW1haLRZnNZnZcq9VktVrZNXTTB9NoNGS5XNoDcPulUun3vxRZmz+ZTKzhvjbvgOFptV6vDUmBooCuOW6gek42m7Wp7xocRlCger0usVjMIMMPSPfDLfU5jb0C6iDC7dEpfLlcbAo/aqCD0mZGYaMN1QbquqctTyaT39XAaAsciDbivzXQrWEKpuBuejvAXC5n3+n0dGtrIpH4vjUw/LbU9o1GIxv0o7ew/qbTV9+i99ZAt27q8qCbe7t/3VvY18L9Sdf1ugZ+0kB93QsBQVkCEhAUAONsIAFBATDOBhIQFADjbCABQQEwzgYSEBQA42wgAUEBMM4GEhAUAONsIAFBATDOBhIQFADjbCABQQEwzgYSEBQA42wgAUEBMM4GEhAUAONsIAFBATDOBhIQFADjbCABQQEwzgYSEBQA4z92vyNfXU0apAAAAABJRU5ErkJggg==' ? '/storage/'+item.imagen : item.imagen" :alt="item.nombre" class="w-16 h-16 rounded-lg object-cover" />
                        <div class="flex-1">
                          <h3 class="font-medium">{{ item.nombre }}</h3>
                          <h3 class="font-thin text-xl text-gray-500">{{ item.detalle }}</h3>
                          <span class="text-xl font-medium">${{ (item.precio * item.quantity).toFixed(1) }}</span>
                          <div class="flex w-full">
                            <button class="text-xl text-gray-50 bg-orange-400 hover:bg-orange-500 rounded-full p-1 hover:text-gray-700" @click="decrementQuantity(item, person.id)">
                              <MinusIcon />
                            </button>
                            <button v-if="item.quantity < item.stock" :disabled="item.quantity >= item.stock" class="text-xl ml-4 text-gray-50 bg-blue-400 hover:bg-blue-500 rounded-full p-1 hover:text-gray-700" @click="incrementQuantity(item, person.id)">
                              <PlusIcon />
                            </button>
                            <button v-if="deletedProducts.some((p) => p.id === item.id && p.persona_id === person.id)" @click="restoreItem(item.id, person.id)" class="text-xl ml-6 text-gray-50 bg-green-400 hover:bg-green-500 rounded-full p-1 hover:text-gray-700">
                              Restaurar
                            </button>
                          </div>
                        </div>
                        <div class="flex items-center bg-white justify-around flex-col">
                          <span class="text-xl">x{{ item.quantity }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
  
                <div class="border-t pt-4 mb-6">
                  <div class="flex justify-between mb-2">
                    <span class="text-gray-900 font-bold">Total</span>
                    <span class="font-medium">${{ total.toFixed(1) }}</span>
                  </div>
                </div>
  
                <button class="w-full py-3 px-4 rounded-lg bg-blue-500 text-white font-medium hover:bg-blue-600 flex justify-center items-center gap-2" @click="handleSubmit" :disabled="isSubmitting">
                  <PenBoxIcon class="w-4 h-4" />
                  Modificar Orden
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, computed } from 'vue';
  import {
    MinusIcon,
    PenBoxIcon,
    PlusIcon,
    XIcon,
    Trash2 as TrashIcon,
  } from 'lucide-vue-next';
  import Swal from 'sweetalert2';
  import { router, usePage } from '@inertiajs/vue3';
  
  console.log("props", usePage().props);

  const props = defineProps({
    table: { type: Object, required: false },
    reConsumo: { type: Boolean, default: false },
    para_llevar: { type: Boolean, default: false },
    orden: { type: Object, default: () => [] },
    delivery: { type: Boolean, default: false },
  });
  
  const emit = defineEmits(['close', 'submit']);
  const people = ref(
    props.orden?.productos?.reduce((acc, item) => {
      let person = acc.find((p) => p.id === item.persona_id);
      if (!person) {
        person = { id: item.persona_id, cartItems: [] };
        acc.push(person);
      }
      if (item.estado === 'pendiente') {
        person.cartItems.push({
          ...item.producto,
          quantity: item.cantidad,
          stock: item.cantidad,
          precio: parseFloat(item.producto.precio), // Asegura el formato numérico
        });
      }
      return acc;
    }, []) || []
  );
  
  // Si no hay personas asociadas en los productos, agregar al menos una.
  if (people.value.length === 0) {
    people.value.push({ id: 1, cartItems: [] });
  }
  
  const selectedPerson = ref(people.value[0]?.id || 1);
  const isSubmitting = ref(false);
  const deletedProducts = ref([]);
  const modifiedProducts = ref([]);
  
  const total = computed(() => {
    return people.value.reduce((sum, person) => {
      return sum + person.cartItems.reduce((pSum, item) => pSum + (item.precio * item.quantity), 0);
    }, 0);
  });
  
  function incrementQuantity(item, personId) {
    const person = people.value.find((p) => p.id === personId);
    if (!person) return;
    const cartItem = person.cartItems.find((i) => i.id === item.id);
  
    if (cartItem && cartItem.quantity < item.stock) {
      cartItem.quantity++;
      trackModifiedProduct(item, personId, "increment");
    }
  }
  
  function decrementQuantity(item, personId) {
    const person = people.value.find((p) => p.id === personId);
    if (!person) return;
    const cartItem = person.cartItems.find((i) => i.id === item.id);
  
    if (cartItem) {
      if (cartItem.quantity > 1) {
        cartItem.quantity--;
        trackModifiedProduct(item, personId, "decrement");
      } else {
        person.cartItems = person.cartItems.filter((i) => i.id !== item.id);
        deletedProducts.value.push({ id: item.id, persona_id: personId, quantity: item.quantity, price: item.precio });
      }
    }
  }
  
  function trackModifiedProduct(item, personId, action) {
    const existing = modifiedProducts.value.find((p) => p.id === item.id && p.persona_id === personId);
  
    if (existing) {
      existing.quantity += action === "increment" ? 1 : -1;
    } else {
      modifiedProducts.value.push({ id: item.id, persona_id: personId, quantity: action === "increment" ? 1 : -1, price: item.precio });
    }
  }
  
  function restoreItem(productId, personId) {
    const person = people.value.find((p) => p.id === personId);
    if (!person) return;
  
    const deletedIndex = deletedProducts.value.findIndex(
      (p) => p.id === productId && p.persona_id === personId
    );
  
    if (deletedIndex !== -1) {
      const restoredItem = deletedProducts.value.splice(deletedIndex, 1)[0];
      const cartItem = person.cartItems.find((i) => i.id === productId);
  
      if (cartItem) {
        cartItem.quantity += restoredItem.quantity;
      } else {
        person.cartItems.push({
          id: restoredItem.id,
          quantity: restoredItem.quantity,
          precio: restoredItem.precio,
        });
      }
    }
  }
  
  function handleSubmit() {
    if (isSubmitting.value) return;
  
    isSubmitting.value = true;
  
    const payload = {
      id: props.orden?.id || 0,
      mesa: props.table?.id || 0,
      productos: people.value.flatMap((person) =>
        person.cartItems.map((item) => ({
          id: item.id,
          cantidad: item.quantity,
          subtotal: item.precio * item.quantity,
          precio: item.precio,
          persona_id: person.id,
        }))
      ),
      productosEliminados: deletedProducts.value,
      productosModificados: modifiedProducts.value,
      total: total.value,
    };
  
    router.post('/productosEliminados', payload, {
      onSuccess: () => {
        Swal.fire({
          icon: "success",
          title: "Pedido actualizado",
          toast: true,
          position: "top-end",
          timer: 1500,
          showConfirmButton: false,
        });
        emit("submit", payload);
        isSubmitting.value = false;
        emit("close");
        emit("close");
      },
      onError: (e) => {
        console.log(e);
        Swal.fire({
          icon: "error",
          title: "Error al actualizar el pedido",
          toast: true,
          position: "top-end",
          timer: 1500,
          showConfirmButton: false,
        });
        isSubmitting.value = false;
      },
    });
    emit("close");
  }
  
  let startX = 0;
  let currentX = 0;
  
  function startSwipe(event) {
    startX = event.touches[0].clientX;
    currentX = startX;
  }
  
  function moveSwipe(event) {
    currentX = event.touches[0].clientX;
    const deltaX = currentX - startX;
    
    // Limitar el desplazamiento a la derecha solamente y hasta un máximo
    if (deltaX > 0) {
      const maxSwipe = event.currentTarget.offsetWidth * 0.7; // 70% del ancho
      const swipeAmount = Math.min(deltaX, maxSwipe);
      event.currentTarget.style.transform = `translateX(${swipeAmount}px)`;
    }
  }
  
  function endSwipe(itemId, event) {
    const deltaX = currentX - startX;
    const swipeThreshold = event.currentTarget.offsetWidth / 3; // Reducido a 1/3 para que sea más sensible
  
    if (deltaX > swipeThreshold) {
      event.currentTarget.style.transform = `translateX(${event.currentTarget.offsetWidth}px)`;
      setTimeout(() => removeItem(itemId), 300); // Add a delay for the animation to complete
    } else {
      event.currentTarget.style.transform = `translateX(0)`;
    }
  }
  
  function removeItem(productId) {
    const person = people.value.find((p) =>
      p.cartItems.some((item) => item.id === productId)
    );
    if (!person) return;
  
    const product = person.cartItems.find((item) => item.id === productId);
  
    if (product) {
      deletedProducts.value.push({
        id: productId,
        persona_id: person.id, // Asociar el producto con la persona
        quantity: product.quantity,
        price: product.precio,
      });
      person.cartItems = person.cartItems.filter((item) => item.id !== productId);
    }
  }
  
  function addPerson() {
    const newId = people.value.length + 1;
    people.value.push({ id: newId, cartItems: [] });
  }
  </script>
  
  <style>
  .touch-container {
    touch-action: pan-y; /* Permite scroll vertical pero detecta swipe horizontal */
    transition: transform 0.3s ease;
    position: relative;
    z-index: 1;
  }
  </style>