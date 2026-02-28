<template>
    <div class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
        <div class="relative bg-white z-50 rounded-lg shadow-xl w-full max-w-3xl overflow-hidden">
            <div class="flex items-center justify-between bg-blue-500 px-8 py-5">
                <h2 class="text-2xl font-bold text-white">Tambah Data Barang</h2>
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
                    <label for="kode_barang" class="block text-sm font-medium text-gray-700">Kode barang<span class="text-red-600">*</span></label>
                    <input
                        type="text"
                        id="kode_barang"
                        name="kode_barang"
                        v-model="barang.kode_barang"
                        class="mt-1 block w-full p-3 border bg-slate-200 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        readonly
                    />
                </div>

                <!-- Nama -->
                <div>
                    <label for="nama_barang" class="block text-sm font-medium text-gray-700">Nama barang<span class="text-red-600">*</span></label>
                    <input
                        type="text"
                        id="nama_barang"
                        name="nama_barang"
                        class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Masukkan nama barang"
                        v-model="barang.nama_barang"
                    />
                    <p v-if="validation.nama_barang" class="mt-1 text-sm text-red-500">{{ validation.nama_barang[0] }}</p>
                </div>

                <!-- Quantity -->
                <div>
                    <label for="stok" class="block text-sm font-medium text-gray-700">Stok<span class="text-red-600">*</span></label>
                    <input
                        type="text"
                        id="stok"
                        name="stok"
                        class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Masukkan jumlah stok"
                        v-model="barang.stok"
                    />
                    <p v-if="validation.stok" class="mt-1 text-sm text-red-500">{{ validation.stok[0] }}</p>
                </div>

                <!-- Harga -->
                <div>
                    <label for="harga" class="block text-sm font-medium text-gray-700">Harga<span class="text-red-600">*</span></label>
                    <input
                        type="text"
                        id="harga"
                        name="harga"
                        class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Masukkan harga barang"
                        v-model="barang.harga"
                    />
                    <p v-if="validation.harga" class="mt-1 text-sm text-red-500">{{ validation.harga[0] }}</p>
                </div>

                <div>
                    <label for="tanggal_masuk" class="block text-sm font-medium text-gray-700">Tanggal Masuk<span class="text-red-600">*</span></label>
                    <input 
                    type="date"
                    id="tanggal_masuk"
                    name="tanggal_masuk"
                    v-model="barang.tanggal_masuk"
                    class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    >
                    <p v-if="validation.tanggal_masuk" class="mt-1 text-sm text-red-500">{{ validation.tanggal_masuk[0] }}</p>
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
import { reactive, onMounted } from 'vue'
import axios from 'axios'
import swal from 'sweetalert'

const emit = defineEmits(['close', 'updateData'])

const barang = reactive({
    kode_barang: '',
    nama_barang: '',
    stok: '',
    harga: '',
    tanggal_masuk: '',
})

const validation = reactive({})

onMounted(() => {
    generateKodeBarang()
})

function generateKodeBarang() {
    const token = localStorage.getItem('token')

    axios.get(`http://127.0.0.1:8000/api/generate-kode-barang`, {
        headers: {
            Authorization: `Bearer ${token}`,
        },
    })
    .then((response) => {
        barang.kode_barang = response.data.kode_barang
    })
    .catch(() => {
        swal({
            title: 'Error!',
            text: 'Gagal mengambil kode barang.',
            icon: 'error',
            timer: 2000
        })
    })
}

function store(){
    const token = localStorage.getItem('token')

    axios
        .post(`http://127.0.0.1:8000/api/barangs`, barang, {
            headers: {
                Authorization: `Bearer ${token}`,
            },
        })
        .then(() => {
            swal({
                title: 'Berhasil!',
                text: 'Data barang berhasil ditambahkan.',
                icon: 'success',
                timer: 2000
            })
            emit('close') 
            emit('updateData')
            Object.assign(barang, {
                nama_barang: '',
                stok: '',
                harga: '',
                tanggal_masuk: '',
            })
        })
        .catch((err) => {
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
        })
}
</script>