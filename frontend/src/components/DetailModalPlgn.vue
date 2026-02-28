<template>
    <div class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
        <div class="relative bg-white z-50 rounded-lg shadow-xl w-full max-w-2xl overflow-hidden">
            <div class="flex items-center justify-between bg-blue-500 px-8 py-5">
                <h2 class="text-2xl font-bold text-white">Detail Data Pelanggan</h2>
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

            <div class="space-y-4 px-8 py-6">
                <div>
                    <p><strong>Nama</strong> <br> {{ pelanggan.nama }}</p>
                </div>
                <div>
                    <p><strong>No HP</strong> <br> {{ pelanggan.no_hp }}</p>
                </div>
                <div>
                    <p><strong>Alamat</strong> <br> {{ pelanggan.alamat }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
    
<script>
    import { reactive, ref, watch } from 'vue';
    import axios from 'axios';
    import { useRouter } from 'vue-router';
    
    export default {
        props:{
            pelangganId: Number
        },
        setup(props) {
            const router = useRouter();
            const pelanggan = reactive({});
            const loading = ref(true);
            const error = ref(null);
    
            function detailPelanggan(id){
                if(!id) return
                
                loading.value = true
                error.value = null

                const token = localStorage.getItem('token');
                if(!token){
                    swal('Error', 'Token tidak ditemukan. Silakan login ulang!', 'error');
                    router.push('/');
                    return;
                }

                axios
                .get(`http://127.0.0.1:8000/api/pelanggans/${id}`, {
                    headers: {
                        Authorization: `Bearer ${token}`,
                    },
                })
                .then((response) => {
                    pelanggan.nama = response.data.data.nama;
                    pelanggan.no_hp = response.data.data.no_hp;
                    pelanggan.alamat = response.data.data.alamat;
                })
                .catch((err) => {
                    if (err.response && err.response.status === 404) {
                    error.value = 'Data tidak ditemukan!';
                    } else {
                    error.value = 'Terjadi kesalahan pada server.';
                    }
                })
                .finally(() => {
                    loading.value = false;
                });
            }

            watch(() => props.pelangganId, (newId) => {
                detailPelanggan(newId)
            }, {
                immediate: true
            })
        
            return {
                pelanggan,
                loading,
                error,
            };
        },
    };
</script>