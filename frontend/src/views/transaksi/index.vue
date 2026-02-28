<template>
    <div>
        <Navbar />
    </div>
    <div class="p-10 bg-gray-200 min-h-screen mt-10">
        <h1 class="text-3xl ml-5 mt-5 uppercase flex font-bold  items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-10">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
            </svg>
            Transaksi
        </h1>
        <div class="grid grid-cols-2 gap-6 mt-5">
            <div class="ml-6 mt-2">
                <button 
                    @click="showModalCreate = true"  class="flex items-center p-2 text-md font-medium tracking-wide text-white bg-blue-600 rounded-lg hover:bg-blue-500 transition w-28">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah
                </button>

                <!-- Modal create -->
                <CreateModalTransaksi 
                    v-if="showModalCreate" 
                    @close="showModalCreate = false" 
                    @updateData="fetchData(page)"
                />

                <!-- Modal edit -->
                <EditModalTransaksi
                    v-if="showModalEdit"
                    :transaksiId = "selectedId"
                    @close="showModalEdit = false"
                    @updateData="fetchData(page)"
                />

                <!-- Modal Detail -->
                <DetailModalTransaksi
                    v-if="showModalDetail"
                    :transaksiId = "selectedId"
                    @close="showModalDetail = false"
                />

                <!-- Modal pembayaran -->
                <PembayaranModal
                    v-if="pembayaranModal"
                    @close="pembayaranModal = false"
                    @updateData="fetchData(page)"
                />

            </div>
            <div class="grid grid-cols-2 ml-72">
                <form
                        class="md:flex hidden flex-row flex-wrap items-center lg:ml-auto">
                        <div class="relative flex w-full flex-wrap items-stretch">
                            <span
                                class="z-10 h-full leading-snug font-normaltext-center text-blueGray-300 absolute bg-transparent rounded text-base items-center justify-center w-8 pl-3 py-3">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 23" stroke-width="1.5" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                            </span>
                            <input
                                type="text"
                                v-model="search"
                                placeholder="Cari..."
                                class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600 relative bg-white rounded text-sm shadow outline-none focus:outline-none focus:ring w-full pl-10"
                            />
                        </div>
                </form>
                <button @click="pembayaranModal = true" class="flex items-center ml-auto mr-6 px-2 mx-1 text-md font-medium tracking-wide text-white bg-green-500 rounded-lg hover:bg-green-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <span class="text-sm">Pembayaran</span>
                </button>
            </div>
        </div>

        <div v-if="loading" class="flex justify-center items-center">
            <div class="lds-roller">Memuat...<div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
        </div>

        <div class="overflow-auto rounded-lg shadow mt-3 container">
            <table v-if="transaksis.data" class="table-auto w-full text-md text-gray-700 border border-gray-300">
                <thead class="bg-gray-50 border-b-2 border-gray-200">
                    <tr>
                        <th class="p-3 text-md font-semibold tracking-wide text-left">No</th>
                        <th class="p-3 text-md font-semibold tracking-wide text-left">ID Transaksi</th>
                        <th class="p-3 text-md font-semibold tracking-wide text-left">Pelanggan</th>
                        <th class="p-3 text-md font-semibold tracking-wide text-left">Tanggal</th>
                        <th class="p-3 text-md font-semibold tracking-wide text-left">Total Pembayaran</th>
                        <th class="p-3 text-md font-semibold tracking-wide text-left">Status</th>
                        <th class="p-3 text-md font-semibold tracking-wide text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="!transaksis.data || !transaksis.data.data || transaksis.data.data.length === 0">
                        <td colspan="10" class="p-3 text-center bg-white text-sm text-gray-700 whitespace-nowrap">
                            Data transaksi tidak ada.
                        </td>
                    </tr>
                    <tr v-else 
                        v-for="(transaksi, index) in transaksis.data.data"
                        :key="index"  
                        class="bg-white border-b odd:bg-gray-100 even:bg-white text-left">
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap">{{ index + transaksis.data.from }}</td>
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap">{{ transaksi.id_transaksi }}</td>
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap">{{ transaksi.pelanggan ?? 'Data hilang' }}</td>
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap">{{ transaksi.tanggal }}</td>
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap">{{ formatRupiah(transaksi.total_pembayaran) }}</td>
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap">
                                <span
                                    class="inline-block px-2 py-1 text-xs font-semibold text-white rounded-full"
                                    :class="transaksi.status === 'Lunas' ? 'bg-green-500' : 'bg-red-500'">
                                    {{ transaksi.status == 'Lunas' ? 'Lunas' : 'Belum Lunas' }}
                                </span>
                            </td>
                            <td class="flex items-center mt-2 mb-2">
                                <button @click="detailTransaksi(transaksi.id)" class="flex items-center p-2 px-3 mx-1 text-md font-medium tracking-wide text-white bg-blue-600 rounded-lg hover:bg-blue-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    <span class="text-sm">Detail</span>
                                </button>
                                <button @click="editTransaksi(transaksi.id)" class="flex items-center p-2 px-3 mx-1 text-md font-medium tracking-wide text-white bg-cyan-500 rounded-lg hover:bg-cyan-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                    <span class="text-sm">Ubah</span>
                                </button>
                                <button @click="destroy(transaksi.id)" class="flex items-center p-2 px-3 mx-1 text-md font-medium tracking-wide text-white bg-red-600 rounded-lg hover:bg-red-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                    <span class="text-sm">Hapus</span>
                                </button>
                            </td>
                        </tr>
                </tbody>
            </table>
        </div>

        <!-- Paginasi -->
        <div class="mt-3 flex justify-end mr-5 items-center space-x-1.5">
            <button
                class="px-3 py-2 bg-white text-gray-700 rounded hover:bg-gray-400 disabled:opacity-50"
                :disabled="currentPage === 1"
                @click="fetchData(currentPage - 1)">
                << Previous
            </button>

            <button
                v-for="page in totalPages"
                :key="page"
                :class="[
                'px-4 py-2 rounded',
                currentPage === page ? 'bg-blue-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-400'
                ]"
                @click="fetchData(page)">
                {{ page }}
            </button>

            <button
                class="px-3 py-2 bg-white text-gray-700 rounded hover:bg-gray-400 disabled:opacity-50"
                :disabled="currentPage === totalPages"
                @click="fetchData(currentPage + 1)"    >
                Next >>
            </button>
        </div>
    </div>

