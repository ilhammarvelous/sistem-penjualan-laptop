<template>
    <div class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
        <div class="relative bg-white z-50 rounded-lg shadow-xl w-full max-w-3xl overflow-hidden">
            <div class="flex items-center justify-between bg-blue-500 px-4 py-5">
                <h2 class="text-2xl font-bold text-white px-4">Edit Data Transaksi</h2>
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

            <form @submit.prevent="update()" class="">
                <div class="grid grid-cols-2 gap-4 px-8 pt-6">
                <!-- ID transaksi -->
                    <div>
                        <label for="id_transaksi" class="block text-sm font-medium text-gray-700">ID Transaksi<span class="text-red-600">*</span></label>
                        <input 
                        type="text"
                        id="id_transaksi"
                        v-model="transaksi.id_transaksi"
                        class="mt-1 block w-full bg-slate-200 p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        readonly
                        >
                        <p v-if="validation.id_transaksi" class="mt-1 text-sm text-red-500">{{ validation.id_transaksi[0] }}</p>
                    </div>

                    <!-- Nama pelanggan -->
                    <div>
                        <label for="mahasiswa" class="block text-sm font-medium text-gray-700">Pelanggan<span class="text-red-600">*</span></label>
                        <select
                            class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            name="mahasiswa"
                            id="mahasiswa"
                            v-model="selectedPelanggan" 
                            required>
                            <option value="" disabled selected>-- Pilih pelanggan --</option>
                            <option v-for="plgn in pelanggan" :key="plgn.id" :value="plgn.id">
                                {{ plgn.nama }}
                            </option>
                        </select>
                        <p v-if="validation.pelanggan_id" class="mt-1 text-sm text-red-500">{{ validation.pelanggan_id[0] }}</p>
                    </div>
                </div>

                <!-- detail barang -->
                <div class="mb-4 pt-2 px-8">
                    <div 
                        class="grid grid-cols-3 gap-4 mb-3"
                        v-for="(item, index) in transaksi.items" :key="index"
                        >
                        <div>
                                <label for="matkul" class="block text-sm font-medium text-gray-700">Barang<span class="text-red-600">*</span></label>
                                <select
                                    class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                    v-model="item.barang_id"
                                    @change="setHargaOtomatis(item)"
                                    >
                                    <option value="" disabled selected>-- pilih barang --</option>
                                    <option v-for="brg in barang" :key="brg.id" :value="brg.id">
                                        {{ brg.nama_barang }}
                                    </option>
                                </select>
                                <p v-if="validation.barang_id" class="mt-1 text-sm text-red-500">{{ validation.barang_id[0] }}</p>
                        </div>

                        <div>
                            <label for="harga" class="block text-sm font-medium text-gray-700">Harga</label>
                            <input 
                                type="text"
                                id="harga"
                                :value="formatRupiah(item.harga)"
                                class="mt-1 block w-full p-3 border bg-slate-200 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                readonly
                            >
                        </div>  

                        <!-- quantity -->
                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity<span class="text-red-600">*</span></label>
                            <div class="flex items-center gap-2">
                                <input 
                                type="number"
                                v-model="item.quantity"
                                id="quantity"
                                class="mt-1 block w-40 p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                >
                                <button 
                                    v-if="index == 0 "
                                    @click="addItem"
                                    type="button"
                                    class="bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded-lg shadow-sm transition"
                                >+</button>
                                <button 
                                    v-if="index !== 0 "
                                    @click="removeItem(index)"
                                    type="button"
                                    class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-lg shadow-sm transition"
                                >×</button>
                            </div>
                            <p v-if="validation.quantity" class="mt-1 text-sm text-red-500">{{ validation.quantity[0] }}</p>
                            <p v-if="validation[`quantity_${index}`]" class="mt-1 text-sm text-red-500">
                                {{ validation[`quantity_${index}`][0] }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 mb-4 gap-4 px-8">
                <!-- Total Pembayaran -->
                    <div>
                        <label for="pembayaran" class="block text-sm font-medium text-gray-700">Total Pembayaran</label>
                        <input 
                        type="text"
                        id="pembayaran"
                        class="mt-1 block w-full p-3 border bg-slate-200 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        :value="formatRupiah(totalPembayaran)"
                        readonly
                        >
                    </div>

                <!-- Tanggal -->
                    <div>
                        <label for="tanggal" class="block text-sm font-medium text-gray-700">Tanggal<span class="text-red-600">*</span></label>
                        <input 
                        type="date"
                        id="tanggal"
                        v-model="transaksi.tanggal"
                        class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        >
                        <p v-if="validation.tanggal" class="mt-1 text-sm text-red-500">{{ validation.tanggal[0] }}</p>
                    </div>
                </div>

                <!-- Tombol Submit -->
                <div class="col-span-2 px-8 py-3 mb-3">
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
    import { reactive, ref, watch, onMounted, computed } from 'vue'
    import axios from 'axios'
    import swal from 'sweetalert'
    import { useRouter } from 'vue-router';

    const router = useRouter()
    const barang = ref([]);
    const pelanggan = ref([]);
    const selectedPelanggan = ref("");
    const selectedMaktul = ref("");
    const loading = ref(true);
    const error = ref(null);
    const validation = reactive({})
    const emit = defineEmits(['close', 'updateData'])
    const props = defineProps({
        transaksiId: Number
    })
    const detail = ref([]);

    const transaksi = reactive({
        id_transaksi: '',
        pelanggan: '',
        tanggal: '',
        items: [],
        total_pembayaran: '',
    });

    const namaPelangganTerpilih = computed(() => {
        const plg = pelanggan.value.find(p => p.id === selectedPelanggan.value);
        return plg ? plg.nama : 'Pilih Pelanggan';
    });


    function calculateTotal() {
        transaksi.total_pembayaran = transaksi.items.reduce((total, item) => total + item.harga * item.quantity, 0);
    }

    function addItem() {
        transaksi.items.push({ barang_id: '', harga: '', quantity: '1', detail: null });
    }

    function removeItem(index) {
        transaksi.items.splice(index, 1);
        calculateTotal();
    }

    function setHargaOtomatis(item) {
        const selected = barang.value.find(mk => mk.id === item.barang_id);
        if (selected) {
            item.harga = selected.harga;
        }
    }


    const formatRupiah = (angka) => {
        if (!angka) return '';
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(angka);
    }

    const totalPembayaran = computed(() => {
        return transaksi.items.reduce((total, item) => {
            const harga = parseInt(item.harga) || 0;
            const qty = parseInt(item.quantity) || 0;
            return total + harga * qty;
        }, 0);
    });

    const fetchPelanggan = async () => {
        try {
            const token = localStorage.getItem('token');

            const response = await axios.get(`http://127.0.0.1:8000/api/select-pelanggan`, {
                headers: {
                    Authorization: `Bearer ${token}`
                },
            });
            pelanggan.value = response.data;
        } catch (err) {
            console.error('Gagal mengambil data:', err.response ? err.response.data : err.message);
        }
    }

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

    function fetchData(id) {
        const token = localStorage.getItem('token');
        if (!token) {
            swal('Error', 'Token tidak ditemukan. Silakan login ulang!', 'error');
            router.push('/');
            return;
        }

        axios.get(`http://127.0.0.1:8000/api/transaksis/${id}`, {
            headers: { Authorization: `Bearer ${token}` },
        })
        .then((res) => {
            // const transaksi = res.data.data.transaksi;
            // const detail = res.data.data.detail;

            transaksi.id_transaksi = res.data.data.transaksi.id_transaksi;
            transaksi.pelanggan = res.data.data.transaksi.pelanggan.nama;
            selectedPelanggan.value = res.data.data.transaksi.pelanggan_id;
            transaksi.tanggal = res.data.data.transaksi.tanggal;
            transaksi.items = res.data.data.detail.map(item => ({
                barang_id: item.barang_id,
                harga: item.harga,
                quantity: item.quantity,
            }));

            calculateTotal();
        })
        .catch(() => swal('Error', 'Gagal memuat data transaksi!', 'error'));
    }

    function detailTransaksi(id){
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
            const response = axios.get(`http://127.0.0.1:8000/api/transaksis/${id}`, {
            headers: {
                Authorization: `Bearer ${token}`,
            },
            });

            const data = response.data.data;
            Object.assign(transaksi, data.transaksi);
            detail.value = data.detail;
        } catch (err) {
            console.error(err);
            error.value = 'Gagal mengambil data transaksi.';
        } finally {
            loading.value = false;
        }
    }

    function update() {
            const token = localStorage.getItem('token');

            const items = transaksi.items.map(item => ({
                barang_id: item.barang_id,
                harga: item.harga,
                quantity: item.quantity
            }));

            const data = {
                // id_transaksi: transaksi.id_transaksi,
                pelanggan_id: selectedPelanggan.value,
                tanggal: transaksi.tanggal,
                total_pembayaran: totalPembayaran.value,
                items: items
            };

            axios.put(`http://127.0.0.1:8000/api/transaksis/${props.transaksiId}`, data, {
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
                emit('close')
                emit('updateData')
            })
            .catch((err) => {
                if (err.response && err.response.status === 422) {
                    Object.assign(validation, err.response.data.errors)
                    swal({
                        title: 'Gagal!',
                        text: 'Terjadi kesalahan saat memperbarui data.',
                        icon: 'error',
                        showConfirmButton: true,
                        timer: 2000
                    });
                } else {
                    swal('Error', 'Terjadi kesalahan pada server.', 'error');
                }
            })
    }

    onMounted(() => {
        fetchPelanggan();
        fetchBarang();
    })

        watch(() => props.transaksiId, (newId) => {
            if (newId) {
                fetchData(newId)
            } else {
                transaksi.pelanggan = ''
                transaksi.items = []
                transaksi.tanggal = ''
                transaksi.total_pembayaran = 0
                transaksi.id_transaksi = ''
            }
        }, { immediate: true })

        watch(() => props.transaksiId, (newId) => {
            detailTransaksi(newId)
        }, {
            immediate: true
        })  

        watch(() => transaksi.items, (items) => {
            items.forEach(setHargaOtomatis);
        }, { deep: true });


        watch(totalPembayaran, (newTotal) => {
            transaksi.total_pembayaran = newTotal;
        });
</script>

