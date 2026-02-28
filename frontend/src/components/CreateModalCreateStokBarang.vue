<template>
        <div class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
        <div class="relative bg-white z-50 rounded-lg shadow-xl w-full max-w-3xl overflow-hidden">
            <div class="flex items-center justify-between bg-blue-500 px-8 py-5">
                <h2 class="text-2xl font-bold text-white">Tambah Stok Barang</h2>
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
                <!-- Barang -->
                <div>
                    <label for="barang" class="block text-sm font-medium text-gray-700">Nama Barang<span class="text-red-600">*</span></label>
                    <select 
                        class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        name="barang"
                        id="barang"
                        v-model="selectedBarangId"
                        >
                        <option value="" disabled>-- Pilih Nama Barang --</option>
                        <option v-for="brg in barang" :key="brg.id" :value="brg.id">
                            {{ brg.nama_barang }}
                        </option>
                    </select>
                </div>

                <!-- stok -->
                <div>
                    <label for="stok" class="block text-sm font-medium text-gray-700">Stok<span class="text-red-600">*</span></label>
                    <input
                        type="text"
                        id="stok"
                        name="stok"
                        v-model="stok_masuk"
                        class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Masukkan jumlah stok"
                    />
                    <p v-if="validation.stok_masuk" class="mt-1 text-sm text-red-500">{{ validation.stok_masuk[0] }}</p>
                </div>

                    <!-- Tanggal -->
                <div>
                    <label for="tanggal" class="block text-sm font-medium text-gray-700">Tanggal Masuk<span class="text-red-600">*</span></label>
                    <input 
                    type="date"
                    id="tanggal"
                    v-model="tanggal_masuk"
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
    import { reactive, onMounted, ref } from 'vue'
    import axios from 'axios'
    import swal from 'sweetalert'

    const emit = defineEmits(['close', 'updateData'])

    const barang = ref([]);
    const selectedBarangId = ref('');
    const stok_masuk = ref('');
    const tanggal_masuk = ref('');
    const validation = reactive({});

    onMounted(() => {
        fetchBarang();
    });

    const fetchBarang = async () => {
    try {
            const token = localStorage.getItem('token');

            const response = await axios.get(`http://127.0.0.1:8000/api/select-barang`, {
                headers: {
                    Authorization: `Bearer ${token}`
                },
            });
            barang.value = response.data;
    } catch (err) {
            console.error('Gagal mengambil data:', err.response ? err.response.data : err.message);
        }
    }

    function store(){
        const data = {
            barang_id: selectedBarangId.value,
            stok_masuk: stok_masuk.value,
            tanggal_masuk: tanggal_masuk.value,
        }

        const token = localStorage.getItem('token')

        axios.post(`http://127.0.0.1:8000/api/barangs/tambah-stok-barang`, data, {
            headers: {
                Authorization: `Bearer ${token}`,
            },
        })
        .then((response) => {
            if (response.data.success) {
                swal({
                    title: 'Berhasil!',
                    text: response.data.message,
                    icon: 'success',
                    showConfirmButton: true,
                    timer: 2000
                })

                stok_masuk.value = "",
                tanggal_masuk.value = "",
                selectedBarangId.value = "";
                                
                emit('close'); 
                emit('updateData')
            } else {
                swal({
                    title: 'Gagal!',
                    icon: 'error',
                    text: response.data.message || 'Terjadi kesalahan',
                    showConfirmButton: true,
                    timer: 2000
                });
            }
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
        });
    }

</script>