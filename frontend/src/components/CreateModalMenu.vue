<template>
    <div class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
        <div class="relative bg-white z-50 rounded-lg shadow-xl w-full max-w-3xl overflow-hidden">
            <div class="flex items-center justify-between bg-blue-500 px-8 py-5">
                <h2 class="text-2xl font-bold text-white">Tambah Data Menu</h2>
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

            <form @submit.prevent="store()" class="grid grid-cols-2 gap-4 px-8 py-6">
                <!-- Kode -->
                <div>
                    <label for="menu" class="block text-sm font-medium text-gray-700">Menu<span class="text-red-600">*</span></label>
                    <input
                        type="text"
                        id="menu"
                        name="menu"
                        class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Masukkan menu"
                        v-model="menu.menu"
                    />
                    <p v-if="validation.menu" class="mt-1 text-sm text-red-500">{{ validation.menu[0] }}</p>
                </div>

                <!-- Tombol Submit -->
                <div class="col-span-2">
                    <button
                        type="submit"
                        class="w-50 py-3 px-4 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-500 focus:ring-4 focus:ring-blue-300">
                        Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive } from 'vue'
import axios from 'axios'
import swal from 'sweetalert'

const emit = defineEmits(['close', 'updateData'])

const menu = reactive({
    menu: '',
})

const validation = reactive({})

async function store() {
    try {
        const token = localStorage.getItem('token');

        await axios.post(`http://127.0.0.1:8000/api/menu`, menu, {
            headers: {
                Authorization: `Bearer ${token}`,
            },
        })

        swal({
            title: 'Berhasil!',
            text: 'Data menu berhasil ditambahkan.',
            icon: 'success',
            showConfirmButton: true,
            timer: 2000
        });

        emit('close');
        emit('updateData');

        Object.assign(menu, {
            menu: '',
        });
    } catch (err) {
        if (err.response && err.response.status === 422) {
                Object.assign(validation, err.response.data.errors)
                swal({
                    title: 'Gagal!',
                    icon: 'error',
                    text: 'Terjadi kesalahan, Silahkan cek inputan anda !!!',
                    showConfirmButton: true,
                    timer: 2000
                });
            } else {
                swal({
                    title: 'Gagal!',
                    icon: 'error',
                    text: 'Terjadi kesalahan pada server.',
                    showConfirmButton: true,
                    timer: 2000
                });
        }
    }
}
</script>