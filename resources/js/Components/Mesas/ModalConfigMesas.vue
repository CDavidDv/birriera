<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center" role="dialog" aria-modal="true">
    <div class="bg-white rounded-lg p-6 w-full max-w-4xl max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold">Configuración de Mesas</h2>
        <button 
          @click="handleClose" 
          class="text-gray-500 hover:text-gray-700 transition-colors" 
          aria-label="Cerrar modal"
        >
          <X class="w-6 h-6" />
        </button>
      </div>

      <!-- Table List -->
      <div class="space-y-4 mb-6">
        <div 
          v-if="tables.length" 
          v-for="(table, index) in tables" 
          :key="table.id" 
          class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
        >
          <input 
            v-model="table.nombre" 
            type="text" 
            :placeholder="'Mesa/Barra'" 
            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{'border-red-500 ring-red-500': getTableError(table.id, 'nombre')}" 
            @blur="validateTable(table)" 
          />
          <select 
            v-model="table.tipo" 
            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{'border-red-500 ring-red-500': getTableError(table.id, 'tipo')}"
            @change="validateTable(table)"
          >
            <option value="" disabled>Selecciona tipo</option>
            <option value="mesa">Mesa</option>
            <option value="barra">Barra</option>
          </select>
          <div class="flex flex-col gap-2">
            <button 
              @click="moveUp(index)" 
              v-if="index > 0"
              :disabled="index === 0"
              class="px-4 py-2 bg-gray-300 text-gray-700 hover:bg-gray-400 rounded-lg transition-colors flex items-center justify-center"
            >
              <ArrowUp />
            </button>
            <button 
              @click="moveDown(index)" 
              v-if="index < tables.length - 1"
              :disabled="index === tables.length - 1"
              class="px-4 py-2 bg-gray-300 text-gray-700 hover:bg-gray-400 rounded-lg transition-colors flex items-center justify-center"
            >
              <ArrowDown />
            </button>
          </div>

          <button 
            @click="removeTable(table)"
            :disabled="!canRemoveTable(table)"
            class="p-2 text-red-500 hover:text-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
            :title="getRemoveTableTitle(table)"
          >
            <Trash2 class="w-5 h-5" />
          </button>
          
        </div>
        
        <div v-else class="text-center py-8 text-gray-500">
          No hay mesas configuradas. Añade una nueva mesa para comenzar.
        </div>
      </div>
      

      <!-- Actions -->
      <div class="flex justify-between">
        <button 
          @click="addNewTable" 
          class="py-2 px-4 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors flex items-center gap-2"
        >
          <Plus class="w-5 h-5" /> Añadir Nueva Mesa
        </button>
        <div class="space-x-2">
          <button 
            @click="handleClose" 
            class="py-2 px-4 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors"
          >
            Cancelar
          </button>
          <button 
            @click="saveSettings" 
            class="py-2 px-4 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="hasValidationErrors || isSaving"
          >
            <span v-if="isSaving">Guardando...</span>
            <span v-else>Guardar Cambios</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { X, Trash2, Plus, ArrowUpToLineIcon, ArrowDown, ArrowUp } from 'lucide-vue-next';
import Swal from 'sweetalert2';
import { router, usePage } from '@inertiajs/vue3';

const emit = defineEmits(['close', 'update']);

// Al inicio del componente, hacer una copia profunda
const originalTables = JSON.parse(JSON.stringify(usePage().props.mesas));
const tables = ref(
  originalTables.map((table, index) => ({
    ...table,
    posicion: table.posicion || index + 1, // Asigna posición inicial si está vacía
  }))
);
tables.value.sort((a, b) => a.posicion - b.posicion);



const validationErrors = ref({});
const isSaving = ref(false);

const hasValidationErrors = computed(() => Object.keys(validationErrors.value).length > 0);

const canRemoveTable = (table) => table.estado === 'libre';
const getRemoveTableTitle = (table) =>
  table.estado !== 'libre' ? 'No se puede eliminar una mesa ocupada' : 'Eliminar mesa';


const getTableError = (tableId, field) => {
  return validationErrors.value[tableId]?.[field];
};

const validateTable = (table) => {
  const errors = {};
  
  if (!table.nombre?.trim()) {
    errors.nombre = 'El nombre es requerido';
  }
  if (!table.tipo) {
    errors.tipo = 'El tipo es requerido';
  }
  
  if (Object.keys(errors).length) {
    validationErrors.value[table.id] = errors;
  } else {
    delete validationErrors.value[table.id];
  }
};

