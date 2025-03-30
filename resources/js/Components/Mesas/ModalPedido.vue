<template>
  <div class="fixed  inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-gray-50 rounded-xl max-h-[90vh] w-full max-w-7xl overflow-auto">
      <div class="p-6 flex ">
        <div class="flex w-full  gap-6">
          <!-- Main Content -->
          <div class=" w-3/6">
            <!-- Categories -->
            <div class="w-full">
              <div class="flex overflow-x-auto gap-4">
                <button v-for="category in categories" :key="category.name" @click="setActiveCategory(category)" :class="['p-4 rounded-xl  size-fit flex flex-col items-center  transition-colors',
                  category.active ? 'bg-blue-500 text-white' : 'bg-white hover:bg-gray-50'
                ]">
                  <component :is="category.icon" class="w-6 h-6" />
                  <div class="text-xl font-medium">{{ category.name }}</div>
                </button>
              </div>
            </div>

            <!-- Menu Section -->
            <h2 class="text-2xl  font-bold mb-6">Pedido para {{ table?.nombre || 'mesa desconocida' }}</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 w-full lg:grid-cols-3 gap-3">
              <div v-for="item in filteredMenuItems" :key="item.id" class="bg-white w-full size-fit rounded-xl p-1  ">
                <div class="flex gap-4 ">
                  <div class="flex-1 flex flex-col">
                    <div class="flex w-full gap-2 ">
                      <img :src="item.imagen" :alt="item.nombre" class="size-24  rounded-xl object-cover" />
                      <div class="flex flex-col">
                        <h3 class="font-medium  text-2xl mb-1">{{ item.nombre }}</h3>
                        <p v-if="item.detalle" class="text-xl text-gray-500 mb-2">{{ item.detalle }}</p>
                      </div>
                    </div>
                    <span v-if="item.tipo === 'bebida'">Cantidad {{ item.cantidad }}</span>
                    <span class="text-2xl justify-center text-center font-bold">${{ item.precio || 0 }}</span>
                    <div class="flex items-center justify-between flex-row sm:flex-col">
                      <div class="flex flex-col ">
                        <div>
                          <input type="number" placeholder="Cantidad"
                            class="h-8 rounded-full w-full border border-gray-800 shadow-2xl hover:bg-gray-50"
                            v-model="cantidadInput[item.id]" min="1" />

                        </div>
                        <div class="flex  justify-around w-full mt-3  ">
                          <button @click="decrementQuantity(item, selectedPerson)"
                            class="h-10 rounded-full flex items-center justify-center border border-1 shadow-2xl border-gray-800 hover:bg-gray-50"
                            :disabled="item.quantity <= 0">

                            <MinusIcon class="w-12" />
                          </button>
                          <span class="w-4 text-center">{{ item.quantity }}</span>
                          <button :disabled="item.cantidad == 0 && item.tipo == 'bebida'"
                            @click="incrementQuantity(item, selectedPerson)"
                            class="h-10 shadow-lg rounded-full flex z-10 items-center justify-center bg-blue-500 text-white hover:bg-blue-600">
                            <PlusIcon class=" w-14" />
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Invoice -->
          <div class="w-3/6 ">
            <div class="bg-white rounded-xl p-6">

              <div class="flex justify-between  mb-6">
                <h2 class="text-xl font-bold">Orden <span v-if="para_llevar">para llevar</span><span v-else>{{
                    table.nombre }}</span></h2>
                <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">
                  <XIcon class="w-6 h-6" />
                </button>
              </div>
              <div class="w-full">
                <div v-if="orden && orden.length === 0" class="flex justify-between">
                  <label class="py-2 font-semibold" for="a-nombre-de">Para:</label>
                  <input type="text" class="border rounded-lg px-3 py-2 mb-5" placeholder="Nombre"
                    v-model="orden.nombre_cliente" />
                </div>
                <div v-else-if="orden?.nombre_cliente"
                  class="flex justify-between items-center bg-gray-50 rounded-lg shadow-md p-4 mb-5">
                  <label class="font-semibold text-gray-700" for="a-nombre-de">A nombre de:</label>
                  <div class="flex items-center space-x-3">
                    <AlertCircleIcon class="size-4 text-blue-500" />
                    <!-- Nombre destacado -->
                    <span class="text-blue-800 font-bold text-lg border-b-2 border-blue-400">
                      {{ orden.nombre_cliente }}
                    </span>
                  </div>
                </div>



                <div v-else
                  :class="[!editarNombreCliente ? 'flex justify-between w-full items-center bg-gray-50/90 rounded-lg shadow-md p-2 mb-5' : 'flex justify-between w-full items-center']">
                  <div v-if="!editarNombreCliente" class="flex w-full items-center space-x-3 justify-center">
                    <AlertCircleIcon class="size-4 text-yellow-500" />
                    <label class="text-lg font-semibold text-gray-400" for="a-nombre-de">Sin nombre</label>
                  </div>
                  <div v-else class="flex justify-between">
                    <label class="py-2 font-semibold" for="a-nombre-de">Para:</label>
                    <input type="text" class="border rounded-lg px-3 py-2 mb-5" placeholder="Nombre"
                      v-model="newNombre_cliente" />
                  </div>
                  <button @click="handleEditNombreCliente">
                    <PencilIcon class="size-5 text-gray-500 hover:text-gray-800" />
                  </button>
                </div>
              </div>


              <!-- Selección de Persona -->
              <div class="mb-4 flex justify-between flex-col md:flex-row">
                <div class="flex items-center space-x-3 border px-2 rounded-lg"
                  @click="check_para_llevar = !check_para_llevar">
                  <input type="checkbox" class="size-5 rounded-lg" v-model="check_para_llevar">
                  <label class="text-lg font-semibold text-gray-400">Para llevar</label>
                </div>
                <button @click="addPerson" class="mt-2 text-white bg-blue-500 px-2 py-1 rounded-lg shadow-xl text-xl">+
                  Agregar Persona</button>
              </div>


              <!-- Listado de pedidos por persona -->

              <div class="space-y-4 mb-6 shadow-lg p-2 " v-for="person in people" :key="person.id">
                <div @click="selectedPerson = person.id"
                  class="flex mb-2 gap-2 bg-blue-50 mx-2 text-xl  transition-all  w-full"
                  :class="{ 'font-bold border-b-green-600 w-full border-b-2 pb-1 bg-green-300 rounded-lg  justify-center animate-pulse text-2xl': selectedPerson === person.id }">
                  <h3>
                    Persona {{ person.id }}
                  </h3>
                  <p v-if="selectedPerson === person.id" class="size-3 rounded-full my-auto bg-green-500">
                  </p>
                </div>

                <div v-for="item in person.cartItems" :key="item.id" class="">
                  <div class="flex flex-row">
                    <img :src="item.imagen" :alt="item.nombre" class="size-8 rounded-lg object-cover" />
                    <div class="flex w-full justify-between">
                      <div class="flex flex-col">
                        <h3 class="font-medium">{{ item.nombre }}</h3>
                        <h3 class=" font-thin text-xl text-gray-500">{{ item.detalle }}</h3>
                        <span class="text-xl font-medium">${{ (item.precio * item.quantity).toFixed(1) }}</span>
                      </div>
                      <div class="flex items-center justify-around flex-col ">
                        <span class="text-2xl">x{{ item.quantity }}</span>
                        <span v-if="item.estado" :class="[
                          'px-2 text-xl text-white rounded-lg flex flex-col justify-center items-center place-items-center transition-colors',
                          item.estado === 'libre' ? 'bg-green-400 hover:bg-green-200' :
                            item.estado === 'ocupada' ? 'bg-red-400 hover:bg-red-200' :
                              item.estado === 'espera_pago' ? 'bg-blue-400 hover:bg-blue-200' :
                                item.estado === 'espera_entrega' ? 'bg-gray-400 hover:bg-gray-200' :
                                  item.estado === 'para_llevar' ? 'bg-orange-400 hover:bg-orange-200' :
                                    item.estado === 'espera' ? 'bg-blue-400 hover:bg-blue-200' :
                                      ''
                        ]">{{ textoEstado(item.estado) }}</span>
                      </div>
                      
                    </div>
                    
                  </div>
                  <!-- Lista de opciones de personalización -->
                  <div class="flex  flex-row gap-2 over overflow-auto">
                        <div v-for="option in personalizaciones" :key="option" class="capitalize ">
                          <label class="flex items-center gap-1 p-1 rounded-lg border cursor-pointer hover:bg-gray-100">
                            <input type="checkbox" :checked="item.personalizacion.includes(option)"
                              @change="(e) => updatePersonalizaciones(item, option, e.target.checked)" class="hidden">
                            <span class="p-1 px-2 rounded-lg text-sm transition-all" :class="item.personalizacion.includes(option)
                              ? 'bg-blue-500 text-white'
                              : 'bg-gray-100 text-gray-700'">
                              {{ option }}
                            </span>
                          </label>
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

              <button
                class="w-full py-3 px-4 rounded-lg bg-blue-500 text-white font-medium hover:bg-blue-600 flex justify-center items-center gap-2"
                @click="handleSubmit" :disabled="isSubmitting">
                <ChefHatIcon class="size-8" />
                Mandar a Cocina
              </button>

            </div>
            <div class="mt-32">
              <label class="block text-gray-700 font-medium mb-2" for="observaciones">Observaciones:</label>
              <input type="text" name="observaciones" id="observaciones" v-model="orden.observaciones"
                class="w-full p-2 border rounded-md">
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>


