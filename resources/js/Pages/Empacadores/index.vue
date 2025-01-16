<script setup>

import Empacadores from '@/Components/Empacadores.vue';
import AppLayout from '@/Layouts/AppLayout.vue';

</script>


<template>
    <AppLayout title="Tablero principal">
        <div class="py-6 px-2">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl rounded-lg">
                    <div class="flex justify-between p-6 lg:p-8 bg-white border-b border-gray-200">
                        <Empacadores />
                    </div>    
                </div>
            </div>
        </div>
    </AppLayout>
</template>