const validateAllTables = () => {
  tables.value.forEach(validateTable);
  return !hasValidationErrors.value;
};


const moveUp = (index) => {
  if (index > 0) {
    const temp = tables.value[index];
    tables.value[index] = tables.value[index - 1];
    tables.value[index - 1] = temp;
    syncPositions();
  }
};

const moveDown = (index) => {
  if (index < tables.value.length - 1) {
    const temp = tables.value[index];
    tables.value[index] = tables.value[index + 1];
    tables.value[index + 1] = temp;

    syncPositions();
  }
};

// Función para sincronizar las posiciones según el orden actual del arreglo
const syncPositions = () => {
  tables.value.forEach((table, index) => {
    table.posicion = index + 1; // Las posiciones son 1-based
  });

};





const addNewTable = () => {
  const newTable = {
    id: Date.now(),
    nombre: '',
    tipo: '',
    estatus: 'libre',
    esNueva: true,
    posicion:  tables.value.length + 1
  };
  tables.value.push(newTable);
};

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

const removeTable = (table) => {
  
  const result = Swal.fire({
      title: '¿Eliminar mesa?',
      text: 'Esta acción no se puede deshacer.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Eliminar',
      cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
      if (table.estado !== 'libre') {
    Toast.fire({
      icon: "error",
      title: "No se puede eliminar una mesa ocupada"
    });
    return;
  }

  try {
      if (!table.esNueva) {
        router.delete(`/mesas/${table.id}`);
      }
      
      tables.value = tables.value.filter(t => t.id !== table.id);
      emit('update', tables.value);
      
      Toast.fire({
        icon: "success",
        title: "Mesa eliminada con éxito"
      });
    } catch (error) {
      console.error('Error al eliminar mesa:', error);
      Toast.fire({
        icon: "error",
        title: "Error al eliminar mesa"
      });
    }
    }
  
};



const isTableModified = (current, original) => {
  return JSON.stringify({ nombre: current.nombre, tipo: current.tipo, posicion: current.posicion }) !== 
  JSON.stringify({ nombre: original.nombre, tipo: original.tipo, posicion: original.posicion });
};

const saveSettings = async () => {
  if (!validateAllTables()) {
    await Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Corrige los errores antes de guardar',
    });
    return;
  }

  isSaving.value = true;

  try {
    // Actualiza las posiciones basadas en el índice actual
    tables.value.forEach((table, index) => {
      table.posicion = index + 1; // Las posiciones empiezan en 1
    });

    const payload = {
      nuevasMesas: tables.value
        .filter((table) => table.esNueva)
        .map(({ nombre, tipo, posicion }) => ({ nombre, tipo, posicion })),
      mesasActualizadas: tables.value
        .filter((table) => {
          if (table.esNueva) return false;
          const original = usePage().props.mesas.find((m) => m.id === table.id);
          return original && isTableModified(table, original);
        })
        .map(({ id, nombre, tipo, posicion }) => ({ id, nombre, tipo, posicion })),
      mesasEliminadas: originalTables
        .filter((original) => !tables.value.some((table) => table.id === original.id))
        .map(({ id }) => id),
    };

    

    router.post('/mesas', payload, {
      preserveScroll: true,
      preserveState: false,
      onSuccess: () => {
        Swal.fire({
          icon: 'success',
          title: 'Éxito',
          text: 'Configuración guardada con éxito',
        });
        emit('update', tables.value);
      },
      onError: (errors) => {
        console.error('Errores al guardar:', errors);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No se pudo guardar la configuración',
        });
      },
    });
  } catch (error) {
    console.error('Error al guardar configuración:', error);
    await Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'No se pudo guardar la configuración',
    });
  } finally {
    isSaving.value = false;
  }
};


const handleClose = async () => {
  const hasChanges = tables.value.some(table => {
    const original = originalTables.find(t => t.id === table.id);
    return table.esNueva || (original && isTableModified(table, original));
  });
  
  if (hasChanges) {
    const result = await Swal.fire({
      title: '¿Cerrar sin guardar?',
      text: 'Los cambios no guardados se perderán',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Cerrar',
      cancelButtonText: 'Cancelar'
    });
    
    if (result.isConfirmed) {
      emit('close');
    }
  } else {
    emit('close');
  }
};


</script>
