<template>
    <div>
        <Navbar />
    </div>

    <div class="p-10 bg-gray-200 min-h-screen mt-10">
        <h1 class="text-3xl ml-5 mt-5 uppercase flex font-bold  items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-10">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
            </svg>
            Pengguna
        </h1>
        <div class="grid grid-cols-2 gap-6 mt-5">
            <div class="ml-6 mt-2">
                <button 
                    @click="showModalCreate = true" class="flex items-center p-2 text-md font-medium tracking-wide text-white bg-blue-600 rounded-lg hover:bg-blue-500 transition w-28">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah
                </button>

                <!-- Modal create -->
                <CreateModalUser
                    v-if="showModalCreate" 
                    @close="showModalCreate = false" 
                    @updateData="getUser(page)"
                />

                <!-- Modal detail -->
                <!-- <DetailModalMatkul 
                    v-if="showModalDetail"
                    :matkulId = "selectedId"
                    @close="showModalDetail = false" 
                /> -->

                <!-- Modal edit -->
                <EditModalUser 
                    v-if="showModalEdit"
                    :userId = "selectedId"
                    @close="showModalEdit = false"
                    @updateData="getUser(page)"
                />

            </div>
            <form
                    class="md:flex hidden flex-row flex-wrap items-center lg:ml-auto mr-6">
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
        </div>

        <div v-if="loading" class="flex justify-center items-center">
            <div class="lds-roller">Memuat...<div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
        </div>

        <div class="overflow-auto rounded-lg shadow mt-3 container">
            <table v-if="users.data" class="table-auto w-full text-md text-gray-700 border border-gray-300">
                <thead class="bg-gray-50 border-b-2 border-gray-200">
                    <tr>
                        <th class="p-3 text-md font-semibold tracking-wide text-left">No</th>
                        <th class="p-3 text-md font-semibold tracking-wide text-left">Nama</th>
                        <th class="p-3 text-md font-semibold tracking-wide text-left">Email</th>
                        <th class="p-3 text-md font-semibold tracking-wide text-left">Nomor WA</th>
                        <th class="p-3 text-md font-semibold tracking-wide text-left">Role</th>
                        <th class="p-3 text-md font-semibold tracking-wide text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="!users.data || !users.data.data || users.data.data.length === 0">
                        <td colspan="8" class="p-3 text-center bg-white text-sm text-gray-700 whitespace-nowrap">
                            Data user tidak ada.
                        </td>
                    </tr>
                    <tr 
                        v-else 
                        v-for="(user, index) in users.data.data"
                        :key="index"
                        class="bg-white border-b odd:bg-gray-100 even:bg-white text-left">
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap">{{ index + users.data.from }}</td>
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap">{{ user.name }}</td>
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap">{{ user.email }}</td>
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap">{{ user.no_wa }}</td>
                            <td class="p-3 text-sm text-gray-700 whitespace-nowrap">
                                <span
                                    class="inline-block px-2 py-1 text-xs font-semibold text-white rounded-full"
                                    :class="user.role === 'admin' ? 'bg-pink-500' : 'bg-indigo-500'">
                                    {{ user.role == 'admin' ? 'admin' : 'super admin' }}
                                </span>
                            </td>
                            <td class="flex items-center mt-2 mb-2">
                                <button @click="editUser(user.id)" class="flex items-center p-2 px-3 mx-1 text-md font-medium tracking-wide text-white bg-cyan-500 rounded-lg hover:bg-cyan-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/>
                                    </svg>
                                    <span class="text-sm">Ubah</span>
                                </button>
                                <button 
                                    v-if="user.id !== authUserId"
                                    @click.prevent="destroy(user.id, index)" class="flex items-center p-2 px-3 mx-1 text-md font-medium tracking-wide text-white bg-red-600 rounded-lg hover:bg-red-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                    <span class="text-sm">Hapus</span>
                                </button>
                                <!-- <span v-else class="text-gray-500">Tidak dapat dihapus</span> -->
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
                @click="getUser(currentPage - 1)">
                << Previous
            </button>

            <button
                v-for="page in totalPages"
                :key="page"
                :class="[
                'px-4 py-2 rounded',
                currentPage === page ? 'bg-blue-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-400'
                ]"
                @click="getUser(page)">
                {{ page }}
            </button>

            <button
                class="px-3 py-2 bg-white text-gray-700 rounded hover:bg-gray-400 disabled:opacity-50"
                :disabled="currentPage === totalPages"
                @click="getUser(currentPage + 1)">
                Next >>
            </button>
        </div>
    </div>

</template>

<script setup>
import Navbar from '@/components/Navbar.vue';
import CreateModalUser from '@/components/CreateModalUser.vue';
import EditModalUser from '@/components/EditModalUser.vue';
import axios from 'axios';
import { onMounted, ref, computed, watch } from 'vue';
import swal from 'sweetalert';
// import { useAuthStore } from '@/stores/auth';