<script setup>
import { ref, computed, reactive, watch } from 'vue'
import {
  SoupIcon,
  ChefHatIcon,
  PlusIcon,
  MinusIcon,
  CupSoda,
  PackageOpenIcon,
  ForkKnifeCrossed,
  XIcon,
  AlertCircleIcon
} from 'lucide-vue-next'
import Swal from 'sweetalert2'
import { router, usePage } from '@inertiajs/vue3';
import { Settings, PencilIcon } from 'lucide-vue-next';

const props = defineProps({
  table: {
    type: Object,
    required: false
  },
  reConsumo: {
    type: Boolean,
    default: false
  },
  para_llevar: {
    type: Boolean,
    default: false
  },
  orden: {
    type: Object,
    default: () => []
  },
  delivery: {
    type: Boolean,
    default: false
  },
  ordenParaLlevar: {
    type: Number,
    default: 0
  }
})

const check_para_llevar = ref(false)

const cantidadInput = reactive({})

// Personalización
const personalizaciones = [
  'con todo',
  'sin cebolla',
  'sin verdura',
]

// Estado inicial con "con todo" seleccionado
const selectedPersonalizaciones = ref(['con todo']);

// Watch para manejar las opciones de "con todo"
watch(selectedPersonalizaciones, (newValue) => {
  if (newValue.includes('con todo')) {
    // Si 'con todo' está seleccionado, desmarcar todas las otras opciones
    selectedPersonalizaciones.value = ['con todo'];
  } else {
    // Si 'con todo' no está seleccionado, se permiten las otras opciones
    selectedPersonalizaciones.value = newValue.filter(item => item !== 'con todo');
  }
});

