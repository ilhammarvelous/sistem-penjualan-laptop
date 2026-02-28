<template>
    <div class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
        <div class="relative bg-white z-50 rounded-lg shadow-xl w-full max-w-2xl overflow-hidden">
            <div class="flex items-center justify-between bg-blue-500 px-8 py-6">
                <h2 class="text-2xl font-bold text-white">Detail Data Transaksi</h2>
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

            <div v-else class="space-y-4 px-8 py-5 ">
                <div class="flex justify-between">
                    <div>
                        <p class="mb-1"><strong>ID Transaksi :</strong> {{ transaksi.id_transaksi }}</p>
                        <p class="mb-1"><strong>Nama pelanggan :</strong> {{ transaksi.pelanggan }}</p>
                        <p class="mb-1"><strong>Tanggal :</strong> {{ transaksi.tanggal }}</p>
                        <p class="mb-1"><strong>Total Pembayaran :</strong> {{ formatRupiah(transaksi.total_pembayaran) }}</p>
                        <p class="mb-1"><strong>Status : </strong> 
                            <span
                                class="inline-block px-2 py-1 text-xs font-semibold text-white rounded-full"
                                :class="transaksi.status === 'Lunas' ? 'bg-green-500' : 'bg-red-500'">
                                {{ transaksi.status == 'Lunas' ? 'Lunas' : 'Belum Lunas' }}
                            </span>
                        </p>
                    </div>
                    <div>
                        <button @click="cetakPDF()" class="flex items-center p-2 px-3 mx-1 text-md font-medium tracking-wide text-white bg-yellow-500 rounded-lg hover:bg-yellow-400">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            <span class="text-sm">Cetak</span>
                        </button>
                    </div>
                </div>

                <hr class="my-4"/>

                <div>
                    <h3 class="font-semibold text-lg mb-2">Detail Barang</h3>
                    <table class="w-full text-left border border-gray-900">
                        <thead class="bg-gray-300">
                        <tr>
                            <th class="p-2 border">Barang</th>
                            <th class="p-2 border">Harga</th>
                            <th class="p-2 border">Quantity</th>
                            <th class="p-2 border">Subtotal</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(item, index) in detail" :key="index">
                            <td class="p-2 border">{{ item.barang }}</td>
                            <td class="p-2 border">{{ formatRupiah(item.harga) }}</td>
                            <td class="p-2 border">{{ item.quantity }}</td>
                            <td class="p-2 border">{{ formatRupiah(item.subtotal) }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                
            </div>
        </div>
    </div>
</template>

<script setup>
    import { reactive, ref, watch } from 'vue';
    import axios from 'axios';
    import swal from 'sweetalert';
    import { useRouter } from 'vue-router';

    const props = defineProps({
        transaksiId: Number
    })

    const router = useRouter();
    const loading = ref(false);
    const error = ref(null);
    const transaksi = reactive({});
    const detail = ref([]);

    function formatRupiah(value) {
        if (!value) return 'Rp0';
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(value);
    }

    async function detailTransaksi(id){
        if(!id) return

        loading.value = true
        error.value = null

        const token = localStorage.getItem('token');

        if(!token){
            swal('Error', 'Token tidak ditemukan. Silakan login ulang!', 'error');
            router.push('/');
            return;
        }

        try {
            const response = await axios.get(`http://127.0.0.1:8000/api/transaksis/${id}`, {
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            });

            const data = response.data.data;
            Object.assign(transaksi, data.transaksi);
            transaksi.encrypted_id = id;
            // console.log(transaksi.encrypted_id);
            detail.value = data.detail;
        } catch (err) {
            console.error(err);
            error.value = 'Gagal mengambil data transaksi.';
        } finally {
            loading.value = false;
        }
    }

    function cetakPDF(){
        const url = `http://127.0.0.1:8000/api/export-pdf/${transaksi.encrypted_id}`;
        const target = '_blank_' + Date.now();
        window.open(url, target);
            // headers: {
            //     Authorization: `Bearer ${token}`,
            // },
    }

    watch(() => props.transaksiId, (newId) => {
        detailTransaksi(newId)
    }, {
        immediate: true
    })  
</script>