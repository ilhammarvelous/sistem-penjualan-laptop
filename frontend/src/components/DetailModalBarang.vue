<template>
    <div class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
        <div class="relative bg-white z-50 rounded-lg shadow-xl w-full max-w-2xl overflow-hidden">
            <div class="flex items-center justify-between bg-blue-500 px-8 py-5">
                <h2 class="text-2xl font-bold text-white">Detail Data Barang</h2>
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
            <div class="space-y-4 px-8 py-5">
                <div>
                    <p><strong>Kode barang</strong> <br> {{ barang.kode_barang }}</p>
                </div>
                <div>
                    <p><strong>Nama barang</strong> <br> {{ barang.nama_barang }}</p>
                </div>
                <div>
                    <p><strong>Harga</strong> <br> {{ formatRupiah(barang.harga) }}</p>
                </div>
                <!-- <div>
                    <p><strong>Stok</strong> <br> {{ barang.total_stok_keseluruhan }}</p>
                </div> -->
            </div>

            <div class="space-y-2 px-8 py-4">
                <h3 class="font-semibold text-lg mb-2">Riwayat Masuk dan Keluar Stok Barang</h3>
                <div class="flex gap-4">

                    <div class="w-full overflow-y-auto max-h-60">
                        <table class="w-full text-left border border-gray-900 table-fixed">
                            <thead class="bg-gray-300 sticky top-0">
                                <tr>
                                    <th class="p-2 border">Jumlah Stok Masuk</th>
                                    <th class="p-2 border">Tanggal Masuk Stok</th>
                                </tr>
                            </thead>
                                <tbody>
                                    <tr v-for="detail in barang.barang_details.filter(d => d.stok_masuk !== null && d.tanggal_masuk !== null)" :key="detail.id">
                                        <td class="p-2 border table-cell w-full">{{ detail.stok_masuk }}</td>
                                        <td class="p-2 border table-cell w-full">{{ detail.tanggal_masuk }}</td>
                                    </tr>
                                </tbody>
                        </table>
                    </div>

                    <div class="w-full overflow-y-auto max-h-60">
                        <table class="w-full text-left border border-gray-900  table-fixed">
                            <thead class="bg-gray-300 sticky top-0">
                                <tr>
                                    <th class="p-2 border">Jumlah Stok Keluar</th>
                                    <th class="p-2 border">Tanggal Keluar Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                                    <tr v-for="detail in barang.barang_details.filter(d => d.stok_keluar !== null && d.tanggal_keluar !== null)" :key="detail.id">
                                        <td class="p-2 border">{{ detail.stok_keluar }}</td>
                                        <td class="p-2 border">{{ detail.tanggal_keluar }}</td>
                                    </tr>
                            </tbody>
                        </table>
                    </div>

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
        barangId: Number
    })

    const router = useRouter();
    const barang = reactive({
        barang_details: []
    });
    const loading = ref(true);
    const error = ref(null);

    function formatRupiah(value) {
        if (!value) return '';
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(value);
    }

    function detailBarang(id){
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
            .get(`http://127.0.0.1:8000/api/barangs/${id}`, {
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            })
            .then((response) => {
                barang.kode_barang = response.data.data.kode_barang;
                barang.nama_barang = response.data.data.nama_barang;
                barang.harga = response.data.data.harga;
                // barang.stok = response.data.data.total_stok_keseluruhan;
                barang.barang_details = response.data.data.barang_details;
            })
            .catch((err) => {
                if (err.response && err.response.status === 404) {
                    error.value = 'Data barang tidak ditemukan!';
                } else {
                    error.value = 'Terjadi kesalahan pada server.';
                }
            })
            .finally(() => {
                loading.value = false;
            });
    }

            watch(() => props.barangId, (newId) => {
                detailBarang(newId)
            }, {
                immediate: true
            })  
</script>