<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">
    <div class="bg-white sm:w-10/12 md:w-8/12 w-11/12 max-h-[90vh] overflow-auto rounded-lg shadow-lg">
      <!-- Header -->
      <div class="p-4 border-b flex justify-between items-center">
        <h3 class="text-lg font-semibold">Seleccionar Imagen</h3>
        <button @click="close" class="text-gray-500 hover:text-gray-700">
          <XIcon />
        </button>
      </div>

      <!-- Tabs -->
      <div class="flex border-b">
        <button
          @click="activeTab = 'gallery'"
          class="px-4 py-2 font-medium"
          :class="activeTab === 'gallery' ? 'border-b-2 border-blue-500 text-blue-500' : 'text-gray-500'"
        >
          Galería existente
        </button>
        <button
          @click="activeTab = 'upload'"
          class="px-4 py-2 font-medium"
          :class="activeTab === 'upload' ? 'border-b-2 border-blue-500 text-blue-500' : 'text-gray-500'"
        >
          Subir nueva
        </button>
      </div>

      <!-- Content -->
      <div class="p-4 overflow-y-auto" style="max-height: calc(90vh - 130px);">
        <!-- Gallery Tab -->
        <div v-if="activeTab === 'gallery'" class="space-y-4">
          <div class="flex flex-wrap gap-4">
            <div
              v-for="(image, index) in paginatedImages"
              :key="index"
              class="relative w-[120px] h-[120px] border rounded-lg overflow-hidden cursor-pointer group"
              :class="{'ring-2 ring-blue-500': isImageSelected(image)}"
              @click="selectImage(image)"
            >
              <img :src="image?.url" :alt="image?.nombre" class="w-full h-full object-cover" />
              <div v-if="isImageSelected(image)" class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all flex items-center justify-center">
                <div class="absolute top-2 right-2 bg-blue-500 text-white rounded-full p-1" @click.stop="openImageModal(image)">
                  <ImageIcon />
                </div>
                <div class="absolute bottom-2 right-2 bg-red-500 text-white rounded-full p-1" @click.stop="deleteImage(image)">
                  <Trash2Icon />
                </div>
              </div>
            </div>
            <div v-if="!existingImages?.length" class="w-full text-center py-8 text-gray-500">
              No hay imágenes disponibles
            </div>
          </div>

          <!-- Pagination if needed -->
          <div v-if="existingImages?.length > 0" class="flex justify-center mt-4 space-x-2">
            <button
              v-for="page in totalPages"
              :key="page"
              @click="currentPage = page"
              class="w-8 h-8 rounded-full flex items-center justify-center"
              :class="currentPage === page ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
            >
              {{ page }}
            </button>
          </div>
        </div>

        <!-- Upload Tab -->
        <div v-if="activeTab === 'upload'" class="space-y-4">
          <div
            class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-blue-500 transition-colors"
            @click="triggerFileInput"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleFileDrop"
            :class="{'border-blue-500 bg-blue-50': isDragging || previewImage}"
          >
            <div v-if="!previewImage">
              <ImageIcon class="mx-auto text-gray-400"/>
              <p class="mt-2 text-sm text-gray-600">Arrastra y suelta una imagen aquí o haz clic para seleccionar</p>
              <p class="text-xs text-gray-500 mt-1">PNG, JPG, WEBP hasta 4MB</p>
            </div>
            <div v-else class="relative">
              <img :src="previewImage" alt="Preview" class="max-h-[300px] mx-auto rounded" />
              <button
                @click.stop="clearPreview"
                class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 shadow-md hover:bg-red-600"
              >
                <XIcon />
              </button>
            </div>
          </div>

          <input
              type="file"
              ref="fileInput"
              accept="image/png, image/jpeg, image/jpg, image/webp"
              @change="handleFileChange"
              class="hidden"
              />

          <div v-if="previewImage" class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Nombre de la imagen</label>
            <input
              v-model="newImageName"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Ingresa un nombre para la imagen"
            />
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="p-4 border-t flex justify-end space-x-2">
        <button @click="close" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
          Cancelar
        </button>

        <button
          v-if="activeTab === 'gallery'"
          @click="confirmSelection"
          class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
          :disabled="selectedImageIndex === null"
          :class="{'opacity-50 cursor-not-allowed': selectedImageIndex === null}"
        >
          Seleccionar
        </button>

        <button
          v-if="activeTab === 'upload'"
          @click="uploadImage"
          class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600"
          :disabled="!previewImage"
          :class="{'opacity-50 cursor-not-allowed': !previewImage}"
        >
          Subir imagen
        </button>
      </div>
    </div>
  </div>

  <!-- Image Modal -->
  <div v-if="isImageModalOpen" class="fixed overflow-auto inset-0 bg-black bg-opacity-75 flex justify-center items-center z-50">
    <div class="bg-white p-4 rounded-lg max-w-4xl max-h-[90vh]  overflow-auto">
      <div class="w-full justify-end  flex">
        <button @click="closeImageModal" class=" text-gray-600 block hover:text-gray-800">
          <XIcon />
        </button>
      </div>
      <img :src="selectedImage?.url" :alt="selectedImage?.nombre" class="w-full h-auto rounded-lg" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { ImageIcon, XIcon, Trash2Icon } from 'lucide-vue-next';