const updatePersonalizaciones = (item, option, isChecked) => {
  if (option === 'con todo') {
    item.personalizacion = isChecked ? ['con todo'] : [];
  } else {
    item.personalizacion = item.personalizacion.filter(p => p !== 'con todo');
    if (isChecked) {
      item.personalizacion.push(option);
    } else {
      item.personalizacion = item.personalizacion.filter(p => p !== option);
    }
  }
};

const editarNombreCliente = ref(false)
const handleEditNombreCliente = () => {
  editarNombreCliente.value = !editarNombreCliente.value
}
const newNombre_cliente = ref('')
const emit = defineEmits(['close', 'submit'])

const menuItems = ref(usePage().props.inventario)

const people = ref(
  props?.orden?.productos?.length > 0
    ? props.orden.productos.reduce((acc, item) => {
      let person = acc.find(p => p.id === item.persona_id);
      if (!person) {
        person = { id: item.persona_id, cartItems: [] };
        acc.push(person);
      }
      return acc;
    }, [])
    : [{ id: 1, cartItems: [] }] // Estado inicial si no hay orden
);

const selectedPerson = ref(people.value[0]?.id || 1);

function handleSubmit() {
  if (total.value === 0 && !editarNombreCliente.value && !props.orden?.observaciones.value === '') {
    Toast.fire({
      icon: "error",
      title: "No hay productos en el pedido"
    });
    return;
  }

  if (isSubmitting.value) {
    return; // Prevenir múltiples envíos
  }

  isSubmitting.value = true; // Desactiva el botón

  console.log(people.value)
  router.post('/pedidos', {
    mesa: props?.table?.id || 0,
    nombre_cliente: props.orden?.nombre_cliente || newNombre_cliente.value || null,
    productos: people.value.flatMap(person =>
      person.cartItems.map(item => ({
        id: item.id,
        cantidad: item.quantity,
        personalizacion: item.personalizacion,
        estado: item.estado,
        subtotal: item.precio * item.quantity,
        personalizacion: item.personalizacion.join(', '),
        persona_id: person.id
      }))
    ),
    total: total.value,
    reConsumo: props.reConsumo,
    para_llevar: check_para_llevar.value,
    delivery: props.delivery,
    observaciones: props.orden?.observaciones || null,
    id_pedido_llevar: props.ordenParaLlevar || null
  },
    {
      onSuccess: (response) => {
        console.log(response)
        Toast.fire({
          icon: "success",
          title: "Pedido enviado a cocina"
        });
        emit('submit', {
          table: props.table,
          people: people.value,
          total: total.value
        });
        isSubmitting.value = false; // Reactiva el botón
        emit('close');
      },
      onError: (error) => {
        console.error(error);
        isSubmitting.value = false; // Reactiva el botón en caso de error
      },
      preserveScroll: true,
      preserveState: false
    });
}

