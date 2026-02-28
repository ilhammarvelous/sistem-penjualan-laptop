<template>
    <div class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
    <div class="relative bg-white z-50 rounded-lg shadow-xl w-full max-w-3xl overflow-hidden">
      <div class="flex items-center justify-between bg-blue-500 px-8 py-5">
        <h2 class="text-2xl font-bold text-white">Tambah Data Transaksi</h2>
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

      <form @submit.prevent="store()">
        <div class="grid grid-cols-2 gap-4 px-8 pt-6">
          <!-- ID transaksi -->
          <div>
            <label for="id_transaksi" class="block text-sm font-medium text-gray-700">ID Transaksi<span class="text-red-600">*</span></label>
            <input 
              type="text"
              id="id_transaksi"
              v-model="id_transaksi"
              class="mt-1 block w-full p-3 border bg-slate-200 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
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
                  v-model="selectedPelanggan">
                  <option value="" disabled selected>-- Pilih Nama --</option>
                  <option v-for="plgn in pelanggan" :key="plgn.id" :value="plgn.id">
                      {{ plgn.nama }}
                  </option>
              </select>
              <p v-if="validation.pelanggan_id" class="mt-1 text-sm text-red-500">{{ validation.pelanggan_id[0] }}</p>
          </div>
        </div>

        <div class="mb-4 pt-4 px-8">
          <div 
            class="grid grid-cols-3 gap-4 mb-3"
            v-for="(item, index) in items" 
            :key="index"
            >
            <!-- Nama barang -->
            <div>
                    <label for="barang" class="block text-sm font-medium text-gray-700">Barang<span class="text-red-600">*</span></label>
                    <select
                        class="mt-1 block w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        name="barang"
                        id="barang"
                        v-model="item.barang_id"
                        @change="updateItemDetail(index)"
                        >
                        <option value="" disabled selected>-- Pilih Barang --</option>
                        <option v-for="brg in barang" :key="brg.id" :value="brg.id">
                            {{ brg.nama_barang }}
                        </option>
                    </select>
                    <p v-if="validation.barang_id" class="mt-1 text-sm text-red-500">{{ validation.barang_id[0] }}</p>
            </div>

            <!-- harga -->
            <div>
              <label for="harga" class="block text-sm font-medium text-gray-700">Harga</label>
              <input 
                type="text"
                id="harga"
                class="mt-1 block w-full p-3 border bg-slate-200 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                :value="formatRupiah(item.harga)"
                readonly
              >
            </div>  

            <!-- quantity -->
            <div>
              <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity<span class="text-red-600">*</span></label>
              <div class="flex items-center gap-2">
                <input 
                  type="number"
                  id="quantity"
                  min="1"
                  class="mt-1 block w-40 p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  v-model="item.quantity"
                  >
                  <button 
                    v-if="index === 0"
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
              <p v-if="validation[`quantity_${index}`]" class="mt-1 text-sm text-red-500">
                {{ validation[`quantity_${index}`][0] }}
              </p>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-2 mb-4 gap-4 px-8 pt-1">
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
              v-model="tanggal"
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
            Submit
          </button>
        </div>
      </form>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, reactive, defineEmits, watch, nextTick } from 'vue'
import axios from 'axios'
import swal from 'sweetalert'

const pelanggan = ref([]);
const barang = ref([]);
const selectedPelanggan = ref("");
const selectedBarang = ref('');
const id_transaksi = ref('');
const hargaBarang = ref('');
const quantity = ref('');
const tanggal = ref('');
const selectedDetail = ref(null);
const emit = defineEmits(['close', 'updateData']);
const validation = reactive({})

const items = ref([
  {
    barang_id: '',
    harga: '',
    quantity: '1',
    detail: null,
  }
]);

onMounted(() => {
    generateKodeIdTransaksi();
    fetchPelanggan();
    fetchBarang();
});

function generateKodeIdTransaksi() {
    const token = localStorage.getItem('token')

    axios.get(`http://127.0.0.1:8000/api/generate-kode-transaksi`, {
        headers: {
            Authorization: `Bearer ${token}`,
        },
    })
    .then((response) => {
        id_transaksi.value = response.data.id_transaksi
    })
    .catch(() => {
        swal({
            title: 'Error!',
            text: 'Gagal generate id transaksi.',
            icon: 'error',
            timer: 2000
        })
    })
}

function addItem() {
  items.value.push({ barang_id: '', harga: '', quantity: '1', detail: null });
}


watch(
  items,
  (newItems) => {
    newItems.forEach((it, idx) => {
      const stok = it.detail?.stok ?? 0;
      if (parseInt(it.quantity) > stok) {
        validation[`quantity_${idx}`] = ['Jumlah kuantitas tidak mencukupi !!!'];
      } else {
        delete validation[`quantity_${idx}`];
      }
    });
  },
  { deep: true }
);

function updateItemDetail(index) {
  const sel = barang.value.find(b => b.id === items.value[index].barang_id) || {};
  items.value[index].detail = sel;
  items.value[index].harga = sel.harga || '';
}

function removeItem(index) {
  items.value.splice(index, 1);
}

const totalPembayaran = computed(() => {
  return items.value.reduce((total, item) => {
    const harga = parseInt(item.harga) || 0;
    const qty = parseInt(item.quantity) || 0;
    return total + harga * qty;
  }, 0);
});

watch(selectedBarang, (id) => {
  const selected = barang.value.find(b => b.id === id);
  if (selected) {
    hargaBarang.value = selected.harga;
  } else {
    hargaBarang.value = '';
  }
});

watch(selectedBarang, (val) => {
  const selected = barang.value.find(b => b.id === val);
  selectedDetail.value = selected;
});

const formatRupiah = (angka) => {
  if (!angka) return '';
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(angka);
}

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

  function store() {
    const data = {
      id_transaksi: id_transaksi.value,
      pelanggan_id: selectedPelanggan.value,
      tanggal: tanggal.value,
      items: items.value.map(item => ({
        barang_id: item.barang_id,
        harga: item.harga,
        quantity: item.quantity
      })),
      total_pembayaran: totalPembayaran.value,
    };

    const token = localStorage.getItem('token');
    axios
        .post(`http://127.0.0.1:8000/api/transaksis`, data, {
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

                id_transaksi.value = "";
                selectedPelanggan.value = "";
                selectedBarang.value = "";
                quantity.value = "";
                hargaBarang.value = "";
                tanggal.value = "";
                totalPembayaran.value = "";
                
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
            } else if (err.response && err.response.status === 400) {
                swal({
                  title: 'Stok Tidak Cukup!',
                  icon: 'error',
                  text: err.response.data.message,
                  showConfirmButton: true,
                  timer: 2000
                });
            } 
            else {
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