import Swal from 'sweetalert2';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';

// Props
const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
});

// Emits
const emit = defineEmits(['close', 'select-image', 'upload-image']);

const img = usePage();

// State
const existingImages = ref(img.props.imagenes || []);
const activeTab = ref('gallery');
const selectedImage = ref(null);
const fileInput = ref(null);
const previewImage = ref(null);
const newImageName = ref('');
const isDragging = ref(false);
const currentPage = ref(1);
const itemsPerPage = 12;
const isImageModalOpen = ref(false);
const selectedFile = ref(null);

// Computed
const totalPages = computed(() => {
  return Math.ceil(existingImages.value.length / itemsPerPage);
});

const paginatedImages = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return existingImages.value.slice(start, end);
});

// Methods
const close = () => {
  emit('close');
  resetState();
};

const selectImage = (image) => {
  selectedImage.value = image;
};

const openImageModal = (image) => {
  selectedImage.value = image;
  isImageModalOpen.value = true;
};

const closeImageModal = () => {
  isImageModalOpen.value = false;
};

const isImageSelected = (image) => {
  return selectedImage.value?.url === image.url;
};

const confirmSelection = () => {
  if (selectedImage.value) {
    emit('select-image', selectedImage.value);
    close();
  }
};

const triggerFileInput = () => {
  fileInput.value?.click();
};

const handleFileChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    processFile(file);
  }
};

const handleFileDrop = (event) => {
  isDragging.value = false;
  const file = event.dataTransfer.files[0];
  if (file && (file.type === 'image/png' || file.type === 'image/jpeg' || file.type === 'image/jpg' || file.type === 'image/webp')) {
    processFile(file);
  } else {
    Toast.fire({
      icon: 'error',
      title: 'Solo se permiten imágenes PNG, JPG o WEBP'
    });
  }
};

const processFile = (file) => {
  const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'];
  const maxSizeMB = 4;

  if (!allowedTypes.includes(file.type)) {
    Toast.fire({
      icon: 'error',
      title: 'Solo se permiten imágenes PNG, JPG o WEBP.'
    });
    return;
  }

  if (file.size > maxSizeMB * 1024 * 1024) {
    Toast.fire({
      icon: 'error',
      title: `La imagen es demasiado grande. El tamaño máximo es ${maxSizeMB}MB.`
    });
    return;
  }

  const reader = new FileReader();
  reader.onload = (e) => {
    previewImage.value = e.target.result;
    newImageName.value = file.name.split('.')[0];
    selectedFile.value = file;
  };
  reader.readAsDataURL(file);
};

const clearPreview = () => {
  previewImage.value = null;
  newImageName.value = '';
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};

const Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3000,
  timerProgressBar: true,
  didOpen: (toast) => {
    toast.addEventListener('mouseenter', Swal.stopTimer)
    toast.addEventListener('mouseleave', Swal.resumeTimer)
  }
});

const uploadImage = async () => {
  if (selectedFile.value) {
    const formData = new FormData();
    formData.append('image', selectedFile.value);
    formData.append('name', newImageName.value);

    try {
      Toast.fire({
        icon: 'info',
        title: 'Subiendo imagen...'
      });

      await axios.post('/api/images', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      const response = await axios.get('/images/all');
      existingImages.value = response.data.imagenes || [];

      clearPreview();
      activeTab.value = 'gallery';

      Toast.fire({
        icon: 'success',
        title: 'Imagen subida exitosamente'
      });

    } catch (error) {
      console.error('Error al subir la imagen:', error);
      Toast.fire({
        icon: 'error',
        title: 'Error al subir la imagen'
      });
    }
  }
};

const deleteImage = (image) => {
  Swal.fire({
    title: '¿Estás seguro?',
    text: 'Esta acción eliminará la imagen seleccionada.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await axios.delete(`/api/images/${image.id}`);

        existingImages.value = existingImages.value.filter(img => img.id !== image.id);
        selectedImage.value = null;

        Toast.fire({
          icon: 'success',
          title: 'Imagen eliminada exitosamente'
        });
      } catch (error) {
        console.error('Error al eliminar la imagen:', error);
        Toast.fire({
          icon: 'error',
          title: 'Error al eliminar la imagen'
        });
      }
    }
  });
};

const resetState = () => {
  activeTab.value = 'gallery';
  selectedImage.value = null;
  previewImage.value = null;
  newImageName.value = '';
  isDragging.value = false;
};

onMounted(() => {
  if (!existingImages.value) {
    existingImages.value = [];
  }
});
const selectedImageIndex = computed(() => {
  return existingImages.value.findIndex(img => img.id === selectedImage.value?.id);
});
</script>
