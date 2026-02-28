<template>
    <div class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
        <div class="relative bg-white z-50 rounded-lg shadow-xl w-full max-w-3xl overflow-hidden">
            <div class="flex items-center justify-between bg-blue-500 px-8 py-5">
                <h2 class="text-2xl font-bold text-white">Ubah Data Pelanggan</h2>
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
            <!-- Nama -->
            <div>
                <label for="nama" class="block text-sm font-medium text-gray-700">Nama</label>
                <input
                    type="text"
                    id="nama"
                    name="nama"
                    class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    v-model="pelanggan.nama"
                />
                <p v-if="validation.nama" class="mt-1 text-sm text-red-500">{{ validation.nama[0] }}</p>
            </div>

            <!-- No hp -->
            <div>
                <label for="no_hp" class="block text-sm font-medium text-gray-700">No hp</label>
                <input
                    type="text"
                    id="no_hp"
                    name="no_hp"
                    class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    v-model="pelanggan.no_hp"
                />
                <p v-if="validation.no_hp" class="mt-1 text-sm text-red-500">{{ validation.no_hp[0] }}</p>
            </div>

            <!-- Nim -->
            <div>
                <label for="nim" class="block text-sm font-medium text-gray-700">Alamat</label>
                <input
                    type="text"
                    id="alamat"
                    name="alamat"
                    class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    v-model="pelanggan.alamat"
                />
                <p v-if="validation.alamat" class="mt-1 text-sm text-red-500">{{ validation.alamat[0] }}</p>
            </div>

            <!-- Tombol Submit -->
            <div class="col-span-2 py-1">
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

<script>
    import { reactive, ref, watch } from 'vue'
    import axios from 'axios'
    import swal from 'sweetalert'
    import { useRouter } from 'vue-router';

    export default {
        props:{
            pelangganId: Number
        },
        setup(props, {emit}) {
            let pelanggan = reactive({
                nama: '',
                no_hp: '',
                alamat: '',
            })

            const router = useRouter();
            const loading = ref(true);
            const error = ref(null);

            const validation = reactive({})

            function editPelanggan(id){
                if(!id) return
                    
                loading.value = true
                error.value = null

                const token = localStorage.getItem('token');
                if(!token){
                    swal('Error', 'Token tidak ditemukan. Silakan login ulang!', 'error');
                    router.push('/');
                    return;
                }

                axios.get(`http://127.0.0.1:8000/api/pelanggans/${id}`, {
                    headers: {
                        Authorization: `Bearer ${token}`,
                    },
                })
                .then((result) => {
                    pelanggan.nama = result.data.data.nama
                    pelanggan.no_hp = result.data.data.no_hp
                    pelanggan.alamat = result.data.data.alamat
                }).catch((err) => {
                    console.log(err.response.data);
                    swal('Error', 'Gagal memuat data pelanggan !!', 'error');
                });
            }

            function update() {
                const token = localStorage.getItem('token');
                if(!token){
                    swal('Error', 'Token tidak ditemukan. Silakan login ulang!', 'error');
                    router.push('/');
                    return;
                }

                axios.put(`http://127.0.0.1:8000/api/pelanggans/${props.pelangganId}`, pelanggan, {
                    headers: {
                        Authorization: `Bearer ${token}`,
                    },
                })
                .then(() => {
                    swal({
                        title: 'Berhasil!',
                        text: 'Data pelanggan berhasil diperbarui.',
                        icon: 'success',
                        showConfirmButton: true,
                        timer: 2000
                    })
                    emit('updated')
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

            watch(() => props.pelangganId, (newId) => {
                editPelanggan(newId)
            }, {
                immediate: true
            })

            return {
                pelanggan,
                validation,
                loading,
                error,
                update
            }
        },
    }
</script>
