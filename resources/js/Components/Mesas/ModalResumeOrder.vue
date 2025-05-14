<template>
    <div  class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
      <div class="bg-gray-50 rounded-xl max-h-[90vh] w-full max-w-7xl overflow-auto">
        <div class="p-6">
          <div class="flex w-full">
            <!-- Invoice -->
            <div class="full w-full">
              <div class="bg-white rounded-xl p-6">
                <div class="flex justify-between items-center mb-6">
                  <h2 class="text-xl font-bold">Resumen Orden <span v-if="para_llevar">para llevar</span></h2>
                  <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">
                    <XIcon class="w-6 h-6" />
                  </button>
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
                    <!-- Contenido del item -->
                    <div class="touch-container bg-white w-full relative z-10">
                      <div class="flex gap-3 w-full">
                        <img :src="item.imagen !== 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAFAAAABQCAYAAACOEfKtAAAAAXNSR0IArs4c6QAAAohJREFUeF7t1dunAlEYBfBvIpGSrtJVL5H+//+i10Qv6Sq6kHroIh3fxz7mjHSqZZOsIWZq1jT7N2vvCXq93k24vS0QEPBtOwsSEPMjIOhHQAKiAmCeayABQQEwzgYSEBQA42wgAUEBMM4GEhAUAONsIAFBATDOBhIQFADjbCABQQEwzgYSEBQA42wgAUEBMM4GEhAUAONsIAFBATDOBhIQFADjbCABQQEwzgYSEBQA42wgAUEBMO69gZVKRcrlsgRBIMfjUfr9vt1ys9mUQqFg+/v9XobD4UtDuZdPp9PSarUkHo/L9XqV6XQqm83mpeu+erJXQB2QDnS73cpisZButyuHw8E+1WpV5vO5nM/nP+c8M4B8Pn83n0qlLK4PQ/9Xj90De+a675zjFVBvqNPpyG63M0C3r99nMhkZDAZ2z+12W06nk4zHYzvW1haLRZnNZnZcq9VktVrZNXTTB9NoNGS5XNoDcPulUun3vxRZmz+ZTKzhvjbvgOFptV6vDUmBooCuOW6gek42m7Wp7xocRlCger0usVjMIMMPSPfDLfU5jb0C6iDC7dEpfLlcbAo/aqCD0mZGYaMN1QbquqctTyaT39XAaAsciDbivzXQrWEKpuBuejvAXC5n3+n0dGtrIpH4vjUw/LbU9o1GIxv0o7ew/qbTV9+i99ZAt27q8qCbe7t/3VvY18L9Sdf1ugZ+0kB93QsBQVkCEhAUAONsIAFBATDOBhIQFADjbCABQQEwzgYSEBQA42wgAUEBMM4GEhAUAONsIAFBATDOBhIQFADjbCABQQEwzgYSEBQA42wgAUEBMM4GEhAUAONsIAFBATDOBhIQFADjbCABQQEwzgYSEBQA4z92vyNfXU0apAAAAABJRU5ErkJggg==' ? '/storage/'+item.imagen : item.imagen" :alt="item.nombre" class=" size-28 rounded-lg object-cover" />
                        <div class="flex-1">
                          <h3 class="font-medium">{{ item.nombre }}</h3>
                          <h3 class="font-thin text-xl text-gray-500">{{ item.detalle }}</h3>
                          <span class="text-xl font-medium">${{ (item.precio * item.quantity).toFixed(1) }}</span>
                        </div>
                        <div class="flex items-center bg-white justify-around flex-col">
                          <span class="text-xl">x{{ item.quantity }}</span>
                          <div>
                            <!--si es entregado en verde y si no en rojo-->
                            <span class="p-1 px-2 rounded-lg my-1 flex capitalize text-lg transition-all" :class="item.estado.includes('entregado') ? 'bg-green-500 text-white' : 'bg-red-500 text-white'">{{ item.estado.includes('entregado') ? 'Entregado' : 'Pendiente' }}</span>
                            <!--si es para llevar, mostrar para llevar en naranja y si no verde-->
                            <span class="p-1 px-2 rounded-lg my-1 flex capitalize text-lg transition-all" :class="item.tipo_servicio === 'para_llevar' ? 'bg-orange-400 text-white' : 'bg-green-500 text-white'">{{ item.tipo_servicio === 'para_llevar' ? 'Para llevar' : 'En mesa' }}</span>
                          </div>
                        </div>
                      </div>
                      
                      <div class="flex  gap-2 justify-center " v-if="item.personalizacion" >
                        <div v-for="personalizar in item.personalizacion.split(', ')" :key="personalizar">
                          <span class="p-1 px-2 rounded-lg my-1 flex capitalize text-lg transition-all" 
                                :class="personalizar.includes('con todo') 
                                      ? 'bg-blue-500 text-white' 
                                      : 'bg-orange-400 text-white'">
                            {{ personalizar }}
                          </span>
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
  
                <button class="w-full py-3 px-4 rounded-lg bg-blue-500 text-white font-medium hover:bg-blue-600 flex justify-center items-center gap-2" @click="$emit('close')" >
                  <CheckCheckIcon class="w-4 h-4" />
                  Cerrar resumen
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
    PenBoxIcon,
    XIcon,
    Trash2 as TrashIcon,
    CheckCheckIcon,
  } from 'lucide-vue-next';
  
  const props = defineProps({
    table: { type: Object, required: false },
    reConsumo: { type: Boolean, default: false },
    para_llevar: { type: Boolean, default: false },
    orden: { type: Object, default: () => [] },
    delivery: { type: Boolean, default: false },
  });

  const people = ref(
    props.orden?.productos?.reduce((acc, item) => {
      let person = acc.find((p) => p.id === item.persona_id);
      if (!person) {
        person = { id: item.persona_id, cartItems: [] };
        acc.push(person);
      }
      
      person.cartItems.push({
        ...item.producto,
        quantity: item.cantidad,
        stock: item.cantidad,
        tipo_servicio: item.tipo_servicio,  
        precio: parseFloat(item.producto.precio), // Asegura el formato numérico
        personalizacion: item.personalizacion ? item.personalizacion : null,
        estado: item.estado ? item.estado : null
      });
      
      return acc;
    }, []) || []
  );
  
  // Si no hay personas asociadas en los productos, agregar al menos una.
  if (people.value.length === 0) {
    people.value.push({ id: 1, cartItems: [] });
  }
  
  const selectedPerson = ref(people.value[0]?.id || 1);
  const isSubmitting = ref(false);
  
  const total = computed(() => {
    return people.value.reduce((sum, person) => {
      return sum + person.cartItems.reduce((pSum, item) => pSum + (item.precio * item.quantity), 0);
    }, 0);
  });
  
  const emit = defineEmits(['close']);
  </script>
  