let users = ref([]);
let showModalCreate = ref(false);
const currentPage = ref(1);
const totalPages = ref(0);
const loading = ref(true);
const selectedId = ref(null);
const showModalEdit = ref(false);
const authUserId = ref(null);
const search = ref("");
// const authStore = useAuthStore();

onMounted( async() => {
    getUser();
    // await authStore.fetchUserPermissions();
})

    // const canCreate = computed(() => authStore.permissions?.['Manage User']?.create || false);
    // const canRead = computed(() => authStore.permissions?.['Manage User']?.read || false);
    // const canUpdate = computed(() => authStore.permissions?.['Manage User']?.update || false);
    // const canDelete = computed(() => authStore.permissions?.['Manage User']?.delete || false);

const getUser = async (page = 1) => {
    try {
            const token = localStorage.getItem('token');

            const response = await axios.get(`http://127.0.0.1:8000/api/user?page=${page}`, {
                params: { search: search.value, page },
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            });
            users.value.data = response.data.data
            authUserId.value = response.data.id
            currentPage.value = response.data.data.current_page
            totalPages.value = response.data.data.last_page;
        } catch (err) {
            console.error('Gagal mengambil data:', err.response ? err.response.data : err.message);
        } finally{
            loading.value = false
        }
}

watch(search, () => {
    getUser(1);
});

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

                        await axios.delete(`http://127.0.0.1:8000/api/user/${id}`, {
                            headers: {
                                Authorization: `Bearer ${token}`,
                            },
                        });

                        if (users.value && users.value.length > 0) {
                            users.value = users.value.filter((item) => item.id !== id);
                        } else {
                            getUser();
                        }

                        swal({
                            title: "Berhasil!",
                            text: "Data user berhasil dihapus",
                            icon: "success",
                            showConfirmButton: true,
                            timer: 2000
                        });
                    } catch (err) {
                        swal({
                            title: "Error!",
                            text: err.response?.data?.message || "Terjadi kesalahan pada server.",
                            icon: "error",
                            showConfirmButton: true,
                            timer: 2000
                        });
                    }
                } else {
                    swal({
                        title: "Dibatalkan",
                        icon: "info",
                        text: "Data user tidak jadi dihapus",
                        showConfirmButton: true,
                        timer: 1500
                    });
                }
            });
    };

function editUser(id){
    selectedId.value = id
    showModalEdit.value = true
}
</script>

<style>
    .lds-roller,
    .lds-roller div,
    .lds-roller div:after {
        box-sizing: border-box;
    }
    .lds-roller {
        display: inline-block;
        position: relative;
        width: 100vh;
        height: 100vh;
        align-items: center;
        z-index: 9999;
    }
    .lds-roller div {
        animation: lds-roller 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
        transform-origin: 40px 40px;
    }
    .lds-roller div:after {
        content: " ";
        display: block;
        position: absolute;
        width: 7.2px;
        height: 7.2px;
        border-radius: 50%;
        background: currentColor;
        margin: -3.6px 0 0 -3.6px;
    }
    .lds-roller div:nth-child(1) {
        animation-delay: -0.036s;
    }
    .lds-roller div:nth-child(1):after {
        top: 62.62742px;
        left: 62.62742px;
    }
    .lds-roller div:nth-child(2) {
        animation-delay: -0.072s;
    }
    .lds-roller div:nth-child(2):after {
        top: 67.71281px;
        left: 56px;
    }
    .lds-roller div:nth-child(3) {
        animation-delay: -0.108s;
    }
    .lds-roller div:nth-child(3):after {
        top: 70.90963px;
        left: 48.28221px;
    }
    .lds-roller div:nth-child(4) {
        animation-delay: -0.144s;
    }
    .lds-roller div:nth-child(4):after {
        top: 72px;
        left: 40px;
    }
    .lds-roller div:nth-child(5) {
        animation-delay: -0.18s;
    }
    .lds-roller div:nth-child(5):after {
        top: 70.90963px;
        left: 31.71779px;
    }
    .lds-roller div:nth-child(6) {
        animation-delay: -0.216s;
    }
    .lds-roller div:nth-child(6):after {
        top: 67.71281px;
        left: 24px;
    }
    .lds-roller div:nth-child(7) {
        animation-delay: -0.252s;
    }
    .lds-roller div:nth-child(7):after {
        top: 62.62742px;
        left: 17.37258px;
    }
    .lds-roller div:nth-child(8) {
        animation-delay: -0.288s;
    }
    .lds-roller div:nth-child(8):after {
        top: 56px;
        left: 12.28719px;
    }
    @keyframes lds-roller {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }
</style>