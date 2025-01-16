<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto flex gap-6">
      <!-- Main Content -->
      <div class="flex-1">
        <!-- Categories -->
        <div class="grid grid-cols-4 sm:grid-cols-8 gap-4 mb-8">
          <button 
            v-for="category in categories" 
            :key="category.name"
            @click="setActiveCategory(category)"
            :class="[ 'p-4 rounded-xl flex flex-col items-center gap-2 transition-colors',
              category.active ? 'bg-blue-500 text-white' : 'bg-white hover:bg-gray-50'
            ]"
          >
            <component :is="category.icon" class="w-6 h-6" />
            <div class="text-sm font-medium">{{ category.name }}</div>
          </button>
        </div>

        <!-- Menu Section -->
        <h2 class="text-2xl font-bold mb-6">Menú</h2>
        <h2>Pedido para {{ table?.nombre || 'mesa desconocida' }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-for="item in filteredMenuItems" :key="item.id" class="bg-white rounded-xl p-4">
            <div class="flex gap-4">
              <img :src="item.image" :alt="item.name" class="w-20 h-20 rounded-xl object-cover" />
              <div class="flex-1">
                <h3 class="font-medium mb-1">{{ item.name }}</h3>
                <p class="text-sm text-gray-500 mb-2">{{ item.description }}</p>
                <div class="flex items-center justify-between flex-col">
                  <span class="text-lg font-bold">${{ item.price.toFixed(1) }}</span>
                  <div class="flex items-center gap-3">
                    <button 
                      @click="decrementQuantity(item, selectedPerson)"
                      class="w-8 h-8 rounded-full flex items-center justify-center border border-gray-200 hover:bg-gray-50"
                      :disabled="!item.quantity"
                    >
                      <MinusIcon class="w-4 h-4" />
                    </button>
                    <span class="w-4 text-center">{{ item.quantity }}</span>
                    <button 
                      @click="incrementQuantity(item, selectedPerson)"
                      class="w-8 h-8 rounded-full flex items-center justify-center bg-blue-500 text-white hover:bg-blue-600"
                    >
                      <PlusIcon class="w-4 h-4" />
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Invoice -->
      <div class="w-80 flex-shrink-0">
        <div class="bg-white rounded-xl p-6 sticky top-6">
          <h2 class="text-xl font-bold mb-6">Ticket</h2>

          <!-- Selección de Persona -->
          <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">Seleccionar Persona:</label>
            <select v-model="selectedPerson" class="w-full p-2 border rounded-md">
              <option v-for="person in people" :key="person.id" :value="person.id">Persona {{ person.id }}</option>
            </select>
            <button @click="addPerson" class="mt-2 text-blue-500 text-sm">+ Agregar Persona</button>
          </div>

          <!-- Listado de pedidos por persona -->
          <div class="space-y-4 mb-6" v-for="person in people" :key="person.id">
            <h3 class="text-lg font-bold mb-2">Persona {{ person.id }}</h3>
            <div v-for="item in person.cartItems" :key="item.id" class="flex gap-3">
              <img :src="item.image" :alt="item.name" class="w-16 h-16 rounded-lg object-cover" />
              <div class="flex-1">
                <h3 class="font-medium">{{ item.name }}</h3>
                <span class="text-sm font-medium">${{ (item.price * item.quantity).toFixed(1) }}</span>
              </div>
              <span class="text-sm">x{{ item.quantity }}</span>
            </div>
          </div>

          <div class="border-t pt-4 mb-6">
            <div class="flex justify-between mb-2">
              <span class="text-gray-900 font-bold">Total</span>
              <span class="font-medium">${{ total.toFixed(1) }}</span>
            </div>
          </div>

          <button 
            class="w-full py-3 px-4 rounded-lg bg-blue-500 text-white font-medium hover:bg-blue-600 flex justify-center"
            @click="sendToKitchen"
          >
          <ChefHatIcon  class="size-4 m-1"/> Mandar a Cocina 
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { 
  SoupIcon,
  ChefHatIcon,
  PlusIcon,
  MinusIcon,
  CupSoda,
  PackageOpenIcon,
  ForkKnifeCrossed
} from 'lucide-vue-next'
import Swal from 'sweetalert2';

defineProps({
  table: Object, // La mesa seleccionada que se pasa como prop.
});

const emit = defineEmits(['close', 'submit']); // Eventos para cerrar y enviar el pedido.

const img = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAFAAAABQCAYAAACOEfKtAAAAAXNSR0IArs4c6QAAAohJREFUeF7t1dunAlEYBfBvIpGSrtJVL5H+//+i10Qv6Sq6kHroIh3fxz7mjHSqZZOsIWZq1jT7N2vvCXq93k24vS0QEPBtOwsSEPMjIOhHQAKiAmCeayABQQEwzgYSEBQA42wgAUEBMM4GEhAUAONsIAFBATDOBhIQFADjbCABQQEwzgYSEBQA42wgAUEBMM4GEhAUAONsIAFBATDOBhIQFADjbCABQQEwzgYSEBQA42wgAUEBMO69gZVKRcrlsgRBIMfjUfr9vt1ys9mUQqFg+/v9XobD4UtDuZdPp9PSarUkHo/L9XqV6XQqm83mpeu+erJXQB2QDnS73cpisZButyuHw8E+1WpV5vO5nM/nP+c8M4B8Pn83n0qlLK4PQ/9Xj90De+a675zjFVBvqNPpyG63M0C3r99nMhkZDAZ2z+12W06nk4zHYzvW1haLRZnNZnZcq9VktVrZNXTTB9NoNGS5XNoDcPulUun3vxRZmz+ZTKzhvjbvgOFptV6vDUmBooCuOW6gek42m7Wp7xocRlCger0usVjMIMMPSPfDLfU5jb0C6iDC7dEpfLlcbAo/aqCD0mZGYaMN1QbquqctTyaT39XAaAsciDbivzXQrWEKpuBuejvAXC5n3+n0dGtrIpH4vjUw/LbU9o1GIxv0o7ew/qbTV9+i99ZAt27q8qCbe7t/3VvY18L9Sdf1ugZ+0kB93QsBQVkCEhAUAONsIAFBATDOBhIQFADjbCABQQEwzgYSEBQA42wgAUEBMM4GEhAUAONsIAFBATDOBhIQFADjbCABQQEwzgYSEBQA42wgAUEBMM4GEhAUAONsIAFBATDOBhIQFADjbCABQQEwzgYSEBQA4z92vyNfXU0apAAAAABJRU5ErkJggg=='

const menuItems = ref([
    {
      id: 1,
      name: 'Taco',
      description: 'Detalles',
      category: 'Comida',
      price: 15.0,
      quantity: 2,
      image: img
    },
    {
      id: 2,
      name: 'Plato CHICO',
      description: 'Detalles',
      price: 85.0,
      category: 'Paquetes',
      quantity: 2,
      image: img
    },
    {
      id: 3,
      name: 'Plato GRANDE',
      description: 'Detalles',
      price: 110.0,category: 'Paquetes',
      quantity: 0,
      image: img
    },
    {
      id: 4,
      name: 'QUESABIRRIA',
      description: 'Detalles',
      price: 42.0,
      category: 'Comida',
      quantity: 0,
      image: img
    },
    {
      id: 5,
      name: '1/4 Kilo',
      description: 'Detalles',
      price: 130.0,
      quantity: 0,
      category: 'Paquetes',
      image: img
    },
    {
      id: 6,
      name: '1/2 Kilo',
      description: 'Detalles',
      price: 240.0,
      quantity: 0,
      category: 'Comida',
      image: img
    },
    {
      id: 7,
      name: 'Coca Cola',
      description: '500 ml',
      price: 240.0,
      quantity: 1,
      category: 'Bebidas',
      image: img
    },{
      id: 8,
      name: 'Manzanita',
      description: '600 ml',
      price: 240.0,
      quantity: 0,
      category: 'Bebidas',
      image: img
    }
  ])

const people = ref([{ id: 1, cartItems: [] }])
const selectedPerson = ref(1)

function setActiveCategory(category) {
  categories.value.forEach(cat => cat.active = false)
  category.active = true
}

const filteredMenuItems = computed(() => {
  const activeCategory = categories.value.find(cat => cat.active)
  return menuItems.value.filter(item => item.category === activeCategory.name)
})

function addPerson() {
  const newId = people.value.length + 1
  people.value.push({ id: newId, cartItems: [] })
}

function incrementQuantity(item, personId) {
  const person = people.value.find(p => p.id === personId)
  const cartItem = person.cartItems.find(i => i.id === item.id)
  if (cartItem) cartItem.quantity++
  else person.cartItems.push({ ...item, quantity: 1 })
}

function decrementQuantity(item, personId) {
  const person = people.value.find(p => p.id === personId)
  const cartItem = person.cartItems.find(i => i.id === item.id)
  if (cartItem && cartItem.quantity > 0) cartItem.quantity--
}

const total = computed(() => {
  return people.value.reduce((sum, person) => {
    return sum + person.cartItems.reduce((pSum, item) => pSum + (item.price * item.quantity), 0)
  }, 0)
})
const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 1500,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.onmouseenter = Swal.stopTimer;
        toast.onmouseleave = Swal.resumeTimer;
    }
});

function sendToKitchen() {
  Toast.fire({
    icon: "success",
    title: "Pedido enviado a cocina"
  });
}



const categories = ref([
  { name: 'Comida', icon: ForkKnifeCrossed, active: false },
  { name: 'Paquetes', icon: PackageOpenIcon, active: true },
  { name: 'Bebidas', icon: CupSoda, active: false },
  { name: 'Extras', icon: SoupIcon, active: false },
])


</script>