const textoEstado = (estado) => {
  switch (estado) {
    case 'espera_entrega':
      return 'Espera Entrega';
    case 'espera_pago':
      return 'Espera Pago';
    case 'para_llevar':
      return 'Para Llevar';
    case 'libre':
      return 'Libre';
    case 'ocupada':
      return 'Ocupada';
    case 'espera':
      return 'Entregado';
    default:
      return 'Estado Desconocido';
  }
}

const categories = ref([
  { name: 'Comida', catego: 'comida', icon: ForkKnifeCrossed, active: true },
  { name: 'Bebidas', catego: 'bebida', icon: CupSoda, active: false },
  { name: 'Paquetes', catego: 'paquete', icon: PackageOpenIcon, active: false },
  { name: 'Extras', catego: 'extras', icon: SoupIcon, active: false },
])

function setActiveCategory(category) {
  categories.value.forEach(cat => cat.active = false)
  category.active = true
}

const filteredMenuItems = computed(() => {
  const activeCategory = categories.value.find(cat => cat.active)
  return menuItems.value.filter(item => item.tipo === activeCategory.catego)
})

function addPerson() {
  const newId = people.value.length + 1
  people.value.push({ id: newId, cartItems: [] })
}

function incrementQuantity(item, personId) {
  const person = people.value.find((p) => p.id === personId);
  if (!person) return;

  const cantidad = cantidadInput[item.id] || 1; // Si no hay cantidad ingresada, usa 1

  let cartItem = person.cartItems.find((i) => i.id === item.id);
  if (cartItem) {
    cartItem.quantity += cantidad;
  } else {
    person.cartItems.push({ ...item, quantity: cantidad, personalizacion: [] });
  }

  cantidadInput[item.id] = ''; // Resetea el input después de agregar
}

function decrementQuantity(item, personId) {
  const person = people.value.find(p => p.id === personId);
  if (!person) return;

  const cartItem = person.cartItems.find(i => i.id === item.id);
  if (cartItem) {
    if (cartItem.quantity > 1) {
      cartItem.quantity--;
    } else {
      person.cartItems = person.cartItems.filter(i => i.id !== item.id);
    }
  }
}

const total = computed(() => {
  return people.value.reduce((sum, person) => {
    return sum + person.cartItems.reduce((pSum, item) => pSum + (item.precio * item.quantity), 0)
  }, 0)
})

const isSubmitting = ref(false)

const Toast = Swal.mixin({
  toast: true,
  position: "top-end",
  showConfirmButton: false,
  timer: 1500,
  timerProgressBar: true,
  didOpen: (toast) => {
    toast.onmouseenter = Swal.stopTimer
    toast.onmouseleave = Swal.resumeTimer
  }
})
</script>

<style>
input[type="checkbox"]:checked+span {
  background-color: #23ad2a;
  color: white;
}
</style>