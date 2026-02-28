<template>
    <div class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
        <div class="relative bg-white z-50 rounded-lg shadow-xl w-full max-w-2xl overflow-hidden">
            <div class="flex items-center justify-between bg-blue-500 px-8 py-5">
                <h2 class="text-2xl font-bold text-white">Pembayaran</h2>
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

            <div class="p-8 text-center" v-if="loading">Memuat data...</div>
            <div v-else-if="error" class="text-red-500">{{ error }}</div>

            <form @submit.prevent="store()"
                v-for="(transaksi, index) in transaksis" :key="index" 
                class="grid grid-cols-2 gap-4 px-8 py-6">
                <!-- id transaksi -->
                <div>
                    <label for="id_transaksi" class="block text-sm font-medium text-gray-700">Pilih ID Transaksi<span class="text-red-600">*</span></label>
                        <select
                            class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            name="id_transaksi"
                            id="id_transaksi"
                            v-model="transaksis[0].id_transaksi"
                            @change="updateItemDetail(index)"
                            >
                            <option value="" disabled selected>-- Pilih ID Transaksi --</option>
                            <option v-for="st in selectTransaksi" :key="st.id" :value="st.id">
                                {{ st.id_transaksi}}
                            </option>
                        </select>
                        <p v-if="validation.transaksis_id" class="mt-1 text-sm text-red-500">{{ validation.transaksis_id[0] }}</p>
                </div>
                <!-- total pembayaran -->
                <div>
                    <label for="total_pembayaran" class="block text-sm font-medium text-gray-700">Total pembayaran<span class="text-red-600">*</span></label>
                    <input
                        type="text"
                        id="total_pembayaran"
                        name="total_pembayaran"
                        :value="formatRupiah(transaksi.total_pembayaran)"
                        class="mt-1 block w-full p-3 border bg-slate-200 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        readonly
                        />
                </div>

                    <div>
                    <label for="via" class="block text-sm font-medium text-gray-700">Via<span class="text-red-600">*</span></label>
                    <select
                        class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        name="via"
                        id="via"
                        v-model="via"
                        >
                        <option value="" disabled selected>-- Pilih Via Pembayaran --</option>
                        <option>Cash</option>
                        <option>Transfer</option>
                    </select>
                    <p v-if="validation.via" class="mt-1 text-sm text-red-500">{{ validation.via[0] }}</p>
                </div>

                <!-- Bayar -->
                <div>
                    <label for="bayar" class="block text-sm font-medium text-gray-700">Bayar<span class="text-red-600">*</span></label>
                    <input
                        type="text"
                        id="bayar"
                        name="bayar"
                        v-model="bayar"
                        class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Masukkan nominal pembayaran"
                    />
                    <p v-if="validation.pembayaran" class="mt-1 text-sm text-red-500">{{ validation.pembayaran[0] }}</p>
                </div>

                <!-- Tanggal -->
                <div>
                    <label for="tanggal" class="block text-sm font-medium text-gray-700">Tanggal<span class="text-red-600">*</span></label>
                    <input 
                    type="date"
                    id="tanggal"
                    v-model="tanggal"
                    class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    >
                    <p v-if="validation.tanggal" class="mt-1 text-sm text-red-500">{{ validation.tanggal[0] }}</p>
                </div>

                <!-- Tombol Submit -->
                <div class="col-span-2 pt-2">
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
    import { reactive, ref, watch, onMounted } from 'vue';
    import axios from 'axios';
    import swal from 'sweetalert';
    import { useRouter } from 'vue-router';

    const selectTransaksi = ref([]);
    const tanggal = ref('');
    const bayar = ref('');
    const via = ref('');
    const validation = reactive({})
    const emit = defineEmits(['close', 'updateData']);
    const transaksis = ref([
        {
            id_transaksi: '',
            total_pembayaran: '',
        }
    ]);

    onMounted(() => {
        getTransaksi();
    })

    function updateItemDetail(index) {
        const sel = selectTransaksi.value.find(s => s.id === transaksis.value[index].id_transaksi) || {};
        transaksis.value[index].total_pembayaran = sel.total_pembayaran || '';
    }

    const formatRupiah = (angka) => {
        if (!angka) return '';
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(angka);
    }

    const getTransaksi = async () => {
        try {
            const token = localStorage.getItem('token');

            const response = await axios.get(`http://127.0.0.1:8000/api/select-idTransaksi`, {
                headers: {
                    Authorization: `Bearer ${token}`
                },
            });
            selectTransaksi.value = response.data;
        } catch (err) {
            console.error('Gagal mengambil data:', err.response ? err.response.data : err.message);
        }
    }

    function store(){
        const data = {
            transaksis_id: transaksis.value[0].id_transaksi,
            total_pembayaran: transaksis.value[0].total_pembayaran,
            via: via.value,
            pembayaran: bayar.value,
            tanggal: tanggal.value,
        }

        console.log(data);
        const token = localStorage.getItem('token');

        axios
        .post(`http://127.0.0.1:8000/api/payment-transaksi`, data, {
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

                // id_transaksi.value = "";
                // selectedMahasiswa.value = "";
                // selectedMatkul.value = "";
                // tanggal.value = "";
                // totalPembayaran.value = "";
                
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