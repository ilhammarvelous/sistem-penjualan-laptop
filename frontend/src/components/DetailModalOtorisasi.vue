<template>
    <div class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
        <div class="relative bg-white z-50 rounded-lg shadow-xl w-full max-w-3xl overflow-hidden">
            <div class="flex items-center justify-between bg-blue-500 px-8 py-5">
                <h2 class="text-2xl font-bold text-white">Detail otorisasi user</h2>
                <div class="absolute top-0 right-0 p-3">
                    <button
                        @click="$emit('close')"
                        class="text-gray-500 hover:text-gray-800 bg-gray-300 hover:bg-gray-300 rounded-full p-2">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="size-7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div v-if="loading" class="flex justify-center items-center">
                <div>Memuat....<div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
            </div>

            <div class="modal space-y-2 px-8 py-6">
                <ol class="space-y-2 list-decimal list-inside">
                    <li v-for="menu in menus" :key="menu.id" class="flex items-center space-x-2">
                        <span class="w-2P h-2 bg-blue-500 inline-block rounded-full"></span>
                        <span class="text-base">{{ menu }}</span>
                    </li>
                </ol>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { ref, onMounted, defineProps, defineEmits } from 'vue';
    import axios from 'axios';

    const props = defineProps({
        user: Number,
    });
    const emit = defineEmits(['close']);
    const menus = ref([]);
    const loading = ref(true);
    
    const fetchUserMenus = async () => {
        try {
            const token = localStorage.getItem('token');

            const response = await axios.get(`http://127.0.0.1:8000/api/menus/${props.user}`, {
                headers: {
                    Authorization: `Bearer ${token}`
                },
            });

            menus.value = response.data.menus
        } catch (err) {
            console.error(err.response ? err.response.data : err.message);
        } finally{
            loading.value = false
        }
    }

    onMounted(async () => {
        await fetchUserMenus();
    })
</script>


