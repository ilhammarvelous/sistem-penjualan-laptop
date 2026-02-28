<template>
    <div class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
        <div class="relative bg-white z-50 rounded-lg shadow-xl w-full max-w-3xl overflow-hidden">
            <div class="flex items-center justify-between bg-blue-500 px-8 py-5">
                <h2 class="text-2xl font-bold text-white">Edit Data Barang</h2>
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
        <form @submit.prevent="update()" class="grid grid-cols-2 gap-4 px-8 py-6">
            <!-- Kode -->
            <div>
            <label for="kode" class="block text-sm font-medium text-gray-700">Kode barang</label>
            <input
                type="text"
                id="kode"
                name="kode"
                class="mt-1 block w-full p-3 border bg-slate-200 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                v-model="barang.kode_barang"
                readonly
            />
            <p v-if="validation.kode_barang" class="mt-1 text-sm text-red-500">{{ validation.kode_barang[0] }}</p>
            </div>

            <!-- Nama -->
            <div>
            <label for="nama" class="block text-sm font-medium text-gray-700">Nama barang</label>
            <input
                type="text"
                id="nama_barang"
                name="nama_barang"
                class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                v-model="barang.nama_barang"
            />
            <p v-if="validation.nama_barang" class="mt-1 text-sm text-red-500">{{ validation.nama_barang[0] }}</p>
            </div>

            <!-- quantity -->
            <!-- <div>
                <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                <input
                    type="text"
                    id="quantity"
                    name="quantity"
                    class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    v-model="barang.quantity"
                />
                <p v-if="validation.quantity" class="mt-1 text-sm text-red-500">{{ validation.quantity[0] }}</p>
            </div> -->

            <!-- Harga -->
            <div>
                <label for="harga" class="block text-sm font-medium text-gray-700">Harga</label>
                    <input
                        type="text"
                        id="harga"
                        name="harga"
                        class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        v-model="barang.harga"
                    />
                    <p v-if="validation.harga" class="mt-1 text-sm text-red-500">{{ validation.harga[0] }}</p>
                </div>

            <div class="col-span-2">
                <button
                    type="submit"
                    class="w-50 py-3 px-4 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-500 focus:ring-4 focus:ring-blue-300">
                    Update
                </button>
            </div>
        </form>
        </div>
    </div>
</template>

<script setup>
    import { reactive, ref, watch } from 'vue'
    import axios from 'axios'
    import swal from 'sweetalert'
    import { useRouter } from 'vue-router';

    const router = useRouter()
    const emit = defineEmits(['close', 'updateData'])
    const loading = ref(true);
    const error = ref(null);
    const validation = reactive({})
    
    const props = defineProps({
        barangId: Number
    })

    let barang = reactive({
        kode_barang: '',
        nama_barang: '',
        harga: '',
    })

    function editBarang(id){
            if(!id) return
                
            loading.value = true
            error.value = null

            const token = localStorage.getItem('token');
            if(!token){
                swal('Error', 'Token tidak ditemukan. Silakan login ulang!', 'error');
                router.push('/');
                return;
            }

            axios.get(`http://127.0.0.1:8000/api/barangs/${id}`, {
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            })
            .then((result) => {
                barang.kode_barang = result.data.data.kode_barang;
                barang.nama_barang = result.data.data.nama_barang;
                barang.harga = result.data.data.harga;
            }).catch((err) => {
                swal('Error', err.response.data, 'error');
            });
    }

    function update() {
            const token = localStorage.getItem('token');

            if(!token){
                swal('Error', 'Token tidak ditemukan. Silakan login ulang!', 'error');
                router.push('/');
                return;
            }

            axios.put(`http://127.0.0.1:8000/api/barangs/${props.barangId}`, barang, {
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            })
            .then(() => {
                swal({
                    title: 'Berhasil!',
                    text: 'Data barang berhasil diperbarui.',
                    icon: 'success',
                    showConfirmButton: true,
                    timer: 2000
                })
                emit('updateData')
                emit('close')
            })
            .catch((err) => {
                if (err.response && err.response.status === 422) {
                    Object.assign(validation, err.response.data.errors)
                    swal('Gagal!', 'Terjadi kesalahan saat memperbarui data. Silahkan cek inputan data Anda !!', 'error');
                } else {
                    swal('Error', 'Terjadi kesalahan pada server.', 'error');
                }
            })
    }

        watch(() => props.barangId, (newId) => {
            editBarang(newId)
        }, {
            immediate: true
        })
</script>