</template>

<script setup>
    import Navbar from '@/components/Navbar.vue';
    import CreateModalTransaksi from '@/components/CreateModalTransaksi.vue';
    import EditModalTransaksi from '@/components/EditModalTransaksi.vue';
    import DetailModalTransaksi from '@/components/DetailModalTransaksi.vue';
    import PembayaranModal from '@/components/PembayaranModal.vue';
    import { onMounted, ref, computed, watch } from 'vue';
    import axios from 'axios';
    import swal from 'sweetalert';
    // import { useAuthStore } from '@/stores/auth';

    let showModalCreate = ref(false);
    const transaksis = ref([]);
    const showModalEdit = ref(false);
    const showModalDetail = ref(false);
    const pembayaranModal = ref(false);
    const selectedId = ref(null);
    const currentPage = ref(1);
    const totalPages = ref(0);
    const loading = ref(true);
    const search = ref("");
    // const authStore = useAuthStore();

    const formatRupiah = (angka) => {
        if (!angka) return '';
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(angka);
    }

    const fetchData = async (page = 1) => {
        try {
            const token = localStorage.getItem('token');

            const response = await axios.get(`http://127.0.0.1:8000/api/transaksis?page=${page}`, {
                params: { search: search.value, page },
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            });
            transaksis.value.data = response.data.data;
            currentPage.value = response.data.data.current_page
            totalPages.value = response.data.data.last_page;
        } catch (err) {
            swal('Error', 'Gagal mengambil data', 'error');
        } finally{
            loading.value = false
        }
    }

    watch(search, () => {
        fetchData(1);
    });

    onMounted( async() => {
        fetchData();
        // await authStore.fetchUserPermissions();
    });

    // const canCreate = computed(() => authStore.permissions?.['Transaksi']?.create || false);
    // const canRead = computed(() => authStore.permissions?.['Transaksi']?.read || false);
    // const canUpdate = computed(() => authStore.permissions?.['Transaksi']?.update || false);
    // const canDelete = computed(() => authStore.permissions?.['Transaksi']?.delete || false);


    const destroy = async (id) => {
            swal({
                title: "Apakah Anda yakin?",
                text: "Setelah dihapus, Anda tidak akan dapat memulihkan data ini lagi!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then(async (willDelete) => {
                if (willDelete) {
                    try {
                        const token = localStorage.getItem("token");

                        if (!token) {
                            swal("Gagal!", "Anda harus login terlebih dahulu!", "error");
                            return;
                        }

                        await axios.delete(`http://127.0.0.1:8000/api/transaksis/${id}`, {
                            headers: {
                                Authorization: `Bearer ${token}`,
                            },
                        });

                        if (transaksis.value && transaksis.value.length > 0) {
                            transaksis.value = transaksis.value.filter((item) => item.id !== id);
                        } else {
                            fetchData();
                        }

                        swal({
                            title: "Berhasil!",
                            text: "Berhasil menghapus data transaksi",
                            icon: "success",
                            showConfirmButton: true,
                            timer: 1500
                        });
                    } catch (err) {
                        swal("Error!", err.response?.data?.message || "Terjadi kesalahan pada server.", "error");
                    }
                } else {
                    swal({
                        title: "Dibatalkan",
                        icon: "info",
                        text: "Data transaksi tidak jadi dihapus",
                        showConfirmButton: true,
                        timer: 1500
                    });
                }
            });
    };

    function editTransaksi(id){
        selectedId.value = id
        showModalEdit.value = true
    }

    function detailTransaksi(id){
        selectedId.value = id
        showModalDetail.value = true
    }

</script>