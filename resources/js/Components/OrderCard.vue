<template>
    <div :class="cardStyles" v-if="order.groupedItems && Object.keys(order.groupedItems).length > 0">
        <!-- Header -->
        <div class="px-2 py-3 border-b">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <span class="text-lg font-bold text-gray-900">Orden #{{ order.id }}</span>
                    <span
                        v-if="order.isUrgent"
                        class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full"
                    >
                        Urgente
                    </span>
                </div>
                <div class="flex items-center space-x-1">
                    <ClockIcon class="w-4 h-4 text-gray-400" />
                    <span class="text-lg text-gray-600">
                        {{ formatTime(order.created_at) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Grouped Items -->
        <div class="divide-y" >
            <div
                v-for="(personItems, personId) in order.groupedItems"
                :key="personId"
                class="p-4"
            >
                <div class="flex items-center space-x-2 mb-3">
                    <UserIcon class="w-5 h-5 text-gray-600" />
                    <h3 class="font-medium text-gray-900 bg-green-200 px-2 rounded-xl">
                        Persona {{ personId }}
                    </h3>
                </div>
                

                <div class="space-y-3">
                    <div
                        v-for="item in filterItems(personItems, empacadores)"
                        :key="item.id"
                        class="flex items-start justify-between p-2 rounded-lg hover:bg-gray-50"
                    >
                    
                    <div>
                        <div class="flex items-start space-x-3" >
                            
                            <span class="font-medium text-gray-900 text-2xl">{{ item.cantidad }}x</span>
                            <div class="flex flex-col">
                                <span class="text-gray-900 text-xl">{{ item.producto?.nombre }}</span>
                                <span v-if="item.observaciones" class="text-lg text-gray-500">{{ item.observaciones }}</span>
                                <span >{{ item.estado }}</span>
                                <span v-if="item?.tipo_servicio" :class="{
                                    'text-lg text-gray-500 bg-green-200 px-2 rounded-xl': item?.tipo_servicio === 'para_comer',
                                    'text-lg text-gray-500 bg-orange-200 px-2 rounded-xl': item?.tipo_servicio === 'para_llevar'
                                    }">{{ item?.tipo_servicio === 'para_llevar' ? 'Para llevar' : 'Para comer' }}</span>
                                <span v-if="item.producto?.detalle" class="text-lg text-gray-500">
                                    {{ item.producto?.detalle }}
                                    
                                </span>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-4 py-3 border-t">
            <div class="flex justify-around w-full">
                <div class=" items-center space-x-2">
                    <div class="flex items-center justify-center space-x-2">
                        <MapPinIcon class="w-4 h-4 text-gray-400" />
                        <span v-if="order?.mesa" class="text-2xl font-medium text-gray-700">
                            {{ order?.mesa?.nombre }}
                        </span>
                        <span v-else class="text-xl font-medium text-gray-700">
                            Para llevar
                        </span>
                        <div v-if="empacadores">
                            <span v-if="order.pagado" class="bg-green-500 text-white px-2 rounded-xl">
                                Pagado 
                            </span>
                            <span v-else class="bg-red-500 text-white text-lg px-2 rounded-xl">
                                No pagado
                            </span>
                        </div>
                    </div>
                    <span v-if="order?.nombre_cliente" class=" font-medium text-lg text-gray-700">
                        A nombre de <span class="font-bold text-2xl text-black">{{ order?.nombre_cliente }}</span>
                    </span>
                </div>
            </div>
            <button
                class="mt-4 w-full bg-green-500 text-white py-2 rounded-lg hover:bg-green-600"
                @click="handleComplete(order)"
            >
                Entregar
            </button>
        </div>
    </div>

   
</template>

<script setup>
import { ClockIcon, UserIcon, MapPinIcon, ClipboardListIcon } from 'lucide-vue-next';

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
    empacadores: {
        type: Boolean,
        required: false,
        default: false,
    }
});

const emit = defineEmits(['complete']);

// Styles
const cardStyles = 'w-80 border p-4 rounded-xl bg-gray-50 shadow-lg';

// Format Time Function
function formatTime(timestamp) {
    return new Date(timestamp).toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true,
    });
}

// Handle Complete Order
function handleComplete(order) {
    emit('complete', order.id);
}

const filterItems = (items, empacadores) => {
    console.log(items)
    if (empacadores) return items

    return items.filter(item => (item.estado === 'espera_entrega' || item.estado === 'espera_empacar'));
};

console.log(props.order.groupedItems)
</script>
